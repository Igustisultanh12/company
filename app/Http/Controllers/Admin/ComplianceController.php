<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Site\PageController;
use App\Models\Document;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Legalitas & kepatuhan perizinan (kewajiban dari Sertifikat Standar OSS & SPPL).
 */
class ComplianceController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Compliance/Index', [
            'legal' => PageController::legal(),
            'items' => self::items(),
            'legalDocuments' => Document::query()
                ->whereHas('category', fn ($q) => $q->where('slug', 'legalitas-perusahaan'))
                ->latest()->get(['id', 'code', 'title', 'document_number', 'document_date', 'expires_at', 'original_name', 'size']),
        ]);
    }

    public static function items(): array
    {
        $docs = Document::query()->whereNotNull('tags')->get(['id', 'code', 'title', 'tags', 'document_date', 'created_at']);

        return collect(config('company.compliance'))->map(function ($item) use ($docs) {
            $tag = 'compliance:'.$item['key'];
            $matched = $docs->filter(fn ($d) => in_array($tag, (array) $d->tags, true))
                ->sortByDesc(fn ($d) => ($d->document_date ?? $d->created_at)?->timestamp)->values();
            $latest = $matched->first();
            $lastDate = $latest ? ($latest->document_date ?? $latest->created_at) : null;
            $nextDue = ($item['period_months'] && $lastDate) ? $lastDate->copy()->addMonths($item['period_months']) : null;

            $status = ! $latest ? 'missing' : ($nextDue && $nextDue->isPast() ? 'overdue' : ($nextDue && $nextDue->lte(now()->addDays(30)) ? 'due_soon' : 'ok'));

            return $item + [
                'tag' => $tag,
                'status' => $status,
                'last_date' => $lastDate?->toDateString(),
                'next_due' => $nextDue?->toDateString(),
                'documents' => $matched->take(5)->map(fn ($d) => ['id' => $d->id, 'code' => $d->code, 'title' => $d->title])->values(),
            ];
        })->values()->all();
    }

    public static function summary(): array
    {
        $items = self::items();

        return [
            'total' => count($items),
            'ok' => count(array_filter($items, fn ($i) => $i['status'] === 'ok')),
            'attention' => array_values(array_filter($items, fn ($i) => $i['status'] !== 'ok')),
        ];
    }
}
