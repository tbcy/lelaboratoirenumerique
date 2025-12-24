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
- **CSS**: Tailwind CSS 4.x with `@tailwindcss/vite` plugin
- **Build**: Vite 7.x
- **Database**: SQLite (default)

### Key Directories
- `app/Http/Controllers/` - PageController (static pages), ContactController (form handling)
- `resources/views/pages/` - Blade templates for each page (home, projects, services, about, contact)
- `resources/views/layouts/app.blade.php` - Main layout
- `resources/views/components/` - Reusable components (navigation, footer)
- `resources/css/app.css` - Tailwind theme and custom component classes

### Routes (French URLs)
- `/` - Home
- `/projets` - Projects
- `/services` - Services
- `/a-propos` - About
- `/contact` - Contact (GET: display, POST: send)

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

# Deploy to production (SSH pull)
ssh o2switch_perso "cd ~/production/lelaboratoirenumerique && git pull origin main"

# Rebuild assets (REQUIRED when modifying Tailwind classes)
# Note: Vite 7 requires Node.js 20+, use alt-nodejs22 on o2switch
ssh o2switch_perso "cd ~/production/lelaboratoirenumerique && export PATH=/opt/alt/alt-nodejs22/root/usr/bin:\$PATH && npm run build"

# Clear Laravel caches if needed
ssh o2switch_perso "cd ~/production/lelaboratoirenumerique && php artisan cache:clear && php artisan config:clear && php artisan view:clear"
```

### Important Notes

- **Tailwind CSS 4.x** : Les classes arbitraires (ex: `w-[450px]`) doivent être compilées. Toujours lancer `npm run build` après modification des templates Blade.
- **Node.js** : Le serveur a Node 16 par défaut mais Vite 7 nécessite Node 20+. Utiliser `/opt/alt/alt-nodejs22/root/usr/bin/` pour les commandes npm.

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