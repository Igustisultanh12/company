<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Document;
use App\Models\DocumentCategory;
use App\Support\Catalog;
use App\Support\Uploads;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'category' => $request->integer('category') ?: null,
            'confidentiality' => (string) $request->query('confidentiality', ''),
            'year' => $request->integer('year') ?: null,
            'expiring' => $request->boolean('expiring'),
        ];

        $documents = Document::query()->visibleTo($user)
            ->with(['category:id,name,color', 'uploader:id,name'])
            ->when($filters['q'] !== '', fn ($w) => $w->where(fn ($x) => $x
                ->where('title', 'like', '%'.$filters['q'].'%')
                ->orWhere('document_number', 'like', '%'.$filters['q'].'%')
                ->orWhere('code', 'like', '%'.$filters['q'].'%')
                ->orWhere('description', 'like', '%'.$filters['q'].'%')))
            ->when($filters['category'], fn ($w) => $w->where('category_id', $filters['category']))
            ->when($filters['confidentiality'] !== '' && $user->isAdmin(), fn ($w) => $w->where('confidentiality', $filters['confidentiality']))
            ->when($filters['year'], fn ($w) => $w->whereYear('document_date', $filters['year']))
            ->when($filters['expiring'], fn ($w) => $w->whereNotNull('expires_at')->whereDate('expires_at', '<=', now()->addDays(60)))
            ->latest()
            ->paginate(15)->withQueryString();

        $categories = DocumentCategory::query()
            ->withCount(['documents' => fn ($w) => $w->visibleTo($user)])
            ->orderBy('name')->get();

        $base = Document::query()->visibleTo($user);

        return Inertia::render('Panel/Documents/Index', [
            'documents' => $documents,
            'categories' => $categories,
            'filters' => $filters,
            'canManage' => $user->isAdmin(),
            'confidentiality' => Catalog::CONFIDENTIALITY,
            'complianceOptions' => collect(config('company.compliance'))->map(fn ($c) => ['key' => $c['key'], 'title' => $c['title']])->values(),
            'stats' => [
                'total' => (clone $base)->count(),
                'size' => (int) (clone $base)->sum('size'),
                'expiring' => (clone $base)->whereNotNull('expires_at')->whereDate('expires_at', '<=', now()->addDays(60))->count(),
                'this_month' => (clone $base)->where('created_at', '>=', now()->startOfMonth())->count(),
            ],
        ]);
    }

    public function file(Request $request, Document $document, string $mode = 'view'): StreamedResponse
    {
        abort_unless($document->canBeAccessedBy($request->user()), 403);
        abort_unless(Storage::disk('local')->exists($document->file_path), 404, 'Berkas tidak ditemukan.');

        $inline = $mode === 'view' && $document->is_previewable;
        if (! $inline) {
            $document->increment('download_count');
        }
        AuditLog::record($inline ? 'document_viewed' : 'document_downloaded', $document->title.' ('.$document->code.')', $document);

        $name = Uploads::safeDownloadName($document->original_name);

        return Storage::disk('local')->response($document->file_path, $name, [
            'Content-Type' => $document->mime ?: 'application/octet-stream',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ], $inline ? 'inline' : 'attachment');
    }
}
