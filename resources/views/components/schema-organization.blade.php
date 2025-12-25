<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "Organization",
    "name": "Le Laboratoire Numérique",
    "url": "{{ config('app.url') }}",
    "logo": "{{ asset('images/logo.png') }}",
    "description": "Développeur Full-Stack spécialisé en Laravel et Ionic. Création d'applications web et mobiles sur mesure.",
    "founder": {
        "@@type": "Person",
        "name": "Thomas Bourcy"
    },
    "foundingDate": "2020",
    "areaServed": {
        "@@type": "Country",
        "name": "France"
    },
    "contactPoint": {
        "@@type": "ContactPoint",
        "contactType": "customer service",
        "availableLanguage": ["French", "English"],
        "url": "{{ route('contact') }}"
    },
    "knowsAbout": [
        "Laravel",
        "Ionic",
        "PHP",
        "JavaScript",
        "Développement Web",
        "Applications Mobiles",
        "Full-Stack Development"
    ]
}
</script>
