@extends('layouts.app')
@section('title', '#'.$tag->name.' செய்திகள்')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold">#{{ $tag->name }}</h1>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mt-6">
        @forelse($articles as $article)
            @include('frontend.partials.article-card', ['article' => $article])
        @empty
            <p class="text-gray-400 col-span-full">No articles found for this tag.</p>
        @endforelse
    </div>
    <div class="mt-6">{{ $articles->links() }}</div>
</div>
@endsection
