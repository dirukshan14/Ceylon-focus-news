@csrf
@if($article ?? false) @method('PUT') @endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-4">
        <div class="bg-white rounded-lg border p-4">
            <label class="block text-sm font-medium mb-1">Title</label>
            <input type="text" name="title" required value="{{ old('title', $article->title ?? '') }}"
                   class="w-full border rounded px-3 py-2 text-sm">

            <label class="block text-sm font-medium mt-4 mb-1">Short Description / Excerpt</label>
            <textarea name="excerpt" rows="2" class="w-full border rounded px-3 py-2 text-sm">{{ old('excerpt', $article->excerpt ?? '') }}</textarea>

            <label class="block text-sm font-medium mt-4 mb-1">Full Content</label>
            {{-- In production, initialize a rich text editor (TipTap/Quill/CKEditor) on this textarea. --}}
            <textarea name="body" id="body-editor" rows="14" required
                      class="w-full border rounded px-3 py-2 text-sm">{{ old('body', $article->body ?? '') }}</textarea>
            <p class="text-xs text-gray-400 mt-1">Supports headings, bold/italic, links, images, tables, blockquotes, and embeds once a rich editor is wired up here.</p>
        </div>

        <div class="bg-white rounded-lg border p-4">
            <h3 class="font-semibold text-sm mb-3">SEO</h3>
            <label class="block text-sm font-medium mb-1">SEO Title</label>
            <input type="text" name="seo_title" value="{{ old('seo_title', $article->seo_title ?? '') }}"
                   class="w-full border rounded px-3 py-2 text-sm">
            <label class="block text-sm font-medium mt-3 mb-1">SEO Description</label>
            <textarea name="seo_description" rows="2" class="w-full border rounded px-3 py-2 text-sm">{{ old('seo_description', $article->seo_description ?? '') }}</textarea>
            <label class="block text-sm font-medium mt-3 mb-1">SEO Keywords</label>
            <input type="text" name="seo_keywords" value="{{ old('seo_keywords', $article->seo_keywords ?? '') }}"
                   class="w-full border rounded px-3 py-2 text-sm">
        </div>
    </div>

    <div class="space-y-4">
        <div class="bg-white rounded-lg border p-4">
            <label class="block text-sm font-medium mb-1">Status</label>
            <select name="status" class="w-full border rounded px-3 py-2 text-sm">
                @foreach(['draft','pending_review','scheduled','published','archived'] as $status)
                    @continue(in_array($status, ['scheduled','published']) && ! $canPublish)
                    <option value="{{ $status }}" @selected(old('status', $article->status ?? ($canPublish ? 'published' : 'pending_review')) === $status)>
                        {{ ucfirst(str_replace('_',' ',$status)) }}
                    </option>
                @endforeach
            </select>
            @unless($canPublish)
                <p class="text-xs text-amber-600 mt-1">Your role can't publish directly - submit for review and an Editor or Administrator will publish it.</p>
            @endunless

            <label class="block text-sm font-medium mt-3 mb-1">Publish Date (for scheduled)</label>
            <input type="datetime-local" name="published_at"
                   value="{{ old('published_at', isset($article) && $article->published_at ? $article->published_at->format('Y-m-d\TH:i') : '') }}"
                   class="w-full border rounded px-3 py-2 text-sm">

            <div class="mt-3 space-y-1 text-sm">
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $article->is_featured ?? false))> Featured
                </label>
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="is_breaking" value="1" @checked(old('is_breaking', $article->is_breaking ?? false))> Breaking News
                </label>
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="is_trending" value="1" @checked(old('is_trending', $article->is_trending ?? false))> Trending
                </label>
            </div>
        </div>

        <div class="bg-white rounded-lg border p-4">
            <label class="block text-sm font-medium mb-1">Category</label>
            <select name="category_id" id="category_id" required class="w-full border rounded px-3 py-2 text-sm">
                <option value="">— Select a category —</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" @selected(old('category_id', $article->category_id ?? '') == $cat->id)>{{ $cat->name }}</option>
                @endforeach
            </select>

            <label class="block text-sm font-medium mt-3 mb-1">Subcategory (optional)</label>
            <select name="subcategory_id" id="subcategory_id" class="w-full border rounded px-3 py-2 text-sm">
                <option value="">— None —</option>
                @foreach($categories as $cat)
                    @foreach($cat->children as $child)
                        <option value="{{ $child->id }}" data-parent="{{ $cat->id }}"
                            @selected(old('subcategory_id', $article->subcategory_id ?? '') == $child->id)>{{ $child->name }}</option>
                    @endforeach
                @endforeach
            </select>
            <p class="text-xs text-gray-400 mt-1">Only subcategories belonging to the selected category above are shown.</p>

            <label class="block text-sm font-medium mt-3 mb-1">Tags (comma separated)</label>
            <input type="text" name="tags_raw" id="tags_raw"
                   value="{{ old('tags', isset($article) ? $article->tags->pluck('name')->join(', ') : '') }}"
                   class="w-full border rounded px-3 py-2 text-sm" placeholder="election, budget, colombo">
            <div id="tags-hidden-inputs"></div>
        </div>

        <div class="bg-white rounded-lg border p-4">
            <label class="block text-sm font-medium mb-1">Featured Image</label>
            <input type="file" name="featured_image" accept="image/*" class="w-full text-sm">
            @if(isset($article) && $article->featured_image)
                <img src="{{ asset('storage/'.$article->featured_image) }}" class="mt-2 rounded h-24 object-cover">
            @endif

            <label class="block text-sm font-medium mt-3 mb-1">Gallery Images</label>
            <input type="file" name="gallery[]" accept="image/*" multiple class="w-full text-sm">
        </div>

        <button type="submit" class="w-full bg-brand text-white rounded py-2 text-sm font-medium">
            {{ isset($article) ? 'Update Article' : 'Create Article' }}
        </button>
    </div>
</div>

<script>
    // Convert the comma-separated tags field into tags[] array inputs before submit.
    document.querySelector('form').addEventListener('submit', function () {
        const raw = document.getElementById('tags_raw').value;
        const container = document.getElementById('tags-hidden-inputs');
        container.innerHTML = '';
        raw.split(',').map(t => t.trim()).filter(Boolean).forEach(tag => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'tags[]';
            input.value = tag;
            container.appendChild(input);
        });
    });

    // Only show subcategories that belong to the currently selected category,
    // so it's impossible to accidentally save a subcategory under the wrong
    // parent (which previously broke homepage category grouping entirely).
    (function () {
        const categorySelect = document.getElementById('category_id');
        const subcategorySelect = document.getElementById('subcategory_id');
        const allSubOptions = Array.from(subcategorySelect.options).filter(o => o.value !== '');

        function filterSubcategories() {
            const selectedParent = categorySelect.value;
            const currentSub = subcategorySelect.value;
            let currentStillValid = false;

            allSubOptions.forEach(function (option) {
                const belongs = option.dataset.parent === selectedParent;
                option.hidden = !belongs;
                option.disabled = !belongs;
                if (belongs && option.value === currentSub) {
                    currentStillValid = true;
                }
            });

            if (!currentStillValid) {
                subcategorySelect.value = '';
            }
        }

        categorySelect.addEventListener('change', filterSubcategories);
        filterSubcategories(); // run once on load (handles edit page + old() values)
    })();
</script>
