<!DOCTYPE html>
<html lang="fr" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title', 'Le Laboratoire Numérique - Développeur Laravel & Ionic')</title>
    <meta name="description" content="@yield('description', 'Développeur Full-Stack spécialisé en Laravel et Ionic. Création d\'applications web et mobiles sur mesure.')">
    <meta name="author" content="Thomas Bourcy">
    <meta name="robots" content="index, follow">

    <!-- Canonical URL -->
    <link rel="canonical" href="@yield('canonical', url()->current())">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:url" content="@yield('canonical', url()->current())">
    <meta property="og:title" content="@yield('title', 'Le Laboratoire Numérique - Développeur Laravel & Ionic')">
    <meta property="og:description" content="@yield('description', 'Développeur Full-Stack spécialisé en Laravel et Ionic. Création d\'applications web et mobiles sur mesure.')">
    <meta property="og:image" content="@yield('og_image', asset('images/og-default.png'))">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:locale" content="fr_FR">
    <meta property="og:site_name" content="Le Laboratoire Numérique">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="@yield('canonical', url()->current())">
    <meta name="twitter:title" content="@yield('title', 'Le Laboratoire Numérique - Développeur Laravel & Ionic')">
    <meta name="twitter:description" content="@yield('description', 'Développeur Full-Stack spécialisé en Laravel et Ionic. Création d\'applications web et mobiles sur mesure.')">
    <meta name="twitter:image" content="@yield('og_image', asset('images/og-default.png'))">

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="icon" type="image/x-icon" href="/favicon.ico">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Styles -->
    @vite('resources/css/app.css')

    @stack('styles')

    <!-- Schema.org Global -->
    <x-schema-organization />
    <x-schema-website />
    @stack('schema')
</head>
<body class="antialiased">
    <!-- Navigation -->
    <x-navigation />

    <!-- Main Content -->
    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    <x-footer />

    <!-- Scripts -->
    @vite('resources/js/app.js')

    @stack('scripts')
</body>
</html>
