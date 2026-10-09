@extends('layouts.app')

@section('title', $news->title . ' - Daily News Portal')

@section('content')
<div class="article-layout">

    <article class="article-main">
        <nav class="breadcrumb">
            <a href="{{ route('home') }}">Home</a> /
            <a href="{{ route('news.category', $news->category->slug) }}">{{ $news->category->name }}</a>
        </nav>

        <h1>{{ $news->title }}</h1>
        <small>By {{ $news->author->name }} &middot; {{ $news->published_at->format('M d, Y H:i') }} &middot; {{ $news->views }} views</small>

        @if($news->image)
            <img src="{{ $news->image_url }}" alt="{{ $news->title }}" class="article-image">
        @endif

        @if(!empty($ads['article_top']))
            <div class="ad-slot">
                <a href="{{ $ads['article_top']->url }}" target="_blank" rel="noopener">
                    <img src="{{ $ads['article_top']->image_url }}" alt="{{ $ads['article_top']->title }}">
                </a>
            </div>
        @endif

        <div class="article-body">
            {!! $news->content !!}
        </div>

        @if(!empty($ads['article_bottom']))
            <div class="ad-slot">
                <a href="{{ $ads['article_bottom']->url }}" target="_blank" rel="noopener">
                    <img src="{{ $ads['article_bottom']->image_url }}" alt="{{ $ads['article_bottom']->title }}">
                </a>
            </div>
        @endif

        {{-- Related news --}}
        @if($relatedNews->isNotEmpty())
            <section class="related-news">
                <h2>Related News</h2>
                <div class="news-grid">
                    @foreach($relatedNews as $item)
                        <article class="news-card">
                            <a href="{{ route('news.show', $item->slug) }}">
                                <img src="{{ $item->image_url }}" alt="{{ $item->title }}">
                                <h3>{{ $item->title }}</h3>
                            </a>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif
    </article>

    <aside class="article-sidebar">
        {{-- Recently added news --}}
        <section class="recent-news">
            <h2>Recent News</h2>
            <ul>
                @foreach($recentNews as $item)
                    <li>
                        <a href="{{ route('news.show', $item->slug) }}">{{ $item->title }}</a>
                        <small>{{ $item->published_at->diffForHumans() }}</small>
                    </li>
                @endforeach
            </ul>
        </section>

        @foreach($ads['sidebar'] as $ad)
            <div class="ad-slot">
                <a href="{{ $ad->url }}" target="_blank" rel="noopener">
                    <img src="{{ $ad->image_url }}" alt="{{ $ad->title }}">
                </a>
            </div>
        @endforeach
    </aside>

</div>
@endsection
