<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Portfolio;
use App\Models\Post;
use App\Models\Service;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $urls = collect(['home', 'about', 'services.index', 'pricing', 'portfolio.index', 'blog.index', 'contact', 'order.create', 'track', 'verify.index'])
            ->map(fn ($r) => ['loc' => route($r), 'lastmod' => now()->toDateString()]);

        Service::query()->active()->get(['slug', 'updated_at'])->each(fn ($s) => $urls->push(['loc' => route('services.show', $s->slug), 'lastmod' => $s->updated_at?->toDateString()]));
        Portfolio::query()->active()->get(['slug', 'updated_at'])->each(fn ($p) => $urls->push(['loc' => route('portfolio.show', $p->slug), 'lastmod' => $p->updated_at?->toDateString()]));
        Post::query()->published()->get(['slug', 'updated_at'])->each(fn ($p) => $urls->push(['loc' => route('blog.show', $p->slug), 'lastmod' => $p->updated_at?->toDateString()]));

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n".'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        foreach ($urls as $u) {
            $xml .= '<url><loc>'.e($u['loc']).'</loc>'.($u['lastmod'] ? '<lastmod>'.$u['lastmod'].'</lastmod>' : '').'</url>';
        }
        $xml .= '</urlset>';

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }
}
