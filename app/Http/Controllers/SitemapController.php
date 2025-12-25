<?php

namespace App\Http\Controllers;

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

        $content = view('sitemap', compact('pages'))->render();

        return response($content, 200)
            ->header('Content-Type', 'application/xml');
    }
}
