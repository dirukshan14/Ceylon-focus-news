@extends('layouts.app')
@section('title', $category->name.' செய்திகள்')
@section('meta_description', $category->description ?: $category->name.' தொடர்பான சமீபத்திய செய்திகள் மற்றும் புதுப்பிப்புகள்.')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold">{{ $category->name }}</h1>
    @if($category->description)<p class="text-gray-500 mt-1">{{ $category->description }}</p>@endif

    @include('frontend.partials.ad-slot', ['position' => 'homepage_top'])

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mt-6">
        <div class="lg:col-span-2">
            @if($featured)
                <a href="{{ route('news.show', $featured->slug) }}" class="block relative rounded-lg overflow-hidden mb-6">
                    <img src="{{ $featured->featured_image ? asset('storage/'.$featured->featured_image) : 'https://placehold.co/900x400' }}" class="w-full h-64 object-cover">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 to-transparent flex flex-col justify-end p-4">
                        <h2 class="text-white text-xl font-bold">{{ $featured->title }}</h2>
                    </div>
                </a>
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @foreach($articles as $article)
                    @include('frontend.partials.article-card', ['article' => $article])
                    @if($loop->index === 3)
                        <div class="sm:col-span-2">@include('frontend.partials.ad-slot', ['position' => 'between_cards'])</div>
                    @endif
                @endforeach
            </div>
            <div class="mt-6">{{ $articles->links() }}</div>
        </div>

        <aside class="space-y-6">
            @include('frontend.partials.ad-slot', ['position' => 'sidebar'])
            <div class="bg-white rounded-lg border p-4">
                <h3 class="font-bold mb-3">{{ $category->name }}யில் பிரபலமானவை</h3>
                <ol class="space-y-2 text-sm">
                    @foreach($popular as $i => $article)
                        <li><a href="{{ route('news.show', $article->slug) }}" class="hover:underline">{{ $i+1 }}. {{ $article->title }}</a></li>
                    @endforeach
                </ol>
            </div>
        </aside>
    </div>
</div>
@endsection
