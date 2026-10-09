@extends('layouts.admin')
@section('title', 'Dashboard')

@section('content')
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
    @php
        $cards = [
            'Total News' => $stats['total_news'],
            'Published' => $stats['published_news'],
            'Drafts' => $stats['draft_news'],
            'Scheduled' => $stats['scheduled_news'],
            'Categories' => $stats['total_categories'],
            'Videos' => $stats['total_videos'],
            'Ad Impressions' => number_format($stats['total_ad_impressions']),
            'Ad Clicks' => number_format($stats['total_ad_clicks']),
        ];
    @endphp
    @foreach($cards as $label => $value)
        <div class="bg-white rounded-lg shadow-sm p-4 border">
            <p class="text-xs uppercase text-gray-500">{{ $label }}</p>
            <p class="text-2xl font-bold mt-1">{{ $value }}</p>
        </div>
    @endforeach
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="bg-white rounded-lg shadow-sm border">
        <div class="px-4 py-3 border-b font-semibold">Recent Articles</div>
        <ul class="divide-y">
            @forelse($recentArticles as $article)
                <li class="px-4 py-3 flex justify-between text-sm">
                    <span class="truncate">{{ $article->title }}</span>
                    <span class="text-gray-400">{{ $article->created_at->diffForHumans() }}</span>
                </li>
            @empty
                <li class="px-4 py-3 text-sm text-gray-400">No articles yet.</li>
            @endforelse
        </ul>
    </div>

    <div class="bg-white rounded-lg shadow-sm border">
        <div class="px-4 py-3 border-b font-semibold">Popular Articles</div>
        <ul class="divide-y">
            @forelse($popularArticles as $article)
                <li class="px-4 py-3 flex justify-between text-sm">
                    <span class="truncate">{{ $article->title }}</span>
                    <span class="text-gray-400">{{ number_format($article->views_count) }} views</span>
                </li>
            @empty
                <li class="px-4 py-3 text-sm text-gray-400">No data yet.</li>
            @endforelse
        </ul>
    </div>
</div>
@endsection
