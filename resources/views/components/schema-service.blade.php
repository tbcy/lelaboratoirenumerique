@props(['services' => []])

<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "ItemList",
    "itemListElement": [
        @foreach($services as $index => $service)
        {
            "@@type": "ListItem",
            "position": {{ $index + 1 }},
            "item": {
                "@@type": "Service",
                "name": "{{ $service['name'] }}",
                "description": "{{ $service['description'] }}",
                "provider": {
                    "@@type": "Organization",
                    "name": "Le Laboratoire Numérique"
                },
                "areaServed": {
                    "@@type": "Country",
                    "name": "France"
                },
                "serviceType": "{{ $service['type'] ?? 'Développement Web' }}"
            }
        }@if(!$loop->last),@endif
        @endforeach
    ]
}
</script>
