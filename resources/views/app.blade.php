<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @class(['dark' => ($appearance ?? 'system') == 'dark'])>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#153b27">

        {{-- Inline script to detect system dark mode preference and apply it immediately --}}
        <script>
            (function() {
                const appearance = '{{ $appearance ?? "system" }}';

                if (appearance === 'system') {
                    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

                    if (prefersDark) {
                        document.documentElement.classList.add('dark');
                    }
                }
            })();
        </script>

        {{-- Inline style to set the HTML background color based on our theme in app.css --}}
        <style>
            html {
                background-color: oklch(1 0 0);
            }

            html.dark {
                background-color: oklch(0.145 0 0);
            }
        </style>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon-32.png" type="image/png" sizes="32x32">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        @fonts

        @viteReactRefresh
        @vite(['resources/css/app.css', 'resources/js/app.tsx', "resources/js/pages/{$page['component']}.tsx"])
        <x-inertia::head>
            {{--
                Without SSR these server-side defaults are what link previews (WhatsApp, Facebook) and
                the first crawl see. The data-inertia attribute lets each page's <Head> replace them in
                the browser. With SSR on, the page's own tags are rendered here instead.
            --}}
            @php
                $siteName = 'Desa Wisata Pasirnanjung Geulis';
                $siteDescription = app()->getLocale() === 'en'
                    ? 'Pasirnanjung Geulis Tourism Village: destinations, facility rental, events and local products made by villagers.'
                    : 'Desa Wisata Pasirnanjung Geulis: destinasi, sewa fasilitas, event, dan produk UMKM karya warga.';
                $siteImage = rescue(
                    fn () => app(\App\Domain\Content\Services\SiteSettings::class)->imageUrl(\App\Domain\Content\Models\SiteSetting::HERO_IMAGE),
                    null,
                    false,
                ) ?? (is_file(public_path('images/landing/about.jpg')) ? asset('images/landing/about.jpg') : asset('images/brand/icon-512.png'));
            @endphp
            <title data-inertia>{{ $siteName }}</title>
            <meta data-inertia name="description" content="{{ $siteDescription }}">
            <meta data-inertia property="og:type" content="website">
            <meta data-inertia property="og:site_name" content="{{ $siteName }}">
            <meta data-inertia property="og:title" content="{{ $siteName }}">
            <meta data-inertia property="og:description" content="{{ $siteDescription }}">
            <meta data-inertia property="og:url" content="{{ url()->current() }}">
            <meta data-inertia property="og:image" content="{{ $siteImage }}">
            <meta data-inertia name="twitter:card" content="summary_large_image">
        </x-inertia::head>
    </head>
    <body class="font-sans antialiased">
        <x-inertia::app />
    </body>
</html>
