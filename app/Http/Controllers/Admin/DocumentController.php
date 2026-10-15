<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Document;
use App\Models\DocumentCategory;
use App\Support\Catalog;
use App\Support\Uploads;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Manajemen arsip dokumen (khusus Admin). Penelusuran ada di Panel\DocumentController.
 */
class DocumentController extends Controller
{
    private const MIMES = 'pdf,doc,docx,xls,xlsx,ppt,pptx,odt,ods,txt,csv,jpg,jpeg,png,webp,zip,rar';

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request, true);
        $file = $request->file('file');
        $path = Uploads::privateFile($file, 'archives');

        $doc = Document::query()->create($data + [
            'code' => Document::generateCode(),
            'file_path' => $path,
            'original_name' => mb_substr($file->getClientOriginalName(), 0, 200),
            'mime' => $file->getMimeType(),
            'size' => $file->getSize(),
            'sha256' => hash_file('sha256', $file->getRealPath()),
            'uploaded_by' => $request->user()->id,
        ]);
        AuditLog::record('document_uploaded', 'Mengunggah arsip: '.$doc->title.' ('.$doc->code.')', $doc);

        return back()->with('success', 'Dokumen diarsipkan dengan kode '.$doc->code);
    }

    public function update(Request $request, Document $document): RedirectResponse
    {
        $data = $this->validated($request, false);
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = Uploads::privateFile($file, 'archives');
            Storage::disk('local')->delete($document->file_path);
            $data += [
                'file_path' => $path,
                'original_name' => mb_substr($file->getClientOriginalName(), 0, 200),
                'mime' => $file->getMimeType(),
                'size' => $file->getSize(),
                'sha256' => hash_file('sha256', $file->getRealPath()),
            ];
        }
        $document->update($data);
        AuditLog::record('document_updated', 'Memperbarui arsip: '.$document->title, $document);

        return back()->with('success', 'Dokumen diperbarui.');
    }

    public function destroy(Document $document): RedirectResponse
    {
        AuditLog::record('document_deleted', 'Menghapus arsip: '.$document->title.' ('.$document->code.')', $document);
        Storage::disk('local')->delete($document->file_path);
        $document->forceDelete();

        return back()->with('success', 'Dokumen dihapus permanen.');
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        $data = $this->validatedCategory($request);
        DocumentCategory::query()->create($data + ['slug' => $this->slug($data['name'])]);

        return back()->with('success', 'Kategori ditambahkan.');
    }

    public function updateCategory(Request $request, DocumentCategory $category): RedirectResponse
    {
        $category->update($this->validatedCategory($request));

        return back()->with('success', 'Kategori diperbarui.');
    }

    public function destroyCategory(DocumentCategory $category): RedirectResponse
    {
        $category->documents()->update(['category_id' => null]);
        $category->delete();

        return back()->with('success', 'Kategori dihapus. Dokumen di dalamnya menjadi tanpa kategori.');
    }

    private function validated(Request $request, bool $creating): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'document_number' => ['nullable', 'string', 'max:120'],
            'category_id' => ['nullable', 'integer', 'exists:document_categories,id'],
            'description' => ['nullable', 'string', 'max:2000'],
            'document_date' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date'],
            'confidentiality' => ['required', Rule::in(array_keys(Catalog::CONFIDENTIALITY))],
            'tags' => ['nullable', 'array', 'max:15'],
            'tags.*' => ['string', 'max:60'],
            'file' => [$creating ? 'required' : 'nullable', 'file', 'max:51200', 'mimes:'.self::MIMES],
        ], [], ['title' => 'judul', 'file' => 'berkas', 'confidentiality' => 'tingkat akses']);

        $data['tags'] = array_values(array_unique(array_filter(array_map(fn ($t) => Str::limit(trim($t), 60, ''), $data['tags'] ?? []))));
        unset($data['file']);

        return $data;
    }

    private function validatedCategory(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'color' => ['required', Rule::in(array_merge(Catalog::COLORS, ['slate']))],
            'description' => ['nullable', 'string', 'max:255'],
        ]);
    }

    private function slug(string $name): string
    {
        $base = Str::slug($name) ?: 'kategori';
        $slug = $base;
        $i = 2;
        while (DocumentCategory::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
