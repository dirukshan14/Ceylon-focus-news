<?php

namespace App\Http\Controllers;

use App\Models\Video;

class VideoController extends Controller
{
    public function index()
    {
        $featured = Video::published()->featured()->latest('published_at')->limit(3)->get();

        $videos = Video::published()
            ->with(['category', 'author'])
            ->latest('published_at')
            ->paginate(15);

        return view('frontend.videos-index', compact('featured', 'videos'));
    }

    public function show(Video $video)
    {
        abort_unless($video->status === 'published', 404);

        $video->incrementViews();
        $video->load(['category', 'author', 'tags']);

        $related = Video::published()
            ->when($video->category_id, fn ($q) => $q->where('category_id', $video->category_id))
            ->where('id', '!=', $video->id)
            ->latest('published_at')
            ->limit(6)
            ->get();

        return view('frontend.video-show', compact('video', 'related'));
    }
}
