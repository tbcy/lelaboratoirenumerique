@props(['items' => []])

<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "BreadcrumbList",
    "itemListElement": [
        {
            "@@type": "ListItem",
            "position": 1,
            "name": "Accueil",
            "item": "{{ config('app.url') }}"
        }
        @if(count($items) > 0)
            @foreach($items as $index => $item)
        ,{
            "@@type": "ListItem",
            "position": {{ $index + 2 }},
            "name": "{{ $item['name'] }}",
            "item": "{{ $item['url'] }}"
        }
            @endforeach
        @endif
    ]
}
</script>
