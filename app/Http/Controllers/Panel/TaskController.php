<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Task;
use App\Models\User;
use App\Models\UserNotification;
use App\Support\Catalog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class TaskController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $assignee = $request->integer('assignee') ?: null;
        $q = trim((string) $request->query('q', ''));

        $tasks = $this->scope($user)
            ->with(['assignee:id,name,avatar_path', 'order:id,code,client_name'])
            ->withCount('comments')
            ->when($user->isAdmin() && $assignee, fn ($w) => $w->where('assigned_to', $assignee))
            ->when($q !== '', fn ($w) => $w->where('title', 'like', "%{$q}%"))
            ->where(fn ($w) => $w->where('status', '!=', 'done')->orWhere('completed_at', '>=', now()->subDays(30)))
            ->orderBy('position')->orderByDesc('id')
            ->limit(400)
            ->get();

        return Inertia::render('Panel/Tasks/Index', [
            'tasks' => $tasks,
            'users' => $user->isAdmin()
                ? User::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'role', 'avatar_path'])
                : [],
            'orders' => Order::query()->whereNotIn('status', ['completed', 'cancelled'])
                ->when(! $user->isAdmin(), fn ($w) => $w->where(fn ($x) => $x->where('assigned_to', $user->id)->orWhere('referral_agent_id', $user->id)))
                ->latest()->limit(100)->get(['id', 'code', 'client_name', 'status', 'website_type']),
            'filters' => ['assignee' => $assignee, 'q' => $q],
            'statuses' => Catalog::TASK_STATUSES,
            'priorities' => Catalog::TASK_PRIORITIES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        $data = $this->validated($request);
        $data['created_by'] = $user->id;
        $data['assigned_to'] = $user->isAdmin() ? ($data['assigned_to'] ?? null) : $user->id;
        $data['status'] = $data['status'] ?? 'todo';
        $data['position'] = (int) Task::query()->where('status', $data['status'])->max('position') + 1;

        $task = Task::query()->create($data);
        if ($task->assigned_to && $task->assigned_to !== $user->id) {
            UserNotification::send($task->assigned_to, 'Tugas baru: '.$task->title, 'Dari '.$user->name.($task->due_date ? ' • tenggat '.$task->due_date->format('d M Y') : ''), route('panel.tasks.index'), 'info');
        }

        return back()->with('success', 'Tugas ditambahkan.');
    }

    public function show(Request $request, Task $task): JsonResponse
    {
        $this->authorizeAccess($request->user(), $task);
        $task->load(['assignee:id,name,avatar_path', 'creator:id,name', 'order:id,code,client_name', 'comments.user:id,name,avatar_path']);

        return response()->json(['task' => $task]);
    }

    public function update(Request $request, Task $task): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user->isAdmin() || $task->created_by === $user->id, 403);
        $data = $this->validated($request);
        if (! $user->isAdmin()) {
            unset($data['assigned_to']);
        }
        $previousAssignee = $task->assigned_to;
        $this->applyStatus($task, $data['status'] ?? $task->status);
        unset($data['status']);
        $task->fill($data)->save();

        if ($task->assigned_to && $task->assigned_to !== $previousAssignee && $task->assigned_to !== $user->id) {
            UserNotification::send($task->assigned_to, 'Tugas ditugaskan ke kamu: '.$task->title, 'Dari '.$user->name, route('panel.tasks.index'));
        }

        return back()->with('success', 'Tugas diperbarui.');
    }

    public function move(Request $request, Task $task): RedirectResponse
    {
        $user = $request->user();
        $this->authorizeAccess($user, $task);
        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(Catalog::TASK_STATUSES))],
            'order' => ['nullable', 'array', 'max:400'],
            'order.*' => ['integer'],
        ]);

        $old = $task->status;
        $this->applyStatus($task, $data['status']);
        $task->save();

        if (! empty($data['order'])) {
            $ids = $this->scope($user)->whereIn('id', $data['order'])->pluck('id')->all();
            foreach (array_values(array_intersect($data['order'], $ids)) as $i => $id) {
                Task::query()->whereKey($id)->update(['position' => $i]);
            }
        }

        if ($old !== $task->status && $task->created_by && $task->created_by !== $user->id) {
            UserNotification::send($task->created_by, 'Status tugas berubah: '.$task->title, $user->name.' memindahkan ke "'.(Catalog::TASK_STATUSES[$task->status]['label'] ?? $task->status).'"', route('panel.tasks.index'));
        }

        return back();
    }

    public function destroy(Request $request, Task $task): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user->isAdmin() || $task->created_by === $user->id, 403);
        $task->delete();

        return back()->with('success', 'Tugas dihapus.');
    }

    public function comment(Request $request, Task $task): JsonResponse
    {
        $user = $request->user();
        $this->authorizeAccess($user, $task);
        $data = $request->validate(['body' => ['required', 'string', 'max:2000']]);
        $comment = $task->comments()->create(['user_id' => $user->id, 'body' => $data['body']]);

        $notify = collect([$task->assigned_to, $task->created_by])->filter(fn ($id) => $id && $id !== $user->id)->unique()->all();
        UserNotification::send($notify, 'Komentar baru di tugas "'.$task->title.'"', $user->name.': '.$data['body'], route('panel.tasks.index'));

        return response()->json(['comment' => $comment->load('user:id,name,avatar_path')]);
    }

    private function applyStatus(Task $task, string $status): void
    {
        if ($status === 'done' && $task->status !== 'done') {
            $task->completed_at = now();
        } elseif ($status !== 'done') {
            $task->completed_at = null;
        }
        $task->status = $status;
    }

    private function scope(User $user): Builder
    {
        return Task::query()->when(! $user->isAdmin(), fn ($w) => $w->where(fn ($x) => $x->where('assigned_to', $user->id)->orWhere('created_by', $user->id)));
    }

    private function authorizeAccess(User $user, Task $task): void
    {
        abort_unless($user->isAdmin() || $task->assigned_to === $user->id || $task->created_by === $user->id, 403);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:5000'],
            'order_id' => ['nullable', 'integer', 'exists:orders,id'],
            'assigned_to' => ['nullable', 'integer', Rule::exists('users', 'id')->where('is_active', true)],
            'status' => ['nullable', Rule::in(array_keys(Catalog::TASK_STATUSES))],
            'priority' => ['required', Rule::in(array_keys(Catalog::TASK_PRIORITIES))],
            'due_date' => ['nullable', 'date'],
        ], [], ['title' => 'judul', 'priority' => 'prioritas', 'due_date' => 'tenggat']);
    }
}
