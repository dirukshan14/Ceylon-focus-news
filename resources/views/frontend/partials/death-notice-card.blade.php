<a href="{{ route('death-notices.show', $notice->slug) }}"
   class="block bg-white rounded-lg border overflow-hidden hover:shadow-md transition text-center">
    <div class="pt-5 pb-3 px-4">
        <img src="{{ $notice->photo ? asset('storage/'.$notice->photo) : 'https://placehold.co/200x200?text=%F0%9F%95%AF' }}"
             alt="{{ $notice->name }}" class="w-20 h-20 rounded-full object-cover mx-auto border-2 border-slate-200">
        <p class="font-semibold mt-3 leading-snug">{{ $notice->name }}</p>
        @if($notice->displayAge())
            <p class="text-xs text-gray-400 mt-0.5">வயது {{ $notice->displayAge() }}</p>
        @endif
        @if($notice->location)
            <p class="text-xs text-gray-500 mt-0.5">{{ $notice->location }}</p>
        @endif
    </div>
    <div class="bg-slate-800 text-white text-xs py-1.5">முழு விவரம் காண</div>
</a>
