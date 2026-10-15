<?php

use App\Http\Middleware\EnsureAccountActive;
use App\Http\Middleware\EnsureRole;
use App\Http\Middleware\EnsureTwoFactorForAdmins;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Percayai reverse proxy (Cloudflare Tunnel / Nginx aaPanel) agar Laravel
        // tahu request aslinya HTTPS dan membuat URL https://, serta membaca IP
        // pengunjung asli. Symfony hanya mempercayai REMOTE_ADDR langsung, jadi
        // header X-Forwarded-For palsu dari klien tidak bisa menyamar IP lain.
        $middleware->trustProxies(at: '*');

        $middleware->web(append: [
            SecurityHeaders::class,
            HandleInertiaRequests::class,
        ]);

        $middleware->alias([
            'role' => EnsureRole::class,
            'active' => EnsureAccountActive::class,
            '2fa.admin' => EnsureTwoFactorForAdmins::class,
        ]);

        $middleware->redirectGuestsTo(fn () => route('login'));
        $middleware->redirectUsersTo(fn (Request $request) => route($request->user()?->dashboardRoute() ?? 'home'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || ($request->expectsJson() && ! $request->header('X-Inertia')),
        );

        // Halaman error bergaya (404, 403, 500, 503, 419) untuk produksi.
        $exceptions->respond(function (Response $response, Throwable $e, Request $request) {
            $status = $response->getStatusCode();

            if ($status === 419) {
                return back()->with('error', 'Sesi Anda telah berakhir, silakan coba lagi.');
            }

            if (in_array($status, [403, 404, 429, 500, 503], true) && ! $request->expectsJson()
                && (! app()->hasDebugModeEnabled() || in_array($status, [403, 404, 429], true))) {
                // Jika halaman error Inertia sendiri gagal (mis. aset Vite belum di-build
                // atau database belum siap), kembalikan respons bawaan Laravel agar
                // tidak berakhir sebagai layar 500 kosong.
                try {
                    return Inertia::render('Error', ['status' => $status, 'message' => $status === 403 ? $e->getMessage() : null])
                        ->toResponse($request)
                        ->setStatusCode($status);
                } catch (Throwable $renderError) {
                    report($renderError);

                    return $response;
                }
            }

            return $response;
        });
    })->create();
