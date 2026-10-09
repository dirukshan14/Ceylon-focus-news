<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;

class ArticleController extends Controller
{
    public function show(Article $article)
    {
        // SECURITY: unpublished articles are previewable only by admin-panel users,
        // not by any registered reader.
        abort_unless($article->isPublished() || (auth()->check() && auth()->user()->canAccessAdmin()), 404);

        $article->load(['author', 'category', 'subcategory', 'tags', 'images', 'videos']);
        $article->incrementViews();

        $related = Article::published()
            ->inCategory($article->category_id)
            ->where('id', '!=', $article->id)
            ->latest('published_at')
            ->limit(4)
            ->get();

        return view('frontend.article-show', compact('article', 'related'));
    }

    public function category(Category $category)
    {
        $featured = Article::published()->inCategory($category->id)->featured()->latest('published_at')->first();

        $articles = Article::published()->inCategory($category->id)
            ->when($featured, fn ($q) => $q->where('id', '!=', $featured->id))
            ->with(['author'])
            ->latest('published_at')
            ->paginate(12);

        $popular = Article::published()->inCategory($category->id)->popular()->limit(5)->get();

        return view('frontend.category', compact('category', 'featured', 'articles', 'popular'));
    }

    public function tag(Tag $tag)
    {
        $articles = $tag->articles()->published()->latest('published_at')->paginate(12);

        return view('frontend.tag', compact('tag', 'articles'));
    }

    public function author(User $author)
    {
        abort_unless($author->articles()->published()->exists(), 404);

        $articles = $author->articles()->published()->latest('published_at')->paginate(12);

        return view('frontend.author', compact('author', 'articles'));
    }
}
