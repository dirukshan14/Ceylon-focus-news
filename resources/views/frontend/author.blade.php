@extends('layouts.app')
@section('title', $author->name.' - எழுத்தாளர்')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">
    <div class="flex items-center gap-4 mb-8">
        <img src="{{ $author->avatar ? asset('storage/'.$author->avatar) : 'https://placehold.co/100x100?text='.substr($author->name,0,1) }}"
             class="w-20 h-20 rounded-full object-cover">
        <div>
            <h1 class="text-2xl font-bold">{{ $author->name }}</h1>
            @if($author->bio)<p class="text-gray-500 text-sm mt-1 max-w-xl">{{ $author->bio }}</p>@endif
        </div>
    </div>

    <h2 class="text-lg font-bold mb-4">{{ $author->name }} எழுதிய கட்டுரைகள்</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($articles as $article)
            @include('frontend.partials.article-card', ['article' => $article])
        @endforeach
    </div>
    <div class="mt-6">{{ $articles->links() }}</div>
</div>
@endsection
