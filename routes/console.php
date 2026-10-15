<?php

use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

/*
| php artisan admin:create
| Membuat akun admin baru atau mereset password admin yang sudah ada.
*/
Artisan::command('admin:create {email?} {--name=} {--password=}', function (?string $email = null) {
    $email ??= $this->ask('Email admin');
    if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $this->error('Email tidak valid.');

        return 1;
    }
    $name = $this->option('name') ?: ($this->ask('Nama lengkap', 'Administrator'));
    $password = $this->option('password') ?: Str::password(14, symbols: false);

    $user = User::withTrashed()->firstOrNew(['email' => $email]);
    $user->fill(['name' => $user->exists ? $user->name : $name, 'role' => User::ROLE_ADMIN, 'is_active' => true, 'password' => $password]);
    $user->deleted_at = null;
    $user->email_verified_at ??= now();
    $user->save();

    $this->info(($user->wasRecentlyCreated ? 'Admin dibuat' : 'Admin diperbarui').": {$email}");
    $this->warn("Password: {$password}");
    $this->line('Segera login, ganti password, lalu aktifkan 2FA & PIN TTE.');

    return 0;
})->purpose('Buat / reset akun administrator');
