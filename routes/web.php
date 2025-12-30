<?php

use App\Http\Controllers\BlogController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/projets', [PageController::class, 'projects'])->name('projects');
Route::get('/services', [PageController::class, 'services'])->name('services');
Route::get('/a-propos', [PageController::class, 'about'])->name('about');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::post('/contact', [ContactController::class, 'send'])->name('contact.send');

// Blog
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/categorie/{slug}', [BlogController::class, 'category'])->name('blog.category');
Route::get('/blog/tag/{slug}', [BlogController::class, 'tag'])->name('blog.tag');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');

// Pages légales
Route::get('/mentions-legales', [PageController::class, 'legal'])->name('legal');
Route::get('/politique-de-confidentialite', [PageController::class, 'privacy'])->name('privacy');

// SEO
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
