@csrf
@if($video ?? false) @method('PUT') @endif

<div class="max-w-2xl bg-white rounded-lg border p-4 space-y-4">
    <div>
        <label class="block text-sm font-medium mb-1">Title</label>
        <input type="text" name="title" required value="{{ old('title', $video->title ?? '') }}" class="w-full border rounded px-3 py-2 text-sm">
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Description</label>
        <textarea name="description" rows="3" class="w-full border rounded px-3 py-2 text-sm">{{ old('description', $video->description ?? '') }}</textarea>
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Video URL</label>
        <input type="url" name="source_url" required value="{{ old('source_url', $video->source_url ?? '') }}"
               placeholder="https://www.youtube.com/watch?v=..." class="w-full border rounded px-3 py-2 text-sm">
        <p class="text-xs text-gray-400 mt-1">Paste a YouTube, Facebook, Instagram, TikTok, Vimeo, or direct .mp4 link. The embeddable player is generated automatically.</p>
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Thumbnail Image</label>
        <input type="file" name="thumbnail" accept="image/*" class="w-full text-sm">
        @if(isset($video) && $video->thumbnail)
            <img src="{{ $video->thumbnailUrl() }}" class="mt-2 h-20 rounded border">
        @endif
        <p class="text-xs text-gray-400 mt-1">Shown on the homepage, videos list, and search results. Recommended 16:9 (e.g. 800×450).</p>
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Start at (timestamp)</label>
        <div class="flex items-center gap-2">
            <input type="number" name="start_seconds" min="0" value="{{ old('start_seconds', $video->start_seconds ?? '') }}"
                   placeholder="0" class="w-32 border rounded px-3 py-2 text-sm">
            <span class="text-sm text-gray-400">seconds{{ isset($video) && $video->start_seconds ? ' ('.$video->formattedStartTime().')' : '' }}</span>
        </div>
        <p class="text-xs text-gray-400 mt-1">Optional. The player will jump straight to this point automatically - useful for pointing to one person's segment within a longer recording (e.g. a funeral livestream).</p>
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Category</label>
        <select name="category_id" class="w-full border rounded px-3 py-2 text-sm">
            <option value="">— None —</option>
            @foreach($categories as $cat)
                <option value="{{ $cat->id }}" @selected(old('category_id', $video->category_id ?? '') == $cat->id)>{{ $cat->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Status</label>
        <select name="status" class="w-full border rounded px-3 py-2 text-sm">
            @foreach(['draft','published','archived'] as $status)
                <option value="{{ $status }}" @selected(old('status', $video->status ?? 'published') === $status)>{{ ucfirst($status) }}</option>
            @endforeach
        </select>
    </div>
    <label class="flex items-center gap-2 text-sm">
        <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $video->is_featured ?? false))> Featured video
    </label>
    <button type="submit" class="w-full bg-brand text-white rounded py-2 text-sm font-medium">
        {{ isset($video) ? 'Update Video' : 'Add Video' }}
    </button>
</div>
