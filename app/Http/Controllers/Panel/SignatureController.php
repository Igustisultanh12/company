<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Setting;
use App\Models\SignatureRequest;
use App\Models\UserNotification;
use App\Services\TteService;
use App\Support\Catalog;
use App\Support\Uploads;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class SignatureController extends Controller
{
    public function __construct(private readonly TteService $tte) {}

    public function index(Request $request): Response
    {
        $user = $request->user();
        $status = (string) $request->query('status', '');
        $q = trim((string) $request->query('q', ''));

        $base = SignatureRequest::query()->when(! $user->isAdmin(), fn ($w) => $w->where('user_id', $user->id));

        $items = (clone $base)->with(['user:id,name,avatar_path', 'signer:id,name'])
            ->when($status !== '', fn ($w) => $w->where('status', $status))
            ->when($q !== '', fn ($w) => $w->where(fn ($x) => $x->where('title', 'like', "%{$q}%")->orWhere('code', 'like', "%{$q}%")->orWhere('document_number', 'like', "%{$q}%")))
            ->latest()->paginate(12)->withQueryString();

        $counts = (clone $base)->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        return Inertia::render('Panel/Tte/Index', [
            'items' => $items,
            'counts' => $counts,
            'filters' => ['status' => $status, 'q' => $q],
            'statuses' => Catalog::TTE_STATUSES,
            'isAdmin' => $user->isAdmin(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Panel/Tte/Form', ['config' => $this->config(), 'item' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateRequest($request, true);
        $file = $request->file('file');
        $path = Uploads::privateFile($file, 'tte/original');

        try {
            $pages = $this->tte->pageCount(Storage::disk('local')->path($path));
        } catch (\Throwable $e) {
            Storage::disk('local')->delete($path);
            throw ValidationException::withMessages(['file' => 'PDF tidak dapat diproses: '.$e->getMessage()]);
        }

        $placements = $this->cleanPlacements($data['placements'], $pages);

        $req = SignatureRequest::query()->create([
            'code' => SignatureRequest::generateCode(),
            'user_id' => $request->user()->id,
            'title' => $data['title'],
            'document_number' => $data['document_number'] ?? null,
            'purpose' => $data['purpose'] ?? null,
            'original_path' => $path,
            'original_name' => mb_substr($file->getClientOriginalName(), 0, 200),
            'original_sha256' => hash_file('sha256', $file->getRealPath()),
            'page_count' => $pages,
            'placements' => $placements,
            'include_stamp' => (bool) ($data['include_stamp'] ?? false),
            'status' => 'pending',
        ]);

        AuditLog::record('tte_requested', 'Mengajukan TTE: '.$req->title, $req);
        UserNotification::toAdmins('Pengajuan TTE baru', $req->title.' • oleh '.$request->user()->name, route('panel.tte.show', $req), 'warning');

        return redirect()->route('panel.tte.show', $req)->with('success', 'Pengajuan TTE terkirim & menunggu pengesahan.');
    }

    public function show(Request $request, SignatureRequest $signatureRequest): Response
    {
        $this->authorizeView($request, $signatureRequest);
        $signatureRequest->load(['user:id,name,position,avatar_path', 'signer:id,name']);
        $user = $request->user();

        return Inertia::render('Panel/Tte/Show', [
            'item' => $signatureRequest,
            'urls' => [
                'original' => route('panel.tte.file', [$signatureRequest, 'original']),
                'preview' => route('panel.tte.file', [$signatureRequest, 'preview']),
                'signed' => $signatureRequest->signed_path ? route('panel.tte.file', [$signatureRequest, 'signed']) : null,
                'verify' => route('verify.show', $signatureRequest->code),
            ],
            'can' => [
                'approve' => $user->isAdmin() && $signatureRequest->status === 'pending',
                'reject' => $user->isAdmin() && $signatureRequest->status === 'pending',
                'revoke' => $user->isAdmin() && $signatureRequest->status === 'approved',
                'revise' => $signatureRequest->user_id === $user->id && in_array($signatureRequest->status, ['pending', 'rejected'], true),
                'delete' => $signatureRequest->status !== 'approved' && ($user->isAdmin() || ($signatureRequest->user_id === $user->id && $signatureRequest->status !== 'revoked')),
            ],
            'hasPin' => ! empty($user->tte_pin),
            'config' => $this->config(),
        ]);
    }

    public function edit(Request $request, SignatureRequest $signatureRequest): Response
    {
        $this->authorizeRevise($request, $signatureRequest);

        return Inertia::render('Panel/Tte/Form', [
            'config' => $this->config(),
            'item' => $signatureRequest->only(['id', 'code', 'title', 'document_number', 'purpose', 'placements', 'include_stamp', 'status', 'note', 'page_count']),
            'originalUrl' => route('panel.tte.file', [$signatureRequest, 'original']),
        ]);
    }

    public function update(Request $request, SignatureRequest $signatureRequest): RedirectResponse
    {
        $this->authorizeRevise($request, $signatureRequest);
        $data = $this->validateRequest($request, false);

        $pages = $signatureRequest->page_count;
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = Uploads::privateFile($file, 'tte/original');
            try {
                $pages = $this->tte->pageCount(Storage::disk('local')->path($path));
            } catch (\Throwable $e) {
                Storage::disk('local')->delete($path);
                throw ValidationException::withMessages(['file' => 'PDF tidak dapat diproses: '.$e->getMessage()]);
            }
            Storage::disk('local')->delete($signatureRequest->original_path);
            $signatureRequest->forceFill([
                'original_path' => $path,
                'original_name' => mb_substr($file->getClientOriginalName(), 0, 200),
                'original_sha256' => hash_file('sha256', $file->getRealPath()),
                'page_count' => $pages,
            ]);
        }

        $signatureRequest->fill([
            'title' => $data['title'],
            'document_number' => $data['document_number'] ?? null,
            'purpose' => $data['purpose'] ?? null,
            'placements' => $this->cleanPlacements($data['placements'], $pages),
            'include_stamp' => (bool) ($data['include_stamp'] ?? false),
        ]);
        $wasRejected = $signatureRequest->status === 'rejected';
        $signatureRequest->forceFill(['status' => 'pending', 'rejected_at' => null])->save();

        AuditLog::record('tte_revised', 'Merevisi pengajuan TTE: '.$signatureRequest->title, $signatureRequest);
        if ($wasRejected) {
            UserNotification::toAdmins('Pengajuan TTE direvisi', $signatureRequest->title.' • oleh '.$request->user()->name, route('panel.tte.show', $signatureRequest), 'warning');
        }

        return redirect()->route('panel.tte.show', $signatureRequest)->with('success', 'Pengajuan diperbarui & dikirim ulang.');
    }

    public function destroy(Request $request, SignatureRequest $signatureRequest): RedirectResponse
    {
        $user = $request->user();
        $allowed = $signatureRequest->status !== 'approved'
            && ($user->isAdmin() || ($signatureRequest->user_id === $user->id && $signatureRequest->status !== 'revoked'));
        abort_unless($allowed, 403, 'Dokumen yang sudah sah tidak dapat dihapus. Gunakan fitur "Cabut".');

        Storage::disk('local')->delete(array_filter([$signatureRequest->original_path, $signatureRequest->signed_path]));
        AuditLog::record('tte_deleted', 'Menghapus pengajuan TTE: '.$signatureRequest->title.' ('.$signatureRequest->code.')', $signatureRequest);
        $signatureRequest->delete();

        return redirect()->route('panel.tte.index')->with('success', 'Pengajuan TTE dihapus.');
    }

    public function file(Request $request, SignatureRequest $signatureRequest, string $variant): HttpResponse
    {
        $this->authorizeView($request, $signatureRequest);
        $filename = Uploads::safeDownloadName(pathinfo($signatureRequest->original_name, PATHINFO_FILENAME).'.pdf');
        $headers = ['X-Content-Type-Options' => 'nosniff', 'Cache-Control' => 'private, no-store'];

        if ($variant === 'preview') {
            try {
                $binary = $this->tte->render($signatureRequest, [
                    'name' => (string) Setting::get('tte_signer_name', $request->user()->name),
                    'position' => (string) Setting::get('tte_signer_position', ''),
                    'date' => now(),
                ], true);
            } catch (\Throwable $e) {
                abort(422, 'Pratinjau gagal dibuat: '.$e->getMessage());
            }

            return response($binary, 200, $headers + [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="pratinjau-'.$filename.'"',
            ]);
        }

        $path = $variant === 'signed' ? $signatureRequest->signed_path : $signatureRequest->original_path;
        abort_unless($path && Storage::disk('local')->exists($path), 404);

        if ($variant === 'signed') {
            AuditLog::record('tte_downloaded', 'Mengunduh dokumen TTE '.$signatureRequest->code, $signatureRequest);
        }

        $prefix = $variant === 'signed' ? 'TTE-' : '';

        return Storage::disk('local')->response($path, $prefix.$filename, $headers + ['Content-Type' => 'application/pdf'], $request->boolean('download') ? 'attachment' : 'inline');
    }

    private function config(): array
    {
        return [
            'caption_enabled' => Setting::bool('tte_caption_enabled', true),
            'caption_ratio' => TteService::CAPTION_RATIO,
            'caption_text' => Setting::get('tte_caption_text', 'Ditandatangani secara elektronik'),
            'signer_name' => Setting::get('tte_signer_name'),
            'signer_position' => Setting::get('tte_signer_position'),
            'stamp_available' => (string) Setting::get('tte_stamp_path', '') !== '',
            'max_mb' => 20,
        ];
    }

    private function validateRequest(Request $request, bool $fileRequired): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'document_number' => ['nullable', 'string', 'max:120'],
            'purpose' => ['nullable', 'string', 'max:1000'],
            'file' => [$fileRequired ? 'required' : 'nullable', 'file', 'mimes:pdf', 'max:20480'],
            'placements' => ['required', 'string', 'max:20000'],
            'include_stamp' => ['boolean'],
        ], [], ['title' => 'perihal / judul', 'file' => 'berkas PDF', 'placements' => 'posisi QR']);

        if ($request->hasFile('file') && ! Uploads::isRealPdf($request->file('file'))) {
            throw ValidationException::withMessages(['file' => 'Berkas bukan PDF yang valid.']);
        }

        $placements = json_decode($data['placements'], true);
        if (! is_array($placements) || count($placements) < 1 || count($placements) > 50) {
            throw ValidationException::withMessages(['placements' => 'Tentukan minimal satu posisi QR TTE pada dokumen.']);
        }
        $data['placements'] = $placements;

        return $data;
    }

    private function cleanPlacements(array $placements, int $pages): array
    {
        $clean = [];
        foreach ($placements as $p) {
            if (! is_array($p)) {
                continue;
            }
            $page = (int) ($p['page'] ?? 0);
            if ($page < 1 || $page > $pages) {
                continue;
            }
            $clean[] = [
                'page' => $page,
                'x' => round(max(0, min(0.98, (float) ($p['x'] ?? 0))), 5),
                'y' => round(max(0, min(0.98, (float) ($p['y'] ?? 0))), 5),
                'w' => round(max(0.05, min(0.6, (float) ($p['w'] ?? 0.16))), 5),
            ];
        }
        if (! $clean) {
            throw ValidationException::withMessages(['placements' => 'Posisi QR tidak valid untuk halaman dokumen ini.']);
        }

        return $clean;
    }

    private function authorizeView(Request $request, SignatureRequest $req): void
    {
        $user = $request->user();
        abort_unless($user->isAdmin() || $req->user_id === $user->id, 403);
    }

    private function authorizeRevise(Request $request, SignatureRequest $req): void
    {
        abort_unless($req->user_id === $request->user()->id && in_array($req->status, ['pending', 'rejected'], true), 403);
    }
}
