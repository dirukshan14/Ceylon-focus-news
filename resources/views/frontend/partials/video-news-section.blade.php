@if($videos->isNotEmpty())
@php $main = $videos->first(); @endphp
<section id="video-news">
    <div class="flex items-center justify-center gap-4 mb-5">
        <span class="h-0.5 flex-1 bg-amber-500 max-w-xs"></span>
        <h2 class="text-xl font-bold text-slate-800 whitespace-nowrap">வீடியோ செய்திகள்</h2>
        <span class="h-0.5 flex-1 bg-amber-500 max-w-xs"></span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 bg-slate-900 rounded-lg overflow-hidden">
        {{-- Main player --}}
        <div class="lg:col-span-2 relative aspect-video bg-black" id="video-news-player">
            <div id="video-news-thumb" class="absolute inset-0 cursor-pointer group" onclick="playMainVideo()" data-embed="{{ $main->playbackUrl() }}">
                <img id="video-news-thumb-img" src="{{ $main->thumbnailUrl() ?: 'https://placehold.co/900x500?text=Video' }}"
                     class="absolute inset-0 w-full h-full object-cover opacity-80">
                <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/10 to-black/40"></div>

                <div class="absolute top-0 left-0 right-0 p-4 flex items-start gap-3">
                    <img src="{{ \App\Models\Setting::get('logo') ? asset('storage/'.\App\Models\Setting::get('logo')) : asset('images/logo-header.png') }}"
                         class="w-9 h-9 rounded-full object-cover flex-shrink-0 border-2 border-white/50 bg-white">

                    <div class="min-w-0">
                        <p id="video-news-title" class="text-white font-bold leading-snug line-clamp-2">{{ $main->title }}</p>
                        <p class="text-slate-300 text-xs mt-0.5">{{ \App\Models\Setting::get('site_name', config('app.name')) }}</p>
                    </div>
                </div>

                <div class="absolute inset-0 flex items-center justify-center">
                    <span class="w-16 h-16 rounded-full bg-white/90 flex items-center justify-center group-hover:scale-110 transition shadow-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#dc2626" class="w-7 h-7 ml-1"><path d="M8 5v14l11-7z"/></svg>
                    </span>
                </div>

                <div class="absolute bottom-3 left-3 flex gap-2">
                    <span class="w-8 h-8 rounded-full bg-black/50 flex items-center justify-center text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4"><path d="M4 12v7a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-7M16 6l-4-4-4 4M12 2v14"/></svg>
                    </span>
                    @if($main->start_seconds)
                        <span class="h-8 px-3 rounded-full bg-black/50 flex items-center gap-1 text-white text-xs">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-3.5 h-3.5"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" d="M12 7v5l3 3"/></svg>
                            {{ $main->formattedStartTime() }}
                        </span>
                    @endif
                </div>

                @if($main->provider === 'youtube')
                    <span class="absolute bottom-3 right-3 bg-black/70 text-white text-xs font-semibold px-3 py-1.5 rounded-full flex items-center gap-1.5">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4"><path d="M21.6 7.2s-.2-1.5-.8-2.2c-.8-.8-1.7-.8-2.1-.9C15.9 4 12 4 12 4h0s-3.9 0-6.7.2c-.4 0-1.3 0-2.1.9-.6.6-.8 2.2-.8 2.2S2.2 9 2.2 10.7v1.6c0 1.8.2 3.5.2 3.5s.2 1.5.8 2.2c.8.9 1.9.8 2.4.9 1.7.2 7.4.2 7.4.2s3.9 0 6.7-.3c.4 0 1.3 0 2.1-.9.6-.6.8-2.2.8-2.2s.2-1.7.2-3.5v-1.6C22 9 21.6 7.2 21.6 7.2zM9.9 14.6V8.4l5.4 3.1-5.4 3.1z"/></svg>
                        Watch on YouTube
                    </span>
                @endif
            </div>
            <iframe id="video-news-iframe" class="hidden absolute inset-0 w-full h-full" frameborder="0" allow="autoplay; encrypted-media; picture-in-picture" allowfullscreen></iframe>
        </div>

        {{-- Playlist --}}
        <div class="max-h-[420px] lg:max-h-none overflow-y-auto divide-y divide-slate-800">
            @foreach($videos as $i => $video)
                <a href="javascript:void(0)"
                   class="video-news-item flex gap-3 p-3 hover:bg-slate-800 transition {{ $i === 0 ? 'bg-brand' : '' }}"
                   data-embed="{{ $video->playbackUrl() }}"
                   data-title="{{ $video->title }}"
                   data-thumb="{{ $video->thumbnailUrl() ?: 'https://placehold.co/300x170?text=Video' }}"
                   onclick="selectVideo(this)">
                    <div class="relative w-24 h-16 flex-shrink-0 rounded overflow-hidden">
                        <img src="{{ $video->thumbnailUrl() ?: 'https://placehold.co/300x170?text=Video' }}" class="w-full h-full object-cover">
                        <span class="absolute inset-0 flex items-center justify-center bg-black/20">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white" class="w-4 h-4"><path d="M8 5v14l11-7z"/></svg>
                        </span>
                    </div>
                    <div class="min-w-0">
                        <p class="text-white text-sm font-medium leading-snug line-clamp-2">{{ $video->title }}</p>
                        <p class="text-slate-400 text-xs mt-1">{{ $video->published_at?->format('H:i') }}</p>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
    <div class="text-right mt-3">
        <a href="{{ route('videos.index') }}" class="text-sm text-brand hover:underline">அனைத்து வீடியோக்களும் காண →</a>
    </div>
</section>

<script>
    function playMainVideo() {
        const thumb = document.getElementById('video-news-thumb');
        const iframe = document.getElementById('video-news-iframe');
        iframe.src = thumb.dataset.embed || iframe.src;
        thumb.classList.add('hidden');
        iframe.classList.remove('hidden');
    }

    function selectVideo(el) {
        document.querySelectorAll('.video-news-item').forEach(item => item.classList.remove('bg-brand'));
        el.classList.add('bg-brand');

        const thumb = document.getElementById('video-news-thumb');
        thumb.dataset.embed = el.dataset.embed;
        document.getElementById('video-news-thumb-img').src = el.dataset.thumb;
        document.getElementById('video-news-title').textContent = el.dataset.title;

        // If a video is already playing, swap it immediately; otherwise wait
        // for the viewer to click the play button on the new thumbnail.
        const iframe = document.getElementById('video-news-iframe');
        if (!iframe.classList.contains('hidden')) {
            iframe.src = el.dataset.embed;
        } else {
            thumb.classList.remove('hidden');
        }
    }
</script>
@endif
