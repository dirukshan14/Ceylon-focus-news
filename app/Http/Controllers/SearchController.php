<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Video;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $term = mb_substr(trim((string) $request->query('q', '')), 0, 100);
        $categoryId = $request->query('category_id');
        $categoryId = is_numeric($categoryId) ? (int) $categoryId : null;
        $sort = $request->query('sort', 'latest') === 'popular' ? 'popular' : 'latest';

        $articles = collect();
        $videos = collect();

        if ($term !== '') {
            $articleQuery = Article::published()->with(['author', 'category'])->search($term);

            if ($categoryId) {
                $articleQuery->inCategory((int) $categoryId);
            }

            $articleQuery = $sort === 'popular' ? $articleQuery->popular() : $articleQuery->latest('published_at');
            $articles = $articleQuery->paginate(12, ['*'], 'articles_page')->withQueryString();

            $videos = Video::published()->where('title', 'like', '%'.addcslashes($term, '\\%_').'%')->limit(6)->get();
        }

        return view('frontend.search', compact('term', 'articles', 'videos', 'categoryId', 'sort'));
    }
}
