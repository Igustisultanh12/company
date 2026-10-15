<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Carbon::setLocale('id');

        // Deteksi dini query N+1 & atribut salah ketik saat development.
        Model::preventLazyLoading(! $this->app->isProduction());
        Model::preventSilentlyDiscardingAttributes(! $this->app->isProduction());

        if ($this->app->isProduction() && str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        Password::defaults(fn () => Password::min(8)->letters()->mixedCase()->numbers());

        Vite::prefetch(concurrency: 3);

        RateLimiter::for('public-forms', fn (Request $r) => [
            Limit::perMinute(5)->by('pf:'.$r->ip())->response(fn () => back()->with('error', 'Terlalu banyak permintaan. Coba lagi dalam 1 menit.')),
            Limit::perDay(40)->by('pfd:'.$r->ip()),
        ]);
        RateLimiter::for('tracking', fn (Request $r) => Limit::perMinute(10)->by('trk:'.$r->ip()));
        RateLimiter::for('verify', fn (Request $r) => Limit::perMinute(30)->by('vrf:'.$r->ip()));
        RateLimiter::for('uploads', fn (Request $r) => Limit::perMinute(30)->by('up:'.($r->user()?->id ?: $r->ip())));
        RateLimiter::for('password-reset', fn (Request $r) => Limit::perMinute(3)->by('pr:'.$r->ip()));
    }
}
