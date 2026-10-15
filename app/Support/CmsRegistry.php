<?php

namespace App\Support;

use App\Models\Announcement;
use App\Models\Faq;
use App\Models\Package;
use App\Models\Portfolio;
use App\Models\Post;
use App\Models\Service;
use App\Models\TeamMember;
use App\Models\Testimonial;

/**
 * Definisi skema konten untuk CMS generik (Admin > Konten).
 * Field type: text, textarea, markdown, number, money, switch, select, color, icon, list, image, date, datetime, url.
 */
class CmsRegistry
{
    public static function get(string $key): ?array
    {
        return self::all()[$key] ?? null;
    }

    public static function all(): array
    {
        $colorRule = ['nullable', 'string', 'in:'.implode(',', array_merge(Catalog::COLORS, ['slate']))];
        $iconRule = ['nullable', 'string', 'in:'.implode(',', Catalog::ICONS)];

        return [
            'services' => [
                'model' => Service::class,
                'title' => 'Layanan',
                'singular' => 'layanan',
                'description' => 'Layanan yang tampil di website (sesuai KBLI perusahaan).',
                'search' => ['title', 'excerpt'],
                'order' => ['sort_order', 'asc'],
                'slug_from' => 'title',
                'public_route' => 'services.show',
                'columns' => [
                    ['key' => 'title', 'label' => 'Layanan', 'type' => 'title', 'icon' => 'icon', 'color' => 'color', 'sub' => 'excerpt'],
                    ['key' => 'kbli_code', 'label' => 'KBLI'],
                    ['key' => 'sort_order', 'label' => 'Urutan'],
                    ['key' => 'is_active', 'label' => 'Tampil', 'type' => 'toggle'],
                ],
                'fields' => [
                    ['name' => 'title', 'label' => 'Nama layanan', 'type' => 'text', 'span' => 2, 'rules' => ['required', 'string', 'max:150']],
                    ['name' => 'slug', 'label' => 'Slug URL', 'type' => 'text', 'help' => 'Kosongkan agar dibuat otomatis.', 'rules' => ['nullable', 'string', 'max:160', 'alpha_dash']],
                    ['name' => 'kbli_code', 'label' => 'Kode KBLI', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:10']],
                    ['name' => 'icon', 'label' => 'Ikon', 'type' => 'icon', 'rules' => $iconRule],
                    ['name' => 'color', 'label' => 'Warna aksen', 'type' => 'color', 'rules' => $colorRule],
                    ['name' => 'excerpt', 'label' => 'Ringkasan singkat', 'type' => 'textarea', 'rows' => 2, 'span' => 2, 'rules' => ['nullable', 'string', 'max:500']],
                    ['name' => 'description', 'label' => 'Deskripsi lengkap', 'type' => 'markdown', 'span' => 2, 'rules' => ['nullable', 'string', 'max:20000']],
                    ['name' => 'features', 'label' => 'Poin keunggulan', 'type' => 'list', 'span' => 2, 'rules' => ['nullable', 'array', 'max:20'], 'item_rules' => ['string', 'max:200']],
                    ['name' => 'sort_order', 'label' => 'Urutan', 'type' => 'number', 'rules' => ['nullable', 'integer', 'min:0', 'max:9999']],
                    ['name' => 'is_active', 'label' => 'Tampilkan di website', 'type' => 'switch', 'rules' => ['boolean']],
                ],
                'defaults' => ['icon' => 'Sparkles', 'color' => 'violet', 'is_active' => true, 'sort_order' => 0, 'features' => []],
            ],

            'packages' => [
                'model' => Package::class,
                'title' => 'Paket Harga',
                'singular' => 'paket',
                'description' => 'Paket harga pembuatan website yang tampil di halaman Harga & formulir request.',
                'search' => ['name', 'description'],
                'order' => ['sort_order', 'asc'],
                'slug_from' => 'name',
                'with' => ['service:id,title'],
                'columns' => [
                    ['key' => 'name', 'label' => 'Paket', 'type' => 'title', 'color' => 'color', 'sub' => 'description'],
                    ['key' => 'price', 'label' => 'Harga', 'type' => 'money'],
                    ['key' => 'is_popular', 'label' => 'Populer', 'type' => 'bool'],
                    ['key' => 'is_active', 'label' => 'Tampil', 'type' => 'toggle'],
                ],
                'fields' => [
                    ['name' => 'name', 'label' => 'Nama paket', 'type' => 'text', 'rules' => ['required', 'string', 'max:100']],
                    ['name' => 'slug', 'label' => 'Slug', 'type' => 'text', 'help' => 'Kosongkan agar otomatis.', 'rules' => ['nullable', 'string', 'max:120', 'alpha_dash']],
                    ['name' => 'service_id', 'label' => 'Layanan terkait', 'type' => 'select', 'options_from' => 'services', 'rules' => ['nullable', 'integer', 'exists:services,id']],
                    ['name' => 'color', 'label' => 'Warna', 'type' => 'color', 'rules' => $colorRule],
                    ['name' => 'price', 'label' => 'Harga (Rp)', 'type' => 'money', 'help' => 'Isi 0 untuk "Hubungi Kami".', 'rules' => ['required', 'integer', 'min:0', 'max:100000000000']],
                    ['name' => 'original_price', 'label' => 'Harga coret (Rp)', 'type' => 'money', 'rules' => ['nullable', 'integer', 'min:0', 'max:100000000000']],
                    ['name' => 'price_note', 'label' => 'Catatan harga', 'type' => 'text', 'help' => 'Mis. "Mulai dari" / "Sesuai kebutuhan".', 'rules' => ['nullable', 'string', 'max:100']],
                    ['name' => 'delivery_time', 'label' => 'Estimasi pengerjaan', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:50']],
                    ['name' => 'description', 'label' => 'Deskripsi singkat', 'type' => 'textarea', 'rows' => 2, 'span' => 2, 'rules' => ['nullable', 'string', 'max:500']],
                    ['name' => 'features', 'label' => 'Fitur yang didapat', 'type' => 'list', 'span' => 2, 'rules' => ['nullable', 'array', 'max:30'], 'item_rules' => ['string', 'max:200']],
                    ['name' => 'sort_order', 'label' => 'Urutan', 'type' => 'number', 'rules' => ['nullable', 'integer', 'min:0', 'max:9999']],
                    ['name' => 'is_popular', 'label' => 'Tandai paling populer', 'type' => 'switch', 'rules' => ['boolean']],
                    ['name' => 'is_active', 'label' => 'Tampilkan', 'type' => 'switch', 'rules' => ['boolean']],
                ],
                'defaults' => ['color' => 'violet', 'price' => 0, 'is_active' => true, 'is_popular' => false, 'sort_order' => 0, 'features' => []],
            ],

            'portfolios' => [
                'model' => Portfolio::class,
                'title' => 'Portofolio',
                'singular' => 'portofolio',
                'description' => 'Karya yang ditampilkan di website.',
                'search' => ['title', 'client', 'category'],
                'order' => ['sort_order', 'asc'],
                'slug_from' => 'title',
                'public_route' => 'portfolio.show',
                'columns' => [
                    ['key' => 'title', 'label' => 'Proyek', 'type' => 'title', 'image' => 'image_url', 'color' => 'color', 'sub' => 'client'],
                    ['key' => 'category', 'label' => 'Kategori'],
                    ['key' => 'year', 'label' => 'Tahun'],
                    ['key' => 'is_featured', 'label' => 'Unggulan', 'type' => 'bool'],
                    ['key' => 'is_active', 'label' => 'Tampil', 'type' => 'toggle'],
                ],
                'fields' => [
                    ['name' => 'title', 'label' => 'Judul proyek', 'type' => 'text', 'span' => 2, 'rules' => ['required', 'string', 'max:150']],
                    ['name' => 'slug', 'label' => 'Slug', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:160', 'alpha_dash']],
                    ['name' => 'client', 'label' => 'Klien', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:150']],
                    ['name' => 'category', 'label' => 'Kategori', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:100']],
                    ['name' => 'year', 'label' => 'Tahun', 'type' => 'number', 'rules' => ['nullable', 'integer', 'min:2000', 'max:2100']],
                    ['name' => 'url', 'label' => 'URL website (opsional)', 'type' => 'url', 'rules' => ['nullable', 'url:https,http', 'max:255']],
                    ['name' => 'color', 'label' => 'Warna', 'type' => 'color', 'rules' => $colorRule],
                    ['name' => 'image_path', 'label' => 'Gambar / tangkapan layar', 'type' => 'image', 'span' => 2, 'url_key' => 'image_url'],
                    ['name' => 'excerpt', 'label' => 'Ringkasan', 'type' => 'textarea', 'rows' => 2, 'span' => 2, 'rules' => ['nullable', 'string', 'max:500']],
                    ['name' => 'description', 'label' => 'Cerita proyek', 'type' => 'markdown', 'span' => 2, 'rules' => ['nullable', 'string', 'max:20000']],
                    ['name' => 'tech_stack', 'label' => 'Teknologi', 'type' => 'list', 'span' => 2, 'rules' => ['nullable', 'array', 'max:20'], 'item_rules' => ['string', 'max:50']],
                    ['name' => 'sort_order', 'label' => 'Urutan', 'type' => 'number', 'rules' => ['nullable', 'integer', 'min:0', 'max:9999']],
                    ['name' => 'is_featured', 'label' => 'Unggulan', 'type' => 'switch', 'rules' => ['boolean']],
                    ['name' => 'is_active', 'label' => 'Tampilkan', 'type' => 'switch', 'rules' => ['boolean']],
                ],
                'defaults' => ['color' => 'violet', 'is_active' => true, 'is_featured' => false, 'sort_order' => 0, 'tech_stack' => [], 'year' => (int) date('Y')],
            ],

            'posts' => [
                'model' => Post::class,
                'title' => 'Blog / Artikel',
                'singular' => 'artikel',
                'description' => 'Artikel untuk SEO & edukasi calon klien.',
                'search' => ['title', 'excerpt', 'category'],
                'order' => ['created_at', 'desc'],
                'slug_from' => 'title',
                'public_route' => 'blog.show',
                'with' => ['author:id,name'],
                'set_user' => 'author_id',
                'toggle' => null,
                'columns' => [
                    ['key' => 'title', 'label' => 'Judul', 'type' => 'title', 'image' => 'cover_url', 'sub' => 'category'],
                    ['key' => 'status', 'label' => 'Status', 'type' => 'badge', 'map' => ['draft' => ['Draf', 'slate'], 'published' => ['Terbit', 'emerald']]],
                    ['key' => 'published_at', 'label' => 'Terbit', 'type' => 'date'],
                    ['key' => 'views', 'label' => 'Dilihat'],
                ],
                'fields' => [
                    ['name' => 'title', 'label' => 'Judul', 'type' => 'text', 'span' => 2, 'rules' => ['required', 'string', 'max:200']],
                    ['name' => 'slug', 'label' => 'Slug', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:210', 'alpha_dash']],
                    ['name' => 'category', 'label' => 'Kategori', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:100']],
                    ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['draft' => 'Draf', 'published' => 'Terbitkan'], 'rules' => ['required', 'in:draft,published']],
                    ['name' => 'published_at', 'label' => 'Waktu terbit', 'type' => 'datetime', 'help' => 'Kosongkan = sekarang (jika diterbitkan).', 'rules' => ['nullable', 'date']],
                    ['name' => 'cover_path', 'label' => 'Gambar sampul', 'type' => 'image', 'span' => 2, 'url_key' => 'cover_url'],
                    ['name' => 'excerpt', 'label' => 'Ringkasan', 'type' => 'textarea', 'rows' => 2, 'span' => 2, 'rules' => ['nullable', 'string', 'max:500']],
                    ['name' => 'body', 'label' => 'Isi artikel (Markdown)', 'type' => 'markdown', 'span' => 2, 'rows' => 16, 'rules' => ['nullable', 'string', 'max:100000']],
                ],
                'defaults' => ['status' => 'draft'],
            ],

            'testimonials' => [
                'model' => Testimonial::class,
                'title' => 'Testimoni',
                'singular' => 'testimoni',
                'description' => 'Masukkan hanya testimoni asli dari klien (dengan izin mereka).',
                'search' => ['name', 'company', 'message'],
                'order' => ['sort_order', 'asc'],
                'columns' => [
                    ['key' => 'name', 'label' => 'Nama', 'type' => 'title', 'image' => 'avatar_url', 'sub' => 'company'],
                    ['key' => 'rating', 'label' => 'Rating', 'type' => 'rating'],
                    ['key' => 'is_active', 'label' => 'Tampil', 'type' => 'toggle'],
                ],
                'fields' => [
                    ['name' => 'name', 'label' => 'Nama klien', 'type' => 'text', 'rules' => ['required', 'string', 'max:100']],
                    ['name' => 'role', 'label' => 'Jabatan', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:100']],
                    ['name' => 'company', 'label' => 'Perusahaan / Usaha', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:150']],
                    ['name' => 'rating', 'label' => 'Rating (1–5)', 'type' => 'number', 'rules' => ['required', 'integer', 'min:1', 'max:5']],
                    ['name' => 'avatar_path', 'label' => 'Foto (opsional)', 'type' => 'image', 'span' => 2, 'url_key' => 'avatar_url'],
                    ['name' => 'message', 'label' => 'Testimoni', 'type' => 'textarea', 'rows' => 4, 'span' => 2, 'rules' => ['required', 'string', 'max:1000']],
                    ['name' => 'sort_order', 'label' => 'Urutan', 'type' => 'number', 'rules' => ['nullable', 'integer', 'min:0', 'max:9999']],
                    ['name' => 'is_active', 'label' => 'Tampilkan', 'type' => 'switch', 'rules' => ['boolean']],
                ],
                'defaults' => ['rating' => 5, 'is_active' => true, 'sort_order' => 0],
            ],

            'faqs' => [
                'model' => Faq::class,
                'title' => 'FAQ',
                'singular' => 'pertanyaan',
                'description' => 'Pertanyaan yang sering diajukan calon klien.',
                'search' => ['question', 'answer'],
                'order' => ['sort_order', 'asc'],
                'columns' => [
                    ['key' => 'question', 'label' => 'Pertanyaan', 'type' => 'title', 'sub' => 'answer'],
                    ['key' => 'sort_order', 'label' => 'Urutan'],
                    ['key' => 'is_active', 'label' => 'Tampil', 'type' => 'toggle'],
                ],
                'fields' => [
                    ['name' => 'question', 'label' => 'Pertanyaan', 'type' => 'text', 'span' => 2, 'rules' => ['required', 'string', 'max:255']],
                    ['name' => 'answer', 'label' => 'Jawaban', 'type' => 'textarea', 'rows' => 5, 'span' => 2, 'rules' => ['required', 'string', 'max:3000']],
                    ['name' => 'category', 'label' => 'Kategori', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:50']],
                    ['name' => 'sort_order', 'label' => 'Urutan', 'type' => 'number', 'rules' => ['nullable', 'integer', 'min:0', 'max:9999']],
                    ['name' => 'is_active', 'label' => 'Tampilkan', 'type' => 'switch', 'rules' => ['boolean']],
                ],
                'defaults' => ['is_active' => true, 'sort_order' => 0],
            ],

            'team' => [
                'model' => TeamMember::class,
                'title' => 'Tim',
                'singular' => 'anggota tim',
                'description' => 'Anggota tim yang tampil di halaman Tentang.',
                'search' => ['name', 'position'],
                'order' => ['sort_order', 'asc'],
                'columns' => [
                    ['key' => 'name', 'label' => 'Nama', 'type' => 'title', 'image' => 'photo_url', 'color' => 'color', 'sub' => 'position'],
                    ['key' => 'sort_order', 'label' => 'Urutan'],
                    ['key' => 'is_active', 'label' => 'Tampil', 'type' => 'toggle'],
                ],
                'fields' => [
                    ['name' => 'name', 'label' => 'Nama', 'type' => 'text', 'rules' => ['required', 'string', 'max:100']],
                    ['name' => 'position', 'label' => 'Jabatan', 'type' => 'text', 'rules' => ['required', 'string', 'max:100']],
                    ['name' => 'photo_path', 'label' => 'Foto', 'type' => 'image', 'span' => 2, 'url_key' => 'photo_url'],
                    ['name' => 'bio', 'label' => 'Bio singkat', 'type' => 'textarea', 'rows' => 3, 'span' => 2, 'rules' => ['nullable', 'string', 'max:500']],
                    ['name' => 'instagram', 'label' => 'URL Instagram', 'type' => 'url', 'rules' => ['nullable', 'url:https', 'max:255']],
                    ['name' => 'linkedin', 'label' => 'URL LinkedIn', 'type' => 'url', 'rules' => ['nullable', 'url:https', 'max:255']],
                    ['name' => 'github', 'label' => 'URL GitHub', 'type' => 'url', 'rules' => ['nullable', 'url:https', 'max:255']],
                    ['name' => 'color', 'label' => 'Warna', 'type' => 'color', 'rules' => $colorRule],
                    ['name' => 'sort_order', 'label' => 'Urutan', 'type' => 'number', 'rules' => ['nullable', 'integer', 'min:0', 'max:9999']],
                    ['name' => 'is_active', 'label' => 'Tampilkan', 'type' => 'switch', 'rules' => ['boolean']],
                ],
                'defaults' => ['color' => 'violet', 'is_active' => true, 'sort_order' => 0],
            ],

            'announcements' => [
                'model' => Announcement::class,
                'title' => 'Pengumuman Internal',
                'singular' => 'pengumuman',
                'description' => 'Pengumuman untuk seluruh Admin & Agen (tampil di dashboard).',
                'search' => ['title', 'body'],
                'order' => ['created_at', 'desc'],
                'set_user' => 'created_by',
                'notify' => true,
                'columns' => [
                    ['key' => 'title', 'label' => 'Judul', 'type' => 'title', 'sub' => 'body'],
                    ['key' => 'level', 'label' => 'Jenis', 'type' => 'badge', 'map' => ['info' => ['Info', 'sky'], 'success' => ['Kabar baik', 'emerald'], 'warning' => ['Penting', 'amber'], 'danger' => ['Darurat', 'rose']]],
                    ['key' => 'is_pinned', 'label' => 'Disematkan', 'type' => 'bool'],
                    ['key' => 'is_active', 'label' => 'Aktif', 'type' => 'toggle'],
                ],
                'fields' => [
                    ['name' => 'title', 'label' => 'Judul', 'type' => 'text', 'span' => 2, 'rules' => ['required', 'string', 'max:200']],
                    ['name' => 'level', 'label' => 'Jenis', 'type' => 'select', 'options' => ['info' => 'Info', 'success' => 'Kabar baik', 'warning' => 'Penting', 'danger' => 'Darurat'], 'rules' => ['required', 'in:info,success,warning,danger']],
                    ['name' => 'expires_at', 'label' => 'Berlaku sampai', 'type' => 'date', 'rules' => ['nullable', 'date']],
                    ['name' => 'body', 'label' => 'Isi pengumuman', 'type' => 'textarea', 'rows' => 5, 'span' => 2, 'rules' => ['required', 'string', 'max:5000']],
                    ['name' => 'is_pinned', 'label' => 'Sematkan di atas', 'type' => 'switch', 'rules' => ['boolean']],
                    ['name' => 'is_active', 'label' => 'Aktif', 'type' => 'switch', 'rules' => ['boolean']],
                ],
                'defaults' => ['level' => 'info', 'is_active' => true, 'is_pinned' => false],
            ],
        ];
    }

    /** Navigasi menu konten. */
    public static function menu(): array
    {
        return collect(self::all())->map(fn ($r, $k) => ['key' => $k, 'title' => $r['title']])->values()->all();
    }
}
