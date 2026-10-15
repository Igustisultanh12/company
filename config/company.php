<?php

/*
|--------------------------------------------------------------------------
| Data default perusahaan
|--------------------------------------------------------------------------
| Diambil dari dokumen legalitas (SK Kemenkum AHU, Sertifikat Standar OSS,
| SPPL & Surat Pernyataan Perubahan). Nilai ini hanya dipakai saat seeding;
| setelahnya semua dapat diubah lewat menu Admin > Pengaturan.
| Catatan privasi: NIK, NPWP pribadi & tanggal lahir pemilik sengaja TIDAK
| dimasukkan agar tidak pernah tampil di website publik.
*/

return [
    'defaults' => [
        'company_name' => 'PT Kreatif Sky Abadi',
        'company_short_name' => 'Kreatif Sky',
        'company_legal_type' => 'Perseroan Perorangan',
        'company_tagline' => 'Wujudkan Ide Digitalmu Jadi Nyata',
        'company_description' => 'PT Kreatif Sky Abadi adalah perusahaan teknologi asal Jember, Jawa Timur, yang bergerak di bidang pemrograman komputer, portal web & informasi, serta jasa teknologi informasi. Kami membantu UMKM, sekolah, instansi, dan brand anak muda tampil keren dan bekerja lebih efisien lewat website & sistem digital yang cepat, aman, dan mudah dikelola.',
        'company_vision' => 'Menjadi mitra teknologi kreatif terpercaya yang mendorong transformasi digital UMKM dan instansi di Indonesia.',
        'company_mission' => "Menghadirkan website & aplikasi berkualitas dengan harga terjangkau\nMengutamakan keamanan data dan sistem elektronik\nMemberikan layanan yang ramah, transparan, dan tepat waktu\nTerus berinovasi mengikuti perkembangan teknologi",
        'founder_name' => 'I Gusti Sultan Hajjarul Aswat',
        'founder_position' => 'Direktur & Pendiri',
        'address' => 'Jalan Argopuro No.77 RT.001 RW.021 Krajan, Desa Mayang, Kec. Mayang, Kabupaten Jember, Jawa Timur 68182',
        'city' => 'Jember, Jawa Timur',
        'email' => 'igustisultan@gmail.com',
        'phone' => '',
        'whatsapp' => '',
        'office_hours' => 'Senin – Jumat, 08.00 – 16.00 WIB',
        'maps_embed_url' => 'https://www.google.com/maps?q=Mayang,+Jember,+Jawa+Timur&output=embed',
        'instagram' => '',
        'tiktok' => '',
        'linkedin' => '',
        'github' => '',
        'youtube' => '',

        // Legalitas
        'legal_sk_number' => 'AHU-A012478.AH.01.31.Tahun 2026',
        'legal_sk_date' => '2026-09-01',
        'legal_sk_title' => 'Keputusan Menteri Hukum RI tentang Perubahan Badan Hukum Perseroan Perorangan KREATIF SKY ABADI',
        'legal_nib' => '0109260072761',
        'legal_certificate_number' => '01092600727610001',
        'legal_certificate_date' => '2026-09-01',
        'legal_risk_level' => 'Menengah Rendah',
        'legal_sppl_status' => 'SPPL – Terbit (1 September 2026)',
        'legal_kbli' => json_encode([
            ['code' => '62199', 'title' => 'Aktivitas Pemrograman Komputer Lainnya YTDL'],
            ['code' => '63900', 'title' => 'Aktivitas Jasa Portal Pencarian Web dan Informasi Lainnya'],
            ['code' => '62900', 'title' => 'Aktivitas Jasa Teknologi Informasi dan Komputer Lainnya'],
        ]),

        // SEO
        'seo_title' => 'PT Kreatif Sky Abadi – Jasa Pembuatan Website & Aplikasi di Jember',
        'seo_description' => 'Jasa pembuatan website company profile, toko online, landing page & sistem informasi custom. Berbadan hukum resmi (NIB 0109260072761), desain kekinian, aman & bergaransi.',
        'seo_keywords' => 'jasa pembuatan website, jasa website jember, company profile, toko online, sistem informasi, aplikasi web, PT Kreatif Sky Abadi',

        // Statistik tampilan beranda (dapat diubah admin)
        'stat_projects' => '0',
        'stat_clients' => '0',
        'stat_years' => '1',
        'stat_satisfaction' => '100',

        // Absensi
        'attendance_start' => '08:00',
        'attendance_end' => '16:00',
        'attendance_late_tolerance' => '15',
        'attendance_require_location' => '0',
        'attendance_office_lat' => '-8.1890',
        'attendance_office_lng' => '113.8093',
        'attendance_radius_m' => '0',

        // Invoice
        'invoice_prefix' => 'INV',
        'invoice_bank_name' => '',
        'invoice_bank_account' => '',
        'invoice_bank_holder' => '',
        'invoice_footer' => 'Terima kasih telah mempercayakan proyek digital Anda kepada kami.',
        'invoice_tax_percent' => '0',

        // TTE
        'tte_signer_name' => 'I Gusti Sultan Hajjarul Aswat',
        'tte_signer_position' => 'Direktur PT Kreatif Sky Abadi',
        'tte_caption_enabled' => '1',
        'tte_caption_text' => 'Ditandatangani secara elektronik',
        'tte_stamp_path' => '',
        'tte_qr_color' => '#111827',

        // Notifikasi
        'notify_email_new_order' => '0',
        'force_admin_2fa' => '0',
    ],

    /*
    | Kewajiban perizinan dari Lampiran Sertifikat Standar & SPPL.
    | Dokumen arsip yang diberi tag "compliance:<key>" menandai kewajiban terpenuhi.
    */
    'compliance' => [
        ['key' => 'data_industri', 'title' => 'Laporan Data Industri (tiap 6 bulan)', 'source' => 'Sertifikat Standar KBLI 62199', 'period_months' => 6],
        ['key' => 'sertifikat_kompetensi', 'title' => 'Sertifikat pelatihan kompetensi SDM penyedia jasa', 'source' => 'Sertifikat Standar KBLI 62199', 'period_months' => null],
        ['key' => 'struktur_organisasi', 'title' => 'Dokumen struktur organisasi & ketersediaan SDM berkompetensi teknis', 'source' => 'Sertifikat Standar KBLI 62199', 'period_months' => null],
        ['key' => 'sop_keamanan', 'title' => 'SOP keamanan data & sistem elektronik', 'source' => 'Sertifikat Standar KBLI 62199', 'period_months' => null],
        ['key' => 'sppl', 'title' => 'Pemenuhan komitmen SPPL (pengelolaan lingkungan)', 'source' => 'SPPL Amdalnet', 'period_months' => null],
    ],
];
