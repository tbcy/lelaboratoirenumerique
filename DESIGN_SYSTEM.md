# Design System - Le Laboratoire Numérique

Ce document définit la charte graphique complète du site. **IMPORTANT : Toutes les pages et composants futurs doivent suivre strictement ces guidelines pour maintenir une cohérence visuelle.**

## 🎨 Palette de Couleurs

### Couleurs Principales

#### Primary (Bleu Tech)
Utilisé pour les CTAs principaux, liens, éléments interactifs importants
- `primary-50` : #eff6ff (backgrounds très clairs)
- `primary-100` : #dbeafe (backgrounds clairs)
- `primary-500` : #3b82f6 (couleur standard)
- `primary-600` : #2563eb (boutons, liens - **couleur par défaut**)
- `primary-700` : #1d4ed8 (hover states)
- `primary-900` : #1e3a8a (textes sur fond clair)

#### Secondary (Violet Tech)
Utilisé pour les accents, badges, éléments secondaires
- `secondary-50` : #faf5ff
- `secondary-100` : #f3e8ff
- `secondary-500` : #a855f7
- `secondary-600` : #9333ea (couleur standard)
- `secondary-700` : #7e22ce (hover states)

#### Accent (Cyan)
Utilisé pour les highlights, notifications, éléments d'attention
- `accent-400` : #22d3ee
- `accent-500` : #06b6d4 (couleur standard)
- `accent-600` : #0891b2

### Couleurs Fonctionnelles

- **Success** (Vert) : `success-500` #22c55e - Confirmations, succès
- **Warning** (Orange) : `warning-500` #f97316 - Avertissements
- **Danger** (Rouge) : `danger-500` #ef4444 - Erreurs, actions destructives

### Couleurs Neutres

Utilisées pour textes, backgrounds, borders
- `neutral-50` : #fafafa (background principal)
- `neutral-100` : #f4f4f5 (backgrounds secondaires)
- `neutral-300` : #d4d4d8 (borders)
- `neutral-600` : #52525b (textes secondaires)
- `neutral-900` : #18181b (textes principaux)

## 📝 Typographie

### Fonts
- **Sans-serif** : Inter (headers, body text)
- **Monospace** : JetBrains Mono (code snippets)

### Échelle Typographique

```html
<!-- Heading 1 : Titres principaux des pages -->
<h1 class="heading-1">Titre Principal</h1>
<!-- text-4xl md:text-5xl lg:text-6xl font-bold -->

<!-- Heading 2 : Sections principales -->
<h2 class="heading-2">Section Principale</h2>
<!-- text-3xl md:text-4xl lg:text-5xl font-bold -->

<!-- Heading 3 : Sous-sections -->
<h3 class="heading-3">Sous-section</h3>
<!-- text-2xl md:text-3xl font-bold -->

<!-- Heading 4 : Éléments de card, petits titres -->
<h4 class="heading-4">Petit Titre</h4>
<!-- text-xl md:text-2xl font-semibold -->

<!-- Lead Text : Introductions, descriptions importantes -->
<p class="text-lead">Texte d'introduction important.</p>
<!-- text-lg md:text-xl text-neutral-600 -->
```

## 🧩 Composants

### Boutons

```html
<!-- Bouton Principal (CTAs importants) -->
<button class="btn btn-primary">Action Principale</button>

<!-- Bouton Secondaire -->
<button class="btn btn-secondary">Action Secondaire</button>

<!-- Bouton Outline (actions moins importantes) -->
<button class="btn btn-outline">Action Outline</button>

<!-- Bouton Ghost (navigation, actions discrètes) -->
<button class="btn btn-ghost">Action Ghost</button>
```

**Règles d'usage :**
- 1 seul `btn-primary` par section (le CTA le plus important)
- `btn-secondary` pour actions secondaires mais importantes
- `btn-outline` pour actions alternatives
- `btn-ghost` pour navigation et actions tertiaires

### Cards

```html
<!-- Card Standard (projets, services) -->
<div class="card">
  <h3 class="heading-4">Titre</h3>
  <p class="text-neutral-600">Description...</p>
</div>

<!-- Card avec Border (alternative plus subtile) -->
<div class="card-bordered">
  <h3 class="heading-4">Titre</h3>
  <p class="text-neutral-600">Description...</p>
</div>
```

**Effets disponibles :**
- Ajouter `hover-lift` pour effet de soulèvement au hover
- Ajouter `hover-glow` pour effet de glow au hover

### Badges

```html
<span class="badge badge-primary">Laravel</span>
<span class="badge badge-secondary">Ionic</span>
<span class="badge badge-success">Disponible</span>
<span class="badge badge-warning">Beta</span>
<span class="badge badge-danger">Deprecated</span>
```

**Usage :** Technologies, statuts, catégories

### Inputs & Forms

```html
<!-- Input Standard -->
<input type="text" class="input" placeholder="Votre nom">

<!-- Input avec Erreur -->
<input type="text" class="input input-error" placeholder="Email invalide">
```

## 📐 Layout & Espacements

### Containers

```html
<!-- Container Principal (max-width: 7xl = 1280px) -->
<div class="container-custom">
  <!-- Contenu -->
</div>
```

### Sections

```html
<!-- Section Standard (padding vertical 16/24) -->
<section class="section">
  <div class="container-custom">
    <!-- Contenu -->
  </div>
</section>

<!-- Section Hero (padding vertical plus important) -->
<section class="section-hero">
  <div class="container-custom">
    <!-- Contenu hero -->
  </div>
</section>
```

### Espacements

Utiliser les variables d'espacement pour la cohérence :
- `spacing-xs` : 8px - Espaces très serrés
- `spacing-sm` : 12px - Espaces serrés
- `spacing-md` : 16px - Espacement standard
- `spacing-lg` : 24px - Espacement généreux
- `spacing-xl` : 32px - Espacement important
- `spacing-2xl` : 48px - Entre sections
- `spacing-3xl` : 64px - Entre blocs majeurs
- `spacing-4xl` : 96px - Entre grandes sections

## 🎭 Effets & Animations

### Gradients

```html
<!-- Gradient Principal (bleu vers violet) -->
<div class="gradient-primary text-white p-8">
  Contenu avec gradient
</div>

<!-- Gradient Accent (cyan vers bleu) -->
<div class="gradient-accent text-white p-8">
  Contenu avec gradient accent
</div>
```

### Animations

```html
<!-- Fade In Up (pour apparitions) -->
<div class="animate-fade-in-up">
  Contenu qui apparaît
</div>

<!-- Hover Lift (cards, boutons) -->
<div class="card hover-lift">
  Card avec effet de soulèvement
</div>

<!-- Hover Glow (CTAs, éléments importants) -->
<button class="btn btn-primary hover-glow">
  Bouton avec effet glow
</button>
```

## 📱 Responsive

### Breakpoints

- **sm** : 640px (mobile landscape)
- **md** : 768px (tablette portrait)
- **lg** : 1024px (tablette landscape / petit desktop)
- **xl** : 1280px (desktop)
- **2xl** : 1536px (large desktop)

### Approche Mobile-First

Toujours designer pour mobile d'abord, puis ajouter les breakpoints :

```html
<h1 class="text-3xl md:text-4xl lg:text-5xl">
  Titre responsive
</h1>
```

## 🎯 Guidelines d'Usage

### Structure de Page Type

```html
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page - Le Laboratoire Numérique</title>
    @vite('resources/css/app.css')
</head>
<body>
    <!-- Navigation -->
    <nav>...</nav>

    <!-- Hero Section -->
    <section class="section-hero bg-neutral-50">
        <div class="container-custom">
            <h1 class="heading-1">Titre Principal</h1>
            <p class="text-lead mt-6">Description</p>
            <div class="flex gap-4 mt-8">
                <button class="btn btn-primary">CTA Principal</button>
                <button class="btn btn-outline">CTA Secondaire</button>
            </div>
        </div>
    </section>

    <!-- Content Sections -->
    <section class="section">
        <div class="container-custom">
            <h2 class="heading-2">Section</h2>
            <!-- Cards grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-12">
                <div class="card hover-lift">...</div>
                <div class="card hover-lift">...</div>
                <div class="card hover-lift">...</div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>...</footer>

    @vite('resources/js/app.js')
</body>
</html>
```

### Bonnes Pratiques

1. **Cohérence** : Toujours utiliser les classes définies dans le design system
2. **Hiérarchie** : 1 seul H1 par page, respecter l'ordre H1 > H2 > H3 > H4
3. **Contraste** : S'assurer d'un contraste suffisant pour l'accessibilité
4. **Espacements** : Utiliser les variables d'espacement, pas de valeurs arbitraires
5. **Couleurs** : Utiliser les couleurs du theme, pas de hex codes directs
6. **Responsive** : Tester sur mobile, tablette et desktop
7. **Performance** : Optimiser les images, utiliser le lazy loading

### Exemples de Compositions

#### Card Projet

```html
<div class="card hover-lift hover-glow">
    <!-- Image/Icon -->
    <div class="w-16 h-16 rounded-lg gradient-primary flex items-center justify-center mb-4">
        <svg class="w-8 h-8 text-white">...</svg>
    </div>

    <!-- Technologies -->
    <div class="flex gap-2 mb-4">
        <span class="badge badge-primary">Laravel</span>
        <span class="badge badge-secondary">Ionic</span>
    </div>

    <!-- Content -->
    <h3 class="heading-4 mb-2">Youplago</h3>
    <p class="text-neutral-600 mb-4">Application mobile d'organisation d'événements...</p>

    <!-- CTA -->
    <button class="btn btn-outline w-full">Voir le projet</button>
</div>
```

#### Section avec Gradient Background

```html
<section class="section gradient-primary">
    <div class="container-custom text-white text-center">
        <h2 class="heading-2 text-white">Travaillons ensemble</h2>
        <p class="text-lead text-white/90 mt-4 max-w-2xl mx-auto">
            Vous avez un projet ? Contactez-moi pour en discuter.
        </p>
        <button class="btn bg-white text-primary-600 hover:bg-neutral-50 mt-8">
            Me contacter
        </button>
    </div>
</section>
```

## 🔄 Mises à Jour

**Version** : 1.0
**Date** : 23/11/2025
**Dernière modification** : Création initiale

Pour toute question ou suggestion d'amélioration du design system, documenter ici les changements.
