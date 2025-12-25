@extends('layouts.app')

@section('title', 'Contact - Devis Développement Web & Mobile | Le Laboratoire Numérique')
@section('description', 'Contactez-moi pour discuter de votre projet web ou mobile. Devis gratuit sous 24-48h. Développeur Laravel et Ionic disponible pour vos projets sur mesure.')
@section('og_type', 'website')

@push('schema')
<x-schema-breadcrumb :items="[['name' => 'Contact', 'url' => route('contact')]]" />
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "ContactPage",
    "name": "Contact - Le Laboratoire Numérique",
    "description": "Formulaire de contact pour demander un devis ou discuter d'un projet de développement web ou mobile",
    "url": "{{ route('contact') }}",
    "mainEntity": {
        "@type": "Organization",
        "name": "Le Laboratoire Numérique",
        "email": "contact@lelaboratoirenumerique.com"
    }
}
</script>
@endpush

@section('content')
<!-- Hero Section -->
<section class="gradient-primary py-20 md:py-32">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-white">
            <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-white mb-6 leading-tight text-center">
                Une question ? Un projet ?<br>
                <span class="text-accent-300">Contactez-moi</span>
            </h1>
            <p class="text-xl md:text-2xl text-white/90 text-center">
                Je serais ravi d'échanger avec vous sur vos besoins et vos projets.
            </p>
        </div>
    </div>
</section>

<!-- Contact Section -->
<section class="py-16 md:py-24 bg-neutral-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
            <!-- Formulaire de contact -->
            <div>
                <h2 class="text-3xl md:text-4xl font-bold text-neutral-900 mb-6">Envoyez-moi un message</h2>
                <p class="text-lg text-neutral-600 mb-8">
                    Remplissez le formulaire ci-dessous et je vous répondrai dans les plus brefs délais.
                </p>

                @if(session('success'))
                    <div class="mb-6 p-4 bg-success-50 border border-success-200 rounded-lg">
                        <div class="flex items-start gap-3">
                            <svg class="w-6 h-6 text-success-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="text-success-800">{{ session('success') }}</p>
                        </div>
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-6 p-4 bg-danger-50 border border-danger-200 rounded-lg">
                        <div class="flex items-start gap-3">
                            <svg class="w-6 h-6 text-danger-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div>
                                <p class="text-danger-800 font-semibold mb-1">Erreurs dans le formulaire :</p>
                                <ul class="list-disc list-inside text-danger-700">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif

                <form action="{{ route('contact.send') }}" method="POST" class="space-y-6">
                    @csrf

                    <div>
                        <label for="name" class="block text-sm font-semibold text-neutral-900 mb-2">
                            Nom complet <span class="text-danger-500">*</span>
                        </label>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            value="{{ old('name') }}"
                            class="input @error('name') input-error @enderror"
                            placeholder="Jean Dupont"
                            required
                        >
                        @error('name')
                            <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-semibold text-neutral-900 mb-2">
                            Adresse email <span class="text-danger-500">*</span>
                        </label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="{{ old('email') }}"
                            class="input @error('email') input-error @enderror"
                            placeholder="jean.dupont@example.com"
                            required
                        >
                        @error('email')
                            <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="phone" class="block text-sm font-semibold text-neutral-900 mb-2">
                            Téléphone
                        </label>
                        <input
                            type="tel"
                            id="phone"
                            name="phone"
                            value="{{ old('phone') }}"
                            class="input @error('phone') input-error @enderror"
                            placeholder="+33 6 12 34 56 78"
                        >
                        @error('phone')
                            <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="subject" class="block text-sm font-semibold text-neutral-900 mb-2">
                            Sujet <span class="text-danger-500">*</span>
                        </label>
                        <select
                            id="subject"
                            name="subject"
                            class="input @error('subject') input-error @enderror"
                            required
                        >
                            <option value="">Sélectionnez un sujet</option>
                            <option value="Nouveau projet" {{ old('subject') == 'Nouveau projet' ? 'selected' : '' }}>Nouveau projet</option>
                            <option value="Demande de devis" {{ old('subject') == 'Demande de devis' ? 'selected' : '' }}>Demande de devis</option>
                            <option value="Question technique" {{ old('subject') == 'Question technique' ? 'selected' : '' }}>Question technique</option>
                            <option value="Collaboration" {{ old('subject') == 'Collaboration' ? 'selected' : '' }}>Collaboration</option>
                            <option value="Autre" {{ old('subject') == 'Autre' ? 'selected' : '' }}>Autre</option>
                        </select>
                        @error('subject')
                            <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="message" class="block text-sm font-semibold text-neutral-900 mb-2">
                            Message <span class="text-danger-500">*</span>
                        </label>
                        <textarea
                            id="message"
                            name="message"
                            rows="6"
                            class="input @error('message') input-error @enderror"
                            placeholder="Décrivez votre projet ou votre demande..."
                            required
                        >{{ old('message') }}</textarea>
                        @error('message')
                            <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="pt-4">
                        <button type="submit" class="btn btn-primary w-full hover-glow">
                            <svg class="w-5 h-5 mr-2 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            Envoyer le message
                        </button>
                    </div>
                </form>
            </div>

            <!-- Informations de contact -->
            <div class="space-y-8">
                <div>
                    <h2 class="text-3xl md:text-4xl font-bold text-neutral-900 mb-6">Informations</h2>
                    <p class="text-lg text-neutral-600 mb-8">
                        Vous pouvez également me contacter directement par email ou consulter mes projets en ligne.
                    </p>
                </div>

                <!-- Email -->
                <div class="card hover-lift">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-lg bg-primary-100 text-primary-600 flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold text-neutral-900 mb-1">Email</h3>
                            <a href="mailto:contact@lelaboratoirenumerique.com" class="text-primary-600 hover:text-primary-700 transition-colors">
                                contact@lelaboratoirenumerique.com
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Disponibilité -->
                <div class="card bg-neutral-100">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-lg bg-success-100 text-success-600 flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold text-neutral-900 mb-2">Disponibilité</h3>
                            <p class="text-neutral-600 mb-2">Je réponds généralement sous 24-48h.</p>
                            <div class="flex items-center gap-2 text-success-600">
                                <span class="w-2 h-2 rounded-full bg-success-500"></span>
                                <span class="text-sm font-medium">Disponible pour de nouveaux projets</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Projets en ligne -->
                <div class="card hover-lift">
                    <h3 class="text-xl font-semibold text-neutral-900 mb-4">Mes projets en ligne</h3>
                    <div class="space-y-3">
                        <a href="https://app.youplago.com" target="_blank" rel="noopener noreferrer"
                           class="flex items-center justify-between p-3 rounded-lg bg-neutral-50 hover:bg-neutral-100 transition-colors group">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-purple-600 to-purple-700 flex items-center justify-center">
                                    <span class="text-white font-bold text-sm">Y</span>
                                </div>
                                <div>
                                    <p class="font-medium text-neutral-900">Youplago</p>
                                    <p class="text-sm text-neutral-600">Organisation d'événements</p>
                                </div>
                            </div>
                            <svg class="w-5 h-5 text-neutral-400 group-hover:text-primary-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                            </svg>
                        </a>

                        <a href="https://app.batibid.com" target="_blank" rel="noopener noreferrer"
                           class="flex items-center justify-between p-3 rounded-lg bg-neutral-50 hover:bg-neutral-100 transition-colors group">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-cyan-600 to-blue-600 flex items-center justify-center">
                                    <span class="text-white font-bold text-sm">B</span>
                                </div>
                                <div>
                                    <p class="font-medium text-neutral-900">Batibid</p>
                                    <p class="text-sm text-neutral-600">Plateforme immobilière</p>
                                </div>
                            </div>
                            <svg class="w-5 h-5 text-neutral-400 group-hover:text-primary-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- FAQ rapide -->
                <div class="card bg-primary-50">
                    <h3 class="text-xl font-semibold text-neutral-900 mb-4">Questions fréquentes</h3>
                    <div class="space-y-3 text-sm">
                        <div>
                            <p class="font-semibold text-neutral-900 mb-1">💰 Proposez-vous des devis gratuits ?</p>
                            <p class="text-neutral-600">Oui, n'hésitez pas à décrire votre projet pour recevoir une estimation.</p>
                        </div>
                        <div>
                            <p class="font-semibold text-neutral-900 mb-1">⏱️ Quels sont vos délais moyens ?</p>
                            <p class="text-neutral-600">Cela dépend de la complexité du projet. Comptez 2-4 semaines pour un MVP.</p>
                        </div>
                        <div>
                            <p class="font-semibold text-neutral-900 mb-1">🌍 Travaillez-vous à distance ?</p>
                            <p class="text-neutral-600">Oui, je travaille principalement en télétravail avec mes clients.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
