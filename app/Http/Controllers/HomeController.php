<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\BreakingNews;
use App\Models\Category;
use App\Models\DeathNotice;
use App\Models\Video;

class HomeController extends Controller
{
    public function index()
    {
        $breakingNews = BreakingNews::live()->limit(10)->get();

        $deathNotices = DeathNotice::published()->latest('date_of_death')->limit(5)->get();

        $heroArticle = Article::published()->featured()->latest('published_at')->first();

        $subFeatured = Article::published()->featured()
            ->when($heroArticle, fn ($q) => $q->where('id', '!=', $heroArticle->id))
            ->latest('published_at')
            ->limit(4)
            ->get();

        $latestArticles = Article::published()
            ->with(['author', 'category'])
            ->latest('published_at')
            ->limit(12)
            ->get();

        // Prefer articles explicitly flagged trending; if there aren't enough,
        // top up with the most-viewed published articles so the "Trending"
        // box is never empty just because nothing's been flagged yet.
        $trendingArticles = Article::published()->trending()->popular()->limit(6)->get();
        if ($trendingArticles->count() < 6) {
            $fillerIds = $trendingArticles->pluck('id');
            $filler = Article::published()
                ->when($fillerIds->isNotEmpty(), fn ($q) => $q->whereNotIn('id', $fillerIds))
                ->popular()
                ->limit(6 - $trendingArticles->count())
                ->get();
            $trendingArticles = $trendingArticles->concat($filler);
        }

        $categories = Category::active()->topLevel()->orderBy('order')->limit(8)->get()
            ->map(function ($category) {
                $category->setRelation(
                    'previewArticles',
                    Article::published()->inCategory($category->id)->latest('published_at')->limit(4)->get()
                );

                return $category;
            });

        // Full video section at the bottom of the homepage - not just a small
        // sidebar teaser. Featured videos surface first, padded out with the
        // latest published videos.
        $featuredVideos = Video::published()->featured()->latest('published_at')->limit(4)->get();
        $latestVideos = $featuredVideos->count() >= 8
            ? $featuredVideos->take(8)
            : $featuredVideos->concat(
                Video::published()
                    ->whereNotIn('id', $featuredVideos->pluck('id'))
                    ->latest('published_at')
                    ->limit(8 - $featuredVideos->count())
                    ->get()
            );

        return view('frontend.home', compact(
            'breakingNews', 'deathNotices', 'heroArticle', 'subFeatured', 'latestArticles',
            'trendingArticles', 'categories', 'latestVideos'
        ));
    }
}
