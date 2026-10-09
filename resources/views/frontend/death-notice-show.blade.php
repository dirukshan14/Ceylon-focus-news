@extends('layouts.app')
@section('title', $notice->name.' - அஞ்சலி')
@section('meta_description', 'மறைந்த '.$notice->name.' அவர்களுக்கான அஞ்சலிக் குறிப்பு.')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-10">

    <nav class="text-xs text-gray-500 mb-6">
        <a href="{{ route('home') }}" class="hover:underline">முகப்பு</a> /
        <a href="{{ route('death-notices.index') }}" class="hover:underline">மரண அறிவிப்புகள்</a> /
        <span class="text-gray-700">{{ $notice->name }}</span>
    </nav>

    <div class="bg-white border rounded-xl overflow-hidden shadow-sm">
        <div class="bg-slate-800 h-3"></div>

        <div class="text-center px-6 pt-8 pb-6 border-b">
            <img src="{{ $notice->photo ? asset('storage/'.$notice->photo) : 'https://placehold.co/240x240?text=%F0%9F%95%AF' }}"
                 alt="{{ $notice->name }}" class="w-36 h-36 rounded-full object-cover mx-auto border-4 border-slate-100 shadow-sm">

            <h1 class="text-2xl font-bold mt-5">{{ $notice->name }}</h1>

            <p class="text-gray-500 mt-2 text-sm">
                @if($notice->displayAge()) வயது {{ $notice->displayAge() }} @endif
                @if($notice->location) · {{ $notice->location }} @endif
            </p>

            <p class="text-sm text-gray-400 mt-3">
                @if($notice->date_of_birth)
                    {{ $notice->date_of_birth->format('d.m.Y') }} —
                @endif
                {{ $notice->date_of_death->format('d.m.Y') }}
            </p>
        </div>

        @if($notice->tribute_message)
        <div class="px-6 py-6 border-b">
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-2">அஞ்சலிக் குறிப்பு</h2>
            <p class="text-gray-700 leading-relaxed whitespace-pre-line">{{ $notice->tribute_message }}</p>
        </div>
        @endif

        @if($notice->survived_by)
        <div class="px-6 py-6 border-b">
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-2">இழந்தோர்</h2>
            <p class="text-gray-700 leading-relaxed whitespace-pre-line">{{ $notice->survived_by }}</p>
        </div>
        @endif

        @if($notice->hasFuneralDetails())
        <div class="px-6 py-6 border-b bg-slate-50">
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-3">இறுதிச் சடங்கு விவரங்கள்</h2>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                @if($notice->funeral_date)
                    <div><dt class="text-gray-400">தேதி</dt><dd class="font-medium">{{ $notice->funeral_date->format('d.m.Y') }}</dd></div>
                @endif
                @if($notice->funeral_time)
                    <div><dt class="text-gray-400">நேரம்</dt><dd class="font-medium">{{ $notice->funeral_time }}</dd></div>
                @endif
                @if($notice->funeral_venue)
                    <div class="sm:col-span-2"><dt class="text-gray-400">இடம்</dt><dd class="font-medium">{{ $notice->funeral_venue }}</dd></div>
                @endif
            </dl>
        </div>
        @endif

        @if($notice->video)
        <div class="px-6 py-6 border-b">
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-3">நினைவு நிகழ்ச்சி வீடியோ</h2>
            @include('frontend.partials.video-player', ['video' => $notice->video])
        </div>
        @endif

        @if($notice->contact_name || $notice->contact_phone)
        <div class="px-6 py-6 text-sm text-gray-500">
            தொடர்பு: {{ $notice->contact_name }} @if($notice->contact_phone) — {{ $notice->contact_phone }} @endif
        </div>
        @endif
    </div>

    @if($related->isNotEmpty())
    <section class="mt-10">
        <h2 class="text-lg font-bold mb-4">மற்ற மரண அறிவிப்புகள்</h2>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            @foreach($related as $item)
                @include('frontend.partials.death-notice-card', ['notice' => $item])
            @endforeach
        </div>
    </section>
    @endif
</div>
@endsection
