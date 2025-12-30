@extends('layouts.app')

@section('title', 'Blog - Le Laboratoire Numérique')
@section('description', 'Articles et tutoriels sur le développement web et mobile. Laravel, Ionic, PHP, JavaScript et bonnes pratiques.')

@section('content')
    <!-- Hero Section -->
    <section class="py-16 md:py-20 bg-neutral-50">
        <div class="container-custom">
            <div class="max-w-3xl">
                <h1 class="heading-1">Blog</h1>
                <p class="text-lead mt-4">
                    Articles, tutoriels et retours d'expérience sur le développement web et mobile.
                </p>
            </div>

            <!-- Barre de recherche -->
            <form action="{{ route('blog.index') }}" method="GET" class="mt-8 max-w-xl">
                <div class="relative">
                    <input
                        type="text"
                        name="q"
                        value="{{ $search ?? '' }}"
                        placeholder="Rechercher un article..."
                        class="w-full px-4 py-3 pl-12 pr-4 bg-white border border-neutral-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                    >
                    <svg class="w-5 h-5 text-neutral-400 absolute left-4 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    @if($search)
                        <a href="{{ route('blog.index') }}" class="absolute right-4 top-1/2 -translate-y-1/2 text-neutral-400 hover:text-neutral-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </section>

    <!-- Contenu principal -->
    <section class="py-12 md:py-16">
        <div class="container-custom">
            <div class="flex flex-col lg:flex-row gap-12">
                <!-- Articles -->
                <div class="flex-1">
                    @if($search)
                        <div class="mb-8">
                            <p class="text-neutral-600">
                                {{ $posts->total() }} résultat(s) pour "<strong>{{ $search }}</strong>"
                            </p>
                        </div>
                    @endif

                    <!-- Articles en vedette -->
                    @if($featuredPosts->isNotEmpty() && !$search && !$currentCategory)
                        <div class="mb-12">
                            <h2 class="heading-3 mb-6">Articles en vedette</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                @foreach($featuredPosts as $post)
                                    <x-blog.post-card :post="$post" :featured="true" />
                                @endforeach
                            </div>
                        </div>

                        <h2 class="heading-3 mb-6">Tous les articles</h2>
                    @endif

                    @if($posts->isEmpty())
                        <div class="text-center py-12 bg-neutral-50 rounded-xl">
                            <svg class="w-16 h-16 mx-auto text-neutral-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                            </svg>
                            <h3 class="heading-4 text-neutral-600">Aucun article trouvé</h3>
                            <p class="text-neutral-500 mt-2">
                                @if($search)
                                    Essayez avec d'autres mots-clés.
                                @else
                                    Les articles arrivent bientôt !
                                @endif
                            </p>
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @foreach($posts as $post)
                                <x-blog.post-card :post="$post" />
                            @endforeach
                        </div>

                        <!-- Pagination -->
                        @if($posts->hasPages())
                            <div class="mt-12">
                                {{ $posts->links() }}
                            </div>
                        @endif
                    @endif
                </div>

                <!-- Sidebar -->
                <aside class="lg:w-72 shrink-0 space-y-6">
                    <!-- Catégories -->
                    @if($categories->isNotEmpty())
                        <div class="card">
                            <h3 class="font-bold text-lg mb-4">Catégories</h3>
                            <ul class="space-y-1">
                                <li>
                                    <a
                                        href="{{ route('blog.index') }}"
                                        class="flex items-center justify-between py-2 px-3 rounded-lg transition-colors {{ !$currentCategory ? 'bg-primary-50 text-primary-600 font-medium' : 'hover:bg-neutral-50' }}"
                                    >
                                        <span>Tous les articles</span>
                                    </a>
                                </li>
                                @foreach($categories as $category)
                                    <li>
                                        <a
                                            href="{{ route('blog.category', $category->slug) }}"
                                            class="flex items-center justify-between py-2 px-3 rounded-lg transition-colors {{ $currentCategory === $category->slug ? 'bg-primary-50 text-primary-600 font-medium' : 'hover:bg-neutral-50' }}"
                                        >
                                            <span class="flex items-center gap-2">
                                                @if($category->color)
                                                    <span class="w-2.5 h-2.5 rounded-full" style="background-color: {{ $category->color }};"></span>
                                                @endif
                                                {{ $category->name }}
                                            </span>
                                            <span class="text-sm text-neutral-400">{{ $category->published_posts_count }}</span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- CTA -->
                    <div class="card bg-gradient-to-br from-primary-600 to-primary-700 text-white">
                        <h3 class="font-bold text-lg mb-2">Un projet en tête ?</h3>
                        <p class="text-white/80 text-sm mb-4">
                            Discutons de votre projet et voyons comment je peux vous aider.
                        </p>
                        <a href="{{ route('contact') }}" class="btn bg-white text-primary-600 hover:bg-neutral-100 w-full text-sm">
                            Me contacter
                        </a>
                    </div>
                </aside>
            </div>
        </div>
    </section>
@endsection
