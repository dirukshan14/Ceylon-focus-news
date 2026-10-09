@csrf
@if($category ?? false) @method('PUT') @endif

<div class="bg-white rounded-lg border p-4 max-w-lg space-y-4">
    <div>
        <label class="block text-sm font-medium mb-1">Name</label>
        <input type="text" name="name" required value="{{ old('name', $category->name ?? '') }}" class="w-full border rounded px-3 py-2 text-sm">
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Description</label>
        <textarea name="description" rows="3" class="w-full border rounded px-3 py-2 text-sm">{{ old('description', $category->description ?? '') }}</textarea>
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Parent Category (optional, for subcategories)</label>
        <select name="parent_id" class="w-full border rounded px-3 py-2 text-sm">
            <option value="">— None (top-level) —</option>
            @foreach($parents as $parent)
                <option value="{{ $parent->id }}" @selected(old('parent_id', $category->parent_id ?? '') == $parent->id)>{{ $parent->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Image</label>
        <input type="file" name="image" accept="image/*" class="w-full text-sm">
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Order</label>
        <input type="number" name="order" value="{{ old('order', $category->order ?? 0) }}" class="w-full border rounded px-3 py-2 text-sm">
    </div>
    <label class="flex items-center gap-2 text-sm">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $category->is_active ?? true))> Active
    </label>
    <button type="submit" class="w-full bg-brand text-white rounded py-2 text-sm font-medium">
        {{ isset($category) ? 'Update Category' : 'Create Category' }}
    </button>
</div>
