<!DOCTYPE html>
<html lang="fr" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title', 'Le Laboratoire Numérique - Développeur Laravel & Ionic')</title>
    <meta name="description" content="@yield('description', 'Développeur Full-Stack spécialisé en Laravel et Ionic. Création d\'applications web et mobiles sur mesure.')">

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/favicon.ico">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Styles -->
    @vite('resources/css/app.css')

    @stack('styles')
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
