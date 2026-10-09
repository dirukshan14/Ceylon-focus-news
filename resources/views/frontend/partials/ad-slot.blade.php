@php
    $ads = \App\Models\Advertisement::liveForPosition($position)->limit(1)->get();
@endphp

@foreach($ads as $ad)
    @php $ad->recordImpression(); @endphp
    <div class="ad-slot ad-slot-{{ $position }} flex justify-center py-2">
        @if($ad->type === 'image')
            <a href="{{ route('ads.click', $ad) }}" target="_blank" rel="noopener sponsored">
                <img src="{{ asset('storage/'.$ad->image) }}" alt="{{ $ad->name }}" class="max-w-full">
            </a>
        @else
            {{-- HTML/AdSense ad code, sandboxed in an iframe so it can never touch the parent page's DOM/cookies. --}}
            <iframe srcdoc="{{ $ad->html_code }}" class="w-full border-0" style="min-height:90px;" sandbox="allow-scripts allow-popups"></iframe>
        @endif
    </div>
@endforeach
