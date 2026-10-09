<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') - Admin | {{ \App\Models\Setting::get('site_name', config('app.name')) }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { theme: { extend: { colors: { brand: '#1e3a8a' } } } };
    </script>
    <style>.site-logo { -webkit-user-drag: none; user-select: none; }</style>
</head>
<body class="bg-gray-100 text-gray-800 antialiased">
@php
    $adminSidebarLogo = \App\Models\Setting::get('logo')
        ? asset('storage/'.\App\Models\Setting::get('logo'))
        : asset('images/logo-header.png');
@endphp
<div class="flex min-h-screen">

    {{-- Sidebar --}}
    <aside class="w-64 bg-slate-900 text-slate-200 flex-shrink-0 hidden md:block">
        <div class="px-5 py-5 border-b border-slate-800 bg-white">
            <img src="{{ $adminSidebarLogo }}" alt="{{ \App\Models\Setting::get('site_name', config('app.name')) }}"
                 draggable="false" class="site-logo h-9 w-auto object-contain">
            <span class="block text-xs text-slate-500 mt-2">Admin Panel</span>
        </div>
        <nav class="px-3 py-4 space-y-1 text-sm">
            <a href="{{ route('admin.dashboard') }}" class="block px-3 py-2 rounded hover:bg-slate-800 {{ request()->routeIs('admin.dashboard') ? 'bg-slate-800 text-white' : '' }}">Dashboard</a>

            @can('viewAny', \App\Models\Article::class)
                <a href="{{ route('admin.articles.index') }}" class="block px-3 py-2 rounded hover:bg-slate-800 {{ request()->routeIs('admin.articles.*') ? 'bg-slate-800 text-white' : '' }}">News</a>
            @endcan

            @if(auth()->user()->hasPermission('manage-categories'))
                <a href="{{ route('admin.categories.index') }}" class="block px-3 py-2 rounded hover:bg-slate-800 {{ request()->routeIs('admin.categories.*') ? 'bg-slate-800 text-white' : '' }}">Categories</a>
            @endif

            @if(auth()->user()->hasPermission('manage-videos'))
                <a href="{{ route('admin.videos.index') }}" class="block px-3 py-2 rounded hover:bg-slate-800 {{ request()->routeIs('admin.videos.*') ? 'bg-slate-800 text-white' : '' }}">Videos</a>
            @endif

            @if(auth()->user()->hasPermission('manage-death-notices'))
                <a href="{{ route('admin.death-notices.index') }}" class="block px-3 py-2 rounded hover:bg-slate-800 {{ request()->routeIs('admin.death-notices.*') ? 'bg-slate-800 text-white' : '' }}">Death Notices</a>
            @endif

            @if(auth()->user()->hasPermission('manage-advertisements'))
                <a href="{{ route('admin.advertisements.index') }}" class="block px-3 py-2 rounded hover:bg-slate-800 {{ request()->routeIs('admin.advertisements.*') ? 'bg-slate-800 text-white' : '' }}">Advertisements</a>
            @endif

            @if(auth()->user()->hasPermission('manage-social-links'))
                <a href="{{ route('admin.social-links.index') }}" class="block px-3 py-2 rounded hover:bg-slate-800 {{ request()->routeIs('admin.social-links.*') ? 'bg-slate-800 text-white' : '' }}">Social Media</a>
            @endif

            <div class="pt-3 mt-3 border-t border-slate-800 text-xs uppercase text-slate-500 px-3">Administration</div>

            @if(auth()->user()->hasPermission('manage-administrators'))
                <a href="{{ route('admin.administrators.index') }}" class="block px-3 py-2 rounded hover:bg-slate-800 {{ request()->routeIs('admin.administrators.*') ? 'bg-slate-800 text-white' : '' }}">Administrators</a>
            @endif

            @if(auth()->user()->hasPermission('manage-roles'))
                <a href="{{ route('admin.roles.index') }}" class="block px-3 py-2 rounded hover:bg-slate-800 {{ request()->routeIs('admin.roles.*') ? 'bg-slate-800 text-white' : '' }}">Roles &amp; Permissions</a>
            @endif

            @if(auth()->user()->hasPermission('manage-settings'))
                <a href="{{ route('admin.settings.edit') }}" class="block px-3 py-2 rounded hover:bg-slate-800 {{ request()->routeIs('admin.settings.*') ? 'bg-slate-800 text-white' : '' }}">Settings</a>
            @endif
        </nav>
    </aside>

    <div class="flex-1 flex flex-col min-w-0">
        {{-- Topbar --}}
        <header class="bg-white border-b px-6 py-3 flex items-center justify-between">
            <h1 class="text-lg font-semibold">@yield('title', 'Dashboard')</h1>
            <div class="flex items-center gap-4">
                <span class="text-sm text-gray-500">{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button class="text-sm text-red-600 hover:underline">Logout</button>
                </form>
            </div>
        </header>

        <main class="flex-1 p-6">
            @if(session('success'))
                <div class="mb-4 rounded bg-green-100 border border-green-300 text-green-800 px-4 py-2 text-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 rounded bg-red-100 border border-red-300 text-red-800 px-4 py-2 text-sm">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>
</body>
</html>
