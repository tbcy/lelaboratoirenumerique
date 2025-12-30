<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class UAT_DataSeeder extends Seeder
{
    /**
     * Seed test data for local development.
     */
    public function run(): void
    {
        // Admin user (local uniquement)
        $admin = User::factory()->create([
            'name' => 'Thomas Bourcy',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
        ]);

        // Catégories de test
        $categories = [
            ['name' => 'Développement Web', 'slug' => 'developpement-web', 'color' => '#2563eb', 'sort_order' => 1],
            ['name' => 'Applications Mobiles', 'slug' => 'applications-mobiles', 'color' => '#9333ea', 'sort_order' => 2],
            ['name' => 'Tutoriels', 'slug' => 'tutoriels', 'color' => '#06b6d4', 'sort_order' => 3],
            ['name' => 'Retours d\'expérience', 'slug' => 'retours-experience', 'color' => '#16a34a', 'sort_order' => 4],
            ['name' => 'Outils & Productivité', 'slug' => 'outils-productivite', 'color' => '#ea580c', 'sort_order' => 5],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }

        // Tags de test
        $tags = [
            ['name' => 'Laravel', 'slug' => 'laravel'],
            ['name' => 'PHP', 'slug' => 'php'],
            ['name' => 'Ionic', 'slug' => 'ionic'],
            ['name' => 'JavaScript', 'slug' => 'javascript'],
            ['name' => 'Filament', 'slug' => 'filament'],
            ['name' => 'Vue.js', 'slug' => 'vuejs'],
            ['name' => 'API', 'slug' => 'api'],
            ['name' => 'Base de données', 'slug' => 'base-de-donnees'],
            ['name' => 'Performance', 'slug' => 'performance'],
            ['name' => 'Sécurité', 'slug' => 'securite'],
            ['name' => 'DevOps', 'slug' => 'devops'],
            ['name' => 'UI/UX', 'slug' => 'ui-ux'],
        ];

        foreach ($tags as $tag) {
            Tag::create($tag);
        }

        // Articles de test
        $articles = [
            [
                'title' => 'Bienvenue sur le blog du Laboratoire Numérique',
                'slug' => 'bienvenue-sur-le-blog',
                'excerpt' => 'Premier article du blog où je vous présente ce que vous trouverez ici : tutoriels, retours d\'expérience et conseils techniques.',
                'content' => '<h2>Bienvenue !</h2><p>Je suis ravi de vous accueillir sur le blog du Laboratoire Numérique. Ici, je partage mes expériences en développement web et mobile, acquises au fil de mes projets.</p><h3>Ce que vous trouverez ici</h3><ul><li><strong>Tutoriels techniques</strong> : Des guides pas à pas sur Laravel, Ionic, et d\'autres technologies</li><li><strong>Retours d\'expérience</strong> : Les leçons apprises sur mes projets réels</li><li><strong>Conseils pratiques</strong> : Astuces pour améliorer votre productivité</li></ul><p>N\'hésitez pas à me contacter si vous avez des suggestions de sujets à aborder !</p>',
                'category_id' => 1,
                'is_featured' => true,
                'published_at' => now()->subDays(30),
                'tags' => [1, 2],
            ],
            [
                'title' => 'Pourquoi j\'ai choisi Laravel pour mes projets',
                'slug' => 'pourquoi-laravel-pour-mes-projets',
                'excerpt' => 'Laravel est devenu mon framework PHP de prédilection. Voici les raisons qui m\'ont convaincu après avoir testé plusieurs alternatives.',
                'content' => '<h2>Mon parcours avec PHP</h2><p>Avant Laravel, j\'ai expérimenté avec CodeIgniter, Symfony et même du PHP natif. Chaque approche avait ses avantages, mais Laravel a su me convaincre par son élégance.</p><h3>Les points forts de Laravel</h3><p><strong>Eloquent ORM</strong> : La gestion des bases de données devient un plaisir. Les relations, les scopes, les accessors... tout est intuitif.</p><p><strong>L\'écosystème</strong> : Forge, Vapor, Nova, Livewire, Filament... Laravel dispose d\'un écosystème riche qui couvre tous les besoins.</p><p><strong>La documentation</strong> : Probablement la meilleure documentation de tous les frameworks PHP.</p><h3>Pour quels projets ?</h3><p>Laravel excelle pour les applications web de toutes tailles, des MVP aux applications enterprise. Sa flexibilité permet de l\'adapter à presque tous les cas d\'usage.</p>',
                'category_id' => 1,
                'is_featured' => true,
                'published_at' => now()->subDays(25),
                'tags' => [1, 2],
            ],
            [
                'title' => 'Créer une API RESTful avec Laravel en 30 minutes',
                'slug' => 'api-restful-laravel-30-minutes',
                'excerpt' => 'Guide rapide pour mettre en place une API RESTful complète avec Laravel : routes, controllers, resources et authentification.',
                'content' => '<h2>Introduction</h2><p>Les APIs sont au cœur des applications modernes. Laravel simplifie grandement leur création grâce à ses API Resources et son système de routing.</p><h3>Étape 1 : Configuration</h3><pre><code>php artisan install:api</code></pre><p>Cette commande configure Sanctum pour l\'authentification API.</p><h3>Étape 2 : Créer les routes</h3><p>Dans <code>routes/api.php</code>, définissez vos endpoints :</p><pre><code>Route::apiResource(\'posts\', PostController::class);</code></pre><h3>Étape 3 : API Resources</h3><p>Les Resources permettent de transformer vos modèles en JSON de manière élégante et contrôlée.</p><h3>Conclusion</h3><p>En quelques minutes, vous avez une API fonctionnelle avec CRUD complet et authentification !</p>',
                'category_id' => 3,
                'is_featured' => false,
                'published_at' => now()->subDays(22),
                'tags' => [1, 2, 7],
            ],
            [
                'title' => 'Ionic vs React Native : mon retour après 2 ans',
                'slug' => 'ionic-vs-react-native-retour-experience',
                'excerpt' => 'Après avoir développé des applications avec Ionic et React Native, voici mon analyse comparative basée sur des projets réels.',
                'content' => '<h2>Le contexte</h2><p>J\'ai eu l\'opportunité de travailler sur plusieurs applications mobiles, certaines avec Ionic, d\'autres avec React Native. Voici mon retour d\'expérience.</p><h3>Ionic : mes observations</h3><p><strong>Points forts :</strong></p><ul><li>Une seule codebase pour web, iOS et Android</li><li>Utilisation des technologies web standard</li><li>Excellent pour les apps orientées contenu</li></ul><p><strong>Points faibles :</strong></p><ul><li>Performances sur animations complexes</li><li>Accès natif parfois limité</li></ul><h3>React Native</h3><p><strong>Points forts :</strong></p><ul><li>Performances proches du natif</li><li>Large communauté</li></ul><p><strong>Points faibles :</strong></p><ul><li>Pas de version web native</li><li>Mises à jour parfois douloureuses</li></ul><h3>Mon choix</h3><p>Pour mes projets, Ionic reste mon choix privilégié grâce à sa polyvalence web/mobile.</p>',
                'category_id' => 2,
                'is_featured' => true,
                'published_at' => now()->subDays(20),
                'tags' => [3, 4],
            ],
            [
                'title' => 'Optimiser les performances de votre application Laravel',
                'slug' => 'optimiser-performances-laravel',
                'excerpt' => 'Techniques et bonnes pratiques pour améliorer significativement les performances de vos applications Laravel.',
                'content' => '<h2>Pourquoi optimiser ?</h2><p>Une application rapide améliore l\'expérience utilisateur et le référencement. Voici les techniques que j\'applique systématiquement.</p><h3>1. Eager Loading</h3><p>Évitez le problème N+1 avec <code>with()</code> :</p><pre><code>Post::with([\'author\', \'category\', \'tags\'])->get();</code></pre><h3>2. Cache</h3><p>Utilisez le cache pour les requêtes coûteuses :</p><pre><code>Cache::remember(\'posts\', 3600, fn() => Post::all());</code></pre><h3>3. Optimisation des assets</h3><p>Minifiez CSS/JS et utilisez le versioning avec Vite.</p><h3>4. Configuration</h3><pre><code>php artisan config:cache\nphp artisan route:cache\nphp artisan view:cache</code></pre><h3>Résultats</h3><p>Ces optimisations peuvent réduire les temps de réponse de 50% ou plus !</p>',
                'category_id' => 1,
                'is_featured' => false,
                'published_at' => now()->subDays(18),
                'tags' => [1, 2, 9],
            ],
            [
                'title' => 'Filament v4 : le game changer pour les back-offices',
                'slug' => 'filament-v4-game-changer-back-offices',
                'excerpt' => 'Découvrez les nouveautés de Filament v4 et pourquoi c\'est devenu mon outil préféré pour créer des interfaces d\'administration.',
                'content' => '<h2>Qu\'est-ce que Filament ?</h2><p>Filament est un framework d\'administration pour Laravel. La version 4 apporte des améliorations majeures.</p><h3>Nouveautés v4</h3><ul><li><strong>Performance</strong> : Chargement beaucoup plus rapide</li><li><strong>Composants</strong> : Nouveaux composants UI</li><li><strong>Tailwind 4</strong> : Compatibilité native</li><li><strong>Schemas</strong> : Nouvelle architecture des formulaires</li></ul><h3>Exemple de Resource</h3><p>Créer un CRUD complet en quelques lignes :</p><pre><code>php artisan make:filament-resource Post</code></pre><h3>Mon avis</h3><p>Filament a révolutionné ma façon de créer des back-offices. Ce qui prenait des jours prend maintenant quelques heures.</p>',
                'category_id' => 3,
                'is_featured' => false,
                'published_at' => now()->subDays(15),
                'tags' => [1, 5],
            ],
            [
                'title' => 'Sécuriser son application Laravel : checklist complète',
                'slug' => 'securiser-application-laravel-checklist',
                'excerpt' => 'Une checklist des mesures de sécurité essentielles à implémenter dans toute application Laravel en production.',
                'content' => '<h2>La sécurité n\'est pas optionnelle</h2><p>Trop souvent négligée, la sécurité doit être une priorité dès le début du développement.</p><h3>Checklist essentielle</h3><h4>1. Protection CSRF</h4><p>Laravel l\'active par défaut, ne le désactivez jamais.</p><h4>2. Validation des entrées</h4><p>Toujours valider côté serveur, jamais faire confiance au client.</p><h4>3. Mass Assignment</h4><p>Utilisez <code>$fillable</code> ou <code>$guarded</code> sur tous vos modèles.</p><h4>4. Headers de sécurité</h4><p>Configurez CSP, X-Frame-Options, etc.</p><h4>5. HTTPS</h4><p>Forcez HTTPS en production avec <code>URL::forceScheme(\'https\')</code>.</p><h4>6. Mots de passe</h4><p>Utilisez Hash::make() et des règles de complexité.</p><h3>Outils recommandés</h3><p>Laravel Security Checker, OWASP ZAP pour les audits.</p>',
                'category_id' => 1,
                'is_featured' => false,
                'published_at' => now()->subDays(12),
                'tags' => [1, 2, 10],
            ],
            [
                'title' => 'Déployer une application Laravel sur un hébergement mutualisé',
                'slug' => 'deployer-laravel-hebergement-mutualise',
                'excerpt' => 'Guide pratique pour déployer Laravel sur un hébergement mutualisé type o2switch, OVH ou PlanetHoster.',
                'content' => '<h2>C\'est possible !</h2><p>Contrairement aux idées reçues, Laravel fonctionne très bien sur un hébergement mutualisé.</p><h3>Prérequis</h3><ul><li>PHP 8.1+ avec extensions requises</li><li>Accès SSH (fortement recommandé)</li><li>Composer disponible ou uploadable</li></ul><h3>Étapes de déploiement</h3><h4>1. Configuration du document root</h4><p>Pointez vers le dossier <code>/public</code> de Laravel.</p><h4>2. Fichier .env</h4><p>Configurez les variables d\'environnement pour la production.</p><h4>3. Permissions</h4><pre><code>chmod -R 775 storage bootstrap/cache</code></pre><h4>4. Optimisation</h4><pre><code>php artisan optimize</code></pre><h3>Astuces o2switch</h3><p>Utilisez Node.js 22 via <code>/opt/alt/alt-nodejs22/</code> pour Vite.</p>',
                'category_id' => 3,
                'is_featured' => false,
                'published_at' => now()->subDays(10),
                'tags' => [1, 11],
            ],
            [
                'title' => 'Construire une app de gestion d\'événements avec Ionic',
                'slug' => 'app-gestion-evenements-ionic',
                'excerpt' => 'Retour d\'expérience sur le développement de Youplago, une application de gestion d\'événements entre amis.',
                'content' => '<h2>Le projet Youplago</h2><p>Youplago est né d\'un besoin simple : organiser des événements entre amis sans la galère des groupes WhatsApp.</p><h3>Fonctionnalités développées</h3><ul><li>Création d\'événements avec sondages de dates</li><li>Gestion des budgets partagés</li><li>Listes de courses collaboratives</li><li>Chat intégré</li><li>To-do lists</li></ul><h3>Stack technique</h3><p><strong>Frontend</strong> : Ionic + Vue.js</p><p><strong>Backend</strong> : Laravel API</p><p><strong>Temps réel</strong> : WebSockets avec Pusher</p><h3>Défis rencontrés</h3><p>La synchronisation temps réel entre participants a été le plus gros challenge. La solution : événements WebSocket + queue jobs.</p><h3>Résultat</h3><p>Une app utilisée régulièrement par nos groupes d\'amis !</p>',
                'category_id' => 4,
                'is_featured' => false,
                'published_at' => now()->subDays(8),
                'tags' => [3, 4, 6],
            ],
            [
                'title' => 'Les outils que j\'utilise au quotidien',
                'slug' => 'outils-developpement-quotidien',
                'excerpt' => 'Ma stack d\'outils pour le développement : IDE, terminal, extensions et services qui boostent ma productivité.',
                'content' => '<h2>Mon setup de développement</h2><p>Après des années d\'optimisation, voici les outils sur lesquels je compte au quotidien.</p><h3>IDE : PhpStorm</h3><p>Rien ne bat PhpStorm pour le développement Laravel. L\'intégration avec le framework est exceptionnelle.</p><h3>Terminal : Warp</h3><p>Un terminal moderne avec autocomplétion IA et historique intelligent.</p><h3>Extensions VS Code (pour le front)</h3><ul><li>Vue Language Features</li><li>Tailwind CSS IntelliSense</li><li>ESLint</li></ul><h3>Services</h3><ul><li><strong>GitHub</strong> : Versioning + Actions CI/CD</li><li><strong>TablePlus</strong> : Gestion des BDD</li><li><strong>Insomnia</strong> : Tests API</li><li><strong>Figma</strong> : Design et maquettes</li></ul><h3>Le plus important</h3><p>Maîtrisez vos outils. Un bon setup mal maîtrisé est inutile.</p>',
                'category_id' => 5,
                'is_featured' => false,
                'published_at' => now()->subDays(6),
                'tags' => [11],
            ],
            [
                'title' => 'Créer un système d\'authentification complet avec Laravel',
                'slug' => 'authentification-complete-laravel',
                'excerpt' => 'De l\'inscription à la réinitialisation de mot de passe : implémentez un système d\'auth robuste et sécurisé.',
                'content' => '<h2>Les bases de l\'authentification Laravel</h2><p>Laravel propose plusieurs solutions : Breeze, Jetstream, Fortify. Voyons comment les utiliser.</p><h3>Laravel Breeze</h3><p>La solution la plus simple pour démarrer :</p><pre><code>composer require laravel/breeze --dev\nphp artisan breeze:install</code></pre><h3>Fonctionnalités incluses</h3><ul><li>Inscription / Connexion</li><li>Réinitialisation de mot de passe</li><li>Vérification d\'email</li><li>Confirmation de mot de passe</li></ul><h3>Personnalisation</h3><p>Breeze génère tous les fichiers dans votre projet. Vous pouvez les modifier librement.</p><h3>2FA avec Jetstream</h3><p>Pour l\'authentification à deux facteurs, Jetstream est la solution recommandée.</p><h3>Conseil</h3><p>Commencez simple avec Breeze, évoluez vers Jetstream si nécessaire.</p>',
                'category_id' => 3,
                'is_featured' => false,
                'published_at' => now()->subDays(5),
                'tags' => [1, 2, 10],
            ],
            [
                'title' => 'Gérer les uploads de fichiers avec Spatie Media Library',
                'slug' => 'uploads-fichiers-spatie-media-library',
                'excerpt' => 'Spatie Media Library simplifie la gestion des fichiers uploadés. Découvrez comment l\'intégrer à vos projets Laravel.',
                'content' => '<h2>Pourquoi Spatie Media Library ?</h2><p>Gérer les uploads manuellement est fastidieux. Cette librairie offre une solution élégante.</p><h3>Installation</h3><pre><code>composer require spatie/laravel-medialibrary\nphp artisan vendor:publish --provider="Spatie\\MediaLibrary\\MediaLibraryServiceProvider" --tag="medialibrary-migrations"\nphp artisan migrate</code></pre><h3>Utilisation basique</h3><p>Ajoutez le trait à votre modèle :</p><pre><code>use InteractsWithMedia;\nimplements HasMedia</code></pre><h3>Collections</h3><p>Organisez vos médias en collections :</p><pre><code>$this->addMediaCollection(\'featured_image\')->singleFile();</code></pre><h3>Conversions</h3><p>Générez automatiquement des thumbnails :</p><pre><code>$this->addMediaConversion(\'thumbnail\')\n    ->width(400)\n    ->height(300);</code></pre><h3>Avec Filament</h3><p>L\'intégration Filament est native et simple à configurer.</p>',
                'category_id' => 3,
                'is_featured' => false,
                'published_at' => now()->subDays(4),
                'tags' => [1, 2, 5],
            ],
            [
                'title' => 'Architecture d\'une application Ionic/Laravel',
                'slug' => 'architecture-application-ionic-laravel',
                'excerpt' => 'Comment structurer une application mobile Ionic avec un backend Laravel : bonnes pratiques et patterns.',
                'content' => '<h2>Vue d\'ensemble</h2><p>Une architecture claire est essentielle pour maintenir une application dans le temps.</p><h3>Backend Laravel</h3><p><strong>Structure recommandée :</strong></p><ul><li>API versionnée (<code>/api/v1/</code>)</li><li>Controllers dédiés API</li><li>Resources pour la transformation</li><li>FormRequests pour la validation</li></ul><h3>Frontend Ionic</h3><p><strong>Organisation :</strong></p><ul><li>Services pour les appels API</li><li>Stores (Pinia) pour l\'état global</li><li>Composables pour la logique réutilisable</li></ul><h3>Communication</h3><p>JSON comme format d\'échange, tokens JWT pour l\'auth, WebSockets pour le temps réel.</p><h3>Gestion des erreurs</h3><p>Intercepteurs Axios côté front, Exception Handler personnalisé côté back.</p><h3>Tests</h3><p>PHPUnit pour l\'API, Cypress pour le frontend.</p>',
                'category_id' => 2,
                'is_featured' => false,
                'published_at' => now()->subDays(3),
                'tags' => [1, 3, 6, 7],
            ],
            [
                'title' => 'Tailwind CSS 4 : les nouveautés à connaître',
                'slug' => 'tailwind-css-4-nouveautes',
                'excerpt' => 'Tailwind CSS 4 apporte des changements majeurs. Voici ce qu\'il faut savoir pour migrer vos projets.',
                'content' => '<h2>Une nouvelle ère pour Tailwind</h2><p>Tailwind CSS 4 représente une réécriture majeure du framework.</p><h3>Nouveautés principales</h3><h4>1. Engine Oxide</h4><p>Nouvelle engine ultra-rapide écrite en Rust.</p><h4>2. Configuration CSS</h4><p>Fini le fichier JS, tout se configure dans le CSS avec <code>@theme</code>.</p><h4>3. Variables CSS natives</h4><p>Utilisation massive des custom properties.</p><h3>Points d\'attention</h3><p><strong>Bug connu</strong> : Les classes <code>max-w-xl</code> à <code>max-w-4xl</code> utilisent les mauvaises variables. Définissez des overrides dans votre CSS.</p><h3>Migration</h3><p>La migration peut être automatisée avec :</p><pre><code>npx @tailwindcss/upgrade</code></pre><h3>Mon avis</h3><p>Plus rapide et plus propre, mais attention aux bugs de jeunesse.</p>',
                'category_id' => 1,
                'is_featured' => true,
                'published_at' => now()->subDays(2),
                'tags' => [4, 12],
            ],
            [
                'title' => 'Olympiadus : retour sur 2 ans de développement',
                'slug' => 'olympiadus-retour-2-ans-developpement',
                'excerpt' => 'De l\'idée à 10 000 utilisateurs : l\'histoire d\'Olympiadus, une app de compétitions sportives entre amis.',
                'content' => '<h2>La genèse</h2><p>Tout a commencé lors d\'une soirée jeux entre amis. On voulait un moyen simple d\'organiser nos olympiades.</p><h3>L\'évolution du projet</h3><p><strong>V1</strong> : MVP basique, gestion d\'équipes et scores</p><p><strong>V2</strong> : Ajout des QR codes, classements temps réel</p><p><strong>V3</strong> : Statistiques avancées, mode entreprise</p><h3>Quelques chiffres</h3><ul><li>10 000+ utilisateurs</li><li>50 000+ matchs joués</li><li>5 000+ équipes créées</li><li>1 000+ olympiades organisées</li></ul><h3>Défis techniques</h3><p>Le temps réel pour les scores a été complexe. WebSockets + optimistic updates côté client.</p><h3>Leçons apprises</h3><ul><li>MVP first, toujours</li><li>Écouter les utilisateurs</li><li>Itérer rapidement</li></ul><h3>La suite</h3><p>Nouvelles fonctionnalités en préparation pour 2025 !</p>',
                'category_id' => 4,
                'is_featured' => false,
                'published_at' => now()->subDays(1),
                'tags' => [3, 4, 6, 7],
            ],
        ];

        foreach ($articles as $article) {
            $tags = $article['tags'] ?? [];
            unset($article['tags']);

            $article['author_id'] = $admin->id;
            $article['status'] = 'published';

            $post = Post::create($article);

            if (!empty($tags)) {
                $post->tags()->attach($tags);
            }
        }
    }
}
