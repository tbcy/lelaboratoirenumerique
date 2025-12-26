<footer class="bg-neutral-900 text-white">
    <div class="container-custom">
        <!-- Main Footer -->
        <div class="py-12 md:py-16 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <!-- About -->
            <div class="lg:col-span-2">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-lg gradient-primary flex items-center justify-center">
                        <span class="text-white font-bold text-xl">LN</span>
                    </div>
                    <span class="text-xl font-bold">
                        Le Laboratoire <span class="text-primary-400">Numérique</span>
                    </span>
                </div>
                <p class="text-neutral-400">
                    Développeur Full-Stack spécialisé en Laravel et Ionic. Je conçois et développe des applications web et mobiles sur mesure pour concrétiser vos projets.
                </p>
            </div>

            <!-- Navigation -->
            <div>
                <h3 class="font-semibold text-lg mb-4">Navigation</h3>
                <ul class="space-y-3">
                    <li><a href="{{ route('home') }}" class="text-neutral-400 hover:text-white transition-colors">Accueil</a></li>
                    <li><a href="{{ route('projects') }}" class="text-neutral-400 hover:text-white transition-colors">Projets</a></li>
                    <li><a href="{{ route('services') }}" class="text-neutral-400 hover:text-white transition-colors">Services</a></li>
                    <li><a href="{{ route('about') }}" class="text-neutral-400 hover:text-white transition-colors">À propos</a></li>
                    <li><a href="{{ route('contact') }}" class="text-neutral-400 hover:text-white transition-colors">Contact</a></li>
                </ul>
            </div>
        </div>

        <!-- Bottom Footer -->
        <div class="py-6 border-t border-neutral-800">
            <div class="flex flex-col sm:flex-row items-center justify-center sm:justify-between gap-3 sm:gap-4 text-center sm:text-left">
                <p class="text-neutral-500 text-sm order-2 sm:order-1">
                    © {{ date('Y') }} Le Laboratoire Numérique. Tous droits réservés.
                </p>
                <div class="flex flex-wrap items-center justify-center gap-x-6 gap-y-2 text-sm order-1 sm:order-2">
                    <a href="{{ route('legal') }}" class="text-neutral-500 hover:text-white transition-colors whitespace-nowrap">Mentions légales</a>
                    <a href="{{ route('privacy') }}" class="text-neutral-500 hover:text-white transition-colors whitespace-nowrap">Politique de confidentialité</a>
                </div>
            </div>
        </div>
    </div>
</footer>
