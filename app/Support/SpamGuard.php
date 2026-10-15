<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Anti-spam ringan untuk formulir publik: honeypot + waktu isi minimum.
 */
class SpamGuard
{
    public static function check(Request $request, int $minSeconds = 3): void
    {
        $honeypot = (string) $request->input('website', '');
        $startedAt = (int) $request->input('_started', 0);

        $tooFast = $startedAt > 0 && (now()->getTimestampMs() - $startedAt) < $minSeconds * 1000;

        if ($honeypot !== '' || $tooFast) {
            throw ValidationException::withMessages([
                'form' => 'Permintaan terdeteksi sebagai spam. Silakan coba lagi dengan normal.',
            ]);
        }
    }
}
