<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Agent;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Panel;
use App\Http\Controllers\Site;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Website publik (company profile)
|--------------------------------------------------------------------------
*/
Route::controller(Site\PageController::class)->group(function () {
    Route::get('/', 'home')->name('home');
    Route::get('/tentang', 'about')->name('about');
    Route::get('/layanan', 'services')->name('services.index');
    Route::get('/layanan/{service:slug}', 'service')->name('services.show');
    Route::get('/harga', 'pricing')->name('pricing');
    Route::get('/portofolio', 'portfolios')->name('portfolio.index');
    Route::get('/portofolio/{portfolio:slug}', 'portfolio')->name('portfolio.show');
    Route::get('/blog', 'blog')->name('blog.index');
    Route::get('/blog/{post:slug}', 'post')->name('blog.show');
    Route::get('/kontak', 'contact')->name('contact');
});

Route::post('/kontak', [Site\ContactController::class, 'store'])->middleware('throttle:public-forms')->name('contact.store');

Route::get('/request-website', [Site\OrderRequestController::class, 'create'])->name('order.create');
Route::post('/request-website', [Site\OrderRequestController::class, 'store'])->middleware('throttle:public-forms')->name('order.store');
Route::get('/request-website/terkirim', [Site\OrderRequestController::class, 'success'])->name('order.success');

Route::get('/lacak', [Site\TrackController::class, 'index'])->name('track');
Route::post('/lacak', [Site\TrackController::class, 'lookup'])->middleware('throttle:tracking')->name('track.lookup');
Route::get('/lacak/{code}', [Site\TrackController::class, 'show'])->name('track.show');

Route::get('/verifikasi', [Site\VerifyController::class, 'index'])->name('verify.index');
Route::post('/verifikasi', [Site\VerifyController::class, 'lookup'])->middleware('throttle:verify')->name('verify.lookup');
Route::post('/verifikasi/cek-berkas', [Site\VerifyController::class, 'checkFile'])->middleware('throttle:verify')->name('verify.file');
Route::get('/verifikasi/{code}', [Site\VerifyController::class, 'show'])->middleware('throttle:verify')->name('verify.show');

Route::get('/invoice/{invoice}/lihat', [Site\PublicInvoiceController::class, 'show'])->middleware('signed')->name('invoice.public');
Route::get('/sitemap.xml', Site\SitemapController::class)->name('sitemap');

/*
|--------------------------------------------------------------------------
| Autentikasi
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/masuk', [AuthController::class, 'create'])->name('login');
    Route::post('/masuk', [AuthController::class, 'store'])->name('login.attempt');
    Route::get('/masuk/2fa', [AuthController::class, 'twoFactor'])->name('login.2fa');
    Route::post('/masuk/2fa', [AuthController::class, 'twoFactorVerify'])->name('login.2fa.verify');

    Route::get('/lupa-password', [PasswordResetController::class, 'request'])->name('password.request');
    Route::post('/lupa-password', [PasswordResetController::class, 'email'])->middleware('throttle:password-reset')->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'reset'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'update'])->middleware('throttle:password-reset')->name('password.store');
});

Route::post('/keluar', [AuthController::class, 'destroy'])->middleware('auth')->name('logout');

/*
|--------------------------------------------------------------------------
| Area internal (Admin & Agen)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'active', '2fa.admin'])->group(function () {
    Route::get('/dashboard', Panel\HomeRedirectController::class)->name('dashboard');

    // ---------- Modul bersama ----------
    Route::prefix('panel')->name('panel.')->group(function () {
        Route::get('/profil', [Panel\ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profil', [Panel\ProfileController::class, 'update'])->name('profile.update');
        Route::put('/profil/password', [Panel\ProfileController::class, 'password'])->name('profile.password');
        Route::post('/profil/avatar', [Panel\ProfileController::class, 'avatar'])->middleware('throttle:uploads')->name('profile.avatar');
        Route::post('/profil/2fa', [Panel\TwoFactorController::class, 'enable'])->name('profile.2fa.enable');
        Route::post('/profil/2fa/konfirmasi', [Panel\TwoFactorController::class, 'confirm'])->name('profile.2fa.confirm');
        Route::post('/profil/2fa/kode-pemulihan', [Panel\TwoFactorController::class, 'recovery'])->name('profile.2fa.recovery');
        Route::delete('/profil/2fa', [Panel\TwoFactorController::class, 'disable'])->name('profile.2fa.disable');
        Route::delete('/profil/sesi/{session}', [Panel\ProfileController::class, 'destroySession'])->name('profile.sessions.destroy');
        Route::delete('/profil/sesi', [Panel\ProfileController::class, 'destroyOtherSessions'])->name('profile.sessions.others');

        Route::get('/notifikasi', [Panel\NotificationController::class, 'index'])->name('notifications.index');
        Route::get('/notifikasi/terbaru', [Panel\NotificationController::class, 'latest'])->name('notifications.latest');
        Route::post('/notifikasi/baca-semua', [Panel\NotificationController::class, 'readAll'])->name('notifications.read-all');
        Route::post('/notifikasi/{notification}/baca', [Panel\NotificationController::class, 'read'])->name('notifications.read');

        Route::get('/pengumuman', [Panel\AnnouncementController::class, 'index'])->name('announcements.index');

        // Tugas (Kanban)
        Route::get('/tugas', [Panel\TaskController::class, 'index'])->name('tasks.index');
        Route::post('/tugas', [Panel\TaskController::class, 'store'])->name('tasks.store');
        Route::put('/tugas/{task}', [Panel\TaskController::class, 'update'])->name('tasks.update');
        Route::patch('/tugas/{task}/pindah', [Panel\TaskController::class, 'move'])->name('tasks.move');
        Route::delete('/tugas/{task}', [Panel\TaskController::class, 'destroy'])->name('tasks.destroy');
        Route::get('/tugas/{task}', [Panel\TaskController::class, 'show'])->name('tasks.show');
        Route::post('/tugas/{task}/komentar', [Panel\TaskController::class, 'comment'])->name('tasks.comment');

        // Arsip dokumen
        Route::get('/arsip', [Panel\DocumentController::class, 'index'])->name('documents.index');
        Route::get('/arsip/{document}/berkas/{mode?}', [Panel\DocumentController::class, 'file'])->whereIn('mode', ['view', 'download'])->name('documents.file');

        // TTE
        Route::get('/tte', [Panel\SignatureController::class, 'index'])->name('tte.index');
        Route::get('/tte/ajukan', [Panel\SignatureController::class, 'create'])->name('tte.create');
        Route::post('/tte', [Panel\SignatureController::class, 'store'])->middleware('throttle:uploads')->name('tte.store');
        Route::get('/tte/{signatureRequest}', [Panel\SignatureController::class, 'show'])->name('tte.show');
        Route::get('/tte/{signatureRequest}/revisi', [Panel\SignatureController::class, 'edit'])->name('tte.edit');
        Route::post('/tte/{signatureRequest}/revisi', [Panel\SignatureController::class, 'update'])->middleware('throttle:uploads')->name('tte.update');
        Route::delete('/tte/{signatureRequest}', [Panel\SignatureController::class, 'destroy'])->name('tte.destroy');
        Route::get('/tte/{signatureRequest}/berkas/{variant}', [Panel\SignatureController::class, 'file'])->whereIn('variant', ['original', 'signed', 'preview'])->name('tte.file');

        Route::get('/cuti/{leave}/lampiran', [Panel\LeaveFileController::class, '__invoke'])->name('leaves.file');
    });

    // ---------- Admin ----------
    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {
        Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');

        Route::get('/pesanan', [Admin\OrderController::class, 'index'])->name('orders.index');
        Route::post('/pesanan', [Admin\OrderController::class, 'store'])->name('orders.store');
        Route::get('/pesanan/ekspor', [Admin\OrderController::class, 'export'])->name('orders.export');
        Route::get('/pesanan/{order}', [Admin\OrderController::class, 'show'])->name('orders.show');
        Route::put('/pesanan/{order}', [Admin\OrderController::class, 'update'])->name('orders.update');
        Route::post('/pesanan/{order}/aktivitas', [Admin\OrderController::class, 'activity'])->name('orders.activity');
        Route::delete('/pesanan/{order}', [Admin\OrderController::class, 'destroy'])->name('orders.destroy');

        Route::get('/invoice', [Admin\InvoiceController::class, 'index'])->name('invoices.index');
        Route::get('/invoice/buat', [Admin\InvoiceController::class, 'create'])->name('invoices.create');
        Route::post('/invoice', [Admin\InvoiceController::class, 'store'])->name('invoices.store');
        Route::get('/invoice/{invoice}', [Admin\InvoiceController::class, 'show'])->name('invoices.show');
        Route::get('/invoice/{invoice}/ubah', [Admin\InvoiceController::class, 'edit'])->name('invoices.edit');
        Route::put('/invoice/{invoice}', [Admin\InvoiceController::class, 'update'])->name('invoices.update');
        Route::delete('/invoice/{invoice}', [Admin\InvoiceController::class, 'destroy'])->name('invoices.destroy');
        Route::get('/invoice/{invoice}/cetak', [Admin\InvoiceController::class, 'print'])->name('invoices.print');
        Route::post('/invoice/{invoice}/pembayaran', [Admin\InvoiceController::class, 'addPayment'])->name('invoices.payments.store');
        Route::delete('/pembayaran/{payment}', [Admin\InvoiceController::class, 'deletePayment'])->name('invoices.payments.destroy');

        Route::get('/konten/{resource}', [Admin\CmsController::class, 'index'])->name('cms.index');
        Route::post('/konten/{resource}', [Admin\CmsController::class, 'store'])->middleware('throttle:uploads')->name('cms.store');
        Route::post('/konten/{resource}/{id}', [Admin\CmsController::class, 'update'])->middleware('throttle:uploads')->name('cms.update');
        Route::patch('/konten/{resource}/{id}/toggle', [Admin\CmsController::class, 'toggle'])->name('cms.toggle');
        Route::delete('/konten/{resource}/{id}', [Admin\CmsController::class, 'destroy'])->name('cms.destroy');

        Route::get('/pesan-masuk', [Admin\MessageController::class, 'index'])->name('messages.index');
        Route::patch('/pesan-masuk/{message}', [Admin\MessageController::class, 'read'])->name('messages.read');
        Route::delete('/pesan-masuk/{message}', [Admin\MessageController::class, 'destroy'])->name('messages.destroy');

        Route::post('/arsip', [Admin\DocumentController::class, 'store'])->middleware('throttle:uploads')->name('documents.store');
        Route::post('/arsip/{document}', [Admin\DocumentController::class, 'update'])->name('documents.update');
        Route::delete('/arsip/{document}', [Admin\DocumentController::class, 'destroy'])->name('documents.destroy');
        Route::post('/arsip-kategori', [Admin\DocumentController::class, 'storeCategory'])->name('documents.categories.store');
        Route::put('/arsip-kategori/{category}', [Admin\DocumentController::class, 'updateCategory'])->name('documents.categories.update');
        Route::delete('/arsip-kategori/{category}', [Admin\DocumentController::class, 'destroyCategory'])->name('documents.categories.destroy');
        Route::get('/legalitas', [Admin\ComplianceController::class, 'index'])->name('compliance.index');

        Route::post('/tte/{signatureRequest}/sahkan', [Admin\SignatureApprovalController::class, 'approve'])->name('tte.approve');
        Route::post('/tte/{signatureRequest}/tolak', [Admin\SignatureApprovalController::class, 'reject'])->name('tte.reject');
        Route::post('/tte/{signatureRequest}/cabut', [Admin\SignatureApprovalController::class, 'revoke'])->name('tte.revoke');
        Route::get('/tte-pengaturan', [Admin\SignatureSettingController::class, 'edit'])->name('tte.settings');
        Route::post('/tte-pengaturan', [Admin\SignatureSettingController::class, 'update'])->name('tte.settings.update');
        Route::put('/tte-pengaturan/pin', [Admin\SignatureSettingController::class, 'pin'])->name('tte.settings.pin');
        Route::get('/tte-pengaturan/stempel', [Admin\SignatureSettingController::class, 'stamp'])->name('tte.settings.stamp');

        Route::get('/pengguna', [Admin\UserController::class, 'index'])->name('users.index');
        Route::post('/pengguna', [Admin\UserController::class, 'store'])->name('users.store');
        Route::put('/pengguna/{user}', [Admin\UserController::class, 'update'])->name('users.update');
        Route::patch('/pengguna/{user}/status', [Admin\UserController::class, 'toggle'])->name('users.toggle');
        Route::delete('/pengguna/{user}/2fa', [Admin\UserController::class, 'resetTwoFactor'])->name('users.reset-2fa');
        Route::delete('/pengguna/{user}', [Admin\UserController::class, 'destroy'])->name('users.destroy');

        Route::get('/absensi', [Admin\AttendanceController::class, 'index'])->name('attendance.index');
        Route::get('/absensi/ekspor', [Admin\AttendanceController::class, 'export'])->name('attendance.export');
        Route::put('/absensi', [Admin\AttendanceController::class, 'upsert'])->name('attendance.upsert');

        Route::get('/cuti', [Admin\LeaveController::class, 'index'])->name('leaves.index');
        Route::put('/cuti/{leave}', [Admin\LeaveController::class, 'review'])->name('leaves.review');

        Route::get('/laporan-harian', [Admin\DailyReportController::class, 'index'])->name('reports.index');
        Route::get('/log-aktivitas', [Admin\AuditLogController::class, 'index'])->name('audit.index');

        Route::get('/pengaturan', [Admin\SettingController::class, 'index'])->name('settings.index');
        Route::put('/pengaturan', [Admin\SettingController::class, 'update'])->name('settings.update');
        Route::post('/pengaturan/cache', [Admin\SettingController::class, 'clearCache'])->name('settings.cache');
    });

    // ---------- Agen ----------
    Route::prefix('agen')->name('agent.')->middleware('role:agent')->group(function () {
        Route::get('/', [Agent\DashboardController::class, 'index'])->name('dashboard');

        Route::get('/absensi', [Agent\AttendanceController::class, 'index'])->name('attendance.index');
        Route::post('/absensi/masuk', [Agent\AttendanceController::class, 'checkIn'])->name('attendance.check-in');
        Route::post('/absensi/pulang', [Agent\AttendanceController::class, 'checkOut'])->name('attendance.check-out');

        Route::get('/cuti', [Agent\LeaveController::class, 'index'])->name('leaves.index');
        Route::post('/cuti', [Agent\LeaveController::class, 'store'])->middleware('throttle:uploads')->name('leaves.store');
        Route::delete('/cuti/{leave}', [Agent\LeaveController::class, 'destroy'])->name('leaves.destroy');

        Route::get('/laporan-harian', [Agent\DailyReportController::class, 'index'])->name('reports.index');
        Route::post('/laporan-harian', [Agent\DailyReportController::class, 'store'])->name('reports.store');
        Route::delete('/laporan-harian/{report}', [Agent\DailyReportController::class, 'destroy'])->name('reports.destroy');

        Route::get('/pesanan', [Agent\OrderController::class, 'index'])->name('orders.index');
        Route::get('/pesanan/{order}', [Agent\OrderController::class, 'show'])->name('orders.show');
        Route::post('/pesanan/{order}/progres', [Agent\OrderController::class, 'progress'])->name('orders.progress');
    });
});
