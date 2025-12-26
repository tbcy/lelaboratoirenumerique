@extends('layouts.app')

@section('title', 'Mentions Légales - Le Laboratoire Numérique')
@section('description', 'Mentions légales du site Le Laboratoire Numérique, auto-entreprise de développement web et mobile dirigée par Thomas Bourcy.')

@push('schema')
<x-schema-breadcrumb :items="[['name' => 'Mentions Légales', 'url' => route('legal')]]" />
@endpush

@section('content')
<!-- Hero Section -->
<section class="gradient-primary py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-white text-center">
            <h1 class="text-4xl md:text-5xl font-bold text-white mb-4">Mentions Légales</h1>
            <p class="text-xl text-white/90">
                Informations légales concernant ce site
            </p>
        </div>
    </div>
</section>

<!-- Contenu -->
<section class="py-16 md:py-24 bg-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="space-y-12">

            <!-- Éditeur du site -->
            <div class="mb-12">
                <h2 class="text-2xl font-bold text-neutral-900 mb-4">Éditeur du site</h2>
                <div class="card bg-neutral-50">
                    <p class="mb-2"><strong>Raison sociale :</strong> Le Laboratoire Numérique</p>
                    <p class="mb-2"><strong>Statut juridique :</strong> Auto-entreprise</p>
                    <p class="mb-2"><strong>Dirigeant :</strong> Thomas Bourcy</p>
                    <p class="mb-2"><strong>Adresse :</strong> 44 rue de Nanterre, 92600 Asnières-sur-Seine, France</p>
                    <p class="mb-2"><strong>Email :</strong> <a href="mailto:contact@lelaboratoirenumerique.com" class="text-primary-600 hover:text-primary-700">contact@lelaboratoirenumerique.com</a></p>
                </div>
            </div>

            <!-- Hébergeur -->
            <div class="mb-12">
                <h2 class="text-2xl font-bold text-neutral-900 mb-4">Hébergeur</h2>
                <div class="card bg-neutral-50">
                    <p class="mb-2"><strong>Raison sociale :</strong> o2switch</p>
                    <p class="mb-2"><strong>Adresse :</strong> Chemin des Pardiaux, 63000 Clermont-Ferrand, France</p>
                    <p class="mb-2"><strong>Téléphone :</strong> +33 4 44 44 60 40</p>
                    <p class="mb-2"><strong>Site web :</strong> <a href="https://www.o2switch.fr" target="_blank" rel="noopener" class="text-primary-600 hover:text-primary-700">www.o2switch.fr</a></p>
                </div>
            </div>

            <!-- Propriété intellectuelle -->
            <div class="mb-12">
                <h2 class="text-2xl font-bold text-neutral-900 mb-4">Propriété intellectuelle</h2>
                <p class="text-neutral-600 mb-4">
                    L'ensemble du contenu de ce site (textes, images, vidéos, logos, graphismes, etc.) est la propriété exclusive de Thomas Bourcy / Le Laboratoire Numérique, sauf mention contraire.
                </p>
                <p class="text-neutral-600">
                    Toute reproduction, distribution, modification, adaptation, retransmission ou publication de ces différents éléments est strictement interdite sans l'accord écrit de l'éditeur.
                </p>
            </div>

            <!-- Responsabilité -->
            <div class="mb-12">
                <h2 class="text-2xl font-bold text-neutral-900 mb-4">Limitation de responsabilité</h2>
                <p class="text-neutral-600 mb-4">
                    Le Laboratoire Numérique s'efforce d'assurer l'exactitude et la mise à jour des informations diffusées sur ce site, dont elle se réserve le droit de corriger le contenu à tout moment et sans préavis.
                </p>
                <p class="text-neutral-600">
                    Toutefois, Le Laboratoire Numérique ne peut garantir l'exactitude, la précision ou l'exhaustivité des informations mises à disposition sur ce site. En conséquence, l'utilisateur reconnaît utiliser ces informations sous sa responsabilité exclusive.
                </p>
            </div>

            <!-- Liens hypertextes -->
            <div class="mb-12">
                <h2 class="text-2xl font-bold text-neutral-900 mb-4">Liens hypertextes</h2>
                <p class="text-neutral-600">
                    Ce site peut contenir des liens vers d'autres sites web. Le Laboratoire Numérique ne saurait être tenu responsable du contenu de ces sites tiers. L'existence d'un lien vers un autre site ne constitue pas une validation de ce site ou de son contenu.
                </p>
            </div>

            <!-- Droit applicable -->
            <div class="mb-12">
                <h2 class="text-2xl font-bold text-neutral-900 mb-4">Droit applicable</h2>
                <p class="text-neutral-600">
                    Les présentes mentions légales sont régies par le droit français. En cas de litige et à défaut d'accord amiable, le litige sera porté devant les tribunaux français conformément aux règles de compétence en vigueur.
                </p>
            </div>

            <!-- Contact -->
            <div class="card border-2 border-primary-200 bg-primary-50/30">
                <h3 class="text-xl font-semibold text-neutral-900 mb-3">Une question ?</h3>
                <p class="text-neutral-600 mb-4">
                    Pour toute question concernant les mentions légales de ce site, n'hésitez pas à me contacter.
                </p>
                <a href="{{ route('contact') }}" class="btn btn-primary">
                    Me contacter
                </a>
            </div>

        </div>
    </div>
</section>
@endsection
