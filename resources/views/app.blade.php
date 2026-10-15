@php
    $props = $page['props'] ?? [];
    $meta = $props['meta'] ?? [];
    $site = $props['site'] ?? [];
    $title = $meta['title'] ?? ($site['seo_title'] ?? config('app.name'));
    $description = $meta['description'] ?? ($site['seo_description'] ?? '');
    $image = $meta['image'] ?? asset('og-image.png');
    $nonce = \Illuminate\Support\Facades\Vite::cspNonce();
    $isPanel = str_starts_with($page['component'] ?? '', 'Admin/') || str_starts_with($page['component'] ?? '', 'Agent/') || str_starts_with($page['component'] ?? '', 'Panel/');
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title data-inertia="">{{ $title }}</title>
    <meta data-inertia="description" name="description" content="{{ $description }}">
    @if ($isPanel)
        <meta name="robots" content="noindex, nofollow">
    @else
        <meta property="og:type" content="{{ $meta['type'] ?? 'website' }}">
        <meta property="og:site_name" content="{{ $site['name'] ?? config('app.name') }}">
        <meta property="og:title" content="{{ $title }}">
        <meta property="og:description" content="{{ $description }}">
        <meta property="og:url" content="{{ url()->current() }}">
        <meta property="og:image" content="{{ $image }}">
        <meta name="twitter:card" content="summary_large_image">
        <link rel="canonical" href="{{ url()->current() }}">
    @endif
    <meta name="theme-color" content="#7c3aed">
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="alternate icon" href="{{ asset('favicon.ico') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link rel="stylesheet" href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800|space-grotesk:500,600,700&display=swap">
    <script nonce="{{ $nonce }}">
        (function () {
            try {
                var t = localStorage.getItem('ksa-theme') || 'system';
                var dark = t === 'dark' || (t === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
                document.documentElement.classList.toggle('dark', dark);
            } catch (e) {}
        })();
    </script>
    @routes(auth()->check() ? null : 'guest', $nonce)
    @vite(['resources/js/app.js'])
    @inertiaHead
</head>
<body class="min-h-screen bg-surface text-ink antialiased">
    @inertia
    <noscript>
        <div style="padding:24px;font-family:sans-serif;text-align:center">Aktifkan JavaScript untuk menggunakan website {{ $site['name'] ?? config('app.name') }}.</div>
    </noscript>
</body>
</html>
