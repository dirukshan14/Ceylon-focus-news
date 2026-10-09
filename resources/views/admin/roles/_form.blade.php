@csrf
@if($role ?? false) @method('PUT') @endif

<div class="max-w-2xl bg-white rounded-lg border p-4 space-y-4">
    <div>
        <label class="block text-sm font-medium mb-1">Role Name</label>
        <input type="text" name="name" required value="{{ old('name', $role->name ?? '') }}" class="w-full border rounded px-3 py-2 text-sm">
    </div>

    @unless(isset($role))
    <div>
        <label class="block text-sm font-medium mb-1">Slug (unique identifier)</label>
        <input type="text" name="slug" required value="{{ old('slug') }}" placeholder="ad-manager" class="w-full border rounded px-3 py-2 text-sm">
    </div>
    @endunless

    <div>
        <label class="block text-sm font-medium mb-1">Description</label>
        <input type="text" name="description" value="{{ old('description', $role->description ?? '') }}" class="w-full border rounded px-3 py-2 text-sm">
    </div>

    <div>
        <label class="block text-sm font-medium mb-2">Permissions</label>
        @php $selected = old('permissions', isset($role) ? $role->permissions->pluck('id')->toArray() : []); @endphp
        @foreach($permissions as $group => $items)
            <div class="mb-3">
                <p class="text-xs uppercase text-gray-400 mb-1">{{ $group }}</p>
                <div class="grid grid-cols-2 gap-1">
                    @foreach($items as $permission)
                        <label class="flex items-center gap-2 text-sm">
                            <input type="checkbox" name="permissions[]" value="{{ $permission->id }}" @checked(in_array($permission->id, $selected))>
                            {{ $permission->name }}
                        </label>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    <button type="submit" class="w-full bg-brand text-white rounded py-2 text-sm font-medium">
        {{ isset($role) ? 'Update Role' : 'Create Role' }}
    </button>
</div>
