@extends('layouts.app')

@section('title', 'Tag : ' . $tag->name . ' - Blog - Le Laboratoire Numérique')
@section('description', 'Articles tagués avec ' . $tag->name)

@section('content')
    <!-- Hero Section -->
    <section class="section-hero bg-neutral-50">
        <div class="container-custom">
            <div class="max-w-3xl">
                <nav class="flex items-center gap-2 text-sm text-neutral-500 mb-4">
                    <a href="{{ route('blog.index') }}" class="hover:text-primary-600">Blog</a>
                    <span>/</span>
                    <span>Tag</span>
                    <span>/</span>
                    <span class="text-neutral-900">{{ $tag->name }}</span>
                </nav>

                <div class="flex items-center gap-3 mb-4">
                    <span class="badge badge-secondary text-lg px-4 py-2">{{ $tag->name }}</span>
                </div>

                <h1 class="heading-2">
                    Articles tagués "{{ $tag->name }}"
                </h1>

                <p class="text-neutral-600 mt-4">
                    {{ $posts->total() }} article(s) avec ce tag
                </p>
            </div>
        </div>
    </section>

    <!-- Articles -->
    <section class="section">
        <div class="container-custom">
            @if($posts->isEmpty())
                <div class="text-center py-12 max-w-lg mx-auto">
                    <svg class="w-16 h-16 mx-auto text-neutral-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                    </svg>
                    <h3 class="heading-4 text-neutral-600">Aucun article avec ce tag</h3>
                    <p class="text-neutral-500 mt-2">
                        Les articles arrivent bientôt !
                    </p>
                    <a href="{{ route('blog.index') }}" class="btn btn-primary mt-6">
                        Voir tous les articles
                    </a>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($posts as $post)
                        <x-blog.post-card :post="$post" />
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="mt-12">
                    {{ $posts->links() }}
                </div>
            @endif
        </div>
    </section>

    <!-- Retour au blog -->
    <section class="section bg-neutral-50">
        <div class="container-custom text-center">
            <a href="{{ route('blog.index') }}" class="btn btn-outline">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Retour au blog
            </a>
        </div>
    </section>
@endsection
