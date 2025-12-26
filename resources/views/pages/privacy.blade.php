@extends('layouts.app')

@section('title', 'Politique de Confidentialité - Le Laboratoire Numérique')
@section('description', 'Politique de confidentialité et protection des données personnelles du site Le Laboratoire Numérique.')

@push('schema')
<x-schema-breadcrumb :items="[['name' => 'Politique de Confidentialité', 'url' => route('privacy')]]" />
@endpush

@section('content')
<!-- Hero Section -->
<section class="gradient-primary py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-white text-center">
            <h1 class="text-4xl md:text-5xl font-bold text-white mb-4">Politique de Confidentialité</h1>
            <p class="text-xl text-white/90">
                Votre vie privée est importante pour nous
            </p>
        </div>
    </div>
</section>

<!-- Contenu -->
<section class="py-16 md:py-24 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-4xl mx-auto">

            <!-- Introduction -->
            <div class="mb-16">
                <p class="text-lg text-neutral-700 mb-4 leading-relaxed">
                    Cette politique de confidentialité décrit comment <strong class="text-neutral-900">Le Laboratoire Numérique</strong> collecte, utilise et protège vos données personnelles lorsque vous utilisez ce site web.
                </p>
                <p class="text-lg text-neutral-700 leading-relaxed">
                    En utilisant ce site, vous acceptez les pratiques décrites dans cette politique.
                </p>
            </div>

            <!-- Responsable du traitement -->
            <div class="mb-16">
                <h2 class="text-3xl font-bold text-neutral-900 mb-6">Responsable du traitement des données</h2>
                <div class="card bg-neutral-50">
                    <p class="mb-3 text-neutral-700"><strong class="text-neutral-900">Responsable :</strong> Thomas Bourcy</p>
                    <p class="mb-3 text-neutral-700"><strong class="text-neutral-900">Auto-entreprise :</strong> Le Laboratoire Numérique</p>
                    <p class="text-neutral-700"><strong class="text-neutral-900">Email :</strong> <a href="mailto:contact@lelaboratoirenumerique.com" class="text-primary-600 hover:text-primary-700 underline">contact@lelaboratoirenumerique.com</a></p>
                </div>
            </div>

            <!-- Données collectées -->
            <div class="mb-16">
                <h2 class="text-3xl font-bold text-neutral-900 mb-6">Données collectées</h2>

                <h3 class="text-2xl font-semibold text-neutral-900 mb-4 mt-8">1. Formulaire de contact</h3>
                <p class="text-lg text-neutral-700 mb-4 leading-relaxed">
                    Lorsque vous utilisez le formulaire de contact, nous collectons les informations suivantes :
                </p>
                <ul class="list-disc list-inside text-lg text-neutral-700 mb-6 space-y-2 ml-4 leading-relaxed">
                    <li>Votre nom et prénom</li>
                    <li>Votre adresse email</li>
                    <li>Le contenu de votre message</li>
                </ul>
                <p class="text-lg text-neutral-700 leading-relaxed">
                    Ces données sont collectées uniquement dans le but de traiter votre demande et de vous répondre.
                </p>

                <h3 class="text-2xl font-semibold text-neutral-900 mb-4 mt-8">2. Données de navigation</h3>
                <p class="text-lg text-neutral-700 mb-4 leading-relaxed">
                    Nous pouvons collecter des statistiques anonymes de navigation pour améliorer nos services, incluant :
                </p>
                <ul class="list-disc list-inside text-lg text-neutral-700 mb-6 space-y-2 ml-4 leading-relaxed">
                    <li>Pages visitées</li>
                    <li>Durée de visite</li>
                    <li>Type de navigateur et appareil utilisé</li>
                    <li>Provenance géographique générale</li>
                </ul>
                <p class="text-lg text-neutral-700 leading-relaxed">
                    <strong class="text-neutral-900">Important :</strong> Ces données sont collectées de manière anonyme et agrégée. Nous n'utilisons aucun traceur publicitaire tiers.
                </p>
            </div>

            <!-- Utilisation des données -->
            <div class="mb-16">
                <h2 class="text-3xl font-bold text-neutral-900 mb-6">Utilisation des données</h2>
                <p class="text-lg text-neutral-700 mb-4 leading-relaxed">
                    Les données personnelles collectées via le formulaire de contact sont utilisées exclusivement pour :
                </p>
                <ul class="list-disc list-inside text-lg text-neutral-700 mb-6 space-y-2 ml-4 leading-relaxed">
                    <li>Répondre à vos demandes d'information</li>
                    <li>Traiter vos demandes de contact</li>
                    <li>Vous fournir les services que vous avez sollicités</li>
                </ul>
                <p class="text-lg text-neutral-700 leading-relaxed">
                    Nous n'utilisons <strong class="text-neutral-900">jamais</strong> vos données à des fins commerciales non sollicitées et nous ne les partageons avec aucun tiers.
                </p>
            </div>

            <!-- Conservation des données -->
            <div class="mb-16">
                <h2 class="text-3xl font-bold text-neutral-900 mb-6">Conservation et suppression des données</h2>
                <div class="card border-2 border-success-200 bg-success-50/30">
                    <p class="text-lg text-neutral-700 mb-4 leading-relaxed">
                        <strong class="text-neutral-900">Conservation limitée :</strong> Les données collectées via le formulaire de contact sont conservées uniquement le temps nécessaire au traitement de votre demande.
                    </p>
                    <p class="text-lg text-neutral-700 leading-relaxed">
                        <strong class="text-neutral-900">Suppression sur demande :</strong> Vous pouvez à tout moment demander la suppression de vos données personnelles en nous envoyant un simple email à <a href="mailto:contact@lelaboratoirenumerique.com" class="text-primary-600 hover:text-primary-700 underline">contact@lelaboratoirenumerique.com</a>. Nous procéderons à la suppression de l'ensemble de votre historique dans les meilleurs délais.
                    </p>
                </div>
            </div>

            <!-- Vos droits -->
            <div class="mb-16">
                <h2 class="text-3xl font-bold text-neutral-900 mb-6">Vos droits (RGPD)</h2>
                <p class="text-lg text-neutral-700 mb-4 leading-relaxed">
                    Conformément au Règlement Général sur la Protection des Données (RGPD), vous disposez des droits suivants :
                </p>
                <ul class="list-disc list-inside text-lg text-neutral-700 mb-6 space-y-2 ml-4 leading-relaxed">
                    <li><strong class="text-neutral-900">Droit d'accès :</strong> Vous pouvez demander une copie de vos données personnelles</li>
                    <li><strong class="text-neutral-900">Droit de rectification :</strong> Vous pouvez demander la correction de vos données inexactes</li>
                    <li><strong class="text-neutral-900">Droit à l'effacement :</strong> Vous pouvez demander la suppression de vos données</li>
                    <li><strong class="text-neutral-900">Droit d'opposition :</strong> Vous pouvez vous opposer au traitement de vos données</li>
                    <li><strong class="text-neutral-900">Droit à la portabilité :</strong> Vous pouvez récupérer vos données dans un format structuré</li>
                </ul>
                <p class="text-lg text-neutral-700 leading-relaxed">
                    Pour exercer ces droits, contactez-nous à <a href="mailto:contact@lelaboratoirenumerique.com" class="text-primary-600 hover:text-primary-700 underline">contact@lelaboratoirenumerique.com</a>.
                </p>
            </div>

            <!-- Cookies -->
            <div class="mb-16">
                <h2 class="text-3xl font-bold text-neutral-900 mb-6">Cookies et traceurs</h2>
                <p class="text-lg text-neutral-700 mb-4 leading-relaxed">
                    Ce site utilise uniquement des cookies techniques nécessaires au bon fonctionnement du site (session, préférences).
                </p>
                <p class="text-lg text-neutral-700 leading-relaxed">
                    <strong class="text-neutral-900">Aucun cookie publicitaire ou de tracking tiers n'est utilisé.</strong> Nous respectons votre vie privée et ne partageons aucune donnée avec des régies publicitaires.
                </p>
            </div>

            <!-- Sécurité -->
            <div class="mb-16">
                <h2 class="text-3xl font-bold text-neutral-900 mb-6">Sécurité des données</h2>
                <p class="text-lg text-neutral-700 leading-relaxed">
                    Nous mettons en œuvre des mesures techniques et organisationnelles appropriées pour protéger vos données personnelles contre tout accès non autorisé, modification, divulgation ou destruction. Toutes les communications sont chiffrées via HTTPS.
                </p>
            </div>

            <!-- Modifications -->
            <div class="mb-16">
                <h2 class="text-3xl font-bold text-neutral-900 mb-6">Modifications de la politique</h2>
                <p class="text-lg text-neutral-700 leading-relaxed">
                    Nous nous réservons le droit de modifier cette politique de confidentialité à tout moment. Les modifications seront publiées sur cette page avec une date de mise à jour.
                </p>
            </div>

            <!-- Contact -->
            <div class="card border-2 border-primary-200 bg-primary-50/30">
                <h3 class="text-2xl font-semibold text-neutral-900 mb-4">Des questions sur vos données ?</h3>
                <p class="text-lg text-neutral-700 mb-6 leading-relaxed">
                    Pour toute question concernant le traitement de vos données personnelles ou pour exercer vos droits, n'hésitez pas à me contacter.
                </p>
                <a href="{{ route('contact') }}" class="btn btn-primary">
                    Me contacter
                </a>
            </div>

        </div>
    </div>
</section>
@endsection
