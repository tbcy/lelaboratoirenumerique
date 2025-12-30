@extends('layouts.app')

@section('title', $category->name . ' - Blog - Le Laboratoire Numérique')
@section('description', $category->description ?: 'Articles dans la catégorie ' . $category->name)

@section('content')
    <!-- Hero Section -->
    <section class="section-hero bg-neutral-50">
        <div class="container-custom">
            <div class="max-w-3xl">
                <nav class="flex items-center gap-2 text-sm text-neutral-500 mb-4">
                    <a href="{{ route('blog.index') }}" class="hover:text-primary-600">Blog</a>
                    <span>/</span>
                    <span class="text-neutral-900">{{ $category->name }}</span>
                </nav>

                <div class="flex items-center gap-4 mb-4">
                    @if($category->color)
                        <span class="w-4 h-4 rounded-full" style="background-color: {{ $category->color }};"></span>
                    @endif
                    <h1 class="heading-1">{{ $category->name }}</h1>
                </div>

                @if($category->description)
                    <p class="text-lead">
                        {{ $category->description }}
                    </p>
                @endif

                <p class="text-neutral-600 mt-4">
                    {{ $posts->total() }} article(s) dans cette catégorie
                </p>
            </div>
        </div>
    </section>

    <!-- Articles -->
    <section class="section">
        <div class="container-custom">
            <div class="flex flex-col lg:flex-row gap-12">
                <!-- Articles -->
                <div class="flex-1">
                    @if($posts->isEmpty())
                        <div class="text-center py-12">
                            <svg class="w-16 h-16 mx-auto text-neutral-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                            </svg>
                            <h3 class="heading-4 text-neutral-600">Aucun article dans cette catégorie</h3>
                            <p class="text-neutral-500 mt-2">
                                Les articles arrivent bientôt !
                            </p>
                            <a href="{{ route('blog.index') }}" class="btn btn-primary mt-6">
                                Voir tous les articles
                            </a>
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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

                <!-- Sidebar -->
                <aside class="lg:w-80 shrink-0">
                    <div class="card">
                        <h3 class="heading-4 mb-4">Catégories</h3>
                        <ul class="space-y-2">
                            <li>
                                <a
                                    href="{{ route('blog.index') }}"
                                    class="flex items-center justify-between py-2 px-3 rounded-lg transition-colors hover:bg-neutral-50"
                                >
                                    <span>Tous les articles</span>
                                </a>
                            </li>
                            @foreach($categories as $cat)
                                <li>
                                    <a
                                        href="{{ route('blog.category', $cat->slug) }}"
                                        class="flex items-center justify-between py-2 px-3 rounded-lg transition-colors {{ $cat->id === $category->id ? 'bg-primary-50 text-primary-600' : 'hover:bg-neutral-50' }}"
                                    >
                                        <span class="flex items-center gap-2">
                                            @if($cat->color)
                                                <span class="w-3 h-3 rounded-full" style="background-color: {{ $cat->color }};"></span>
                                            @endif
                                            {{ $cat->name }}
                                        </span>
                                        <span class="text-sm text-neutral-500">{{ $cat->published_posts_count }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </aside>
            </div>
        </div>
    </section>
@endsection
