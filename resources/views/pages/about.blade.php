@extends('layouts.app')

@section('title', 'À propos - Le Laboratoire Numérique')

@section('content')
<!-- Hero Section -->
<section class="gradient-primary py-20 md:py-32">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-white">
            <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-white mb-6 leading-tight text-center">
                Développeur <span class="text-accent-300">passionné</span> par la tech
            </h1>
            <p class="text-xl md:text-2xl text-white/90 text-center">
                Expert Laravel et Ionic, je transforme des idées en applications concrètes depuis plus de 5 ans.
            </p>
        </div>
    </div>
</section>

<!-- Présentation -->
<section class="py-16 md:py-24 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <div>
                <!-- Portrait -->
                <div class="mb-8 flex justify-center lg:justify-start">
                    <div class="w-48 h-48 rounded-2xl overflow-hidden shadow-xl ring-4 ring-primary-100">
                        <img src="{{ asset('images/about-portrait.png') }}" alt="Thomas Bourcy - Développeur Full-Stack" class="w-full h-full object-cover">
                    </div>
                </div>
                <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-neutral-900 mb-6">Qui suis-je ?</h2>
                <div class="space-y-4 text-lg text-neutral-600">
                    <p>
                        Je suis un développeur full-stack spécialisé dans l'écosystème <strong class="text-neutral-900">Laravel</strong>
                        et <strong class="text-neutral-900">Ionic</strong>. Passionné par le développement web et mobile,
                        j'accompagne mes clients dans la réalisation de leurs projets digitaux.
                    </p>
                    <p>
                        Mon expertise s'étend de la conception d'<strong class="text-neutral-900">applications web robustes</strong>
                        avec Laravel à la création d'<strong class="text-neutral-900">applications mobiles hybrides</strong> performantes
                        avec Ionic. Je maîtrise l'ensemble de la chaîne de développement, du backend à l'interface utilisateur.
                    </p>
                    <p>
                        J'ai développé des solutions variées : des <strong class="text-neutral-900">plateformes immobilières</strong>,
                        des <strong class="text-neutral-900">applications mobiles d'organisation d'événements</strong>,
                        et de nombreux <strong class="text-neutral-900">back-offices sur mesure</strong> pour différents secteurs d'activité.
                    </p>
                </div>
            </div>

            <div class="card bg-neutral-50">
                <h3 class="text-2xl font-bold text-neutral-900 mb-6">Mes valeurs</h3>
                <div class="space-y-4">
                    <div class="flex gap-4">
                        <div class="w-12 h-12 rounded-lg bg-primary-100 text-primary-600 flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-lg font-semibold text-neutral-900 mb-1">Qualité</h4>
                            <p class="text-neutral-600">Code propre, testé et documenté pour garantir la pérennité de vos projets.</p>
                        </div>
                    </div>

                    <div class="flex gap-4">
                        <div class="w-12 h-12 rounded-lg bg-secondary-100 text-secondary-600 flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-lg font-semibold text-neutral-900 mb-1">Réactivité</h4>
                            <p class="text-neutral-600">Communication transparente et suivi régulier pour une collaboration efficace.</p>
                        </div>
                    </div>

                    <div class="flex gap-4">
                        <div class="w-12 h-12 rounded-lg bg-accent-100 text-accent-600 flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-lg font-semibold text-neutral-900 mb-1">Innovation</h4>
                            <p class="text-neutral-600">Veille technologique constante pour proposer des solutions modernes et performantes.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Compétences -->
<section class="py-16 md:py-24 bg-neutral-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-neutral-900 mb-4">Stack Technique</h2>
            <p class="text-lg md:text-xl text-neutral-600">
                Les technologies que je maîtrise pour donner vie à vos projets
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <!-- Backend -->
            <div class="card">
                <h3 class="text-xl font-semibold text-neutral-900 mb-4 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-primary-600"></span>
                    Backend
                </h3>
                <div class="space-y-3">
                    <div>
                        <div class="flex justify-between mb-2">
                            <span class="text-neutral-600 font-medium">Laravel / PHP</span>
                            <span class="text-neutral-900 font-semibold">Expert</span>
                        </div>
                        <div class="w-full bg-neutral-200 rounded-full h-2">
                            <div class="bg-primary-600 h-2 rounded-full" style="width: 95%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between mb-2">
                            <span class="text-neutral-600 font-medium">MySQL / PostgreSQL</span>
                            <span class="text-neutral-900 font-semibold">Avancé</span>
                        </div>
                        <div class="w-full bg-neutral-200 rounded-full h-2">
                            <div class="bg-primary-600 h-2 rounded-full" style="width: 90%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between mb-2">
                            <span class="text-neutral-600 font-medium">APIs REST</span>
                            <span class="text-neutral-900 font-semibold">Expert</span>
                        </div>
                        <div class="w-full bg-neutral-200 rounded-full h-2">
                            <div class="bg-primary-600 h-2 rounded-full" style="width: 95%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Frontend -->
            <div class="card">
                <h3 class="text-xl font-semibold text-neutral-900 mb-4 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-secondary-600"></span>
                    Frontend
                </h3>
                <div class="space-y-3">
                    <div>
                        <div class="flex justify-between mb-2">
                            <span class="text-neutral-600 font-medium">Tailwind CSS</span>
                            <span class="text-neutral-900 font-semibold">Expert</span>
                        </div>
                        <div class="w-full bg-neutral-200 rounded-full h-2">
                            <div class="bg-secondary-600 h-2 rounded-full" style="width: 95%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between mb-2">
                            <span class="text-neutral-600 font-medium">Blade / Alpine.js</span>
                            <span class="text-neutral-900 font-semibold">Avancé</span>
                        </div>
                        <div class="w-full bg-neutral-200 rounded-full h-2">
                            <div class="bg-secondary-600 h-2 rounded-full" style="width: 90%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between mb-2">
                            <span class="text-neutral-600 font-medium">JavaScript / TypeScript</span>
                            <span class="text-neutral-900 font-semibold">Avancé</span>
                        </div>
                        <div class="w-full bg-neutral-200 rounded-full h-2">
                            <div class="bg-secondary-600 h-2 rounded-full" style="width: 85%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mobile -->
            <div class="card">
                <h3 class="text-xl font-semibold text-neutral-900 mb-4 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-accent-600"></span>
                    Mobile
                </h3>
                <div class="space-y-3">
                    <div>
                        <div class="flex justify-between mb-2">
                            <span class="text-neutral-600 font-medium">Ionic Framework</span>
                            <span class="text-neutral-900 font-semibold">Expert</span>
                        </div>
                        <div class="w-full bg-neutral-200 rounded-full h-2">
                            <div class="bg-accent-600 h-2 rounded-full" style="width: 95%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between mb-2">
                            <span class="text-neutral-600 font-medium">Capacitor</span>
                            <span class="text-neutral-900 font-semibold">Avancé</span>
                        </div>
                        <div class="w-full bg-neutral-200 rounded-full h-2">
                            <div class="bg-accent-600 h-2 rounded-full" style="width: 90%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between mb-2">
                            <span class="text-neutral-600 font-medium">Angular</span>
                            <span class="text-neutral-900 font-semibold">Avancé</span>
                        </div>
                        <div class="w-full bg-neutral-200 rounded-full h-2">
                            <div class="bg-accent-600 h-2 rounded-full" style="width: 85%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- DevOps -->
            <div class="card">
                <h3 class="text-xl font-semibold text-neutral-900 mb-4 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-success-500"></span>
                    DevOps & Outils
                </h3>
                <div class="space-y-3">
                    <div>
                        <div class="flex justify-between mb-2">
                            <span class="text-neutral-600 font-medium">Git / GitHub</span>
                            <span class="text-neutral-900 font-semibold">Expert</span>
                        </div>
                        <div class="w-full bg-neutral-200 rounded-full h-2">
                            <div class="bg-success-500 h-2 rounded-full" style="width: 95%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between mb-2">
                            <span class="text-neutral-600 font-medium">Docker</span>
                            <span class="text-neutral-900 font-semibold">Intermédiaire</span>
                        </div>
                        <div class="w-full bg-neutral-200 rounded-full h-2">
                            <div class="bg-success-500 h-2 rounded-full" style="width: 70%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between mb-2">
                            <span class="text-neutral-600 font-medium">Linux / SSH</span>
                            <span class="text-neutral-900 font-semibold">Avancé</span>
                        </div>
                        <div class="w-full bg-neutral-200 rounded-full h-2">
                            <div class="bg-success-500 h-2 rounded-full" style="width: 85%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Méthodologies -->
            <div class="card">
                <h3 class="text-xl font-semibold text-neutral-900 mb-4 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-warning-500"></span>
                    Méthodologies
                </h3>
                <div class="space-y-3">
                    <div>
                        <div class="flex justify-between mb-2">
                            <span class="text-neutral-600 font-medium">Architecture MVC</span>
                            <span class="text-neutral-900 font-semibold">Expert</span>
                        </div>
                        <div class="w-full bg-neutral-200 rounded-full h-2">
                            <div class="bg-warning-500 h-2 rounded-full" style="width: 95%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between mb-2">
                            <span class="text-neutral-600 font-medium">Tests (PHPUnit)</span>
                            <span class="text-neutral-900 font-semibold">Avancé</span>
                        </div>
                        <div class="w-full bg-neutral-200 rounded-full h-2">
                            <div class="bg-warning-500 h-2 rounded-full" style="width: 80%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between mb-2">
                            <span class="text-neutral-600 font-medium">Agile / Scrum</span>
                            <span class="text-neutral-900 font-semibold">Avancé</span>
                        </div>
                        <div class="w-full bg-neutral-200 rounded-full h-2">
                            <div class="bg-warning-500 h-2 rounded-full" style="width: 85%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Design -->
            <div class="card">
                <h3 class="text-xl font-semibold text-neutral-900 mb-4 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-danger-500"></span>
                    Design & UX
                </h3>
                <div class="space-y-3">
                    <div>
                        <div class="flex justify-between mb-2">
                            <span class="text-neutral-600 font-medium">UI/UX Design</span>
                            <span class="text-neutral-900 font-semibold">Avancé</span>
                        </div>
                        <div class="w-full bg-neutral-200 rounded-full h-2">
                            <div class="bg-danger-500 h-2 rounded-full" style="width: 85%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between mb-2">
                            <span class="text-neutral-600 font-medium">Responsive Design</span>
                            <span class="text-neutral-900 font-semibold">Expert</span>
                        </div>
                        <div class="w-full bg-neutral-200 rounded-full h-2">
                            <div class="bg-danger-500 h-2 rounded-full" style="width: 95%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between mb-2">
                            <span class="text-neutral-600 font-medium">Accessibilité (WCAG)</span>
                            <span class="text-neutral-900 font-semibold">Intermédiaire</span>
                        </div>
                        <div class="w-full bg-neutral-200 rounded-full h-2">
                            <div class="bg-danger-500 h-2 rounded-full" style="width: 75%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Parcours -->
<section class="py-16 md:py-24 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-neutral-900 mb-4">Mon Parcours</h2>
            <p class="text-lg md:text-xl text-neutral-600">
                Plus de 7 ans d'expérience en développement web et mobile
            </p>
        </div>

        <div class="max-w-7xl mx-auto">
            <div class="space-y-8">
                <!-- Formation -->
                <div class="flex gap-6 items-start">
                    <div class="flex-shrink-0">
                        <div class="w-20 h-20 rounded-xl overflow-hidden shadow-lg">
                            <img src="{{ asset('images/timeline/mba-marketing.png') }}" alt="MBA Marketing Digital" class="w-full h-full object-cover">
                        </div>
                    </div>
                    <div class="flex-1 card">
                        <div class="badge" style="background-color: rgb(224 231 255); color: rgb(67 56 202);">2016 - 2018</div>
                        <h3 class="text-xl font-semibold text-neutral-900 mb-2 mt-2">MBA Marketing Digital</h3>
                        <p class="text-neutral-600">
                            Formation complète en stratégie digitale, gestion de projet et entrepreneuriat.
                            Acquisition des compétences business qui complètent aujourd'hui mon expertise technique
                            pour comprendre les enjeux métier de mes clients.
                        </p>
                    </div>
                </div>

                <!-- Sipper -->
                <div class="flex gap-6 items-start">
                    <div class="flex-shrink-0">
                        <div class="w-20 h-20 rounded-xl overflow-hidden shadow-lg">
                            <img src="{{ asset('images/timeline/sipper-startup.png') }}" alt="Sipper - Startup" class="w-full h-full object-cover">
                        </div>
                    </div>
                    <div class="flex-1 card">
                        <div class="badge" style="background-color: rgb(255 237 213); color: rgb(194 65 12);">2018 - 2020</div>
                        <h3 class="text-xl font-semibold text-neutral-900 mb-2 mt-2">Fondateur & CEO</h3>
                        <p class="text-neutral-600 mb-2">
                            <span class="font-medium text-neutral-700">Sipper</span>
                        </p>
                        <p class="text-neutral-600">
                            Création et développement de ma première startup. Gestion complète : stratégie, développement produit,
                            marketing et relation client. Cette expérience entrepreneuriale m'a permis de développer
                            une vision 360° des projets digitaux et d'apprendre le développement en autodidacte.
                        </p>
                    </div>
                </div>

                <!-- Lancement Freelance -->
                <div class="flex gap-6 items-start">
                    <div class="flex-shrink-0">
                        <div class="w-20 h-20 rounded-xl overflow-hidden shadow-lg">
                            <img src="{{ asset('images/timeline/2020-launch.png') }}" alt="2020 - Lancement" class="w-full h-full object-cover">
                        </div>
                    </div>
                    <div class="flex-1 card border-2 border-primary-200 bg-primary-50/30">
                        <div class="badge badge-primary mb-2">2020</div>
                        <h3 class="text-xl font-semibold text-neutral-900 mb-2">Création du Laboratoire Numérique</h3>
                        <p class="text-neutral-600 mb-2">
                            <span class="font-medium text-neutral-700">Freelance</span> · France
                        </p>
                        <p class="text-neutral-600">
                            Lancement de mon activité indépendante pour offrir des solutions sur mesure. Spécialisation dans l'écosystème
                            Laravel et le développement mobile avec Ionic. Premiers clients dans les secteurs de l'événementiel et du e-commerce.
                        </p>
                    </div>
                </div>

                <!-- Youplago -->
                <div class="flex gap-6 items-start">
                    <div class="flex-shrink-0">
                        <div class="w-20 h-20 rounded-xl overflow-hidden shadow-lg">
                            <img src="{{ asset('images/timeline/2021-youplago.png') }}" alt="2021 - Youplago" class="w-full h-full object-cover">
                        </div>
                    </div>
                    <div class="flex-1 card">
                        <div class="badge badge-secondary mb-2">2021</div>
                        <h3 class="text-xl font-semibold text-neutral-900 mb-2">Lancement de Youplago</h3>
                        <p class="text-neutral-600 mb-2">
                            <span class="font-medium text-neutral-700">Projet personnel</span> · Application mobile
                        </p>
                        <p class="text-neutral-600">
                            Conception et développement complet d'une application mobile d'organisation d'événements entre amis.
                            Architecture backend Laravel, application Ionic avec Angular, déploiement sur iOS et Android.
                            +5 000 téléchargements la première année.
                        </p>
                        <div class="flex flex-wrap gap-2 mt-3">
                            <span class="text-xs px-2 py-1 bg-neutral-100 text-neutral-600 rounded">Ionic</span>
                            <span class="text-xs px-2 py-1 bg-neutral-100 text-neutral-600 rounded">Angular</span>
                            <span class="text-xs px-2 py-1 bg-neutral-100 text-neutral-600 rounded">Laravel API</span>
                            <span class="text-xs px-2 py-1 bg-neutral-100 text-neutral-600 rounded">Firebase</span>
                        </div>
                    </div>
                </div>

                <!-- Olympiadus -->
                <div class="flex gap-6 items-start">
                    <div class="flex-shrink-0">
                        <div class="w-20 h-20 rounded-xl overflow-hidden shadow-lg">
                            <img src="{{ asset('images/timeline/2022-olympiadus.png') }}" alt="Olympiadus" class="w-full h-full object-cover">
                        </div>
                    </div>
                    <div class="flex-1 card">
                        <div class="badge" style="background-color: rgb(224 231 255); color: rgb(67 56 202);">2022</div>
                        <h3 class="text-xl font-semibold text-neutral-900 mb-2 mt-2">Développement d'Olympiadus</h3>
                        <p class="text-neutral-600 mb-2">
                            <span class="font-medium text-neutral-700">Projet personnel</span> · Plateforme sportive
                        </p>
                        <p class="text-neutral-600">
                            Création d'une plateforme de gestion de compétitions sportives avec système de validation par QR code.
                            Classements en temps réel, gestion d'équipes et statistiques avancées.
                            Utilisée par +50 organisations sportives.
                        </p>
                        <div class="flex flex-wrap gap-2 mt-3">
                            <span class="text-xs px-2 py-1 bg-neutral-100 text-neutral-600 rounded">React</span>
                            <span class="text-xs px-2 py-1 bg-neutral-100 text-neutral-600 rounded">Ionic Capacitor</span>
                            <span class="text-xs px-2 py-1 bg-neutral-100 text-neutral-600 rounded">Laravel</span>
                            <span class="text-xs px-2 py-1 bg-neutral-100 text-neutral-600 rounded">WebSockets</span>
                        </div>
                    </div>
                </div>

                <!-- Batibid -->
                <div class="flex gap-6 items-start">
                    <div class="flex-shrink-0">
                        <div class="w-20 h-20 rounded-xl overflow-hidden shadow-lg">
                            <img src="{{ asset('images/timeline/2023-batibid.png') }}" alt="2023 - Batibid" class="w-full h-full object-cover">
                        </div>
                    </div>
                    <div class="flex-1 card">
                        <div class="badge" style="background-color: rgb(207 250 254); color: rgb(14 116 144);">2023</div>
                        <h3 class="text-xl font-semibold text-neutral-900 mb-2 mt-2">Lancement de Batibid</h3>
                        <p class="text-neutral-600 mb-2">
                            <span class="font-medium text-neutral-700">Client : Agence immobilière</span> · Bénin
                        </p>
                        <p class="text-neutral-600">
                            Développement d'une plateforme immobilière complète : back-office de gestion, système de facturation automatique,
                            moteur de recherche avancé et interface utilisateur moderne. Gestion de +500 biens immobiliers.
                        </p>
                        <div class="flex flex-wrap gap-2 mt-3">
                            <span class="text-xs px-2 py-1 bg-neutral-100 text-neutral-600 rounded">Laravel 10</span>
                            <span class="text-xs px-2 py-1 bg-neutral-100 text-neutral-600 rounded">Tailwind CSS</span>
                            <span class="text-xs px-2 py-1 bg-neutral-100 text-neutral-600 rounded">Alpine.js</span>
                            <span class="text-xs px-2 py-1 bg-neutral-100 text-neutral-600 rounded">Stripe</span>
                        </div>
                    </div>
                </div>

                <!-- Mission Data Migration -->
                <div class="flex gap-6 items-start">
                    <div class="flex-shrink-0">
                        <div class="w-20 h-20 rounded-xl overflow-hidden shadow-lg">
                            <img src="{{ asset('images/timeline/2024-datamigration.png') }}" alt="Data Migration" class="w-full h-full object-cover">
                        </div>
                    </div>
                    <div class="flex-1 card">
                        <div class="badge" style="background-color: rgb(209 250 229); color: rgb(4 120 87);">2024</div>
                        <h3 class="text-xl font-semibold text-neutral-900 mb-2 mt-2">Mission Data Migration</h3>
                        <p class="text-neutral-600 mb-2">
                            <span class="font-medium text-neutral-700">Client : Grand groupe industriel</span> · Transformation digitale
                        </p>
                        <p class="text-neutral-600">
                            Développement en 48h d'un outil de suivi de migration de données pour un programme de transformation digitale.
                            Gestion de projets, rédaction de spécifications, tableaux de bord et collaboration d'équipe.
                        </p>
                        <div class="flex flex-wrap gap-2 mt-3">
                            <span class="text-xs px-2 py-1 bg-neutral-100 text-neutral-600 rounded">Laravel 11</span>
                            <span class="text-xs px-2 py-1 bg-neutral-100 text-neutral-600 rounded">Livewire</span>
                            <span class="text-xs px-2 py-1 bg-neutral-100 text-neutral-600 rounded">Tailwind CSS</span>
                        </div>
                    </div>
                </div>

                <!-- Aujourd'hui -->
                <div class="flex gap-6 items-start">
                    <div class="flex-shrink-0">
                        <div class="w-20 h-20 rounded-xl overflow-hidden shadow-lg">
                            <img src="{{ asset('images/timeline/2025-today.png') }}" alt="2025 - Aujourd'hui" class="w-full h-full object-cover">
                        </div>
                    </div>
                    <div class="flex-1 card border-2 border-success-200 bg-success-50/30">
                        <div class="badge badge-success mb-2">2025</div>
                        <h3 class="text-xl font-semibold text-neutral-900 mb-2">Aujourd'hui</h3>
                        <p class="text-neutral-600 mb-2">
                            <span class="font-medium text-neutral-700">Le Laboratoire Numérique</span> · +20 projets livrés
                        </p>
                        <p class="text-neutral-600">
                            Poursuite du développement de solutions sur mesure. Spécialisation renforcée sur Laravel 12 et Ionic 8.
                            Accompagnement de startups et PME dans leur transformation digitale. Disponible pour de nouveaux projets ambitieux.
                        </p>
                        <div class="mt-4">
                            <a href="{{ route('contact') }}" class="inline-flex items-center gap-2 text-primary-600 font-medium hover:text-primary-700">
                                <span>Discutons de votre projet</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Final -->
<section class="gradient-primary py-16 md:py-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-white">
        <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-white mb-6">
            Travaillons ensemble
        </h2>
        <p class="text-xl text-white/90 mb-10">
            Vous avez un projet en tête ? Parlons-en et trouvons la meilleure solution pour le concrétiser.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('contact') }}" class="btn bg-white text-primary-600 hover:bg-neutral-50 shadow-lg">
                Me contacter
            </a>
            <a href="{{ route('projects') }}" class="btn bg-white/10 text-white hover:bg-white/20 backdrop-blur-sm border border-white/20">
                Découvrir mes projets
            </a>
        </div>
    </div>
</section>
@endsection
