<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Post;
use App\Models\Service;
use App\Models\User;
use App\Models\UserNotification;
use App\Support\Catalog;
use App\Support\CmsRegistry;
use App\Support\Uploads;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class CmsController extends Controller
{
    public function index(Request $request, string $resource): Response
    {
        $cfg = $this->config($resource);
        $q = trim((string) $request->query('q', ''));

        $items = $cfg['model']::query()
            ->when(! empty($cfg['with']), fn ($w) => $w->with($cfg['with']))
            ->when($q !== '', function ($w) use ($cfg, $q) {
                $w->where(function ($x) use ($cfg, $q) {
                    foreach ($cfg['search'] as $col) {
                        $x->orWhere($col, 'like', '%'.$q.'%');
                    }
                });
            })
            ->orderBy($cfg['order'][0], $cfg['order'][1])->orderByDesc('id')
            ->paginate(20)->withQueryString();

        if (! empty($cfg['public_route'])) {
            $items->getCollection()->each(fn ($m) => $m->setAttribute('public_url', $m->slug ? route($cfg['public_route'], $m->slug) : null));
        }

        return Inertia::render('Admin/Cms/Resource', [
            'resource' => $resource,
            'schema' => [
                'title' => $cfg['title'],
                'singular' => $cfg['singular'],
                'description' => $cfg['description'] ?? null,
                'columns' => $cfg['columns'],
                'fields' => collect($cfg['fields'])->map(fn ($f) => collect($f)->except(['rules', 'item_rules'])->all())->values(),
                'defaults' => $cfg['defaults'] ?? [],
                'toggle' => array_key_exists('toggle', $cfg) ? $cfg['toggle'] : 'is_active',
            ],
            'options' => $this->options($cfg),
            'items' => $items,
            'filters' => ['q' => $q],
            'menu' => CmsRegistry::menu(),
            'colors' => Catalog::COLORS,
            'icons' => Catalog::ICONS,
        ]);
    }

    public function store(Request $request, string $resource): RedirectResponse
    {
        $cfg = $this->config($resource);
        /** @var Model $model */
        $model = new $cfg['model'];
        $data = $this->validated($request, $cfg, null);
        $this->fillImages($request, $cfg, $model, $data);
        if (! empty($cfg['set_user'])) {
            $data[$cfg['set_user']] = $request->user()->id;
        }
        $model->fill($data)->save();
        AuditLog::record('cms_created', 'Menambah '.$cfg['singular'].': '.$this->label($model), $model);

        if (! empty($cfg['notify']) && $model->getAttribute('is_active')) {
            UserNotification::send(
                User::query()->where('is_active', true)->whereKeyNot($request->user()->id)->pluck('id')->all(),
                '📢 '.$model->getAttribute('title'),
                Str::limit((string) $model->getAttribute('body'), 140),
                route('panel.announcements.index'),
                (string) $model->getAttribute('level')
            );
        }

        return back()->with('success', ucfirst($cfg['singular']).' berhasil ditambahkan.');
    }

    public function update(Request $request, string $resource, int $id): RedirectResponse
    {
        $cfg = $this->config($resource);
        $model = $cfg['model']::query()->findOrFail($id);
        $data = $this->validated($request, $cfg, $model);
        $this->fillImages($request, $cfg, $model, $data);
        $model->fill($data)->save();
        AuditLog::record('cms_updated', 'Mengubah '.$cfg['singular'].': '.$this->label($model), $model);

        return back()->with('success', ucfirst($cfg['singular']).' diperbarui.');
    }

    public function toggle(string $resource, int $id): RedirectResponse
    {
        $cfg = $this->config($resource);
        $field = array_key_exists('toggle', $cfg) ? $cfg['toggle'] : 'is_active';
        abort_unless($field, 404);
        $model = $cfg['model']::query()->findOrFail($id);
        $model->setAttribute($field, ! $model->getAttribute($field))->save();

        return back()->with('success', 'Status tampil diperbarui.');
    }

    public function destroy(string $resource, int $id): RedirectResponse
    {
        $cfg = $this->config($resource);
        $model = $cfg['model']::query()->findOrFail($id);
        foreach ($cfg['fields'] as $f) {
            if ($f['type'] === 'image') {
                Uploads::deletePublic($model->getAttribute($f['name']));
            }
        }
        AuditLog::record('cms_deleted', 'Menghapus '.$cfg['singular'].': '.$this->label($model), $model);
        $model->delete();

        return back()->with('success', ucfirst($cfg['singular']).' dihapus.');
    }

    private function config(string $resource): array
    {
        $cfg = CmsRegistry::get($resource);
        abort_unless($cfg, 404);

        return $cfg;
    }

    private function options(array $cfg): array
    {
        $out = [];
        foreach ($cfg['fields'] as $f) {
            if (($f['options_from'] ?? null) === 'services') {
                $out[$f['name']] = Service::query()->orderBy('sort_order')->pluck('title', 'id');
            }
        }

        return $out;
    }

    private function validated(Request $request, array $cfg, ?Model $model): array
    {
        $rules = [];
        $table = (new $cfg['model'])->getTable();
        foreach ($cfg['fields'] as $f) {
            if ($f['type'] === 'image') {
                $rules[$f['name']] = ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'];
                $rules['remove_'.$f['name']] = ['nullable', 'boolean'];

                continue;
            }
            $r = $f['rules'] ?? ['nullable'];
            if ($f['name'] === 'slug') {
                $r[] = Rule::unique($table, 'slug')->ignore($model?->getKey());
            }
            $rules[$f['name']] = $r;
            if (! empty($f['item_rules'])) {
                $rules[$f['name'].'.*'] = $f['item_rules'];
            }
        }

        $data = $request->validate($rules, [], collect($cfg['fields'])->mapWithKeys(fn ($f) => [$f['name'] => Str::lower($f['label'])])->all());

        foreach ($cfg['fields'] as $f) {
            $name = $f['name'];
            if ($f['type'] === 'image') {
                unset($data[$name], $data['remove_'.$name]);

                continue;
            }
            if ($f['type'] === 'switch') {
                $data[$name] = $request->boolean($name);
            }
            if ($f['type'] === 'list') {
                $data[$name] = array_values(array_filter(array_map('trim', (array) ($data[$name] ?? [])), fn ($v) => $v !== ''));
            }
            if (in_array($f['type'], ['number', 'money'], true) && array_key_exists($name, $data) && $data[$name] === null && in_array('required', $f['rules'] ?? [], true) === false) {
                $data[$name] = in_array($name, ['sort_order'], true) ? 0 : null;
            }
        }

        if (! empty($cfg['slug_from'])) {
            $data['slug'] = ! empty($data['slug']) ? Str::slug($data['slug']) : $this->uniqueSlug($table, (string) ($data[$cfg['slug_from']] ?? 'item'), $model?->getKey());
        }

        if ($cfg['model'] === Post::class && ($data['status'] ?? null) === 'published' && empty($data['published_at'])) {
            $data['published_at'] = $model?->getAttribute('published_at') ?? now();
        }

        return $data;
    }

    private function fillImages(Request $request, array $cfg, Model $model, array &$data): void
    {
        foreach ($cfg['fields'] as $f) {
            if ($f['type'] !== 'image') {
                continue;
            }
            $name = $f['name'];
            if ($request->hasFile($name)) {
                try {
                    $path = Uploads::publicImage($request->file($name), $cfg['model'] === Post::class ? 'posts' : Str::plural(Str::snake(class_basename($cfg['model']))));
                } catch (\RuntimeException $e) {
                    throw ValidationException::withMessages([$name => $e->getMessage()]);
                }
                Uploads::deletePublic($model->getAttribute($name));
                $data[$name] = $path;
            } elseif ($request->boolean('remove_'.$name)) {
                Uploads::deletePublic($model->getAttribute($name));
                $data[$name] = null;
            }
        }
    }

    private function uniqueSlug(string $table, string $source, ?int $ignoreId): string
    {
        $base = Str::slug($source) ?: 'item';
        $slug = $base;
        $i = 2;
        while (DB::table($table)->where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }

    private function label(Model $m): string
    {
        return (string) ($m->getAttribute('title') ?? $m->getAttribute('name') ?? $m->getAttribute('question') ?? '#'.$m->getKey());
    }
}
