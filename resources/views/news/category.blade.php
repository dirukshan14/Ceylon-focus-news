@extends('layouts.app')

@section('title', $category->name . ' - Daily News Portal')

@section('content')
<h1>{{ $category->name }}</h1>

<div class="news-grid">
    @foreach($news as $item)
        <article class="news-card">
            <a href="{{ route('news.show', $item->slug) }}">
                <img src="{{ $item->image_url }}" alt="{{ $item->title }}">
                <h3>{{ $item->title }}</h3>
            </a>
            <small>{{ $item->published_at->diffForHumans() }}</small>
        </article>
    @endforeach
</div>

{{ $news->links() }}
@endsection
