<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreArticleRequest;
use App\Http\Requests\UpdateArticleRequest;
use App\Models\AuditLog;
use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ArticleController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Article::class);

        $query = Article::query()->with(['author', 'category']);

        // Authors only ever see their own articles in the list.
        if (! $request->user()->hasRole('super-admin', 'administrator', 'editor')) {
            $query->where('author_id', $request->user()->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->integer('category_id'));
        }

        if ($request->filled('q')) {
            $query->search($request->string('q'));
        }

        $articles = $query->latest()->paginate(20)->withQueryString();
        $categories = Category::active()->orderBy('name')->get();

        return view('admin.articles.index', compact('articles', 'categories'));
    }

    public function create()
    {
        $this->authorize('create', Article::class);

        $categories = Category::active()->topLevel()->with('children')->orderBy('order')->get();
        $tags = Tag::orderBy('name')->get();
        $canPublish = auth()->user()->hasPermission(\App\Models\Permission::PUBLISH_NEWS);

        return view('admin.articles.create', compact('categories', 'tags', 'canPublish'));
    }

    public function store(StoreArticleRequest $request)
    {
        $data = $request->validated();
        $wasDowngraded = false;

        DB::transaction(function () use ($request, &$data, &$wasDowngraded) {
            $data['author_id'] = $request->user()->id;

            // Never trust the client's status field for publishing rights -
            // downgrade to pending review if the user lacks publish-news.
            if ($data['status'] === 'published' && ! $request->user()->hasPermission(\App\Models\Permission::PUBLISH_NEWS)) {
                $data['status'] = 'pending_review';
                $wasDowngraded = true;
            }

            if ($data['status'] === 'published' && empty($data['published_at'])) {
                $data['published_at'] = now();
            }

            if ($request->hasFile('featured_image')) {
                $data['featured_image'] = $request->file('featured_image')
                    ->store('articles/featured', 'public');
            }

            if ($request->hasFile('og_image')) {
                $data['og_image'] = $request->file('og_image')->store('articles/og', 'public');
            }

            $article = Article::create($data);

            $this->syncTags($article, $data['tags'] ?? []);
            $this->storeGallery($article, $request);

            AuditLog::record('article.created', $article, ['status' => $article->status]);
        });

        if ($wasDowngraded) {
            return redirect()->route('admin.articles.index')
                ->with('success', 'Article saved as "Pending Review" - your account does not have permission to publish news directly. An Editor or Administrator needs to publish it.');
        }

        return redirect()->route('admin.articles.index')
            ->with('success', 'Article created successfully.');
    }

    public function edit(Article $article)
    {
        $this->authorize('update', $article);

        $categories = Category::active()->topLevel()->with('children')->orderBy('order')->get();
        $tags = Tag::orderBy('name')->get();
        $canPublish = auth()->user()->hasPermission(\App\Models\Permission::PUBLISH_NEWS);

        return view('admin.articles.edit', compact('article', 'categories', 'tags', 'canPublish'));
    }

    public function update(UpdateArticleRequest $request, Article $article)
    {
        $data = $request->validated();
        $wasDowngraded = false;

        DB::transaction(function () use ($request, $article, &$data, &$wasDowngraded) {
            $wasPublished = $article->status === 'published';

            if ($data['status'] === 'published' && ! $request->user()->hasPermission(\App\Models\Permission::PUBLISH_NEWS)) {
                $data['status'] = 'pending_review';
                $wasDowngraded = true;
            }

            if ($data['status'] === 'published' && ! $wasPublished && empty($data['published_at'])) {
                $data['published_at'] = now();
            }

            if ($request->hasFile('featured_image')) {
                if ($article->featured_image) {
                    Storage::disk('public')->delete($article->featured_image);
                }
                $data['featured_image'] = $request->file('featured_image')
                    ->store('articles/featured', 'public');
            }

            if ($request->hasFile('og_image')) {
                if ($article->og_image) {
                    Storage::disk('public')->delete($article->og_image);
                }
                $data['og_image'] = $request->file('og_image')->store('articles/og', 'public');
            }

            $article->update($data);

            $this->syncTags($article, $data['tags'] ?? []);
            $this->storeGallery($article, $request);

            AuditLog::record('article.updated', $article, ['status' => $article->status]);

            if (! $wasPublished && $article->status === 'published') {
                AuditLog::record('article.published', $article);
            }
        });

        if ($wasDowngraded) {
            return redirect()->route('admin.articles.index')
                ->with('success', 'Article saved as "Pending Review" - your account does not have permission to publish news directly. An Editor or Administrator needs to publish it.');
        }

        return redirect()->route('admin.articles.index')
            ->with('success', 'Article updated successfully.');
    }

    public function destroy(Article $article)
    {
        $this->authorize('delete', $article);

        AuditLog::record('article.deleted', $article, ['title' => $article->title]);
        $article->delete(); // soft delete

        return redirect()->route('admin.articles.index')
            ->with('success', 'Article moved to trash.');
    }

    /* ---------------- helpers ---------------- */

    private function syncTags(Article $article, array $tagNames): void
    {
        $tagIds = collect($tagNames)
            ->filter()
            ->map(fn ($name) => Tag::firstOrCreate(['name' => trim($name)])->id);

        $article->tags()->sync($tagIds);
    }

    private function storeGallery(Article $article, Request $request): void
    {
        if (! $request->hasFile('gallery')) {
            return;
        }

        $nextOrder = $article->images()->max('order') + 1;

        foreach ($request->file('gallery') as $i => $file) {
            $path = $file->store('articles/gallery', 'public');
            $article->images()->create([
                'path' => $path,
                'order' => $nextOrder + $i,
            ]);
        }
    }
}
