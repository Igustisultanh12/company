<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\UserNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NotificationController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('Panel/Notifications/Index', [
            'notifications' => UserNotification::query()->where('user_id', $request->user()->id)->latest()->paginate(20),
        ]);
    }

    public function latest(Request $request): JsonResponse
    {
        $q = UserNotification::query()->where('user_id', $request->user()->id);

        return response()->json([
            'unread' => (clone $q)->whereNull('read_at')->count(),
            'items' => $q->latest()->limit(8)->get(['id', 'title', 'body', 'url', 'level', 'read_at', 'created_at']),
        ]);
    }

    public function read(Request $request, UserNotification $notification): RedirectResponse
    {
        abort_unless($notification->user_id === $request->user()->id, 404);
        $notification->update(['read_at' => $notification->read_at ?? now()]);

        $url = $notification->url;
        $host = parse_url((string) $url, PHP_URL_HOST);

        return $url && (! $host || $host === $request->getHost()) ? redirect($url) : back();
    }

    public function readAll(Request $request): RedirectResponse
    {
        UserNotification::query()->where('user_id', $request->user()->id)->whereNull('read_at')->update(['read_at' => now()]);

        return back()->with('success', 'Semua notifikasi ditandai sudah dibaca.');
    }
}
