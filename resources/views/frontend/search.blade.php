@extends('layouts.app')
@section('title', $term ? 'Search results for "'.$term.'"' : 'Search')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">
    <form method="GET" class="flex flex-wrap gap-2 mb-8">
        <input type="text" name="q" value="{{ $term }}" placeholder="செய்திகள், வீடியோக்கள் தேடுங்கள்..."
               class="border rounded px-4 py-2 text-sm flex-1 min-w-[200px]">
        <select name="sort" class="border rounded px-3 py-2 text-sm">
            <option value="latest" @selected($sort === 'latest')>சமீபத்தியவை</option>
            <option value="popular" @selected($sort === 'popular')>பிரபலமானவை</option>
        </select>
        <button class="bg-brand text-white px-5 py-2 rounded text-sm">தேடு</button>
    </form>

    @if($term === '')
        <p class="text-gray-400">செய்திகள் மற்றும் வீடியோக்களைத் தேட ஒரு சொல்லை உள்ளிடவும்.</p>
    @else
        <h1 class="text-lg font-bold mb-4">Results for "{{ $term }}"</h1>

        @if($articles->isEmpty() && $videos->isEmpty())
            <p class="text-gray-400">முடிவுகள் எதுவும் இல்லை. வேறு சொல்லைப் பயன்படுத்தி முயற்சிக்கவும்.</p>
        @endif

        @if($articles->isNotEmpty())
            <h2 class="font-semibold mb-3">செய்திகள்</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-4">
                @foreach($articles as $article)
                    @include('frontend.partials.article-card', ['article' => $article])
                @endforeach
            </div>
            <div class="mb-8">{{ $articles->links() }}</div>
        @endif

        @if($videos->isNotEmpty())
            <h2 class="font-semibold mb-3">வீடியோக்கள்</h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                @foreach($videos as $video)
                    <a href="{{ route('videos.show', $video->slug) }}" class="bg-white rounded-lg border overflow-hidden hover:shadow-md">
                        <img src="{{ $video->thumbnailUrl() ?: 'https://placehold.co/400x225?text=Video' }}" class="w-full h-32 object-cover">
                        <p class="p-2 text-sm font-medium">{{ $video->title }}</p>
                    </a>
                @endforeach
            </div>
        @endif
    @endif
</div>
@endsection
