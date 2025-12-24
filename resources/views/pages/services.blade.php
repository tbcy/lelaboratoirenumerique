@extends('layouts.app')

@section('title', 'Services - Le Laboratoire Numérique')

@section('content')
<!-- Hero Section -->
<section class="gradient-primary py-20 md:py-32 overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            <div class="text-white text-center lg:text-left">
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-white mb-6 leading-tight">
                    Des solutions <span class="text-accent-300">sur mesure</span> pour vos projets
                </h1>
                <p class="text-xl md:text-2xl text-white/90">
                    Expert en développement Laravel et Ionic, je transforme vos idées en applications performantes et évolutives.
                </p>
            </div>
            <div class="hidden lg:block">
                <img src="{{ asset('images/services-hero.png') }}" alt="Le Laboratoire Numérique - Création sur mesure" class="w-full max-w-lg mx-auto rounded-3xl drop-shadow-2xl animate-fade-in-up">
            </div>
        </div>
    </div>
</section>

<!-- Services Principaux -->
<section class="py-16 md:py-24 bg-neutral-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-neutral-900 mb-4">Mes Services</h2>
            <p class="text-lg md:text-xl text-neutral-600">
                Je vous accompagne de la conception à la mise en production de vos applications web et mobiles
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Service Laravel -->
            <div class="card hover-lift">
                <div class="w-16 h-16 rounded-lg gradient-primary flex items-center justify-center mb-6">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                    </svg>
                </div>
                <h3 class="text-2xl md:text-3xl font-bold text-neutral-900 mb-4">Développement Laravel</h3>
                <p class="text-neutral-600 mb-6">
                    Création d'applications web robustes et évolutives avec le framework Laravel, de la simple vitrine au système complexe.
                </p>

                <h4 class="text-xl font-semibold text-neutral-900 mb-3">Ce que je développe :</h4>
                <ul class="space-y-3 mb-6">
                    <li class="flex items-start gap-3">
                        <svg class="w-6 h-6 text-success-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span class="text-neutral-600"><strong>Applications web complètes</strong> - Sites institutionnels, plateformes métier, SaaS</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <svg class="w-6 h-6 text-success-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span class="text-neutral-600"><strong>Back-offices personnalisés</strong> - Gestion de contenus, administration, tableaux de bord</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <svg class="w-6 h-6 text-success-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span class="text-neutral-600"><strong>APIs REST & GraphQL</strong> - Création d'APIs sécurisées pour applications mobiles et tierces</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <svg class="w-6 h-6 text-success-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span class="text-neutral-600"><strong>Systèmes d'authentification</strong> - Gestion utilisateurs, rôles et permissions</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <svg class="w-6 h-6 text-success-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span class="text-neutral-600"><strong>Intégrations tierces</strong> - Paiement en ligne, services cloud, webhooks</span>
                    </li>
                </ul>

                <div class="flex flex-wrap gap-2">
                    <span class="badge badge-primary">Laravel 12.x</span>
                    <span class="badge badge-primary">PHP 8.x</span>
                    <span class="badge badge-primary">MySQL</span>
                    <span class="badge badge-primary">PostgreSQL</span>
                    <span class="badge badge-primary">Redis</span>
                </div>
            </div>

            <!-- Service Ionic -->
            <div class="card hover-lift">
                <div class="w-16 h-16 rounded-lg gradient-accent flex items-center justify-center mb-6">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                </div>
                <h3 class="text-2xl md:text-3xl font-bold text-neutral-900 mb-4">Développement Mobile Ionic</h3>
                <p class="text-neutral-600 mb-6">
                    Conception d'applications mobiles hybrides performantes, déployées sur iOS et Android à partir d'un code unique.
                </p>

                <h4 class="text-xl font-semibold text-neutral-900 mb-3">Ce que je crée :</h4>
                <ul class="space-y-3 mb-6">
                    <li class="flex items-start gap-3">
                        <svg class="w-6 h-6 text-success-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span class="text-neutral-600"><strong>Applications hybrides</strong> - Une seule base de code pour iOS, Android et Web</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <svg class="w-6 h-6 text-success-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span class="text-neutral-600"><strong>Applications temps réel</strong> - Chat, notifications push, synchronisation en direct</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <svg class="w-6 h-6 text-success-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span class="text-neutral-600"><strong>Fonctionnalités natives</strong> - Caméra, géolocalisation, stockage local, capteurs</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <svg class="w-6 h-6 text-success-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span class="text-neutral-600"><strong>Interface utilisateur moderne</strong> - Design Material/iOS natif, animations fluides</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <svg class="w-6 h-6 text-success-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span class="text-neutral-600"><strong>Mode hors ligne</strong> - Stockage local et synchronisation automatique</span>
                    </li>
                </ul>

                <div class="flex flex-wrap gap-2">
                    <span class="badge badge-secondary">Ionic 8</span>
                    <span class="badge badge-secondary">Angular</span>
                    <span class="badge badge-secondary">Capacitor</span>
                    <span class="badge badge-secondary">TypeScript</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Expertise Full-Stack -->
<section class="py-16 md:py-24 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-neutral-900 mb-4">Expertise Full-Stack</h2>
            <p class="text-lg md:text-xl text-neutral-600">
                Une approche complète du développement, du backend à l'interface utilisateur
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Backend -->
            <div class="card-bordered text-center">
                <div class="w-12 h-12 rounded-lg bg-primary-100 text-primary-600 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"/>
                    </svg>
                </div>
                <h3 class="text-xl md:text-2xl font-semibold text-neutral-900 mb-3">Backend</h3>
                <ul class="text-neutral-600 space-y-2 text-left">
                    <li>• Architecture MVC & API REST</li>
                    <li>• Gestion de bases de données</li>
                    <li>• Authentification & sécurité</li>
                    <li>• Jobs & queues asynchrones</li>
                    <li>• Tests unitaires & fonctionnels</li>
                </ul>
            </div>

            <!-- Frontend -->
            <div class="card-bordered text-center">
                <div class="w-12 h-12 rounded-lg bg-secondary-100 text-secondary-600 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <h3 class="text-xl md:text-2xl font-semibold text-neutral-900 mb-3">Frontend</h3>
                <ul class="text-neutral-600 space-y-2 text-left">
                    <li>• Interfaces responsive (Tailwind CSS)</li>
                    <li>• Applications SPA modernes</li>
                    <li>• Optimisation des performances</li>
                    <li>• Accessibilité (WCAG)</li>
                    <li>• Progressive Web Apps</li>
                </ul>
            </div>

            <!-- DevOps -->
            <div class="card-bordered text-center">
                <div class="w-12 h-12 rounded-lg bg-accent-100 text-accent-600 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                    </svg>
                </div>
                <h3 class="text-xl md:text-2xl font-semibold text-neutral-900 mb-3">Déploiement</h3>
                <ul class="text-neutral-600 space-y-2 text-left">
                    <li>• Configuration serveur (VPS, cloud)</li>
                    <li>• Déploiement continu (CI/CD)</li>
                    <li>• Monitoring & logs</li>
                    <li>• Optimisation des performances</li>
                    <li>• Sauvegardes & maintenance</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<!-- Processus de Travail -->
<section class="py-16 md:py-24 bg-neutral-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-neutral-900 mb-4">Comment je travaille</h2>
            <p class="text-lg md:text-xl text-neutral-600">
                Une méthodologie éprouvée pour garantir la réussite de votre projet
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <!-- Étape 1 -->
            <div class="relative">
                <div class="card h-full text-center hover-lift">
                    <div class="w-24 h-24 mx-auto mb-4 overflow-hidden rounded-xl">
                        <img src="{{ asset('images/icons/process-analyse.png') }}" alt="Étape Analyse" class="w-full h-full object-cover">
                    </div>
                    <div class="w-8 h-8 rounded-full bg-primary-600 text-white flex items-center justify-center text-sm font-bold mx-auto mb-3">
                        1
                    </div>
                    <h3 class="text-xl font-semibold text-neutral-900 mb-2">Analyse</h3>
                    <p class="text-neutral-600">
                        Échange sur vos besoins, objectifs et contraintes. Définition du périmètre fonctionnel et technique.
                    </p>
                </div>
            </div>

            <!-- Étape 2 -->
            <div class="relative">
                <div class="card h-full text-center hover-lift">
                    <div class="w-24 h-24 mx-auto mb-4 overflow-hidden rounded-xl">
                        <img src="{{ asset('images/icons/process-conception.png') }}" alt="Étape Conception" class="w-full h-full object-cover">
                    </div>
                    <div class="w-8 h-8 rounded-full bg-primary-600 text-white flex items-center justify-center text-sm font-bold mx-auto mb-3">
                        2
                    </div>
                    <h3 class="text-xl font-semibold text-neutral-900 mb-2">Conception</h3>
                    <p class="text-neutral-600">
                        Élaboration de l'architecture technique, des maquettes et du planning. Validation avant développement.
                    </p>
                </div>
            </div>

            <!-- Étape 3 -->
            <div class="relative">
                <div class="card h-full text-center hover-lift">
                    <div class="w-24 h-24 mx-auto mb-4 overflow-hidden rounded-xl">
                        <img src="{{ asset('images/icons/process-dev.png') }}" alt="Étape Développement" class="w-full h-full object-cover">
                    </div>
                    <div class="w-8 h-8 rounded-full bg-primary-600 text-white flex items-center justify-center text-sm font-bold mx-auto mb-3">
                        3
                    </div>
                    <h3 class="text-xl font-semibold text-neutral-900 mb-2">Développement</h3>
                    <p class="text-neutral-600">
                        Développement itératif avec démonstrations régulières. Code propre, testé et documenté.
                    </p>
                </div>
            </div>

            <!-- Étape 4 -->
            <div class="relative">
                <div class="card h-full text-center hover-lift">
                    <div class="w-24 h-24 mx-auto mb-4 overflow-hidden rounded-xl">
                        <img src="{{ asset('images/icons/process-launch.png') }}" alt="Étape Livraison" class="w-full h-full object-cover">
                    </div>
                    <div class="w-8 h-8 rounded-full bg-primary-600 text-white flex items-center justify-center text-sm font-bold mx-auto mb-3">
                        4
                    </div>
                    <h3 class="text-xl font-semibold text-neutral-900 mb-2">Livraison</h3>
                    <p class="text-neutral-600">
                        Tests finaux, déploiement en production, formation et documentation complète du projet.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Final -->
<section class="gradient-primary py-16 md:py-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-white">
        <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-white mb-6">
            Prêt à démarrer votre projet ?
        </h2>
        <p class="text-xl text-white mb-10">
            Discutons de vos besoins et trouvons ensemble la meilleure solution pour concrétiser votre projet.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('contact') }}" class="btn bg-white text-primary-600 hover:bg-neutral-50 shadow-lg">
                Me contacter
            </a>
            <a href="{{ route('projects') }}" class="btn bg-white/10 text-white hover:bg-white/20 backdrop-blur-sm border border-white/20">
                Voir mes projets
            </a>
        </div>
    </div>
</section>
@endsection
