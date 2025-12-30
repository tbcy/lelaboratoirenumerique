@extends('layouts.app')

@section('title', ($post->meta_title ?: $post->title) . ' - Le Laboratoire Numérique')
@section('description', $post->meta_description ?: $post->excerpt)
@section('og_type', 'article')
@section('og_image', $post->featured_image_url ?? asset('images/og-default.jpg'))

@push('schema')
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'BlogPosting',
    'headline' => $post->title,
    'description' => $post->excerpt,
    'image' => $post->featured_image_url,
    'author' => [
        '@type' => 'Person',
        'name' => $post->author->name ?? 'Thomas Bourcy',
    ],
    'publisher' => [
        '@type' => 'Organization',
        'name' => 'Le Laboratoire Numérique',
        'logo' => [
            '@type' => 'ImageObject',
            'url' => asset('images/logo.png'),
        ],
    ],
    'datePublished' => $post->published_at?->toISOString(),
    'dateModified' => $post->updated_at->toISOString(),
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
</script>
@endpush

@section('content')
    <article>
        <!-- Header -->
        <header class="section-hero bg-neutral-50">
            <div class="container-custom">
                <div class="max-w-3xl mx-auto text-center">
                    <!-- Catégorie -->
                    @if($post->category)
                        <a
                            href="{{ route('blog.category', $post->category->slug) }}"
                            class="badge badge-primary mb-4"
                            @if($post->category->color) style="background-color: {{ $post->category->color }}20; color: {{ $post->category->color }};" @endif
                        >
                            {{ $post->category->name }}
                        </a>
                    @endif

                    <h1 class="heading-1">{{ $post->title }}</h1>

                    @if($post->excerpt)
                        <p class="text-lead mt-6">
                            {{ $post->excerpt }}
                        </p>
                    @endif

                    <!-- Meta -->
                    <div class="flex items-center justify-center gap-6 mt-8 text-neutral-600">
                        <div class="flex items-center gap-2">
                            <div class="w-10 h-10 rounded-full bg-primary-100 flex items-center justify-center">
                                <span class="font-medium text-primary-600">
                                    {{ substr($post->author->name ?? 'A', 0, 1) }}
                                </span>
                            </div>
                            <span>{{ $post->author->name ?? 'Auteur' }}</span>
                        </div>
                        <span class="text-neutral-300">|</span>
                        <time datetime="{{ $post->published_at?->toISOString() }}">
                            {{ $post->published_at?->translatedFormat('d F Y') }}
                        </time>
                        <span class="text-neutral-300">|</span>
                        <span>{{ $post->reading_time ?? 1 }} min de lecture</span>
                    </div>
                </div>
            </div>
        </header>

        <!-- Image à la une -->
        @if($post->featured_image_url)
            <div class="container-custom -mt-8">
                <div class="max-w-4xl mx-auto">
                    <img
                        src="{{ $post->featured_image_url }}"
                        alt="{{ $post->title }}"
                        class="w-full rounded-xl shadow-lg"
                    >
                </div>
            </div>
        @endif

        <!-- Contenu -->
        <section class="section">
            <div class="container-custom">
                <div class="max-w-3xl mx-auto">
                    <div class="prose prose-lg prose-neutral max-w-none
                        prose-headings:font-bold prose-headings:text-neutral-900
                        prose-a:text-primary-600 prose-a:no-underline hover:prose-a:underline
                        prose-img:rounded-lg prose-img:shadow-md
                        prose-code:text-primary-600 prose-code:bg-primary-50 prose-code:px-1 prose-code:py-0.5 prose-code:rounded
                        prose-pre:bg-neutral-900 prose-pre:text-neutral-100
                    ">
                        {!! $post->content !!}
                    </div>

                    <!-- Tags -->
                    @if($post->tags->isNotEmpty())
                        <div class="flex flex-wrap gap-2 mt-12 pt-8 border-t border-neutral-200">
                            <span class="text-neutral-600 mr-2">Tags :</span>
                            @foreach($post->tags as $tag)
                                <a
                                    href="{{ route('blog.tag', $tag->slug) }}"
                                    class="badge badge-secondary"
                                >
                                    {{ $tag->name }}
                                </a>
                            @endforeach
                        </div>
                    @endif

                    <!-- Partage -->
                    <div class="flex items-center gap-4 mt-8 pt-8 border-t border-neutral-200">
                        <span class="text-neutral-600">Partager :</span>
                        <a
                            href="https://twitter.com/intent/tweet?url={{ urlencode(url()->current()) }}&text={{ urlencode($post->title) }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="w-10 h-10 rounded-full bg-neutral-100 hover:bg-neutral-200 flex items-center justify-center transition-colors"
                            title="Partager sur Twitter"
                        >
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                            </svg>
                        </a>
                        <a
                            href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode(url()->current()) }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="w-10 h-10 rounded-full bg-neutral-100 hover:bg-neutral-200 flex items-center justify-center transition-colors"
                            title="Partager sur LinkedIn"
                        >
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Articles connexes -->
        @if($relatedPosts->isNotEmpty())
            <section class="section bg-neutral-50">
                <div class="container-custom">
                    <h2 class="heading-3 mb-8">Articles similaires</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        @foreach($relatedPosts as $relatedPost)
                            <x-blog.post-card :post="$relatedPost" />
                        @endforeach
                    </div>
                </div>
            </section>
        @endif
    </article>

    <!-- CTA -->
    <section class="section gradient-primary">
        <div class="container-custom text-center">
            <h2 class="heading-2 text-white">Vous avez un projet ?</h2>
            <p class="text-white/90 text-lg mt-4 max-w-2xl mx-auto">
                Discutons de votre projet et voyons comment je peux vous aider à le concrétiser.
            </p>
            <a href="{{ route('contact') }}" class="btn bg-white text-primary-600 hover:bg-neutral-50 mt-8">
                Me contacter
            </a>
        </div>
    </section>
@endsection
