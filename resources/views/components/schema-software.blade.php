@props([
    'name',
    'description',
    'url' => null,
    'image' => null,
    'category' => 'WebApplication',
    'platforms' => ['Web']
])

<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "SoftwareApplication",
    "name": "{{ $name }}",
    "description": "{{ $description }}",
    "applicationCategory": "{{ $category }}",
    "operatingSystem": "{{ implode(', ', $platforms) }}",
    @if($url)
    "url": "{{ $url }}",
    @endif
    @if($image)
    "image": "{{ $image }}",
    @endif
    "author": {
        "@@type": "Organization",
        "name": "Le Laboratoire Numérique"
    },
    "creator": {
        "@@type": "Person",
        "name": "Thomas Bourcy"
    }
}
</script>
