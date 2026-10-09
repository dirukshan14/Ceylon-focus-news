@extends('layouts.app')
@section('title', $video->title)
@section('meta_description', $video->description)

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">
    @include('frontend.partials.video-player', ['video' => $video])

    <h1 class="text-2xl font-bold mt-4">{{ $video->title }}</h1>
    <p class="text-sm text-gray-400 mt-1">{{ number_format($video->views_count) }} பார்வைகள் · {{ $video->published_at?->diffForHumans() }}</p>
    <p class="text-gray-600 mt-3">{{ $video->description }}</p>

    @include('frontend.partials.ad-slot', ['position' => 'video_page'])

    @if($related->isNotEmpty())
    <section class="mt-10">
        <h2 class="text-lg font-bold mb-4">மேலும் வீடியோக்கள்</h2>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            @foreach($related as $item)
                <a href="{{ route('videos.show', $item->slug) }}" class="bg-white rounded-lg border overflow-hidden hover:shadow-md">
                    <img src="{{ $item->thumbnailUrl() ?: 'https://placehold.co/400x225?text=Video' }}" class="w-full h-32 object-cover">
                    <p class="p-2 text-sm font-medium">{{ $item->title }}</p>
                </a>
            @endforeach
        </div>
    </section>
    @endif
</div>

<script type="application/ld+json">
{
    "@type": "VideoObject",
    "name": {{ Illuminate\Support\Js::from($video->title) }},
    "uploadDate": "{{ $video->published_at?->toIso8601String() }}",
    "thumbnailUrl": {{ Illuminate\Support\Js::from($video->thumbnailUrl()) }}
}
</script>
@endsection
