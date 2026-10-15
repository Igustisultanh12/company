<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MessageController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = ['q' => trim((string) $request->query('q', '')), 'unread' => $request->boolean('unread')];

        return Inertia::render('Admin/Messages/Index', [
            'messages' => ContactMessage::query()
                ->when($filters['q'] !== '', fn ($w) => $w->where(fn ($x) => $x->where('name', 'like', '%'.$filters['q'].'%')->orWhere('email', 'like', '%'.$filters['q'].'%')->orWhere('subject', 'like', '%'.$filters['q'].'%')))
                ->when($filters['unread'], fn ($w) => $w->where('is_read', false))
                ->latest()->paginate(15)->withQueryString(),
            'filters' => $filters,
            'unread' => ContactMessage::query()->where('is_read', false)->count(),
        ]);
    }

    public function read(ContactMessage $message): RedirectResponse
    {
        $message->update(['is_read' => ! $message->is_read]);

        return back();
    }

    public function destroy(ContactMessage $message): RedirectResponse
    {
        $message->delete();

        return back()->with('success', 'Pesan dihapus.');
    }
}
