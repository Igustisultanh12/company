<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Setting;
use App\Models\SignatureRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SignatureSettingController extends Controller
{
    public function edit(Request $request): Response
    {
        return Inertia::render('Admin/Tte/Settings', [
            'settings' => [
                'tte_signer_name' => Setting::get('tte_signer_name', ''),
                'tte_signer_position' => Setting::get('tte_signer_position', ''),
                'tte_caption_enabled' => Setting::bool('tte_caption_enabled', true),
                'tte_caption_text' => Setting::get('tte_caption_text', 'Ditandatangani secara elektronik'),
                'tte_qr_color' => Setting::get('tte_qr_color', '#111827'),
            ],
            'stampUrl' => Setting::get('tte_stamp_path') ? route('admin.tte.settings.stamp').'?v='.substr(md5((string) Setting::get('tte_stamp_path')), 0, 8) : null,
            'hasPin' => ! empty($request->user()->tte_pin),
            'stats' => [
                'approved' => SignatureRequest::query()->where('status', 'approved')->count(),
                'pending' => SignatureRequest::query()->where('status', 'pending')->count(),
                'revoked' => SignatureRequest::query()->where('status', 'revoked')->count(),
                'verifications' => (int) SignatureRequest::query()->sum('verify_count'),
            ],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'tte_signer_name' => ['required', 'string', 'max:120'],
            'tte_signer_position' => ['required', 'string', 'max:150'],
            'tte_caption_enabled' => ['boolean'],
            'tte_caption_text' => ['required', 'string', 'max:80'],
            'tte_qr_color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'stamp' => ['nullable', 'image', 'mimes:png', 'max:2048'],
            'remove_stamp' => ['boolean'],
        ], [], ['stamp' => 'gambar stempel']);

        if ($request->hasFile('stamp')) {
            $path = $this->storeStamp($request->file('stamp')->getRealPath());
            Storage::disk('local')->delete((string) Setting::get('tte_stamp_path', ''));
            Setting::put('tte_stamp_path', $path);
        } elseif ($request->boolean('remove_stamp')) {
            Storage::disk('local')->delete((string) Setting::get('tte_stamp_path', ''));
            Setting::put('tte_stamp_path', '');
        }

        Setting::putMany([
            'tte_signer_name' => $data['tte_signer_name'],
            'tte_signer_position' => $data['tte_signer_position'],
            'tte_caption_enabled' => $request->boolean('tte_caption_enabled'),
            'tte_caption_text' => $data['tte_caption_text'],
            'tte_qr_color' => $data['tte_qr_color'],
        ]);
        AuditLog::record('tte_settings_updated', 'Memperbarui pengaturan TTE');

        return back()->with('success', 'Pengaturan TTE disimpan.');
    }

    public function pin(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'password' => ['required', 'current_password'],
            'pin' => ['required', 'digits:6', 'confirmed'],
        ], ['pin.digits' => 'PIN harus 6 digit angka.'], ['password' => 'password akun']);

        if (preg_match('/^(\d)\1{5}$/', $data['pin']) || in_array($data['pin'], ['123456', '654321', '012345'], true)) {
            throw ValidationException::withMessages(['pin' => 'PIN terlalu mudah ditebak.']);
        }

        $request->user()->forceFill(['tte_pin' => Hash::make($data['pin'])])->save();
        AuditLog::record('tte_pin_changed', 'Mengatur PIN TTE');

        return back()->with('success', 'PIN TTE disimpan. Jangan bagikan PIN kepada siapa pun.');
    }

    public function stamp(): StreamedResponse
    {
        $path = (string) Setting::get('tte_stamp_path', '');
        abort_unless($path !== '' && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->response($path, 'stempel.png', ['Content-Type' => 'image/png', 'Cache-Control' => 'private, max-age=300']);
    }

    /** Encode ulang PNG (dengan transparansi) agar bersih dari metadata. */
    private function storeStamp(string $tmp): string
    {
        $img = @imagecreatefrompng($tmp);
        if (! $img) {
            throw ValidationException::withMessages(['stamp' => 'Gambar stempel harus PNG yang valid.']);
        }
        imagealphablending($img, false);
        imagesavealpha($img, true);
        ob_start();
        imagepng($img, null, 9);
        $bin = ob_get_clean();
        imagedestroy($img);

        $path = 'tte/stamp/'.Str::random(32).'.png';
        Storage::disk('local')->put($path, $bin);

        return $path;
    }
}
