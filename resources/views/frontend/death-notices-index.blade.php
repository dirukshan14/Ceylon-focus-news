@extends('layouts.app')
@section('title', 'மரண அறிவிப்புகள்')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-6">மரண அறிவிப்புகள்</h1>

    @include('frontend.partials.ad-slot', ['position' => 'obituary_top'])

    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 mt-4">
        @forelse($notices as $notice)
            @include('frontend.partials.death-notice-card', ['notice' => $notice])
        @empty
            <p class="text-gray-400 col-span-full">தற்போது அறிவிப்புகள் இல்லை.</p>
        @endforelse
    </div>
    <div class="mt-6">{{ $notices->links() }}</div>
</div>
@endsection
