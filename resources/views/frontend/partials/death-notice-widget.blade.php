@if($notices->isNotEmpty())
<div class="bg-white rounded-lg border overflow-hidden">
    <div class="bg-slate-800 px-4 py-2.5 flex items-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2" class="w-4 h-4 flex-shrink-0"><path d="M12 21s-7-4.5-9.5-9A5.5 5.5 0 0 1 12 6a5.5 5.5 0 0 1 9.5 6c-2.5 4.5-9.5 9-9.5 9z"/></svg>
        <h3 class="text-white font-semibold text-sm">துயர் பகிர்வு</h3>
    </div>

    <div class="divide-y">
        @foreach($notices as $notice)
            <a href="{{ route('death-notices.show', $notice->slug) }}" class="block hover:bg-slate-50 transition">
                <div class="bg-slate-700 px-3 py-1.5">
                    <span class="text-white text-xs font-semibold">மரண அறிவித்தல்</span>
                </div>
                <div class="flex items-center gap-3 p-3">
                    <div class="min-w-0 flex-1">
                        <p class="font-semibold text-sm leading-snug line-clamp-2">{{ $notice->name }}</p>
                        @if($notice->location)
                            <p class="text-xs text-gray-500 mt-1 truncate">{{ $notice->location }}</p>
                        @endif
                        <p class="text-xs text-gray-400 mt-1">{{ $notice->date_of_death->format('d M, Y') }}</p>
                    </div>
                    <img src="{{ $notice->photo ? asset('storage/'.$notice->photo) : 'https://placehold.co/80x80?text=%F0%9F%95%AF' }}"
                         alt="{{ $notice->name }}" class="w-16 h-16 rounded object-cover flex-shrink-0">
                </div>
            </a>
        @endforeach
    </div>

    <a href="{{ route('death-notices.index') }}" class="block text-center text-sm text-brand hover:underline py-2.5 border-t">
        அனைத்தும் காண →
    </a>
</div>
@endif
