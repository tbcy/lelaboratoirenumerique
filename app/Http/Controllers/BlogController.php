<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function index(Request $request): View
    {
        $query = Post::query()
            ->published()
            ->with(['category', 'author', 'media'])
            ->latest('published_at');

        // Recherche
        if ($search = $request->get('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('excerpt', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            });
        }

        // Filtrer par catégorie
        if ($categorySlug = $request->get('categorie')) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $categorySlug));
        }

        $featuredPosts = Post::query()
            ->published()
            ->featured()
            ->with(['category', 'author', 'media'])
            ->latest('published_at')
            ->limit(3)
            ->get();

        $posts = $query->paginate(9)->withQueryString();

        $categories = Category::whereHas('publishedPosts')
            ->withCount('publishedPosts')
            ->orderBy('sort_order')
            ->get();

        return view('pages.blog.index', [
            'posts' => $posts,
            'featuredPosts' => $featuredPosts,
            'categories' => $categories,
            'search' => $search,
            'currentCategory' => $categorySlug,
        ]);
    }

    public function show(string $slug): View
    {
        $post = Post::query()
            ->published()
            ->where('slug', $slug)
            ->with(['category', 'author', 'tags', 'media'])
            ->firstOrFail();

        $relatedPosts = Post::query()
            ->published()
            ->where('id', '!=', $post->id)
            ->where('category_id', $post->category_id)
            ->with(['category', 'media'])
            ->latest('published_at')
            ->limit(3)
            ->get();

        return view('pages.blog.show', [
            'post' => $post,
            'relatedPosts' => $relatedPosts,
        ]);
    }

    public function category(string $slug): View
    {
        $category = Category::where('slug', $slug)->firstOrFail();

        $posts = Post::query()
            ->published()
            ->where('category_id', $category->id)
            ->with(['category', 'author', 'media'])
            ->latest('published_at')
            ->paginate(9);

        $categories = Category::whereHas('publishedPosts')
            ->withCount('publishedPosts')
            ->orderBy('sort_order')
            ->get();

        return view('pages.blog.category', [
            'category' => $category,
            'posts' => $posts,
            'categories' => $categories,
        ]);
    }

    public function tag(string $slug): View
    {
        $tag = Tag::where('slug', $slug)->firstOrFail();

        $posts = $tag->publishedPosts()
            ->with(['category', 'author', 'media'])
            ->latest('published_at')
            ->paginate(9);

        return view('pages.blog.tag', [
            'tag' => $tag,
            'posts' => $posts,
        ]);
    }
}
