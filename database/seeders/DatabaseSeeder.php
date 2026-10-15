<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\DocumentCategory;
use App\Models\Faq;
use App\Models\Package;
use App\Models\Portfolio;
use App\Models\Post;
use App\Models\Service;
use App\Models\Setting;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedSettings();
        $admin = $this->seedUsers();
        $this->seedServicesAndPackages();
        $this->seedPortfolio();
        $this->seedFaqs();
        $this->seedTeam();
        $this->seedDocumentCategories();
        $this->seedContent($admin);
    }

    private function seedSettings(): void
    {
        foreach (config('company.defaults') as $key => $value) {
            Setting::query()->firstOrCreate(['key' => $key], ['value' => $value]);
        }
        cache()->forget(Setting::CACHE_KEY);
    }

    private function seedUsers(): User
    {
        $email = env('SEED_ADMIN_EMAIL', 'admin@kreatifsky.id');
        $admin = User::query()->where('email', $email)->first();

        if (! $admin) {
            $password = env('SEED_ADMIN_PASSWORD') ?: Str::password(14, symbols: false);
            $admin = User::query()->create([
                'name' => 'I Gusti Sultan Hajjarul Aswat',
                'email' => $email,
                'role' => User::ROLE_ADMIN,
                'position' => 'Direktur',
                'is_active' => true,
                'employee_number' => 'KSA-001',
                'joined_at' => '2026-09-01',
                'password' => $password,
            ]);
            $admin->forceFill(['email_verified_at' => now()])->save();
            $this->command?->warn("Akun ADMIN dibuat  → email: {$email}  | password: {$password}");
            $this->command?->warn('Segera ganti password & aktifkan 2FA setelah login pertama.');
        }

        if (env('SEED_DEMO_AGENT', app()->environment('local'))) {
            $agentEmail = 'agen@kreatifsky.id';
            if (! User::query()->where('email', $agentEmail)->exists()) {
                $password = Str::password(12, symbols: false);
                User::query()->create([
                    'name' => 'Agen Demo',
                    'email' => $agentEmail,
                    'role' => User::ROLE_AGENT,
                    'position' => 'Web Developer',
                    'is_active' => true,
                    'employee_number' => 'KSA-002',
                    'joined_at' => now()->toDateString(),
                    'password' => $password,
                ]);
                $this->command?->warn("Akun AGEN demo     → email: {$agentEmail}  | password: {$password}");
            }
        }

        return $admin;
    }

    private function seedServicesAndPackages(): void
    {
        $services = [
            [
                'title' => 'Pembuatan Website', 'icon' => 'Globe', 'color' => 'violet', 'kbli_code' => '62199',
                'excerpt' => 'Company profile, landing page, hingga toko online yang cepat, responsif & SEO-friendly.',
                'description' => "Website adalah wajah digital bisnismu. Kami merancang website yang tidak hanya cantik dipandang, tetapi juga cepat, aman, dan mudah ditemukan di Google.\n\nSetiap website dilengkapi panel admin sehingga kamu bisa mengubah konten sendiri tanpa harus paham coding.",
                'features' => ['Desain custom & kekinian', 'Responsif di semua perangkat', 'Panel admin mudah dipakai', 'SEO dasar & performa tinggi', 'SSL & keamanan standar industri'],
            ],
            [
                'title' => 'Sistem Informasi & Aplikasi Web', 'icon' => 'LayoutDashboard', 'color' => 'fuchsia', 'kbli_code' => '62199',
                'excerpt' => 'Absensi, arsip dokumen, tanda tangan elektronik (TTE), inventaris, kasir — sesuai alur kerjamu.',
                'description' => 'Digitalisasi proses bisnis dan administrasi kantor dengan sistem yang dibuat khusus sesuai kebutuhanmu. Mulai dari absensi pegawai, persuratan, arsip dokumen, tanda tangan elektronik berbasis QR Code, hingga dashboard laporan real-time.',
                'features' => ['Analisis kebutuhan gratis', 'Multi role & hak akses', 'TTE dengan QR verifikasi', 'Laporan & ekspor data', 'Bisa dikembangkan ke Android'],
            ],
            [
                'title' => 'Portal Web & Informasi', 'icon' => 'Newspaper', 'color' => 'orange', 'kbli_code' => '63900',
                'excerpt' => 'Portal berita, direktori, katalog & website informasi publik dengan manajemen konten lengkap.',
                'description' => 'Bangun portal informasi yang rapi dan mudah dicari: portal berita, website desa/sekolah, direktori usaha, atau katalog informasi. Dilengkapi fitur pencarian, kategori, dan manajemen penulis.',
                'features' => ['Manajemen artikel & kategori', 'Pencarian cepat', 'Multi penulis', 'Optimasi berbagi ke media sosial'],
            ],
            [
                'title' => 'UI/UX Design', 'icon' => 'Palette', 'color' => 'pink', 'kbli_code' => '62199',
                'excerpt' => 'Desain antarmuka yang fresh, intuitif & disukai pengguna — dari wireframe sampai prototipe.',
                'description' => 'Pengalaman pengguna yang baik membuat pengunjung betah dan berubah menjadi pelanggan. Kami merancang alur, wireframe, hingga desain visual yang konsisten dengan identitas brand-mu.',
                'features' => ['Riset & user flow', 'Wireframe & prototipe', 'Design system', 'Desain mobile-first'],
            ],
            [
                'title' => 'Maintenance, Domain & Hosting', 'icon' => 'Server', 'color' => 'cyan', 'kbli_code' => '62900',
                'excerpt' => 'Tenang, website-mu selalu online. Update rutin, backup, monitoring & bantuan teknis.',
                'description' => 'Kami bantu urus domain, hosting/VPS, SSL, backup berkala, update keamanan, hingga perbaikan bug agar website-mu selalu prima.',
                'features' => ['Setup domain & SSL', 'Hosting / VPS (aaPanel)', 'Backup berkala', 'Monitoring & update keamanan'],
            ],
            [
                'title' => 'Konsultasi TI & Keamanan', 'icon' => 'ShieldCheck', 'color' => 'lime', 'kbli_code' => '62900',
                'excerpt' => 'Konsultasi transformasi digital, audit keamanan dasar, dan pendampingan teknologi.',
                'description' => 'Bingung mulai digitalisasi dari mana? Kami siap menjadi teman diskusi: memilih teknologi yang tepat, menyusun SOP keamanan data, hingga pelatihan tim.',
                'features' => ['Konsultasi kebutuhan digital', 'Audit keamanan dasar', 'Penyusunan SOP keamanan data', 'Pelatihan pengguna'],
            ],
        ];

        foreach ($services as $i => $s) {
            Service::query()->firstOrCreate(['slug' => Str::slug($s['title'])], $s + ['sort_order' => $i + 1, 'is_active' => true]);
        }

        $web = Service::query()->where('slug', 'pembuatan-website')->first();
        $sys = Service::query()->where('slug', 'sistem-informasi-aplikasi-web')->first();

        $packages = [
            [
                'name' => 'Starter', 'service_id' => $web?->id, 'price' => 1500000, 'original_price' => 2000000, 'color' => 'sky',
                'description' => 'Pas untuk personal branding, UMKM & promosi produk.', 'delivery_time' => '5–7 hari kerja',
                'features' => ['Landing page 1 halaman', 'Desain responsif', 'Tombol chat WhatsApp', 'Form kontak', 'SSL (https)', 'Revisi 2x'],
            ],
            [
                'name' => 'Bisnis', 'service_id' => $web?->id, 'price' => 3500000, 'original_price' => 4500000, 'color' => 'violet', 'is_popular' => true,
                'description' => 'Company profile lengkap dengan panel admin.', 'delivery_time' => '10–14 hari kerja',
                'features' => ['Hingga 7 halaman', 'Panel admin / CMS', 'Blog & galeri', 'SEO dasar', 'Integrasi Google Maps', 'Revisi 3x', 'Gratis maintenance 1 bulan'],
            ],
            [
                'name' => 'Pro', 'service_id' => $sys?->id, 'price' => 7500000, 'color' => 'fuchsia',
                'description' => 'Toko online / sistem informasi dengan fitur custom.', 'delivery_time' => '3–5 minggu',
                'features' => ['Fitur custom sesuai kebutuhan', 'Multi role pengguna', 'Dashboard & laporan', 'Payment gateway / TTE (opsional)', 'Pelatihan penggunaan', 'Gratis maintenance 3 bulan'],
            ],
            [
                'name' => 'Custom', 'service_id' => $sys?->id, 'price' => 0, 'price_note' => 'Sesuai kebutuhan', 'color' => 'orange',
                'description' => 'Proyek skala besar, integrasi sistem & aplikasi Android.', 'delivery_time' => 'Sesuai ruang lingkup',
                'features' => ['Analisis & dokumentasi kebutuhan', 'Arsitektur skalabel', 'Integrasi API', 'Aplikasi Android (opsional)', 'SLA & dukungan prioritas'],
            ],
        ];

        foreach ($packages as $i => $p) {
            Package::query()->firstOrCreate(['slug' => Str::slug($p['name'])], $p + ['sort_order' => $i + 1, 'is_active' => true]);
        }
    }

    private function seedPortfolio(): void
    {
        Portfolio::query()->firstOrCreate(['slug' => 'website-pt-kreatif-sky-abadi'], [
            'title' => 'Website & Sistem Internal PT Kreatif Sky Abadi',
            'client' => 'PT Kreatif Sky Abadi',
            'category' => 'Company Profile + Sistem Informasi',
            'excerpt' => 'Company profile interaktif dengan panel Admin & Agen: pesanan, arsip dokumen, TTE QR, absensi & tugas.',
            'description' => 'Website yang sedang kamu lihat ini! Dibangun dengan Laravel, Inertia & Vue 3. Selain company profile dengan animasi interaktif, sistem ini memiliki panel internal untuk mengelola pesanan website, invoice, arsip dokumen, tanda tangan elektronik dengan QR verifikasi, absensi, cuti, tugas & laporan harian.',
            'tech_stack' => ['Laravel', 'Vue 3', 'Inertia', 'Tailwind CSS', 'MySQL'],
            'year' => 2026, 'color' => 'violet', 'is_featured' => true, 'sort_order' => 1, 'is_active' => true,
        ]);

        // Draf: aktifkan dari panel admin bila informasi klien boleh dipublikasikan.
        Portfolio::query()->firstOrCreate(['slug' => 'sisinden'], [
            'title' => 'SISINDEN – Sistem Administrasi Perkantoran Digital',
            'client' => 'Instansi (dirahasiakan)',
            'category' => 'Sistem Informasi + Aplikasi Android',
            'excerpt' => 'Persuratan, TTE QR Code, verifikasi dokumen publik, absensi & notifikasi WhatsApp dalam satu sistem.',
            'description' => 'Sistem administrasi perkantoran berbasis web & Android dengan alur pengajuan tanda tangan elektronik, penempelan QR Code verifikasi pada PDF, arsip, dan berbagai modul operasional.',
            'tech_stack' => ['Laravel', 'Vue 3', 'Inertia', 'Android', 'FPDI'],
            'year' => 2026, 'color' => 'fuchsia', 'is_featured' => false, 'sort_order' => 2, 'is_active' => false,
        ]);
    }

    private function seedFaqs(): void
    {
        $faqs = [
            ['Berapa lama proses pembuatan website?', 'Tergantung paket & fitur. Landing page sekitar 5–7 hari kerja, company profile 10–14 hari kerja, dan sistem informasi custom 3–5 minggu. Estimasi pasti kami berikan setelah analisis kebutuhan.'],
            ['Bagaimana sistem pembayarannya?', 'Umumnya DP 50% di awal sebagai tanda jadi, dan pelunasan setelah website selesai & disetujui. Semua transaksi disertai invoice resmi dari PT Kreatif Sky Abadi.'],
            ['Apakah saya bisa request fitur atau desain khusus?', 'Tentu! Isi formulir "Request Website", ceritakan kebutuhan, contoh website referensi, warna favorit, dan fitur yang diinginkan. Tim kami akan menghubungi untuk diskusi gratis.'],
            ['Apakah harga sudah termasuk domain & hosting?', 'Paket dapat disesuaikan. Jika kamu belum punya domain/hosting, kami bantu siapkan dengan biaya transparan sesuai pilihan penyedia.'],
            ['Apakah PT Kreatif Sky Abadi berbadan hukum resmi?', 'Ya. Kami adalah Perseroan Perorangan yang disahkan Kementerian Hukum RI (AHU-A012478.AH.01.31.Tahun 2026), memiliki NIB 0109260072761 dan Sertifikat Standar OSS untuk KBLI 62199.'],
            ['Bagaimana cara memantau progres pesanan saya?', 'Setelah mengirim request, kamu mendapat kode pesanan. Gunakan menu "Lacak Pesanan" dengan kode tersebut dan email yang didaftarkan untuk melihat status & progres pengerjaan.'],
            ['Apakah ada garansi dan maintenance?', 'Ada. Setiap proyek mendapatkan garansi perbaikan bug dan masa maintenance gratis sesuai paket. Setelahnya tersedia layanan maintenance bulanan.'],
        ];
        foreach ($faqs as $i => [$q, $a]) {
            Faq::query()->firstOrCreate(['question' => $q], ['answer' => $a, 'sort_order' => $i + 1, 'is_active' => true]);
        }
    }

    private function seedTeam(): void
    {
        TeamMember::query()->firstOrCreate(['name' => 'I Gusti Sultan Hajjarul Aswat'], [
            'position' => 'Direktur & Pendiri',
            'bio' => 'Full-stack developer yang senang membangun sistem yang bikin pekerjaan kantor jadi lebih ringan.',
            'color' => 'violet', 'sort_order' => 1, 'is_active' => true,
        ]);
    }

    private function seedDocumentCategories(): void
    {
        $cats = [
            ['Legalitas Perusahaan', 'violet', 'Akta, SK Kemenkum, NIB, Sertifikat Standar, SPPL'],
            ['Kontrak & Perjanjian', 'fuchsia', 'Kontrak klien, MoU, perjanjian kerja sama'],
            ['Keuangan & Pajak', 'emerald', 'Invoice, bukti bayar, laporan keuangan, pajak'],
            ['SDM & Kepegawaian', 'sky', 'Data pegawai, sertifikat kompetensi, struktur organisasi'],
            ['Surat Masuk', 'amber', 'Surat dari pihak luar'],
            ['Surat Keluar', 'orange', 'Surat resmi yang dikirim perusahaan'],
            ['SOP & Kebijakan', 'cyan', 'SOP keamanan data, kebijakan internal'],
            ['Lainnya', 'slate', 'Dokumen lain-lain'],
        ];
        foreach ($cats as [$name, $color, $desc]) {
            DocumentCategory::query()->firstOrCreate(['slug' => Str::slug($name)], ['name' => $name, 'color' => $color, 'description' => $desc]);
        }
    }

    private function seedContent(User $admin): void
    {
        Announcement::query()->firstOrCreate(['title' => 'Selamat datang di sistem internal Kreatif Sky! 🎉'], [
            'body' => 'Gunakan menu Absensi setiap hari, isi Laporan Harian sebelum pulang, dan pantau Tugas kamu di papan Kanban. Semangat berkarya!',
            'level' => 'success', 'is_pinned' => true, 'is_active' => true, 'created_by' => $admin->id,
        ]);

        Post::query()->firstOrCreate(['slug' => 'kenapa-bisnis-kecil-butuh-website'], [
            'author_id' => $admin->id,
            'title' => 'Kenapa Bisnis Kecil Tetap Butuh Website di Era Media Sosial?',
            'category' => 'Tips Bisnis',
            'excerpt' => 'Media sosial itu penting, tapi website adalah "rumah" digital yang benar-benar kamu miliki. Ini alasannya.',
            'body' => "## Media sosial itu sewa, website itu milik sendiri\n\nAkun media sosial bisa terkena pembatasan, algoritma bisa berubah, dan kamu tidak punya kendali penuh atas tampilannya. Website memberi kamu kontrol penuh atas brand dan data pelanggan.\n\n## Lebih dipercaya calon pelanggan\n\nBisnis yang punya website dengan domain sendiri terlihat lebih profesional dan kredibel — apalagi jika dilengkapi informasi legalitas yang jelas.\n\n## Mudah ditemukan di Google\n\nDengan SEO yang baik, calon pelanggan bisa menemukan bisnismu saat mencari produk atau jasa yang kamu tawarkan.\n\n## Bekerja 24 jam\n\nWebsite menjawab pertanyaan, menampilkan katalog, dan menerima pesanan bahkan saat kamu sedang tidur.\n\nSiap punya website sendiri? Klik **Request Website** dan ceritakan kebutuhanmu!",
            'status' => 'published',
            'published_at' => now(),
        ]);
    }
}
