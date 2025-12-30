@props(['post', 'featured' => false])

<article class="card hover-lift group relative flex flex-col cursor-pointer {{ $featured ? 'md:col-span-2 lg:col-span-1' : '' }}">
    {{-- Lien principal couvrant toute la carte --}}
    <a href="{{ route('blog.show', $post->slug) }}" class="absolute inset-0 z-10" aria-label="Lire {{ $post->title }}"></a>

    @if($post->featured_image_url)
        <div class="overflow-hidden rounded-lg mb-4 -mx-6 -mt-6">
            <img
                src="{{ $post->featured_image_url }}"
                alt="{{ $post->title }}"
                class="w-full h-48 object-cover transition-transform duration-300 group-hover:scale-105"
                loading="lazy"
            >
        </div>
    @endif

    <div class="flex items-center gap-3 mb-3">
        @if($post->category)
            <span
                class="badge badge-primary relative z-20"
                @if($post->category->color) style="background-color: {{ $post->category->color }}20; color: {{ $post->category->color }};" @endif
            >
                {{ $post->category->name }}
            </span>
        @endif
        <span class="text-sm text-neutral-500">
            {{ $post->reading_time ?? 1 }} min de lecture
        </span>
    </div>

    <h3 class="heading-4 mb-2 group-hover:text-primary-600 transition-colors">
        {{ $post->title }}
    </h3>

    @if($post->excerpt)
        <p class="text-neutral-600 mb-4 line-clamp-2">
            {{ $post->excerpt }}
        </p>
    @endif

    <div class="flex items-center justify-between mt-auto pt-4 border-t border-neutral-100">
        <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-full bg-primary-100 flex items-center justify-center">
                <span class="text-sm font-medium text-primary-600">
                    {{ substr($post->author->name ?? 'A', 0, 1) }}
                </span>
            </div>
            <span class="text-sm text-neutral-600">{{ $post->author->name ?? 'Auteur' }}</span>
        </div>
        <time class="text-sm text-neutral-500" datetime="{{ $post->published_at?->toISOString() }}">
            {{ $post->published_at?->translatedFormat('d M Y') }}
        </time>
    </div>
</article>
