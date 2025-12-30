# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Le Laboratoire Numérique - Personal portfolio website built with Laravel 12.x and Tailwind CSS 4.x.

## Development Commands

```bash
# Start full dev environment (server + queue + logs + Vite in parallel)
composer run dev

# Build assets for production
npm run build

# Run tests
composer run test

# Initial setup (install deps, generate key, migrate, build)
composer run setup
```

## Architecture

### Stack
- **Backend**: Laravel 12.x (PHP 8.2+)
- **Admin Panel**: Filament v4.x
- **Media Library**: Spatie Media Library
- **CSS**: Tailwind CSS 4.x with `@tailwindcss/vite` plugin
- **Build**: Vite 7.x
- **Database**: SQLite (default)

### Key Directories
- `app/Http/Controllers/` - PageController (static pages), BlogController, ContactController
- `app/Filament/Resources/` - Filament admin resources (Post, Category, Tag)
- `app/Models/` - Eloquent models (User, Post, Category, Tag)
- `resources/views/pages/` - Blade templates for each page
- `resources/views/pages/blog/` - Blog views (index, show, category, tag)
- `resources/views/layouts/app.blade.php` - Main layout
- `resources/views/components/` - Reusable components (navigation, footer, blog/post-card)
- `resources/css/app.css` - Tailwind theme and custom component classes

### Routes (French URLs)
- `/` - Home
- `/projets` - Projects
- `/blog` - Blog index (with search)
- `/blog/{slug}` - Single article
- `/blog/categorie/{slug}` - Articles by category
- `/blog/tag/{slug}` - Articles by tag
- `/services` - Services
- `/a-propos` - About
- `/contact` - Contact (GET: display, POST: send)

### Admin Panel (Filament v4)
- `/admin` - Dashboard
- `/admin/posts` - Manage blog posts
- `/admin/categories` - Manage categories
- `/admin/tags` - Manage tags

**Local credentials:** admin@test.com / password

## Design System

**CRITICAL**: Always read `DESIGN_SYSTEM.md` before creating/modifying pages or components.

### Color Palette (defined in app.css @theme)
- **Primary**: Blue tech (#2563eb) - CTAs, links
- **Secondary**: Purple tech (#9333ea) - Accents, badges
- **Accent**: Cyan (#06b6d4) - Highlights
- **Neutral**: Gray scale (#18181b to #fafafa)

### Pre-defined CSS Classes (use these, not raw Tailwind)
- **Buttons**: `.btn`, `.btn-primary`, `.btn-secondary`, `.btn-outline`, `.btn-ghost`
- **Cards**: `.card`, `.card-bordered` (combine with `.hover-lift`, `.hover-glow`)
- **Badges**: `.badge-primary`, `.badge-secondary`, `.badge-success`, `.badge-warning`, `.badge-danger`
- **Typography**: `.heading-1` through `.heading-4`, `.text-lead`
- **Layout**: `.section`, `.section-hero`, `.container-custom`
- **Effects**: `.gradient-primary`, `.gradient-accent`, `.animate-fade-in-up`

### Fonts
- Sans: Inter
- Mono: JetBrains Mono

## Mail Configuration

Uses Mailgun (EU endpoint) configured via environment variables:
- `MAIL_MAILER=mailgun`
- `MAILGUN_DOMAIN`, `MAILGUN_SECRET`, `MAILGUN_ENDPOINT`
- `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME`

## Deployment (o2switch)

**Server:** o2switch_perso (see global CLAUDE.md for SSH config)
**Path:** `~/production/lelaboratoirenumerique`

### Deploy Commands

```bash
# Push changes to GitHub first
git push origin main

# Full deploy (pull + composer + npm + migrate + optimize)
ssh o2switch_perso "cd ~/production/lelaboratoirenumerique && \
  git pull origin main && \
  composer install --no-dev --optimize-autoloader && \
  export PATH=/opt/alt/alt-nodejs22/root/usr/bin:\$PATH && npm run build && \
  php artisan migrate --force && \
  php artisan storage:link 2>/dev/null || true && \
  php artisan optimize:clear && php artisan optimize"

# Create admin user in production (first time only)
ssh o2switch_perso "cd ~/production/lelaboratoirenumerique && php artisan make:filament-user"

# Clear Laravel caches if needed
ssh o2switch_perso "cd ~/production/lelaboratoirenumerique && php artisan cache:clear && php artisan config:clear && php artisan view:clear"
```

### Important Notes

- **Tailwind CSS 4.x** : Les classes arbitraires (ex: `w-[450px]`) doivent être compilées. Toujours lancer `npm run build` après modification des templates Blade.
- **Node.js** : Le serveur a Node 16 par défaut mais Vite 7 nécessite Node 20+. Utiliser `/opt/alt/alt-nodejs22/root/usr/bin/` pour les commandes npm.

### Bug connu: Tailwind CSS 4.x max-w-* classes

**ATTENTION** : Tailwind CSS 4.x a un bug où les classes `max-w-xl`, `max-w-2xl`, `max-w-3xl`, `max-w-4xl` utilisent `--spacing-Nxl` au lieu de `--container-Nxl`, résultant en des largeurs de 32-96px au lieu de 576-896px.

**Solution** : Des correctifs sont définis dans `resources/css/app.css` qui surchargent ces classes avec les bonnes valeurs.

### Production URL

https://lelaboratoirenumerique.com

## Projets à Mettre en Avant

1. **Youplago**
   - Application mobile et hybride (Ionic)
   - Organisation d'événements entre amis/professionnels
   - Features : activités, budgets, chat, listes de courses, to-do lists
   - Site : app.youplago.com

2. **Olympiadus**
   - Application mobile pour organiser des olympiades/compétitions sportives
   - Plateforme tout-en-un : entre amis, en famille ou en entreprise
   - Features : gestion d'équipes, validation QR Code, classements temps réel, statistiques
   - Stats : 10K+ utilisateurs, 50K+ matchs, 5K+ équipes, 1K+ olympiades
   - Site : olympiadus.lelaboratoirenumerique.com

3. **Batibid**
   - Plateforme immobilière (Agence au Bénin)
   - Back-office : gestion des annonces et biens
   - Système de facturation automatique
   - Front-end : recherche de biens immobiliers
   - Site : app.batibid.com

## Compétences à Valoriser

- **Laravel** : Développement back-end, API, back-offices
- **Ionic** : Applications mobiles et hybrides (iOS/Android)
- **Développement Full-Stack** : Front-end + Back-end
- **Architecture** : Conception d'applications complètes

## MCP Server (Model Context Protocol)

Le projet intègre un serveur MCP permettant à Claude de gérer les articles de blog via des outils dédiés.

### Configuration

**Endpoint :** `/api/mcp`
**Authentification :** Bearer token via header `Authorization`

**Variables d'environnement :**
```
MCP_API_KEY=<your-secure-token>
MCP_SESSION_ENABLED=true
MCP_SESSION_TTL=1440
```

### Tools Disponibles

**Articles (Post) :**
- `list_posts` - Lister les articles avec filtres (status, category_id, is_featured, search)
- `get_post` - Récupérer un article par ID
- `create_post` - Créer un nouvel article (title, content, excerpt, category_id, tag_ids, etc.)
- `update_post` - Modifier un article existant
- `delete_post` - Supprimer un article (soft delete)
- `publish_post` - Publier un brouillon
- `unpublish_post` - Dépublier un article

**Catégories :**
- `list_categories` - Lister toutes les catégories
- `get_category` - Récupérer une catégorie par ID

**Tags :**
- `list_tags` - Lister tous les tags
- `get_tag` - Récupérer un tag par ID

### Test du Serveur MCP

```bash
# Test ping
curl -X POST http://127.0.0.1:8002/api/mcp \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $MCP_API_KEY" \
  -d '{"jsonrpc":"2.0","method":"ping","id":1}'

# List tools
curl -X POST http://127.0.0.1:8002/api/mcp \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $MCP_API_KEY" \
  -d '{"jsonrpc":"2.0","method":"tools/list","id":2}'

# Create article
curl -X POST http://127.0.0.1:8002/api/mcp \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $MCP_API_KEY" \
  -d '{"jsonrpc":"2.0","method":"tools/call","params":{"name":"create_post","arguments":{"title":"Test","content":"<p>Contenu</p>"}},"id":3}'
```

### Configuration Claude Code

Ajouter dans `~/.claude.json` :

```json
{
  "mcpServers": {
    "labo": {
      "type": "stdio",
      "command": "npx",
      "args": [
        "-y", "supergateway",
        "--streamableHttp", "https://lelaboratoirenumerique.com/api/mcp",
        "--oauth2Bearer", "VOTRE_TOKEN_MCP",
        "--protocolVersion", "2025-03-26"
      ]
    }
  }
}
```

### Architecture MCP

```
app/
├── Http/
│   ├── Controllers/Mcp/
│   │   └── McpStreamableController.php     # Controller principal JSON-RPC
│   ├── Middleware/
│   │   ├── McpAuthentication.php           # Auth Bearer token
│   │   └── McpLogging.php                  # Logging des requêtes
│   └── Requests/Mcp/Post/
│       ├── CreatePostRequest.php           # Validation création
│       └── UpdatePostRequest.php           # Validation modification
├── Services/Mcp/
│   ├── Handlers/
│   │   ├── InitializeHandler.php           # Handler initialize
│   │   ├── PingHandler.php                 # Handler ping
│   │   ├── ToolsHandler.php                # Handler tools/list et tools/call
│   │   └── ResourcesHandler.php            # Handler resources
│   ├── Resources/
│   │   ├── PostResource.php                # Lecture des posts
│   │   ├── CategoryResource.php            # Lecture des catégories
│   │   └── TagResource.php                 # Lecture des tags
│   ├── Tools/
│   │   └── PostTools.php                   # Create/Update/Delete/Publish
│   └── Services (Pagination, ErrorHandling, AuditLog, Validation)
├── Exceptions/Mcp/                         # Exceptions MCP personnalisées
└── config/mcp.php                          # Configuration
```

### Logs MCP

Les logs MCP sont stockés dans `storage/logs/mcp.log` (rotation quotidienne, 30 jours).