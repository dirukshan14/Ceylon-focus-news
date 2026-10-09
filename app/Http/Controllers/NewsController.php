<?php

namespace App\Http\Controllers;

use App\Models\Advertisement;
use App\Models\Category;
use App\Models\News;

class NewsController extends Controller
{
    public function show(News $news)
    {
        abort_unless($news->status === 'published', 404);

        $news->increment('views');

        $recentNews  = News::published()->where('id', '!=', $news->id)->latest('published_at')->take(5)->get();
        $relatedNews = $news->related(4);

        $ads = [
            'article_top'    => Advertisement::active()->position('article_top')->first(),
            'article_bottom' => Advertisement::active()->position('article_bottom')->first(),
            'sidebar'        => Advertisement::active()->position('sidebar')->get(),
        ];

        return view('news.show', compact('news', 'recentNews', 'relatedNews', 'ads'));
    }

    public function category(Category $category)
    {
        $news = $category->publishedNews()->latest('published_at')->paginate(12);

        $ads = [
            'sidebar' => Advertisement::active()->position('sidebar')->get(),
        ];

        return view('news.category', compact('category', 'news', 'ads'));
    }
}
