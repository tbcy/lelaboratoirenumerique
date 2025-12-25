@extends('layouts.app')

@section('title', 'Projets - Portfolio Développeur Laravel & Ionic | Le Laboratoire Numérique')
@section('description', 'Découvrez mes projets phares : Youplago (organisation d\'événements), Olympiadus (compétitions sportives), Batibid (plateforme immobilière). Applications web et mobiles sur mesure.')
@section('og_type', 'website')

@push('schema')
<x-schema-breadcrumb :items="[['name' => 'Projets', 'url' => route('projects')]]" />
<x-schema-software
    name="Youplago"
    description="Application mobile pour l'organisation d'événements entre amis"
    url="https://app.youplago.com"
    :image="asset('images/projects/youplago-mockup.png')"
    category="LifestyleApplication"
    :platforms="['iOS', 'Android', 'Web']"
/>
<x-schema-software
    name="Olympiadus"
    description="Plateforme de gestion de compétitions sportives avec classements en temps réel"
    url="https://olympiadus.lelaboratoirenumerique.com"
    :image="asset('images/projects/olympiadus-mockup.png')"
    category="SportsApplication"
    :platforms="['iOS', 'Android', 'Web']"
/>
<x-schema-software
    name="Batibid"
    description="Plateforme immobilière complète avec back-office et facturation automatique"
    url="https://app.batibid.com"
    :image="asset('images/projects/batibid-mockup.png')"
    category="BusinessApplication"
    :platforms="['Web']"
/>
@endpush

@section('content')

<!-- Hero Section -->
<section class="gradient-primary py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-white">
        <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-white mb-6">
            Mes Projets
        </h1>
        <p class="text-xl md:text-2xl text-white/90">
            Des applications concrètes qui répondent à de vrais besoins
        </p>
    </div>
</section>

<!-- Youplago -->
<section id="youplago" class="py-16 md:py-24 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid md:grid-cols-2 gap-12 items-center">
            <!-- Info -->
            <div>
                <div class="flex items-center gap-4 mb-6">
                    <div class="w-20 h-20 rounded-2xl gradient-primary flex items-center justify-center flex-shrink-0">
                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-3xl md:text-4xl font-bold text-neutral-900">Youplago</h2>
                        <div class="flex flex-wrap gap-2 mt-2">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 text-sm font-medium rounded-full bg-primary-100 text-primary-700">Ionic</span>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 text-sm font-medium rounded-full bg-secondary-100 text-secondary-700">Laravel</span>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 text-sm font-medium rounded-full bg-success-100 text-success-700">Mobile</span>
                        </div>
                    </div>
                </div>

                <p class="text-lg text-neutral-600 mb-6 leading-relaxed">
                    <strong>Youplago</strong> est une application mobile et hybride innovante conçue pour faciliter l'organisation d'événements entre amis, famille ou collègues.
                </p>

                <h3 class="text-xl font-semibold text-neutral-900 mb-4">Fonctionnalités principales</h3>
                <ul class="space-y-3 mb-8">
                    <li class="flex items-start gap-3">
                        <svg class="w-6 h-6 text-primary-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-neutral-700"><strong>Gestion d'événements</strong> : Création, planification et coordination simplifiées</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <svg class="w-6 h-6 text-primary-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-neutral-700"><strong>Gestion des budgets</strong> : Suivi des dépenses partagées et répartition équitable</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <svg class="w-6 h-6 text-primary-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-neutral-700"><strong>Chat en temps réel</strong> : Communication instantanée entre participants</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <svg class="w-6 h-6 text-primary-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-neutral-700"><strong>Listes collaboratives</strong> : Listes de courses, to-do lists partagées</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <svg class="w-6 h-6 text-primary-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-neutral-700"><strong>Planning d'activités</strong> : Organisation et vote pour les activités</span>
                    </li>
                </ul>

                <div class="flex flex-col sm:flex-row gap-4">
                    <a href="https://app.youplago.com" target="_blank" rel="noopener" class="inline-flex items-center justify-center gap-2 px-8 py-4 bg-primary-600 text-white font-semibold rounded-lg hover:bg-primary-700 transition-all shadow-lg hover:shadow-xl">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                        </svg>
                        Visiter Youplago
                    </a>
                </div>
            </div>

            <!-- Visual/Stats -->
            <div class="space-y-6">
                <!-- Showcase Image -->
                <div class="overflow-hidden rounded-2xl shadow-2xl bg-neutral-900">
                    <img src="{{ asset('images/projects/youplago-showcase.png') }}" alt="Youplago - Application mobile événementielle" class="w-full h-auto">
                </div>

                <div class="bg-neutral-50 rounded-2xl p-8">
                    <h3 class="text-xl font-semibold text-neutral-900 mb-6">Stack Technique</h3>
                    <div class="space-y-4">
                        <div>
                            <div class="flex justify-between mb-2">
                                <span class="text-neutral-700 font-medium">Ionic Framework</span>
                                <span class="text-neutral-600">Mobile</span>
                            </div>
                            <div class="h-2 bg-neutral-200 rounded-full overflow-hidden">
                                <div class="h-full bg-primary-600 rounded-full" style="width: 100%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between mb-2">
                                <span class="text-neutral-700 font-medium">Laravel</span>
                                <span class="text-neutral-600">Backend API</span>
                            </div>
                            <div class="h-2 bg-neutral-200 rounded-full overflow-hidden">
                                <div class="h-full bg-secondary-600 rounded-full" style="width: 100%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between mb-2">
                                <span class="text-neutral-700 font-medium">MySQL</span>
                                <span class="text-neutral-600">Base de données</span>
                            </div>
                            <div class="h-2 bg-neutral-200 rounded-full overflow-hidden">
                                <div class="h-full bg-accent-600 rounded-full" style="width: 100%"></div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 pt-8 border-t border-neutral-200">
                        <h4 class="text-lg font-semibold text-neutral-900 mb-4">Plateformes</h4>
                        <div class="flex flex-wrap gap-3">
                            <div class="flex items-center gap-2 px-4 py-2 bg-white rounded-lg border border-neutral-200">
                                <svg class="w-5 h-5 text-neutral-700" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M17.05 20.28c-.98.95-2.05.8-3.08.35-1.09-.46-2.09-.48-3.24 0-1.44.62-2.2.44-3.06-.35C2.79 15.25 3.51 7.59 9.05 7.31c1.35.07 2.29.74 3.08.8 1.18-.24 2.31-.93 3.57-.84 1.51.12 2.65.72 3.4 1.8-3.12 1.87-2.38 5.98.48 7.13-.57 1.5-1.31 2.99-2.54 4.09l.01-.01zM12.03 7.25c-.15-2.23 1.66-4.07 3.74-4.25.29 2.58-2.34 4.5-3.74 4.25z"/>
                                </svg>
                                <span class="text-neutral-700 font-medium">iOS</span>
                            </div>
                            <div class="flex items-center gap-2 px-4 py-2 bg-white rounded-lg border border-neutral-200">
                                <svg class="w-5 h-5 text-neutral-700" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M17.523 15.341c-.54-.537-.97-1.169-1.29-1.892-.32-.723-.48-1.458-.48-2.205 0-1.047.246-1.999.738-2.856.492-.857 1.17-1.53 2.034-2.019-.736-1.076-1.755-1.614-3.056-1.614-.492 0-1.045.123-1.659.369-.614.246-1.059.369-1.335.369-.215 0-.645-.123-1.29-.369-.645-.246-1.2-.369-1.66-.369-2.445 0-4.38 1.93-4.38 4.374 0 1.415.462 2.915 1.386 4.5.924 1.585 2.034 2.377 3.33 2.377.492 0 1.045-.123 1.659-.369.614-.246 1.076-.369 1.386-.369.277 0 .738.123 1.383.369.645.246 1.183.369 1.614.369 1.17 0 2.17-.584 3-1.752zM15.787 3.73c.43-.522.736-1.144.92-1.865C15.417 2.05 14.17 2.572 13.186 3.73c-.369.43-.645.922-.83 1.476-.185.554-.277 1.089-.277 1.604.737-.03 1.413-.245 2.028-.645.615-.4 1.107-.922 1.477-1.566l.203-.37z"/>
                                </svg>
                                <span class="text-neutral-700 font-medium">Android</span>
                            </div>
                            <div class="flex items-center gap-2 px-4 py-2 bg-white rounded-lg border border-neutral-200">
                                <svg class="w-5 h-5 text-neutral-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path>
                                </svg>
                                <span class="text-neutral-700 font-medium">Web</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Batibid -->
<section id="batibid" class="py-16 md:py-24 bg-neutral-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid md:grid-cols-2 gap-12 items-center">
            <!-- Visual/Stats -->
            <div class="space-y-6 order-2 md:order-1">
                <!-- Showcase Image -->
                <div class="overflow-hidden rounded-2xl shadow-2xl bg-neutral-900">
                    <img src="{{ asset('images/projects/batibid-showcase.png') }}" alt="Batibid - Plateforme immobilière" class="w-full h-auto">
                </div>

                <div class="bg-white rounded-2xl p-8">
                    <h3 class="text-xl font-semibold text-neutral-900 mb-6">Stack Technique</h3>
                    <div class="space-y-4">
                        <div>
                            <div class="flex justify-between mb-2">
                                <span class="text-neutral-700 font-medium">Laravel</span>
                                <span class="text-neutral-600">Full-Stack</span>
                            </div>
                            <div class="h-2 bg-neutral-200 rounded-full overflow-hidden">
                                <div class="h-full bg-primary-600 rounded-full" style="width: 100%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between mb-2">
                                <span class="text-neutral-700 font-medium">Blade + Alpine.js</span>
                                <span class="text-neutral-600">Frontend</span>
                            </div>
                            <div class="h-2 bg-neutral-200 rounded-full overflow-hidden">
                                <div class="h-full bg-secondary-600 rounded-full" style="width: 100%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between mb-2">
                                <span class="text-neutral-700 font-medium">MySQL</span>
                                <span class="text-neutral-600">Base de données</span>
                            </div>
                            <div class="h-2 bg-neutral-200 rounded-full overflow-hidden">
                                <div class="h-full bg-accent-600 rounded-full" style="width: 100%"></div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 pt-8 border-t border-neutral-200">
                        <h4 class="text-lg font-semibold text-neutral-900 mb-4">Modules clés</h4>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="px-4 py-3 bg-neutral-50 rounded-lg">
                                <div class="text-neutral-900 font-medium">Gestion annonces</div>
                            </div>
                            <div class="px-4 py-3 bg-neutral-50 rounded-lg">
                                <div class="text-neutral-900 font-medium">Facturation auto</div>
                            </div>
                            <div class="px-4 py-3 bg-neutral-50 rounded-lg">
                                <div class="text-neutral-900 font-medium">Recherche avancée</div>
                            </div>
                            <div class="px-4 py-3 bg-neutral-50 rounded-lg">
                                <div class="text-neutral-900 font-medium">Back-office</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Info -->
            <div class="order-1 md:order-2">
                <div class="flex items-center gap-4 mb-6">
                    <div class="w-20 h-20 rounded-2xl gradient-accent flex items-center justify-center flex-shrink-0">
                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-3xl md:text-4xl font-bold text-neutral-900">Batibid</h2>
                        <div class="flex flex-wrap gap-2 mt-2">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 text-sm font-medium rounded-full bg-primary-100 text-primary-700">Laravel</span>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 text-sm font-medium rounded-full bg-secondary-100 text-secondary-700">Back-office</span>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 text-sm font-medium rounded-full bg-warning-100 text-warning-700">Immobilier</span>
                        </div>
                    </div>
                </div>

                <p class="text-lg text-neutral-600 mb-6 leading-relaxed">
                    <strong>Batibid</strong> est une plateforme immobilière complète développée pour une agence au Bénin, offrant une solution tout-en-un pour la gestion et la promotion de biens immobiliers.
                </p>

                <h3 class="text-xl font-semibold text-neutral-900 mb-4">Fonctionnalités principales</h3>
                <ul class="space-y-3 mb-8">
                    <li class="flex items-start gap-3">
                        <svg class="w-6 h-6 text-accent-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-neutral-700"><strong>Back-office complet</strong> : Gestion des annonces et des biens immobiliers</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <svg class="w-6 h-6 text-accent-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-neutral-700"><strong>Facturation automatique</strong> : Génération et gestion des factures</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <svg class="w-6 h-6 text-accent-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-neutral-700"><strong>Recherche optimisée</strong> : Filtres avancés pour trouver le bien idéal</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <svg class="w-6 h-6 text-accent-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-neutral-700"><strong>Interface utilisateur</strong> : Design moderne et responsive</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <svg class="w-6 h-6 text-accent-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-neutral-700"><strong>Gestion des contacts</strong> : Suivi des demandes et des clients</span>
                    </li>
                </ul>

                <div class="flex flex-col sm:flex-row gap-4">
                    <a href="https://app.batibid.com" target="_blank" rel="noopener" class="inline-flex items-center justify-center gap-2 px-8 py-4 bg-accent-600 text-white font-semibold rounded-lg hover:bg-accent-700 transition-all shadow-lg hover:shadow-xl">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                        </svg>
                        Visiter Batibid
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Olympiadus -->
<section id="olympiadus" class="py-16 md:py-24 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid md:grid-cols-2 gap-12 items-center">
            <!-- Info -->
            <div>
                <div class="flex items-center gap-4 mb-6">
                    <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-indigo-600 to-violet-600 flex items-center justify-center flex-shrink-0">
                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-3xl md:text-4xl font-bold text-neutral-900">Olympiadus</h2>
                        <div class="flex flex-wrap gap-2 mt-2">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 text-sm font-medium rounded-full bg-primary-100 text-primary-700">Mobile</span>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 text-sm font-medium rounded-full bg-secondary-100 text-secondary-700">Laravel</span>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 text-sm font-medium rounded-full bg-indigo-100 text-indigo-700">Sport</span>
                        </div>
                    </div>
                </div>

                <p class="text-lg text-neutral-600 mb-6 leading-relaxed">
                    <strong>Olympiadus</strong> est la plateforme tout-en-un pour créer, gérer et suivre vos compétitions sportives entre amis, en famille ou en entreprise. Organisez vos olympiades comme un pro !
                </p>

                <h3 class="text-xl font-semibold text-neutral-900 mb-4">Fonctionnalités principales</h3>
                <ul class="space-y-3 mb-8">
                    <li class="flex items-start gap-3">
                        <svg class="w-6 h-6 text-indigo-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-neutral-700"><strong>Organisation facile</strong> : Créez votre olympiade en quelques clics</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <svg class="w-6 h-6 text-indigo-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-neutral-700"><strong>Gestion d'équipes</strong> : Formez des équipes et désignez des capitaines</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <svg class="w-6 h-6 text-indigo-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-neutral-700"><strong>Validation QR Code</strong> : Validez les matchs instantanément avec un système sécurisé</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <svg class="w-6 h-6 text-indigo-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-neutral-700"><strong>Classements en temps réel</strong> : Scores et classements calculés automatiquement</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <svg class="w-6 h-6 text-indigo-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-neutral-700"><strong>Statistiques détaillées</strong> : Analysez les performances avec des stats complètes</span>
                    </li>
                </ul>

                <div class="flex flex-col sm:flex-row gap-4">
                    <a href="https://olympiadus.lelaboratoirenumerique.com/fr" target="_blank" rel="noopener" class="inline-flex items-center justify-center gap-2 px-8 py-4 bg-indigo-600 text-white font-semibold rounded-lg hover:bg-indigo-700 transition-all shadow-lg hover:shadow-xl">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                        </svg>
                        Visiter Olympiadus
                    </a>
                </div>
            </div>

            <!-- Visual/Stats -->
            <div class="space-y-6">
                <!-- Showcase Image -->
                <div class="overflow-hidden rounded-2xl shadow-2xl bg-neutral-900">
                    <img src="{{ asset('images/projects/olympiadus-showcase.png') }}" alt="Olympiadus - Plateforme de compétitions sportives" class="w-full h-auto">
                </div>

                <div class="bg-neutral-50 rounded-2xl p-8">
                    <h3 class="text-xl font-semibold text-neutral-900 mb-6">Stack Technique</h3>
                    <div class="space-y-4">
                        <div>
                            <div class="flex justify-between mb-2">
                                <span class="text-neutral-700 font-medium">React / Ionic</span>
                                <span class="text-neutral-600">Mobile</span>
                            </div>
                            <div class="h-2 bg-neutral-200 rounded-full overflow-hidden">
                                <div class="h-full bg-indigo-600 rounded-full" style="width: 100%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between mb-2">
                                <span class="text-neutral-700 font-medium">Laravel</span>
                                <span class="text-neutral-600">Backend API</span>
                            </div>
                            <div class="h-2 bg-neutral-200 rounded-full overflow-hidden">
                                <div class="h-full bg-secondary-600 rounded-full" style="width: 100%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between mb-2">
                                <span class="text-neutral-700 font-medium">MySQL</span>
                                <span class="text-neutral-600">Base de données</span>
                            </div>
                            <div class="h-2 bg-neutral-200 rounded-full overflow-hidden">
                                <div class="h-full bg-accent-600 rounded-full" style="width: 100%"></div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 pt-8 border-t border-neutral-200">
                        <h4 class="text-lg font-semibold text-neutral-900 mb-4">Statistiques</h4>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="text-center p-4 bg-white rounded-lg border border-neutral-200">
                                <div class="text-2xl font-bold text-indigo-600">10K+</div>
                                <div class="text-sm text-neutral-600">Utilisateurs</div>
                            </div>
                            <div class="text-center p-4 bg-white rounded-lg border border-neutral-200">
                                <div class="text-2xl font-bold text-indigo-600">50K+</div>
                                <div class="text-sm text-neutral-600">Matchs joués</div>
                            </div>
                            <div class="text-center p-4 bg-white rounded-lg border border-neutral-200">
                                <div class="text-2xl font-bold text-indigo-600">5K+</div>
                                <div class="text-sm text-neutral-600">Équipes</div>
                            </div>
                            <div class="text-center p-4 bg-white rounded-lg border border-neutral-200">
                                <div class="text-2xl font-bold text-indigo-600">1K+</div>
                                <div class="text-sm text-neutral-600">Olympiades</div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 pt-8 border-t border-neutral-200">
                        <h4 class="text-lg font-semibold text-neutral-900 mb-4">Plateformes</h4>
                        <div class="flex flex-wrap gap-3">
                            <div class="flex items-center gap-2 px-4 py-2 bg-white rounded-lg border border-neutral-200">
                                <svg class="w-5 h-5 text-neutral-700" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M17.05 20.28c-.98.95-2.05.8-3.08.35-1.09-.46-2.09-.48-3.24 0-1.44.62-2.2.44-3.06-.35C2.79 15.25 3.51 7.59 9.05 7.31c1.35.07 2.29.74 3.08.8 1.18-.24 2.31-.93 3.57-.84 1.51.12 2.65.72 3.4 1.8-3.12 1.87-2.38 5.98.48 7.13-.57 1.5-1.31 2.99-2.54 4.09l.01-.01zM12.03 7.25c-.15-2.23 1.66-4.07 3.74-4.25.29 2.58-2.34 4.5-3.74 4.25z"/>
                                </svg>
                                <span class="text-neutral-700 font-medium">iOS</span>
                            </div>
                            <div class="flex items-center gap-2 px-4 py-2 bg-white rounded-lg border border-neutral-200">
                                <svg class="w-5 h-5 text-neutral-700" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M17.523 15.341c-.54-.537-.97-1.169-1.29-1.892-.32-.723-.48-1.458-.48-2.205 0-1.047.246-1.999.738-2.856.492-.857 1.17-1.53 2.034-2.019-.736-1.076-1.755-1.614-3.056-1.614-.492 0-1.045.123-1.659.369-.614.246-1.059.369-1.335.369-.215 0-.645-.123-1.29-.369-.645-.246-1.2-.369-1.66-.369-2.445 0-4.38 1.93-4.38 4.374 0 1.415.462 2.915 1.386 4.5.924 1.585 2.034 2.377 3.33 2.377.492 0 1.045-.123 1.659-.369.614-.246 1.076-.369 1.386-.369.277 0 .738.123 1.383.369.645.246 1.183.369 1.614.369 1.17 0 2.17-.584 3-1.752zM15.787 3.73c.43-.522.736-1.144.92-1.865C15.417 2.05 14.17 2.572 13.186 3.73c-.369.43-.645.922-.83 1.476-.185.554-.277 1.089-.277 1.604.737-.03 1.413-.245 2.028-.645.615-.4 1.107-.922 1.477-1.566l.203-.37z"/>
                                </svg>
                                <span class="text-neutral-700 font-medium">Android</span>
                            </div>
                            <div class="flex items-center gap-2 px-4 py-2 bg-white rounded-lg border border-neutral-200">
                                <svg class="w-5 h-5 text-neutral-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path>
                                </svg>
                                <span class="text-neutral-700 font-medium">Web</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Data Migration -->
<section id="data-migration" class="py-16 md:py-24 bg-neutral-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid md:grid-cols-2 gap-12 items-center">
            <!-- Info -->
            <div>
                <div class="flex items-center gap-4 mb-6">
                    <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-success-600 to-success-700 flex items-center justify-center flex-shrink-0">
                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-3xl md:text-4xl font-bold text-neutral-900">Data Migration</h2>
                        <div class="flex flex-wrap gap-2 mt-2">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 text-sm font-medium rounded-full bg-primary-100 text-primary-700">Laravel</span>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 text-sm font-medium rounded-full bg-warning-100 text-warning-700">Transformation Digitale</span>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 text-sm font-medium rounded-full bg-success-100 text-success-700">Développement Rapide</span>
                        </div>
                    </div>
                </div>

                <p class="text-lg text-neutral-600 mb-6 leading-relaxed">
                    <strong>Data Migration</strong> est une application web conçue pour accompagner les entreprises dans leurs programmes de transformation digitale. Développée en moins de 2 jours, elle permet de gérer et suivre des projets de migration de données complexes.
                </p>

                <h3 class="text-xl font-semibold text-neutral-900 mb-4">Fonctionnalités principales</h3>
                <ul class="space-y-3 mb-8">
                    <li class="flex items-start gap-3">
                        <svg class="w-6 h-6 text-success-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-neutral-700"><strong>Suivi de projets</strong> : Gestion complète des projets de migration</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <svg class="w-6 h-6 text-success-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-neutral-700"><strong>Rédaction de spécifications</strong> : Outils pour documenter les transformations de données</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <svg class="w-6 h-6 text-success-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-neutral-700"><strong>Tableaux de bord</strong> : Visualisation de l'avancement des migrations</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <svg class="w-6 h-6 text-success-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-neutral-700"><strong>Collaboration</strong> : Travail d'équipe sur les projets de migration</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <svg class="w-6 h-6 text-success-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="text-neutral-700"><strong>Export de documentation</strong> : Génération automatique de rapports</span>
                    </li>
                </ul>

                <div class="bg-success-50 border-l-4 border-success-500 p-4 mb-6">
                    <p class="text-success-800 font-medium">
                        ⚡ Projet développé en moins de 2 jours pour répondre à un besoin urgent d'un client en transformation digitale
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row gap-4">
                    <a href="https://data-mig.lelaboratoirenumerique.com/" target="_blank" rel="noopener" class="inline-flex items-center justify-center gap-2 px-8 py-4 bg-success-600 text-white font-semibold rounded-lg hover:bg-success-700 transition-all shadow-lg hover:shadow-xl">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                        </svg>
                        Visiter Data Migration
                    </a>
                </div>
            </div>

            <!-- Visual/Stats -->
            <div class="space-y-6">
                <!-- Showcase Image -->
                <div class="overflow-hidden rounded-2xl shadow-2xl bg-neutral-900">
                    <img src="{{ asset('images/projects/datamigration-showcase.png') }}" alt="Data Migration - Dashboard de suivi de migration" class="w-full h-auto">
                </div>

                <div class="bg-neutral-50 rounded-2xl p-8">
                    <h3 class="text-xl font-semibold text-neutral-900 mb-6">Stack Technique</h3>
                    <div class="space-y-4">
                        <div>
                            <div class="flex justify-between mb-2">
                                <span class="text-neutral-700 font-medium">Laravel</span>
                                <span class="text-neutral-600">Backend</span>
                            </div>
                            <div class="h-2 bg-neutral-200 rounded-full overflow-hidden">
                                <div class="h-full bg-primary-600 rounded-full" style="width: 100%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between mb-2">
                                <span class="text-neutral-700 font-medium">Blade + Tailwind CSS</span>
                                <span class="text-neutral-600">Frontend</span>
                            </div>
                            <div class="h-2 bg-neutral-200 rounded-full overflow-hidden">
                                <div class="h-full bg-secondary-600 rounded-full" style="width: 100%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between mb-2">
                                <span class="text-neutral-700 font-medium">MySQL</span>
                                <span class="text-neutral-600">Database</span>
                            </div>
                            <div class="h-2 bg-neutral-200 rounded-full overflow-hidden">
                                <div class="h-full bg-accent-600 rounded-full" style="width: 100%"></div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 pt-8 border-t border-neutral-200">
                        <h3 class="text-xl font-semibold text-neutral-900 mb-4">Points clés</h3>
                        <div class="space-y-4">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-lg bg-success-100 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-6 h-6 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-semibold text-neutral-900">Développement rapide</p>
                                    <p class="text-sm text-neutral-600">Livré en moins de 48h</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-lg bg-warning-100 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-6 h-6 text-warning-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-semibold text-neutral-900">Gestion complète</p>
                                    <p class="text-sm text-neutral-600">Projets & spécifications</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-lg bg-primary-100 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-semibold text-neutral-900">Collaboration</p>
                                    <p class="text-sm text-neutral-600">Travail d'équipe facilité</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="py-16 md:py-24 gradient-primary">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-white">
        <h2 class="text-3xl md:text-4xl font-bold text-white mb-4">Un projet en tête ?</h2>
        <p class="text-xl text-white/90 mb-8">
            Discutons de votre prochain projet et transformons vos idées en réalité.
        </p>
        <a href="{{ route('contact') }}" class="inline-flex items-center justify-center gap-2 px-8 py-4 bg-white text-primary-600 font-semibold rounded-lg hover:bg-neutral-50 transition-all shadow-lg hover:shadow-xl">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
            </svg>
            Me contacter
        </a>
    </div>
</section>

@endsection
