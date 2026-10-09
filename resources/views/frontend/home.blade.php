@extends('layouts.app')
@section('title', \App\Models\Setting::get('meta_title', config('app.name')))

@section('content')

@if($breakingNews->isNotEmpty())
<div class="bg-red-600 text-white">
    <div class="max-w-6xl mx-auto px-4 py-2 flex items-center gap-3 text-sm overflow-hidden">
        <span class="font-bold uppercase flex-shrink-0">அவசர செய்தி</span>
        <div class="flex gap-8 animate-marquee whitespace-nowrap">
            @foreach($breakingNews as $item)
                <a href="{{ $item->linkUrl() }}" class="hover:underline">{{ $item->text }}</a>
            @endforeach
        </div>
    </div>
</div>
<style>@keyframes marquee { 0% {transform:translateX(0);} 100% {transform:translateX(-50%);} } .animate-marquee{animation:marquee 25s linear infinite;}</style>
@endif

<div class="max-w-6xl mx-auto px-4 py-8 space-y-12">

    @include('frontend.partials.ad-slot', ['position' => 'homepage_top'])

    {{-- Hero --}}
    @if($heroArticle)
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <div class="lg:col-span-2 relative rounded-xl overflow-hidden group shadow-sm">
            <a href="{{ route('news.show', $heroArticle->slug) }}">
                <img src="{{ $heroArticle->featured_image ? asset('storage/'.$heroArticle->featured_image) : 'https://placehold.co/900x500?text=Featured' }}"
                     class="w-full h-80 object-cover group-hover:scale-105 transition duration-300">
                <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/20 to-transparent flex flex-col justify-end p-6">
                    <span class="text-xs font-semibold text-amber-400 uppercase tracking-wide">{{ $heroArticle->category->name }}</span>
                    <h1 class="text-white text-2xl md:text-3xl font-bold mt-2 leading-tight">{{ $heroArticle->title }}</h1>
                    <p class="text-slate-200 text-sm mt-2 max-w-2xl">{{ $heroArticle->excerptOrTruncatedBody(120) }}</p>
                    <p class="text-slate-400 text-xs mt-3">{{ $heroArticle->author->name }} · {{ $heroArticle->published_at?->diffForHumans() }}</p>
                </div>
            </a>
        </div>
        <div class="space-y-4">
            @forelse($subFeatured as $article)
                <a href="{{ route('news.show', $article->slug) }}" class="flex gap-3 group">
                    <img src="{{ $article->featured_image ? asset('storage/'.$article->featured_image) : 'https://placehold.co/150x100' }}" class="w-24 h-16 object-cover rounded-lg flex-shrink-0">
                    <div class="min-w-0">
                        <span class="text-xs text-brand font-semibold uppercase">{{ $article->category->name }}</span>
                        <p class="text-sm font-medium leading-snug group-hover:underline line-clamp-2">{{ $article->title }}</p>
                    </div>
                </a>
            @empty
                <p class="text-sm text-gray-400">மேலும் சிறப்புச் செய்திகள் விரைவில்.</p>
            @endforelse
        </div>
    </div>
    @endif

    {{-- Video News - replaces the old full-width death notices section here. --}}
    @include('frontend.partials.video-news-section', ['videos' => $latestVideos])

    {{-- Latest News --}}
    <section>
        <div class="flex justify-between items-center mb-4 border-b pb-2">
            <h2 class="text-xl font-bold">சமீபத்திய செய்திகள்</h2>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            @forelse($latestArticles as $article)
                @include('frontend.partials.article-card', ['article' => $article])
            @empty
                <p class="text-sm text-gray-400 col-span-full">இன்னும் செய்திகள் வெளியிடப்படவில்லை.</p>
            @endforelse
        </div>
    </section>

    @include('frontend.partials.ad-slot', ['position' => 'homepage_middle'])

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 space-y-10">
            {{-- Category sections --}}
            @foreach($categories as $category)
                @if($category->previewArticles->isNotEmpty())
                <section>
                    <div class="flex justify-between items-center mb-3 border-b pb-2">
                        <h2 class="text-lg font-bold">{{ $category->name }}</h2>
                        <a href="{{ route('category.show', $category->slug) }}" class="text-sm text-brand hover:underline flex-shrink-0">அனைத்தும் காண →</a>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach($category->previewArticles as $article)
                            @include('frontend.partials.article-card', ['article' => $article])
                        @endforeach
                    </div>
                </section>
                @endif
            @endforeach
        </div>

        <aside class="space-y-6">
            @include('frontend.partials.ad-slot', ['position' => 'sidebar'])

            @include('frontend.partials.live-score-widget')

            @include('frontend.partials.death-notice-widget', ['notices' => $deathNotices])

            <div class="bg-white rounded-lg border p-4">
                <h3 class="font-bold mb-3 border-b pb-2">டிரெண்டிங் செய்திகள்</h3>
                <ol class="space-y-3 text-sm">
                    @forelse($trendingArticles as $i => $article)
                        <li class="flex gap-3">
                            <span class="text-xl font-bold text-gray-300 flex-shrink-0">{{ $i + 1 }}</span>
                            <a href="{{ route('news.show', $article->slug) }}" class="hover:underline leading-snug">{{ $article->title }}</a>
                        </li>
                    @empty
                        <li class="text-gray-400">தரவு இல்லை.</li>
                    @endforelse
                </ol>
            </div>
        </aside>
    </div>

    @include('frontend.partials.ad-slot', ['position' => 'homepage_bottom'])
</div>
@endsection
