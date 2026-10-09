@extends('layouts.app')
@section('title', 'வீடியோக்கள்')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-6">வீடியோக்கள்</h1>

    @if($featured->isNotEmpty())
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
            @foreach($featured as $video)
                <a href="{{ route('videos.show', $video->slug) }}" class="block rounded-lg overflow-hidden bg-black relative group">
                    <img src="{{ $video->thumbnailUrl() ?: 'https://placehold.co/400x225?text=Video' }}" class="w-full h-40 object-cover opacity-90 group-hover:opacity-100">
                    <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/80 p-3">
                        <p class="text-white text-sm font-semibold">{{ $video->title }}</p>
                    </div>
                </a>
            @endforeach
        </div>
    @endif

    @include('frontend.partials.ad-slot', ['position' => 'video_page'])

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mt-6">
        @foreach($videos as $video)
            <a href="{{ route('videos.show', $video->slug) }}" class="bg-white rounded-lg border overflow-hidden hover:shadow-md">
                <img src="{{ $video->thumbnailUrl() ?: 'https://placehold.co/400x225?text=Video' }}" class="w-full h-36 object-cover">
                <div class="p-3">
                    <p class="font-medium text-sm">{{ $video->title }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ $video->category?->name }} · {{ number_format($video->views_count) }} பார்வைகள்</p>
                </div>
            </a>
        @endforeach
    </div>
    <div class="mt-6">{{ $videos->links() }}</div>
</div>
@endsection
