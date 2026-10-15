<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Models\UserNotification;
use App\Support\SpamGuard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ContactController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        SpamGuard::check($request);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email:rfc', 'max:150'],
            'phone' => ['nullable', 'string', 'max:30', 'regex:/^[0-9+\-\s()]{8,30}$/'],
            'subject' => ['nullable', 'string', 'max:150'],
            'message' => ['required', 'string', 'min:10', 'max:3000'],
        ], [], ['name' => 'nama', 'message' => 'pesan', 'phone' => 'nomor telepon', 'subject' => 'subjek']);

        $msg = ContactMessage::query()->create($data + [
            'ip_address' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 490, ''),
        ]);

        UserNotification::toAdmins('Pesan baru dari '.$msg->name, Str::limit($msg->message, 120), route('admin.messages.index'), 'info');

        return back()->with('success', 'Pesan terkirim! Tim kami akan segera membalas melalui email / WhatsApp. 🙌');
    }
}
