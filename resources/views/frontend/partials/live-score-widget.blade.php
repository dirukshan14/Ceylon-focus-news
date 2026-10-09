@php
    $liveScoreTitle = \App\Models\Setting::get('live_score_title', 'நேரடி ஸ்கோர்');
    $liveScoreContent = \App\Models\Setting::get('live_score_content');
    $liveScoreUrl = \App\Models\Setting::get('live_score_link');
@endphp
<div id="live-score" class="bg-white rounded-lg border overflow-hidden scroll-mt-28">
    <div class="bg-red-600 px-4 py-2.5 flex items-center gap-2">
        <span class="relative flex h-2.5 w-2.5">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-white opacity-75"></span>
            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-white"></span>
        </span>
        <h3 class="text-white font-semibold text-sm">{{ $liveScoreTitle }}</h3>
    </div>

    <div class="p-4">
        @if($liveScoreContent)
            <div class="text-sm text-gray-700 leading-relaxed">{!! nl2br(e($liveScoreContent)) !!}</div>
        @else
            <p class="text-sm text-gray-400">தற்போது நேரடி ஸ்கோர் கிடைக்கவில்லை.</p>
        @endif
    </div>

    @if($liveScoreUrl)
        <a href="{{ $liveScoreUrl }}" target="_blank" rel="noopener" class="block text-center text-sm text-brand hover:underline py-2.5 border-t">
            முழு ஸ்கோர் காண →
        </a>
    @endif
</div>
