@php $ratio = 'padding-bottom:56.25%;'; @endphp

<div>
    <div class="relative w-full rounded-lg overflow-hidden bg-black" style="{{ $ratio }}">
        @if(in_array($video->provider, ['youtube', 'vimeo', 'facebook', 'instagram']))
            <iframe src="{{ $video->playbackUrl() }}"
                    class="absolute inset-0 w-full h-full"
                    frameborder="0" allow="autoplay; encrypted-media; picture-in-picture" allowfullscreen></iframe>
        @elseif($video->provider === 'mp4')
            <video src="{{ $video->embed_url }}" @if($video->start_seconds) data-start="{{ $video->start_seconds }}" @endif
                   controls class="absolute inset-0 w-full h-full object-contain" id="video-{{ $video->id }}"></video>
            @if($video->start_seconds)
                <script>
                    document.getElementById('video-{{ $video->id }}').addEventListener('loadedmetadata', function () {
                        this.currentTime = {{ $video->start_seconds }};
                    });
                </script>
            @endif
        @elseif($video->provider === 'tiktok')
            {{-- TikTok requires their embed script; blockquote is the officially supported pattern. --}}
            <blockquote class="tiktok-embed absolute inset-0" cite="{{ $video->embed_url }}"></blockquote>
            <script async src="https://www.tiktok.com/embed.js"></script>
        @else
            <iframe src="{{ $video->playbackUrl() }}" class="absolute inset-0 w-full h-full" frameborder="0" allowfullscreen></iframe>
        @endif
    </div>
    @if($video->start_seconds)
        <p class="text-xs text-gray-400 mt-2">{{ $video->formattedStartTime() }} நேரத்திலிருந்து தொடங்குகிறது</p>
    @endif
</div>

