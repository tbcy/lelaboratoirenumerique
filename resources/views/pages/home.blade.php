@extends('layouts.app')

@section('title', 'Accueil - Le Laboratoire Numérique')
@section('description', 'Développeur Full-Stack spécialisé en Laravel et Ionic. Création d\'applications web et mobiles innovantes : Youplago, Batibid et plus encore.')

@section('content')

<!-- Hero Section -->
<section class="gradient-primary py-20 md:py-32">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Hero Content -->
        <div class="text-center text-white mb-16">
            <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-white mb-6 leading-tight">
                Transformez vos idées en <span class="text-accent-300">applications</span> performantes
            </h1>
            <p class="text-xl md:text-2xl text-white/90 mb-10">
                Développeur Full-Stack spécialisé en <strong>Laravel</strong> et <strong>Ionic</strong>.
                Je conçois et développe des solutions web et mobiles sur mesure pour concrétiser vos projets.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
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

        <div class="grid md:grid-cols-2 gap-8">
            <!-- Youplago -->
            <div class="card hover-lift hover-glow">
                <div class="flex items-start gap-4 mb-4">
                    <div class="w-16 h-16 rounded-xl gradient-primary flex items-center justify-center flex-shrink-0">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="heading-4 mb-2">Youplago</h3>
                        <div class="flex flex-wrap gap-2 mb-3">
                            <span class="badge badge-primary">Ionic</span>
                            <span class="badge badge-secondary">Laravel</span>
                            <span class="badge badge-success">Mobile</span>
                        </div>
                    </div>
                </div>
                <p class="text-neutral-600 mb-4 leading-relaxed">
                    Application mobile et hybride pour l'organisation d'événements entre amis et professionnels.
                    Gestion d'activités, budgets, chat en temps réel, listes de courses et to-do lists.
                </p>
                <div class="flex items-center gap-3">
                    <a href="https://app.youplago.com" target="_blank" rel="noopener" class="btn btn-outline flex-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                        </svg>
                        Voir le site
                    </a>
                    <a href="{{ route('projects') }}#youplago" class="btn btn-ghost">
                        En savoir plus
                    </a>
                </div>
            </div>

            <!-- Batibid -->
            <div class="card hover-lift hover-glow">
                <div class="flex items-start gap-4 mb-4">
                    <div class="w-16 h-16 rounded-xl gradient-accent flex items-center justify-center flex-shrink-0">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="heading-4 mb-2">Batibid</h3>
                        <div class="flex flex-wrap gap-2 mb-3">
                            <span class="badge badge-primary">Laravel</span>
                            <span class="badge badge-secondary">Back-office</span>
                            <span class="badge badge-warning">Immobilier</span>
                        </div>
                    </div>
                </div>
                <p class="text-neutral-600 mb-4 leading-relaxed">
                    Plateforme immobilière complète pour une agence au Bénin. Back-office de gestion des annonces,
                    système de facturation automatique et interface de recherche de biens optimisée.
                </p>
                <div class="flex items-center gap-3">
                    <a href="https://app.batibid.com" target="_blank" rel="noopener" class="btn btn-outline flex-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                        </svg>
                        Voir le site
                    </a>
                    <a href="{{ route('projects') }}#batibid" class="btn btn-ghost">
                        En savoir plus
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
            <div class="card-bordered text-center">
                <div class="w-16 h-16 rounded-full bg-danger-100 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-danger-600" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M23.642 5.43a.364.364 0 01.014.1v5.149c0 .135-.073.26-.189.326l-4.323 2.49v4.934a.378.378 0 01-.188.326L9.93 23.949a.316.316 0 01-.066.027c-.008.002-.016.008-.024.01a.348.348 0 01-.192 0c-.011-.002-.02-.008-.03-.012-.02-.008-.042-.014-.062-.025L.533 18.755a.376.376 0 01-.189-.326V2.974c0-.033.005-.066.014-.098.003-.012.01-.02.014-.032a.369.369 0 01.023-.058c.004-.013.015-.022.023-.033l.033-.045c.012-.01.025-.018.037-.027.014-.012.027-.024.041-.034H.53L5.043.05a.375.375 0 01.375 0L9.93 2.647h.002c.015.01.027.021.04.033l.038.027c.013.014.02.03.033.045l.023.033a.35.35 0 01.023.058c.004.013.01.02.013.032.01.031.014.064.014.098v9.652l3.76-2.164V5.527c0-.033.004-.066.013-.098.003-.01.01-.02.013-.032a.487.487 0 01.024-.059c.007-.012.018-.02.025-.033l.033-.045c.012-.01.025-.018.037-.027.014-.013.026-.023.041-.033h.001l4.513-2.598a.375.375 0 01.375 0l4.513 2.598c.016.01.027.021.042.031.012.01.025.018.036.028.013.014.02.03.033.045l.025.033a.35.35 0 01.023.058c.004.013.01.021.013.032.01.031.014.064.014.098zM9.357 3.936L5.657 5.966 9.358 8l3.7-2.035zm-.188 16.26l3.76-2.164V8.05l-3.76 2.163zm-3.937-2.163l3.76 2.163v-9.98l-3.76-2.164zm7.698-12.24l-3.7-2.034-3.7 2.034 3.7 2.035zm.375 2.193v4.921l3.76-2.164V5.594l-3.76 2.165zm4.134-4.92l-3.7-2.035-3.7 2.035 3.7 2.035zm-.188 13.396l3.76-2.164V5.046l-3.76 2.164z"></path>
                    </svg>
                </div>
                <h3 class="heading-4 mb-2">Laravel</h3>
                <p class="text-neutral-600">
                    Framework PHP pour applications web robustes, APIs RESTful et back-offices performants
                </p>
            </div>

            <!-- Ionic -->
            <div class="card-bordered text-center">
                <div class="w-16 h-16 rounded-full bg-primary-100 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-primary-600" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm0 2.182c5.423 0 9.818 4.395 9.818 9.818 0 5.423-4.395 9.818-9.818 9.818-5.423 0-9.818-4.395-9.818-9.818 0-5.423 4.395-9.818 9.818-9.818z"></path>
                    </svg>
                </div>
                <h3 class="heading-4 mb-2">Ionic</h3>
                <p class="text-neutral-600">
                    Développement d'applications mobiles hybrides iOS et Android avec une base de code unique
                </p>
            </div>

            <!-- Full-Stack -->
            <div class="card-bordered text-center">
                <div class="w-16 h-16 rounded-full bg-secondary-100 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-secondary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
                    </svg>
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
        <p class="text-lead text-white/90 mb-8 text-center">
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
