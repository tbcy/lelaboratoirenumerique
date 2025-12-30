<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    /**
     * Generate the sitemap.xml
     */
    public function index(): Response
    {
        $pages = [
            [
                'url' => route('home'),
                'lastmod' => '2025-01-01',
                'changefreq' => 'monthly',
                'priority' => '1.0',
            ],
            [
                'url' => route('projects'),
                'lastmod' => '2025-01-01',
                'changefreq' => 'monthly',
                'priority' => '0.9',
            ],
            [
                'url' => route('blog.index'),
                'lastmod' => now()->format('Y-m-d'),
                'changefreq' => 'weekly',
                'priority' => '0.9',
            ],
            [
                'url' => route('services'),
                'lastmod' => '2025-01-01',
                'changefreq' => 'monthly',
                'priority' => '0.9',
            ],
            [
                'url' => route('about'),
                'lastmod' => '2025-01-01',
                'changefreq' => 'monthly',
                'priority' => '0.8',
            ],
            [
                'url' => route('contact'),
                'lastmod' => '2025-01-01',
                'changefreq' => 'yearly',
                'priority' => '0.7',
            ],
        ];

        // Ajouter les articles de blog publiés
        $posts = Post::published()
            ->select('slug', 'updated_at')
            ->orderBy('published_at', 'desc')
            ->get();

        foreach ($posts as $post) {
            $pages[] = [
                'url' => route('blog.show', $post->slug),
                'lastmod' => $post->updated_at->format('Y-m-d'),
                'changefreq' => 'monthly',
                'priority' => '0.8',
            ];
        }

        // Ajouter les catégories du blog
        $categories = Category::whereHas('publishedPosts')
            ->get();

        foreach ($categories as $category) {
            $pages[] = [
                'url' => route('blog.category', $category->slug),
                'lastmod' => now()->format('Y-m-d'),
                'changefreq' => 'weekly',
                'priority' => '0.6',
            ];
        }

        $content = view('sitemap', compact('pages'))->render();

        return response($content, 200)
            ->header('Content-Type', 'application/xml');
    }
}
