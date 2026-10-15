<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\SignatureRequest;
use App\Support\Uploads;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Halaman publik verifikasi keaslian dokumen ber-TTE (tujuan QR Code).
 */
class VerifyController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Site/Verify', [
            'meta' => ['title' => 'Verifikasi Dokumen – '.Setting::get('company_name'), 'description' => 'Cek keaslian dokumen bertanda tangan elektronik PT Kreatif Sky Abadi.'],
            'document' => null,
            'searched' => null,
            'fileCheck' => session('file_check'),
        ]);
    }

    public function lookup(Request $request): RedirectResponse
    {
        $data = $request->validate(['code' => ['required', 'string', 'max:40']], [], ['code' => 'kode dokumen']);

        return redirect()->route('verify.show', strtoupper(trim($data['code'])));
    }

    public function show(Request $request, string $code): Response
    {
        $doc = SignatureRequest::query()->where('code', $code)->whereIn('status', ['approved', 'revoked'])->with('user:id,name')->first();

        if ($doc) {
            $seen = (array) $request->session()->get('verified_docs', []);
            if (! in_array($doc->id, $seen, true)) {
                $doc->increment('verify_count');
                $request->session()->push('verified_docs', $doc->id);
            }
        }

        return Inertia::render('Site/Verify', [
            'meta' => ['title' => 'Verifikasi '.$code, 'description' => 'Hasil verifikasi dokumen elektronik.'],
            'searched' => $code,
            'document' => $doc ? $this->present($doc) : null,
            'fileCheck' => session('file_check'),
        ]);
    }

    public function checkFile(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:pdf', 'max:20480'],
            'code' => ['nullable', 'string', 'max:40'],
        ], [], ['file' => 'berkas PDF']);

        $file = $request->file('file');
        if (! Uploads::isRealPdf($file)) {
            return back()->withErrors(['file' => 'Berkas bukan PDF yang valid.']);
        }

        $hash = hash_file('sha256', $file->getRealPath());
        $doc = SignatureRequest::query()->where('signed_sha256', $hash)->whereIn('status', ['approved', 'revoked'])->first();

        $result = [
            'hash' => $hash,
            'name' => mb_substr($file->getClientOriginalName(), 0, 120),
            'match' => (bool) $doc,
            'valid' => $doc?->status === 'approved',
            'code' => $doc?->code,
        ];

        $code = $doc?->code ?? trim((string) $request->input('code', ''));

        return $code !== ''
            ? redirect()->route('verify.show', $code)->with('file_check', $result)
            : redirect()->route('verify.index')->with('file_check', $result);
    }

    private function present(SignatureRequest $doc): array
    {
        return [
            'code' => $doc->code,
            'title' => $doc->title,
            'document_number' => $doc->document_number,
            'status' => $doc->status,
            'signer_name' => $doc->signer_name,
            'signer_position' => $doc->signer_position,
            'signed_at' => $doc->signed_at?->toIso8601String(),
            'revoked_at' => $doc->revoked_at?->toIso8601String(),
            'revoke_reason' => $doc->revoke_reason,
            'requested_by' => $doc->user?->name,
            'page_count' => $doc->page_count,
            'hash' => $doc->signed_sha256,
            'verify_count' => $doc->verify_count,
            'company' => Setting::get('company_name'),
        ];
    }
}
