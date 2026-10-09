<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Advertisement;
use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use App\Models\Video;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_news' => Article::count(),
            'published_news' => Article::where('status', 'published')->count(),
            'draft_news' => Article::where('status', 'draft')->count(),
            'scheduled_news' => Article::where('status', 'scheduled')->count(),
            'total_categories' => Category::count(),
            'total_videos' => Video::count(),
            'total_users' => User::whereDoesntHave('roles')->count(),
            'total_administrators' => User::whereHas('roles')->orWhere('is_super_admin', true)->count(),
            'total_ad_impressions' => Advertisement::sum('impressions_count'),
            'total_ad_clicks' => Advertisement::sum('clicks_count'),
            'total_page_views' => Article::sum('views_count'),
        ];

        $popularArticles = Article::published()->popular()->limit(5)->get();
        $recentArticles = Article::latest()->limit(8)->with(['author', 'category'])->get();

        return view('admin.dashboard', compact('stats', 'popularArticles', 'recentArticles'));
    }
}
