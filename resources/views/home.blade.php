@extends('layouts.app')

@section('title', 'Home - Daily News Portal')

@section('content')

    {{-- Featured / top story --}}
    @if($featured)
        <section class="featured-story">
            <a href="{{ route('news.show', $featured->slug) }}">
                <img src="{{ $featured->image_url }}" alt="{{ $featured->title }}">
                <h1>{{ $featured->title }}</h1>
                <p>{{ $featured->excerpt }}</p>
            </a>
            <small>{{ $featured->category->name }} &middot; {{ $featured->published_at->diffForHumans() }}</small>
        </section>
    @endif

    {{-- Latest news grid --}}
    <section class="latest-news">
        <h2>Latest News</h2>
        <div class="news-grid">
            @foreach($latestNews as $item)
                <article class="news-card">
                    <a href="{{ route('news.show', $item->slug) }}">
                        <img src="{{ $item->image_url }}" alt="{{ $item->title }}">
                        <h3>{{ $item->title }}</h3>
                    </a>
                    <small>{{ $item->category->name }} &middot; {{ $item->published_at->diffForHumans() }}</small>
                </article>
            @endforeach
        </div>
    </section>

    {{-- Sidebar ads --}}
    @if($ads['sidebar']->isNotEmpty())
        <aside class="sidebar-ads">
            @foreach($ads['sidebar'] as $ad)
                <div class="ad-slot">
                    <a href="{{ $ad->url }}" target="_blank" rel="noopener">
                        <img src="{{ $ad->image_url }}" alt="{{ $ad->title }}">
                    </a>
                </div>
            @endforeach
        </aside>
    @endif

    {{-- One section per category --}}
    @foreach($categories as $category)
        <section class="category-section">
            <h2><a href="{{ route('news.category', $category->slug) }}">{{ $category->name }}</a></h2>
            <div class="news-grid">
                @foreach($category->publishedNews as $item)
                    <article class="news-card">
                        <a href="{{ route('news.show', $item->slug) }}">
                            <img src="{{ $item->image_url }}" alt="{{ $item->title }}">
                            <h3>{{ $item->title }}</h3>
                        </a>
                    </article>
                @endforeach
            </div>
        </section>
    @endforeach

    {{-- ============================================================
         FOUR-VIDEO SECTION (bottom of homepage)
         Managed from Admin > Homepage Videos. Empty/inactive slots
         are simply skipped.
    ============================================================ --}}
    <section class="video-section">
        <h2>Watch</h2>
        <div class="video-grid">
            @for($pos = 1; $pos <= 4; $pos++)
                @if(isset($videos[$pos]))
                    @php($video = $videos[$pos])
                    <div class="video-card">
                        <div class="video-embed-wrapper">
                            <iframe
                                src="{{ $video->embed_url }}"
                                title="{{ $video->title }}"
                                frameborder="0"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                allowfullscreen>
                            </iframe>
                        </div>
                        <p>{{ $video->title }}</p>
                    </div>
                @else
                    <div class="video-card video-card--empty">
                        <p>No video set for slot {{ $pos }}</p>
                    </div>
                @endif
            @endfor
        </div>
    </section>

@endsection
