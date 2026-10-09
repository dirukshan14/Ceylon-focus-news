<article class="bg-white rounded-lg border overflow-hidden hover:shadow-md transition">
    <a href="{{ route('news.show', $article->slug) }}">
        <img src="{{ $article->featured_image ? asset('storage/'.$article->featured_image) : 'https://placehold.co/600x360?text=News' }}"
             alt="{{ $article->title }}" class="w-full h-40 object-cover">
    </a>
    <div class="p-3">
        <a href="{{ route('category.show', $article->category->slug) }}" class="text-xs font-semibold text-brand uppercase">
            {{ $article->category->name }}
        </a>
        <h3 class="font-semibold mt-1 leading-snug">
            <a href="{{ route('news.show', $article->slug) }}" class="hover:underline">{{ $article->title }}</a>
        </h3>
        <p class="text-sm text-gray-500 mt-1 line-clamp-2">{{ $article->excerptOrTruncatedBody(100) }}</p>
        <div class="flex justify-between text-xs text-gray-400 mt-3">
            <span>{{ $article->author->name }}</span>
            <span>{{ $article->published_at?->diffForHumans() }} · {{ $article->reading_time_minutes }} நிமிட வாசிப்பு</span>
        </div>
    </div>
</article>
