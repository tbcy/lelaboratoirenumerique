@extends('layouts.app')

@section('title', 'Accueil - Le Laboratoire Numérique')
@section('description', 'Développeur Full-Stack spécialisé en Laravel et Ionic. Création d\'applications web et mobiles innovantes : Youplago, Batibid et plus encore.')

@section('content')

<!-- Hero Section -->
<section class="gradient-primary py-20 md:py-32 overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Hero Content with Image -->
        <div class="grid lg:grid-cols-2 gap-12 items-center mb-16">
            <!-- Text Content -->
            <div class="text-center lg:text-left text-white">
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-white mb-6 leading-tight">
                    Transformez vos idées en <span class="text-accent-300">applications</span> performantes
                </h1>
                <p class="text-xl md:text-2xl text-white/90 mb-10">
                    Développeur Full-Stack spécialisé en <strong>Laravel</strong> et <strong>Ionic</strong>.
                    Je conçois et développe des solutions web et mobiles sur mesure pour concrétiser vos projets.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                    <a href="{{ route('projects') }}" class="inline-flex items-center justify-center gap-2 px-8 py-4 bg-white text-primary-600 font-semibold rounded-lg hover:bg-neutral-50 transition-all shadow-lg hover:shadow-xl">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                        Voir mes projets
                    </a>
                    <a href="{{ route('contact') }}" class="inline-flex items-center justify-center gap-2 px-8 py-4 border-2 border-white text-white font-semibold rounded-lg hover:bg-white/10 transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        Me contacter
                    </a>
                </div>
            </div>
            <!-- Hero Image -->
            <div class="hidden lg:block">
                <img src="{{ asset('images/hero-workspace.png') }}?v=2" alt="Espace de travail développeur futuriste" class="w-[500px] h-auto mx-auto rounded-3xl drop-shadow-2xl animate-fade-in-up">
            </div>
        </div>

        <!-- Stats -->
        <div class="max-w-5xl mx-auto">
            <div class="flex flex-wrap justify-center gap-x-12 gap-y-8">
                <div class="text-center min-w-[150px]">
                    <div class="text-5xl font-bold text-white mb-2">5+</div>
                    <div class="text-white/90">Années d'expérience</div>
                </div>
                <div class="text-center min-w-[150px]">
                    <div class="text-5xl font-bold text-white mb-2">20+</div>
                    <div class="text-white/90">Projets réalisés</div>
                </div>
                <div class="text-center min-w-[150px]">
                    <div class="text-5xl font-bold text-white mb-2">100%</div>
                    <div class="text-white/90">Clients satisfaits</div>
                </div>
                <div class="text-center min-w-[150px]">
                    <div class="text-5xl font-bold text-white mb-2">24/7</div>
                    <div class="text-white/90">Support dédié</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Projets Phares -->
<section class="section bg-white">
    <div class="container-custom">
        <div class="mb-12">
            <h2 class="heading-2 mb-4 text-center">Projets Phares</h2>
            <p class="text-lead text-center">
                Découvrez quelques-unes de mes réalisations les plus abouties
            </p>
        </div>

        <div class="grid md:grid-cols-3 gap-8">
            <!-- Youplago -->
            <div class="card hover-lift hover-glow group">
                <!-- Mockup Image -->
                <div class="mb-4 overflow-hidden rounded-lg bg-neutral-900">
                    <img src="{{ asset('images/projects/youplago-mockup.png') }}" alt="Youplago - Application mobile événementielle" class="w-full h-48 object-cover group-hover:scale-105 transition-transform duration-300">
                </div>
                <div class="flex items-start gap-4 mb-4">
                    <div class="w-12 h-12 rounded-xl gradient-primary flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="heading-4 mb-2">Youplago</h3>
                        <div class="flex flex-wrap gap-2">
                            <span class="badge badge-primary">Ionic</span>
                            <span class="badge badge-secondary">Laravel</span>
                        </div>
                    </div>
                </div>
                <p class="text-neutral-600 mb-4 leading-relaxed">
                    Application mobile pour l'organisation d'événements entre amis. Gestion d'activités, budgets, chat en temps réel.
                </p>
                <div class="flex items-center gap-3">
                    <a href="https://app.youplago.com" target="_blank" rel="noopener" class="btn btn-outline flex-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                        </svg>
                        Voir
                    </a>
                    <a href="{{ route('projects') }}#youplago" class="btn btn-ghost">
                        Plus
                    </a>
                </div>
            </div>

            <!-- Olympiadus -->
            <div class="card hover-lift hover-glow group">
                <!-- Mockup Image -->
                <div class="mb-4 overflow-hidden rounded-lg bg-neutral-900">
                    <img src="{{ asset('images/projects/olympiadus-mockup.png') }}" alt="Olympiadus - Plateforme de compétitions sportives" class="w-full h-48 object-cover group-hover:scale-105 transition-transform duration-300">
                </div>
                <div class="flex items-start gap-4 mb-4">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-600 to-violet-600 flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="heading-4 mb-2">Olympiadus</h3>
                        <div class="flex flex-wrap gap-2">
                            <span class="badge badge-primary">Mobile</span>
                            <span class="badge" style="background-color: rgb(238 242 255); color: rgb(67 56 202);">Sport</span>
                        </div>
                    </div>
                </div>
                <p class="text-neutral-600 mb-4 leading-relaxed">
                    Plateforme pour organiser des olympiades et compétitions sportives. Gestion d'équipes, QR codes, classements en temps réel.
                </p>
                <div class="flex items-center gap-3">
                    <a href="https://olympiadus.lelaboratoirenumerique.com/fr" target="_blank" rel="noopener" class="btn btn-outline flex-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                        </svg>
                        Voir
                    </a>
                    <a href="{{ route('projects') }}#olympiadus" class="btn btn-ghost">
                        Plus
                    </a>
                </div>
            </div>

            <!-- Batibid -->
            <div class="card hover-lift hover-glow group">
                <!-- Mockup Image -->
                <div class="mb-4 overflow-hidden rounded-lg bg-neutral-900">
                    <img src="{{ asset('images/projects/batibid-mockup.png') }}" alt="Batibid - Plateforme immobilière" class="w-full h-48 object-cover group-hover:scale-105 transition-transform duration-300">
                </div>
                <div class="flex items-start gap-4 mb-4">
                    <div class="w-12 h-12 rounded-xl gradient-accent flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="heading-4 mb-2">Batibid</h3>
                        <div class="flex flex-wrap gap-2">
                            <span class="badge badge-primary">Laravel</span>
                            <span class="badge badge-warning">Immobilier</span>
                        </div>
                    </div>
                </div>
                <p class="text-neutral-600 mb-4 leading-relaxed">
                    Plateforme immobilière complète. Back-office de gestion des annonces et système de facturation automatique.
                </p>
                <div class="flex items-center gap-3">
                    <a href="https://app.batibid.com" target="_blank" rel="noopener" class="btn btn-outline flex-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                        </svg>
                        Voir
                    </a>
                    <a href="{{ route('projects') }}#batibid" class="btn btn-ghost">
                        Plus
                    </a>
                </div>
            </div>
        </div>

        <div class="text-center mt-12">
            <a href="{{ route('projects') }}" class="btn btn-primary hover-glow">
                Découvrir tous mes projets
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                </svg>
            </a>
        </div>
    </div>
</section>

<!-- Compétences -->
<section class="section bg-neutral-50">
    <div class="container-custom">
        <div class="mb-12">
            <h2 class="heading-2 mb-4 text-center">Expertise Technique</h2>
            <p class="text-lead text-center">
                Des technologies modernes pour des solutions performantes
            </p>
        </div>

        <div class="grid md:grid-cols-3 gap-6">
            <!-- Laravel -->
            <div class="card-bordered text-center hover-lift">
                <div class="w-32 h-32 mx-auto mb-4 overflow-hidden rounded-xl">
                    <img src="{{ asset('images/icons/laravel-backend.png') }}" alt="Développement Laravel Backend" class="w-full h-full object-cover">
                </div>
                <h3 class="heading-4 mb-2">Laravel</h3>
                <p class="text-neutral-600">
                    Framework PHP pour applications web robustes, APIs RESTful et back-offices performants
                </p>
            </div>

            <!-- Ionic -->
            <div class="card-bordered text-center hover-lift">
                <div class="w-32 h-32 mx-auto mb-4 overflow-hidden rounded-xl">
                    <img src="{{ asset('images/icons/ionic-mobile.png') }}" alt="Développement Mobile Ionic" class="w-full h-full object-cover">
                </div>
                <h3 class="heading-4 mb-2">Ionic</h3>
                <p class="text-neutral-600">
                    Développement d'applications mobiles hybrides iOS et Android avec une base de code unique
                </p>
            </div>

            <!-- Full-Stack -->
            <div class="card-bordered text-center hover-lift">
                <div class="w-32 h-32 mx-auto mb-4 overflow-hidden rounded-xl">
                    <img src="{{ asset('images/icons/fullstack.png') }}" alt="Développement Full-Stack" class="w-full h-full object-cover">
                </div>
                <h3 class="heading-4 mb-2">Full-Stack</h3>
                <p class="text-neutral-600">
                    Maîtrise complète du développement front-end et back-end pour des solutions intégrées
                </p>
            </div>
        </div>

        <div class="text-center mt-12">
            <a href="{{ route('services') }}" class="btn btn-outline">
                Voir tous mes services
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                </svg>
            </a>
        </div>
    </div>
</section>

<!-- CTA Final -->
<section class="section gradient-primary">
    <div class="container-custom text-white">
        <h2 class="heading-2 text-white mb-4 text-center">Prêt à démarrer votre projet ?</h2>
        <p class="text-lg md:text-xl text-white/90 mb-8 text-center leading-relaxed">
            Discutons de vos besoins et transformons vos idées en réalité.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('contact') }}" class="btn bg-white text-primary-600 hover:bg-neutral-50 hover-glow">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
                Me contacter
            </a>
            <a href="{{ route('about') }}" class="btn border-2 border-white text-white hover:bg-white/10">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                En savoir plus sur moi
            </a>
        </div>
    </div>
</section>

@endsection
