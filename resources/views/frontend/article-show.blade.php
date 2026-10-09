@extends('layouts.app')
@section('title', $article->seo_title ?: $article->title)
@section('meta_description', $article->seo_description ?: $article->excerptOrTruncatedBody(160))
@section('canonical', $article->canonical_url ?: route('news.show', $article->slug))

@push('head')
<meta property="og:title" content="{{ $article->seo_title ?: $article->title }}">
<meta property="og:description" content="{{ $article->seo_description ?: $article->excerptOrTruncatedBody(160) }}">
<meta property="og:image" content="{{ $article->og_image ? asset('storage/'.$article->og_image) : ($article->featured_image ? asset('storage/'.$article->featured_image) : '') }}">
<meta property="og:type" content="article">
<meta name="twitter:card" content="summary_large_image">
@endpush

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">

    {{-- Breadcrumb --}}
    <nav class="text-xs text-gray-500 mb-4">
        <a href="{{ route('home') }}" class="hover:underline">முகப்பு</a> /
        <a href="{{ route('category.show', $article->category->slug) }}" class="hover:underline">{{ $article->category->name }}</a> /
        <span class="text-gray-700">{{ $article->title }}</span>
    </nav>

    <span class="text-xs font-semibold text-brand uppercase">{{ $article->category->name }}</span>
    <h1 class="text-3xl font-bold mt-2 leading-tight">{{ $article->title }}</h1>
    <p class="text-gray-500 mt-2">{{ $article->excerpt }}</p>

    <div class="flex items-center gap-4 text-sm text-gray-500 mt-4 pb-4 border-b">
        <a href="{{ route('author.show', $article->author_id) }}" class="font-medium text-gray-700 hover:underline">{{ $article->author->name }}</a>
        <span>{{ $article->published_at?->format('M d, Y - h:i A') }}</span>
        <span>{{ $article->reading_time_minutes }} நிமிட வாசிப்பு</span>
        <span>{{ number_format($article->views_count) }} பார்வைகள்</span>
    </div>

    @if($article->featured_image)
        <img src="{{ asset('storage/'.$article->featured_image) }}" class="w-full rounded-lg my-6 max-h-[480px] object-cover">
    @endif

    @include('frontend.partials.ad-slot', ['position' => 'article_top'])

    <div class="prose max-w-none prose-img:rounded-lg">
        {!! \App\Support\HtmlSanitizer::clean($article->body) !!}
    </div>

    @include('frontend.partials.ad-slot', ['position' => 'article_middle'])

    @if($article->images->isNotEmpty())
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 my-6">
            @foreach($article->images as $image)
                <figure>
                    <img src="{{ asset('storage/'.$image->path) }}" class="rounded-lg w-full h-32 object-cover">
                    @if($image->caption)<figcaption class="text-xs text-gray-500 mt-1">{{ $image->caption }}</figcaption>@endif
                </figure>
            @endforeach
        </div>
    @endif

    @foreach($article->videos as $video)
        <div class="my-6">@include('frontend.partials.video-player', ['video' => $video])</div>
    @endforeach

    @if($article->tags->isNotEmpty())
        <div class="flex flex-wrap gap-2 mt-6">
            @foreach($article->tags as $tag)
                <a href="{{ route('tag.show', $tag->slug) }}" class="text-xs bg-gray-100 hover:bg-gray-200 px-3 py-1 rounded-full">#{{ $tag->name }}</a>
            @endforeach
        </div>
    @endif

    @include('frontend.partials.ad-slot', ['position' => 'article_bottom'])

    @if($related->isNotEmpty())
    <section class="mt-10">
        <h2 class="text-lg font-bold mb-4">தொடர்புடைய செய்திகள்</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            @foreach($related as $item)
                @include('frontend.partials.article-card', ['article' => $item])
            @endforeach
        </div>
    </section>
    @endif
</div>

{{-- NewsArticle structured data for SEO --}}
<script type="application/ld+json">
{
    "@type": "NewsArticle",
    "headline": {{ Illuminate\Support\Js::from($article->title) }},
    "datePublished": "{{ $article->published_at?->toIso8601String() }}",
    "author": { "@type": "Person", "name": {{ Illuminate\Support\Js::from($article->author->name) }} }
}
</script>
@endsection
