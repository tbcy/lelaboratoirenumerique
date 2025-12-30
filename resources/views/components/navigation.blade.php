<nav class="bg-white border-b border-neutral-200 sticky top-0 z-50 backdrop-blur-lg bg-white/90">
    <div class="container-custom">
        <div class="flex items-center justify-between h-20">
            <!-- Logo -->
            <a href="{{ route('home') }}" class="flex items-center gap-3 group">
                <div class="w-10 h-10 rounded-lg gradient-primary flex items-center justify-center transition-transform group-hover:scale-105">
                    <span class="text-white font-bold text-xl">LN</span>
                </div>
                <span class="text-xl font-bold text-neutral-900 hidden sm:block">
                    Le Laboratoire <span class="text-primary-600">Numérique</span>
                </span>
            </a>

            <!-- Desktop Navigation -->
            <div class="hidden md:flex items-center gap-8">
                <a href="{{ route('home') }}" class="text-neutral-700 hover:text-primary-600 font-medium transition-colors {{ request()->routeIs('home') ? 'text-primary-600' : '' }}">
                    Accueil
                </a>
                <a href="{{ route('projects') }}" class="text-neutral-700 hover:text-primary-600 font-medium transition-colors {{ request()->routeIs('projects') ? 'text-primary-600' : '' }}">
                    Projets
                </a>
                <a href="{{ route('blog.index') }}" class="text-neutral-700 hover:text-primary-600 font-medium transition-colors {{ request()->routeIs('blog.*') ? 'text-primary-600' : '' }}">
                    Blog
                </a>
                <a href="{{ route('services') }}" class="text-neutral-700 hover:text-primary-600 font-medium transition-colors {{ request()->routeIs('services') ? 'text-primary-600' : '' }}">
                    Services
                </a>
                <a href="{{ route('about') }}" class="text-neutral-700 hover:text-primary-600 font-medium transition-colors {{ request()->routeIs('about') ? 'text-primary-600' : '' }}">
                    À propos
                </a>
                <a href="{{ route('contact') }}" class="btn btn-primary">
                    Contact
                </a>
            </div>

            <!-- Mobile Menu Button -->
            <button
                type="button"
                class="md:hidden p-2 rounded-lg text-neutral-700 hover:bg-neutral-100 transition-colors"
                onclick="toggleMobileMenu()"
                aria-label="Menu"
            >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>
        </div>

        <!-- Mobile Navigation -->
        <div id="mobile-menu" class="hidden md:hidden pb-6 pt-2">
            <div class="flex flex-col gap-4">
                <a href="{{ route('home') }}" class="text-neutral-700 hover:text-primary-600 font-medium transition-colors py-2 {{ request()->routeIs('home') ? 'text-primary-600' : '' }}">
                    Accueil
                </a>
                <a href="{{ route('projects') }}" class="text-neutral-700 hover:text-primary-600 font-medium transition-colors py-2 {{ request()->routeIs('projects') ? 'text-primary-600' : '' }}">
                    Projets
                </a>
                <a href="{{ route('blog.index') }}" class="text-neutral-700 hover:text-primary-600 font-medium transition-colors py-2 {{ request()->routeIs('blog.*') ? 'text-primary-600' : '' }}">
                    Blog
                </a>
                <a href="{{ route('services') }}" class="text-neutral-700 hover:text-primary-600 font-medium transition-colors py-2 {{ request()->routeIs('services') ? 'text-primary-600' : '' }}">
                    Services
                </a>
                <a href="{{ route('about') }}" class="text-neutral-700 hover:text-primary-600 font-medium transition-colors py-2 {{ request()->routeIs('about') ? 'text-primary-600' : '' }}">
                    À propos
                </a>
                <a href="{{ route('contact') }}" class="btn btn-primary w-full">
                    Contact
                </a>
            </div>
        </div>
    </div>
</nav>

<script>
function toggleMobileMenu() {
    const menu = document.getElementById('mobile-menu');
    menu.classList.toggle('hidden');
}

// Close mobile menu when clicking outside
document.addEventListener('click', function(event) {
    const menu = document.getElementById('mobile-menu');
    const button = event.target.closest('button');

    if (!menu.contains(event.target) && !button && !menu.classList.contains('hidden')) {
        menu.classList.add('hidden');
    }
});
</script>
