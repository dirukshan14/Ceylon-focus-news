<!DOCTYPE html>
<html lang="ta">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', \App\Models\Setting::get('meta_title', config('app.name')))</title>
    <meta name="description" content="@yield('meta_description', \App\Models\Setting::get('meta_description', ''))">
    @hasSection('canonical')
        <link rel="canonical" href="@yield('canonical')">
    @endif
    @stack('head')
    <link rel="icon" href="{{ \App\Models\Setting::get('favicon') ? asset('storage/'.\App\Models\Setting::get('favicon')) : asset('images/logo-header.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Tamil:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body, input, textarea, select, button { font-family: 'Noto Sans Tamil', 'Inter', sans-serif; }
        /* Logos are branding, not content - stop the browser's native
           click-and-drag-to-save/drag-out-of-page ghost image behaviour. */
        .site-logo { -webkit-user-drag: none; user-select: none; -webkit-touch-callout: none; }
    </style>
    <script>tailwind.config = { theme: { extend: { colors: { brand: {{ \Illuminate\Support\Js::from(preg_match('/^#[0-9a-fA-F]{6}$/', (string) \App\Models\Setting::get('primary_color')) ? \App\Models\Setting::get('primary_color') : '#1e3a8a') }} } } } };</script>
</head>
<body class="bg-gray-50 text-gray-800">

@php
    $siteName = \App\Models\Setting::get('site_name', config('app.name'));
    $navCategories = \App\Models\Category::active()->topLevel()->orderBy('order')->limit(8)->get();
    $socialLinks = \App\Models\SocialLink::active()->get();
    $liveScoreLink = \App\Models\Setting::get('live_score_link');
    // SECURITY: only http(s) links are allowed here (blocks javascript:/data: URLs).
    if (! $liveScoreLink || ! preg_match('#^https?://#i', $liveScoreLink)) {
        $liveScoreLink = route('home').'#live-score';
    }

    // Prefer whatever the admin uploaded in Settings; fall back to the
    // bundled brand assets so the site never shows a broken image.
    $headerLogoUrl = \App\Models\Setting::get('logo')
        ? asset('storage/'.\App\Models\Setting::get('logo'))
        : asset('images/logo-header.png');
    $footerLogoUrl = \App\Models\Setting::get('footer_logo')
        ? asset('storage/'.\App\Models\Setting::get('footer_logo'))
        : asset('images/logo-footer.jpg');
@endphp

@include('frontend.partials.ad-slot', ['position' => 'header'])

<header class="sticky top-0 z-40 shadow-sm">
    {{-- Top bar: date on the left, centered logo. --}}
    <div class="bg-white border-b">
        <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between relative">
            <span class="text-sm text-slate-600 font-medium hidden sm:inline">{{ \App\Support\TamilDate::format() }}</span>
            <a href="{{ route('home') }}" class="mx-auto sm:mx-0 sm:absolute sm:left-1/2 sm:-translate-x-1/2">
                <img src="{{ $headerLogoUrl }}" alt="{{ $siteName }}" draggable="false"
                     class="site-logo h-11 w-auto object-contain" oncontextmenu="return false;">
            </a>
            <div class="hidden sm:flex gap-3 text-slate-400">
                @foreach($socialLinks as $link)
                    <a href="{{ $link->url }}" target="_blank" rel="noopener" class="hover:text-brand text-xs uppercase">{{ substr($link->platform,0,2) }}</a>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Nav bar: solid brand-color bar, hamburger + links on the left, search on the right. --}}
    <div class="bg-brand relative">
        <div class="max-w-6xl mx-auto px-4 py-2.5 flex items-center justify-between gap-4">
            <div class="flex items-center gap-4 min-w-0">
                <button id="mobile-nav-toggle" class="text-amber-400 flex-shrink-0" aria-label="மெனுவைத் திற">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="w-6 h-6"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <nav class="hidden md:flex gap-5 text-sm font-medium text-white whitespace-nowrap overflow-x-auto">
                    <a href="{{ route('home') }}" class="hover:text-amber-300">முகப்பு</a>
                    @foreach($navCategories as $cat)
                        <a href="{{ route('category.show', $cat->slug) }}" class="hover:text-amber-300">{{ $cat->name }}</a>
                    @endforeach
                    <a href="{{ route('videos.index') }}" class="hover:text-amber-300">வீடியோக்கள்</a>
                    <a href="{{ route('death-notices.index') }}" class="hover:text-amber-300">மரண அறிவிப்புகள்</a>
                    <a href="{{ $liveScoreLink }}" class="hover:text-amber-300" @if(\Illuminate\Support\Str::startsWith($liveScoreLink, 'http')) target="_blank" rel="noopener" @endif>நேரடி ஸ்கோர்</a>
                </nav>
            </div>

            <button id="search-toggle" class="flex items-center gap-1.5 text-white text-sm flex-shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-5 h-5"><circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="m21 21-4.3-4.3"/></svg>
                <span class="hidden sm:inline font-medium">தேடல்</span>
            </button>
        </div>

        <div id="search-bar" class="hidden bg-white border-t px-4 py-3">
            <form action="{{ route('search') }}" method="GET" class="max-w-6xl mx-auto">
                <input type="text" name="q" placeholder="செய்திகளைத் தேடுங்கள்..." autofocus
                       class="w-full border rounded-full px-4 py-2 text-sm">
            </form>
        </div>
    </div>

    <div id="mobile-nav" class="hidden md:hidden bg-white border-t px-4 py-3 space-y-2 text-sm">
        <a href="{{ route('home') }}" class="block">முகப்பு</a>
        @foreach($navCategories as $cat)
            <a href="{{ route('category.show', $cat->slug) }}" class="block">{{ $cat->name }}</a>
        @endforeach
        <a href="{{ route('videos.index') }}" class="block">வீடியோக்கள்</a>
        <a href="{{ route('death-notices.index') }}" class="block">மரண அறிவிப்புகள்</a>
        <a href="{{ $liveScoreLink }}" class="block">நேரடி ஸ்கோர்</a>
    </div>
</header>

<script>
    document.getElementById('mobile-nav-toggle').addEventListener('click', function () {
        document.getElementById('mobile-nav').classList.toggle('hidden');
    });
    document.getElementById('search-toggle').addEventListener('click', function () {
        document.getElementById('search-bar').classList.toggle('hidden');
    });
</script>

@yield('content')

<footer class="bg-slate-900 text-slate-300 mt-12">
    @include('frontend.partials.ad-slot', ['position' => 'footer'])
    <div class="max-w-6xl mx-auto px-4 py-8 text-sm">
        <div class="flex flex-col md:flex-row justify-between gap-4">
            <div>
                <img src="{{ $footerLogoUrl }}" alt="{{ $siteName }}" draggable="false"
                     class="site-logo h-16 w-auto object-contain rounded-md mb-3" oncontextmenu="return false;">
                <p class="text-slate-400 mt-1 max-w-sm">{{ \App\Models\Setting::get('site_description') }}</p>
            </div>
            <div class="flex gap-3">
                @foreach($socialLinks as $link)
                    <a href="{{ $link->url }}" target="_blank" rel="noopener" class="hover:text-white">{{ ucfirst(str_replace('_',' ',$link->platform)) }}</a>
                @endforeach
            </div>
        </div>
        <p class="mt-6 text-slate-500 text-xs">{{ \App\Models\Setting::get('footer_text', '© '.date('Y').' '.$siteName) }}</p>
    </div>
</footer>

</body>
</html>
