<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LeaveFileController extends Controller
{
    public function __invoke(Request $request, LeaveRequest $leave): StreamedResponse
    {
        $user = $request->user();
        abort_unless($user->isAdmin() || $leave->user_id === $user->id, 403);
        abort_unless($leave->attachment_path && Storage::disk('local')->exists($leave->attachment_path), 404);

        return Storage::disk('local')->response($leave->attachment_path, 'lampiran-'.$leave->id.'.'.pathinfo($leave->attachment_path, PATHINFO_EXTENSION), [
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ], 'inline');
    }
}
