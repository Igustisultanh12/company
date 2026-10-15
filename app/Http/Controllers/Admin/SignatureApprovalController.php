<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Setting;
use App\Models\SignatureRequest;
use App\Models\UserNotification;
use App\Services\TteService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class SignatureApprovalController extends Controller
{
    public function __construct(private readonly TteService $tte) {}

    public function approve(Request $request, SignatureRequest $signatureRequest): RedirectResponse
    {
        abort_unless($signatureRequest->status === 'pending', 422, 'Dokumen ini tidak dalam status menunggu.');
        $data = $request->validate([
            'pin' => ['required', 'string', 'max:12'],
            'note' => ['nullable', 'string', 'max:500'],
            'placements' => ['nullable', 'string', 'max:20000'],
        ]);
        $this->checkPin($request, $data['pin']);

        if (! empty($data['placements'])) {
            $placements = json_decode($data['placements'], true);
            if (is_array($placements) && $placements) {
                $clean = [];
                foreach ($placements as $p) {
                    $page = (int) ($p['page'] ?? 0);
                    if ($page >= 1 && $page <= $signatureRequest->page_count) {
                        $clean[] = [
                            'page' => $page,
                            'x' => round(max(0, min(0.98, (float) ($p['x'] ?? 0))), 5),
                            'y' => round(max(0, min(0.98, (float) ($p['y'] ?? 0))), 5),
                            'w' => round(max(0.05, min(0.6, (float) ($p['w'] ?? 0.16))), 5),
                        ];
                    }
                }
                if ($clean) {
                    $signatureRequest->placements = $clean;
                }
            }
        }

        $user = $request->user();
        try {
            $this->tte->sign($signatureRequest, [
                'name' => (string) Setting::get('tte_signer_name', $user->name),
                'position' => (string) Setting::get('tte_signer_position', $user->position ?? ''),
                'date' => now(),
            ]);
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'Gagal menandatangani dokumen: '.$e->getMessage());
        }

        $signatureRequest->forceFill([
            'status' => 'approved',
            'signer_id' => $user->id,
            'note' => $data['note'] ?? null,
            'rejected_at' => null,
        ])->save();

        AuditLog::record('tte_approved', 'Mengesahkan TTE '.$signatureRequest->code.' – '.$signatureRequest->title, $signatureRequest, ['sha256' => $signatureRequest->signed_sha256]);
        UserNotification::send($signatureRequest->user_id, 'Dokumen kamu telah disahkan ✅', $signatureRequest->title, route('panel.tte.show', $signatureRequest), 'success');

        return back()->with('success', 'Dokumen berhasil ditandatangani secara elektronik.');
    }

    public function reject(Request $request, SignatureRequest $signatureRequest): RedirectResponse
    {
        abort_unless($signatureRequest->status === 'pending', 422);
        $data = $request->validate(['note' => ['required', 'string', 'max:500']], [], ['note' => 'alasan']);

        $signatureRequest->forceFill(['status' => 'rejected', 'note' => $data['note'], 'rejected_at' => now(), 'signer_id' => $request->user()->id])->save();
        AuditLog::record('tte_rejected', 'Menolak TTE '.$signatureRequest->code, $signatureRequest);
        UserNotification::send($signatureRequest->user_id, 'Pengajuan TTE perlu revisi', $signatureRequest->title.' – '.$data['note'], route('panel.tte.show', $signatureRequest), 'warning');

        return back()->with('success', 'Pengajuan dikembalikan untuk revisi.');
    }

    public function revoke(Request $request, SignatureRequest $signatureRequest): RedirectResponse
    {
        abort_unless($signatureRequest->status === 'approved', 422);
        $data = $request->validate([
            'pin' => ['required', 'string', 'max:12'],
            'reason' => ['required', 'string', 'max:255'],
        ], [], ['reason' => 'alasan pencabutan']);
        $this->checkPin($request, $data['pin']);

        $signatureRequest->forceFill(['status' => 'revoked', 'revoked_at' => now(), 'revoke_reason' => $data['reason']])->save();
        AuditLog::record('tte_revoked', 'Mencabut TTE '.$signatureRequest->code.': '.$data['reason'], $signatureRequest);
        UserNotification::send($signatureRequest->user_id, 'TTE dokumen dicabut', $signatureRequest->title.' – '.$data['reason'], route('panel.tte.show', $signatureRequest), 'danger');

        return back()->with('success', 'Tanda tangan elektronik dicabut. Halaman verifikasi kini menampilkan status DICABUT.');
    }

    private function checkPin(Request $request, string $pin): void
    {
        $user = $request->user();
        if (empty($user->tte_pin)) {
            throw ValidationException::withMessages(['pin' => 'Anda belum membuat PIN TTE. Buat di menu Pengaturan TTE.']);
        }

        $key = 'tte-pin:'.$user->id;
        $max = config('security.tte_pin_attempts', 5);
        if (RateLimiter::tooManyAttempts($key, $max)) {
            throw ValidationException::withMessages(['pin' => 'PIN terkunci sementara. Coba lagi dalam '.ceil(RateLimiter::availableIn($key) / 60).' menit.']);
        }
        if (! Hash::check($pin, $user->tte_pin)) {
            RateLimiter::hit($key, 900);
            AuditLog::record('tte_pin_failed', 'PIN TTE salah');
            throw ValidationException::withMessages(['pin' => 'PIN TTE salah.']);
        }
        RateLimiter::clear($key);
    }
}
