<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Models\Package;
use App\Models\Portfolio;
use App\Models\Post;
use App\Models\Service;
use App\Models\Setting;
use App\Models\TeamMember;
use App\Models\Testimonial;
use App\Support\Markdown;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class PageController extends Controller
{
    public function home(): Response
    {
        return Inertia::render('Site/Home', [
            'meta' => $this->meta(),
            'services' => Service::query()->active()->limit(6)->get(),
            'packages' => Package::query()->active()->get(),
            'portfolios' => Portfolio::query()->active()->limit(6)->get(),
            'testimonials' => Testimonial::query()->active()->limit(9)->get(),
            'faqs' => Faq::query()->active()->limit(6)->get(),
            'posts' => Post::query()->published()->limit(3)->get(['id', 'title', 'slug', 'excerpt', 'cover_path', 'category', 'published_at', 'body']),
            'stats' => $this->stats(),
            'legal' => $this->legal(),
        ]);
    }

    public function about(): Response
    {
        return Inertia::render('Site/About', [
            'meta' => $this->meta('Tentang Kami', Setting::get('company_description')),
            'profile' => [
                'description' => Setting::get('company_description'),
                'vision' => Setting::get('company_vision'),
                'mission' => array_values(array_filter(array_map('trim', explode("\n", (string) Setting::get('company_mission', ''))))),
                'founder_name' => Setting::get('founder_name'),
                'founder_position' => Setting::get('founder_position'),
                'maps_embed_url' => $this->safeMapsUrl(),
            ],
            'team' => TeamMember::query()->active()->get(),
            'stats' => $this->stats(),
            'legal' => $this->legal(),
        ]);
    }

    public function services(): Response
    {
        return Inertia::render('Site/Services/Index', [
            'meta' => $this->meta('Layanan', 'Layanan pembuatan website, sistem informasi, portal web, UI/UX, maintenance & konsultasi TI dari PT Kreatif Sky Abadi.'),
            'services' => Service::query()->active()->get(),
            'packages' => Package::query()->active()->get(),
        ]);
    }

    public function service(Service $service): Response
    {
        abort_unless($service->is_active, 404);

        return Inertia::render('Site/Services/Show', [
            'meta' => $this->meta($service->title, $service->excerpt),
            'service' => $service,
            'packages' => Package::query()->active()->where('service_id', $service->id)->get(),
            'others' => Service::query()->active()->whereKeyNot($service->id)->limit(3)->get(),
            'descriptionHtml' => Markdown::toHtml((string) $service->description),
        ]);
    }

    public function pricing(): Response
    {
        return Inertia::render('Site/Pricing', [
            'meta' => $this->meta('Harga & Paket', 'Paket pembuatan website mulai dari landing page hingga sistem informasi custom. Transparan, bergaransi & bisa request fitur.'),
            'packages' => Package::query()->active()->with('service:id,title')->get(),
            'faqs' => Faq::query()->active()->get(),
        ]);
    }

    public function portfolios(): Response
    {
        $items = Portfolio::query()->active()->get();

        return Inertia::render('Site/Portfolio/Index', [
            'meta' => $this->meta('Portofolio', 'Karya website & sistem informasi yang telah kami kerjakan.'),
            'portfolios' => $items,
            'categories' => $items->pluck('category')->filter()->unique()->values(),
        ]);
    }

    public function portfolio(Portfolio $portfolio): Response
    {
        abort_unless($portfolio->is_active, 404);

        return Inertia::render('Site/Portfolio/Show', [
            'meta' => $this->meta($portfolio->title, $portfolio->excerpt, $portfolio->image_url),
            'portfolio' => $portfolio,
            'descriptionHtml' => Markdown::toHtml((string) $portfolio->description),
            'others' => Portfolio::query()->active()->whereKeyNot($portfolio->id)->limit(3)->get(),
        ]);
    }

    public function blog(Request $request): Response
    {
        $q = trim((string) $request->query('q', ''));
        $category = (string) $request->query('kategori', '');

        $posts = Post::query()->published()
            ->when($q !== '', fn ($w) => $w->where(fn ($x) => $x->where('title', 'like', '%'.$q.'%')->orWhere('excerpt', 'like', '%'.$q.'%')))
            ->when($category !== '', fn ($w) => $w->where('category', $category))
            ->select(['id', 'title', 'slug', 'excerpt', 'cover_path', 'category', 'published_at', 'body', 'author_id'])
            ->with('author:id,name')
            ->paginate(9)->withQueryString();

        $posts->getCollection()->each(fn ($p) => $p->makeHidden('body'));

        return Inertia::render('Site/Blog/Index', [
            'meta' => $this->meta('Blog & Insight', 'Tips bisnis digital, website, dan teknologi dari tim Kreatif Sky.'),
            'posts' => $posts,
            'categories' => Post::query()->published()->whereNotNull('category')->distinct()->pluck('category'),
            'filters' => ['q' => $q, 'kategori' => $category],
        ]);
    }

    public function post(Request $request, Post $post): Response
    {
        abort_unless($post->status === 'published' && $post->published_at && $post->published_at->isPast(), 404);

        $viewed = (array) $request->session()->get('viewed_posts', []);
        if (! in_array($post->id, $viewed, true)) {
            $post->increment('views');
            $request->session()->push('viewed_posts', $post->id);
        }

        $post->load('author:id,name,avatar_path');

        return Inertia::render('Site/Blog/Show', [
            'meta' => $this->meta($post->title, $post->excerpt, $post->cover_url, 'article'),
            'post' => $post->makeHidden('body'),
            'bodyHtml' => Markdown::toHtml((string) $post->body),
            'related' => Post::query()->published()->whereKeyNot($post->id)->limit(3)->get(['id', 'title', 'slug', 'excerpt', 'cover_path', 'category', 'published_at', 'body'])->each->makeHidden('body'),
        ]);
    }

    public function contact(): Response
    {
        return Inertia::render('Site/Contact', [
            'meta' => $this->meta('Kontak', 'Hubungi PT Kreatif Sky Abadi untuk konsultasi gratis pembuatan website & aplikasi.'),
            'mapsUrl' => $this->safeMapsUrl(),
        ]);
    }

    /** Hanya izinkan embed dari Google Maps (cegah iframe ke domain sembarang). */
    private function safeMapsUrl(): ?string
    {
        $url = (string) Setting::get('maps_embed_url', '');

        return preg_match('#^https://(www\.)?google\.com/maps#', $url) ? $url : null;
    }

    private function stats(): array
    {
        return [
            'projects' => (int) Setting::get('stat_projects', 0),
            'clients' => (int) Setting::get('stat_clients', 0),
            'years' => (int) Setting::get('stat_years', 1),
            'satisfaction' => (int) Setting::get('stat_satisfaction', 100),
        ];
    }

    public static function legal(): array
    {
        return [
            'legal_type' => Setting::get('company_legal_type'),
            'sk_number' => Setting::get('legal_sk_number'),
            'sk_date' => Setting::get('legal_sk_date'),
            'sk_title' => Setting::get('legal_sk_title'),
            'nib' => Setting::get('legal_nib'),
            'certificate_number' => Setting::get('legal_certificate_number'),
            'certificate_date' => Setting::get('legal_certificate_date'),
            'risk_level' => Setting::get('legal_risk_level'),
            'sppl_status' => Setting::get('legal_sppl_status'),
            'kbli' => json_decode((string) Setting::get('legal_kbli', '[]'), true) ?: [],
        ];
    }

    private function meta(?string $title = null, ?string $description = null, ?string $image = null, string $type = 'website'): array
    {
        $site = Setting::get('company_name', config('app.name'));

        return [
            'title' => $title ? $title.' – '.$site : Setting::get('seo_title', $site),
            'description' => Str::limit(strip_tags((string) ($description ?: Setting::get('seo_description'))), 160),
            'image' => $image,
            'type' => $type,
        ];
    }
}
