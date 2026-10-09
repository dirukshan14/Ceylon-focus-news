@php($n = $news ?? null)

<label>Title</label>
<input type="text" name="title" value="{{ old('title', $n->title ?? '') }}" required>

<label>Category</label>
<select name="category_id" required>
    <option value="">-- Select --</option>
    @foreach($categories as $cat)
        <option value="{{ $cat->id }}" @selected(old('category_id', $n->category_id ?? '') == $cat->id)>{{ $cat->name }}</option>
    @endforeach
</select>

<label>Excerpt / Summary</label>
<textarea name="excerpt" rows="3">{{ old('excerpt', $n->excerpt ?? '') }}</textarea>

<label>Content</label>
<textarea name="content" id="content" rows="12" required>{{ old('content', $n->content ?? '') }}</textarea>
{{-- Hook a WYSIWYG editor (TinyMCE / CKEditor / Quill) onto #content if desired --}}

<label>Featured Image</label>
<input type="file" name="image" accept="image/*">
@if($n && $n->image)
    <img src="{{ $n->image_url }}" alt="" style="max-width:200px;display:block;margin-top:8px;">
@endif

<label>Status</label>
<select name="status" required>
    <option value="published" @selected(old('status', $n->status ?? 'published') == 'published')>Published</option>
    <option value="draft" @selected(old('status', $n->status ?? '') == 'draft')>Draft</option>
</select>
