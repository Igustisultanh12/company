<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\LeaveRequest;
use App\Models\UserNotification;
use App\Support\Catalog;
use App\Support\Uploads;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class LeaveController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('Agent/Leaves/Index', [
            'leaves' => LeaveRequest::query()->where('user_id', $user->id)->with('reviewer:id,name')->latest()->paginate(12),
            'usage' => collect(Catalog::LEAVE_TYPES)->keys()->mapWithKeys(fn ($t) => [
                $t => (int) LeaveRequest::query()->where('user_id', $user->id)->where('type', $t)->where('status', 'approved')->whereYear('start_date', now()->year)->sum('days'),
            ]),
            'types' => Catalog::LEAVE_TYPES,
            'statuses' => Catalog::REQUEST_STATUSES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validate([
            'type' => ['required', Rule::in(array_keys(Catalog::LEAVE_TYPES))],
            'start_date' => ['required', 'date', 'after_or_equal:'.now()->subDays(7)->toDateString()],
            'end_date' => ['required', 'date', 'after_or_equal:start_date', 'before_or_equal:'.now()->addYear()->toDateString()],
            'reason' => ['required', 'string', 'min:5', 'max:1000'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:4096'],
        ], [], ['type' => 'jenis', 'start_date' => 'tanggal mulai', 'end_date' => 'tanggal selesai', 'reason' => 'alasan', 'attachment' => 'lampiran']);

        $overlap = LeaveRequest::query()->where('user_id', $user->id)->whereIn('status', ['pending', 'approved'])
            ->whereDate('start_date', '<=', $data['end_date'])->whereDate('end_date', '>=', $data['start_date'])->exists();
        if ($overlap) {
            return back()->withErrors(['start_date' => 'Tanggal bertabrakan dengan pengajuan lain.']);
        }

        $days = Carbon::parse($data['start_date'])->diffInDays(Carbon::parse($data['end_date'])) + 1;
        $leave = LeaveRequest::query()->create([
            'user_id' => $user->id,
            'type' => $data['type'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'days' => (int) $days,
            'reason' => $data['reason'],
            'attachment_path' => $request->hasFile('attachment') ? Uploads::privateFile($request->file('attachment'), 'leaves') : null,
            'status' => 'pending',
        ]);
        AuditLog::record('leave_requested', 'Mengajukan '.Catalog::LEAVE_TYPES[$leave->type].' '.$leave->days.' hari', $leave);
        UserNotification::toAdmins('Pengajuan '.strtolower(Catalog::LEAVE_TYPES[$leave->type]).' dari '.$user->name, $leave->start_date->format('d M').' – '.$leave->end_date->format('d M Y').' ('.$leave->days.' hari)', route('admin.leaves.index'), 'warning');

        return back()->with('success', 'Pengajuan terkirim, menunggu persetujuan admin.');
    }

    public function destroy(Request $request, LeaveRequest $leave): RedirectResponse
    {
        abort_unless($leave->user_id === $request->user()->id && $leave->status === 'pending', 403);
        Uploads::deletePrivate($leave->attachment_path);
        $leave->delete();

        return back()->with('success', 'Pengajuan dibatalkan.');
    }
}
