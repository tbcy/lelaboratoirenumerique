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