<?php

namespace App\Support;

/**
 * Daftar pilihan (label Bahasa Indonesia) yang dipakai backend & frontend.
 */
class Catalog
{
    public const ORDER_STATUSES = [
        'new' => ['label' => 'Baru Masuk', 'color' => 'sky'],
        'reviewing' => ['label' => 'Sedang Ditinjau', 'color' => 'violet'],
        'quoted' => ['label' => 'Penawaran Dikirim', 'color' => 'amber'],
        'deal' => ['label' => 'Deal / DP Diterima', 'color' => 'lime'],
        'in_progress' => ['label' => 'Dalam Pengerjaan', 'color' => 'fuchsia'],
        'revision' => ['label' => 'Revisi', 'color' => 'orange'],
        'completed' => ['label' => 'Selesai', 'color' => 'emerald'],
        'cancelled' => ['label' => 'Dibatalkan', 'color' => 'rose'],
    ];

    public const ORDER_PRIORITIES = [
        'low' => 'Rendah', 'normal' => 'Normal', 'high' => 'Tinggi', 'urgent' => 'Mendesak',
    ];

    public const WEBSITE_TYPES = [
        'company_profile' => ['label' => 'Company Profile', 'icon' => 'Building', 'desc' => 'Website profil perusahaan / instansi yang profesional.'],
        'landing_page' => ['label' => 'Landing Page', 'icon' => 'Rocket', 'desc' => 'Satu halaman fokus konversi untuk produk / kampanye.'],
        'online_store' => ['label' => 'Toko Online', 'icon' => 'ShoppingBag', 'desc' => 'Katalog produk, keranjang & checkout.'],
        'information_system' => ['label' => 'Sistem Informasi', 'icon' => 'LayoutDashboard', 'desc' => 'Aplikasi internal: absensi, inventaris, arsip, dll.'],
        'school' => ['label' => 'Website Sekolah / Kampus', 'icon' => 'GraduationCap', 'desc' => 'Profil, PPDB online, berita & galeri.'],
        'portal' => ['label' => 'Portal Berita / Blog', 'icon' => 'Newspaper', 'desc' => 'Portal informasi dengan manajemen artikel.'],
        'web_app' => ['label' => 'Aplikasi Web Custom', 'icon' => 'CodeXml', 'desc' => 'Fitur khusus sesuai alur bisnis Anda.'],
        'other' => ['label' => 'Lainnya', 'icon' => 'Sparkles', 'desc' => 'Ceritakan kebutuhan unik Anda.'],
    ];

    public const WEBSITE_FEATURES = [
        'responsive' => 'Desain Responsif (HP & Desktop)',
        'admin_panel' => 'Panel Admin / CMS',
        'seo' => 'Optimasi SEO Dasar',
        'blog' => 'Blog / Berita',
        'gallery' => 'Galeri Foto & Video',
        'contact_form' => 'Form Kontak',
        'whatsapp' => 'Tombol Chat WhatsApp',
        'maps' => 'Integrasi Google Maps',
        'multi_language' => 'Multi Bahasa',
        'payment_gateway' => 'Payment Gateway',
        'booking' => 'Reservasi / Booking',
        'member_area' => 'Login Member / Pelanggan',
        'dashboard_report' => 'Dashboard & Laporan',
        'api_integration' => 'Integrasi API Pihak Ketiga',
        'pwa' => 'PWA (bisa di-install di HP)',
        'analytics' => 'Statistik Pengunjung',
        'email_notification' => 'Notifikasi Email / WA',
        'domain_ssl' => 'Domain & SSL',
    ];

    public const BUDGET_RANGES = [
        '< 2jt' => 'Di bawah Rp2 juta',
        '2-5jt' => 'Rp2 – 5 juta',
        '5-10jt' => 'Rp5 – 10 juta',
        '10-25jt' => 'Rp10 – 25 juta',
        '> 25jt' => 'Di atas Rp25 juta',
        'diskusi' => 'Belum tahu, ingin diskusi',
    ];

    public const PAGES_ESTIMATES = ['1' => '1 halaman', '2-5' => '2 – 5 halaman', '6-10' => '6 – 10 halaman', '10+' => 'Lebih dari 10 halaman'];

    public const INVOICE_STATUSES = [
        'draft' => ['label' => 'Draf', 'color' => 'slate'],
        'unpaid' => ['label' => 'Belum Dibayar', 'color' => 'amber'],
        'partial' => ['label' => 'Dibayar Sebagian', 'color' => 'sky'],
        'paid' => ['label' => 'Lunas', 'color' => 'emerald'],
        'cancelled' => ['label' => 'Dibatalkan', 'color' => 'rose'],
    ];

    public const TTE_STATUSES = [
        'pending' => ['label' => 'Menunggu Pengesahan', 'color' => 'amber'],
        'approved' => ['label' => 'Sah / Ditandatangani', 'color' => 'emerald'],
        'rejected' => ['label' => 'Ditolak / Revisi', 'color' => 'rose'],
        'revoked' => ['label' => 'Dicabut', 'color' => 'slate'],
    ];

    public const CONFIDENTIALITY = [
        'public' => ['label' => 'Publik', 'color' => 'emerald', 'desc' => 'Boleh dilihat semua pegawai.'],
        'internal' => ['label' => 'Internal', 'color' => 'sky', 'desc' => 'Untuk seluruh Admin & Agen.'],
        'confidential' => ['label' => 'Rahasia', 'color' => 'rose', 'desc' => 'Hanya Admin.'],
    ];

    public const ATTENDANCE_STATUSES = [
        'present' => ['label' => 'Hadir', 'color' => 'emerald'],
        'late' => ['label' => 'Terlambat', 'color' => 'amber'],
        'leave' => ['label' => 'Cuti', 'color' => 'sky'],
        'sick' => ['label' => 'Sakit', 'color' => 'violet'],
        'permit' => ['label' => 'Izin', 'color' => 'fuchsia'],
        'absent' => ['label' => 'Alpa', 'color' => 'rose'],
    ];

    public const WORK_MODES = ['wfo' => 'Kantor (WFO)', 'wfh' => 'Rumah (WFH)', 'field' => 'Lapangan / Klien'];

    public const LEAVE_TYPES = ['leave' => 'Cuti', 'sick' => 'Sakit', 'permit' => 'Izin'];

    public const REQUEST_STATUSES = [
        'pending' => ['label' => 'Menunggu', 'color' => 'amber'],
        'approved' => ['label' => 'Disetujui', 'color' => 'emerald'],
        'rejected' => ['label' => 'Ditolak', 'color' => 'rose'],
    ];

    public const TASK_STATUSES = [
        'todo' => ['label' => 'To Do', 'color' => 'slate'],
        'in_progress' => ['label' => 'Dikerjakan', 'color' => 'sky'],
        'review' => ['label' => 'Review', 'color' => 'amber'],
        'done' => ['label' => 'Selesai', 'color' => 'emerald'],
    ];

    public const TASK_PRIORITIES = [
        'low' => ['label' => 'Rendah', 'color' => 'slate'],
        'medium' => ['label' => 'Sedang', 'color' => 'sky'],
        'high' => ['label' => 'Tinggi', 'color' => 'orange'],
        'urgent' => ['label' => 'Mendesak', 'color' => 'rose'],
    ];

    public const COLORS = ['violet', 'fuchsia', 'pink', 'rose', 'orange', 'amber', 'lime', 'emerald', 'teal', 'cyan', 'sky', 'indigo'];

    /** Ikon (nama komponen @lucide/vue) yang dapat dipilih untuk layanan. */
    public const ICONS = [
        'CodeXml', 'Globe', 'Rocket', 'Sparkles', 'ShoppingBag', 'LayoutDashboard', 'Smartphone', 'Search',
        'Server', 'ShieldCheck', 'Palette', 'PenTool', 'Megaphone', 'Database', 'Cloud', 'Cpu',
        'Wrench', 'Lightbulb', 'Zap', 'Building', 'GraduationCap', 'Newspaper', 'MessageCircle', 'ChartColumn',
    ];

    public static function labels(array $map): array
    {
        $out = [];
        foreach ($map as $k => $v) {
            $out[$k] = is_array($v) ? $v['label'] : $v;
        }

        return $out;
    }

    public static function forFrontend(): array
    {
        return [
            'orderStatuses' => self::ORDER_STATUSES,
            'orderPriorities' => self::ORDER_PRIORITIES,
            'websiteTypes' => self::WEBSITE_TYPES,
            'websiteFeatures' => self::WEBSITE_FEATURES,
            'budgetRanges' => self::BUDGET_RANGES,
            'pagesEstimates' => self::PAGES_ESTIMATES,
            'invoiceStatuses' => self::INVOICE_STATUSES,
            'tteStatuses' => self::TTE_STATUSES,
            'confidentiality' => self::CONFIDENTIALITY,
            'attendanceStatuses' => self::ATTENDANCE_STATUSES,
            'workModes' => self::WORK_MODES,
            'leaveTypes' => self::LEAVE_TYPES,
            'requestStatuses' => self::REQUEST_STATUSES,
            'taskStatuses' => self::TASK_STATUSES,
            'taskPriorities' => self::TASK_PRIORITIES,
            'colors' => self::COLORS,
            'icons' => self::ICONS,
        ];
    }
}
