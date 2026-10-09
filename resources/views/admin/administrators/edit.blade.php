@extends('layouts.admin')
@section('title', 'Edit Administrator')

@section('content')
<form action="{{ route('admin.administrators.update', $admin) }}" method="POST" class="max-w-lg bg-white rounded-lg border p-4 space-y-4">
    @csrf @method('PUT')
    <div>
        <label class="block text-sm font-medium mb-1">Name</label>
        <input type="text" name="name" required value="{{ old('name', $admin->name) }}" class="w-full border rounded px-3 py-2 text-sm">
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Email</label>
        <input type="email" name="email" required value="{{ old('email', $admin->email) }}" class="w-full border rounded px-3 py-2 text-sm">
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Status</label>
        <select name="status" class="w-full border rounded px-3 py-2 text-sm" {{ $admin->is_super_admin ? 'disabled' : '' }}>
            @foreach(['active','inactive','suspended'] as $status)
                <option value="{{ $status }}" @selected(old('status', $admin->status) === $status)>{{ ucfirst($status) }}</option>
            @endforeach
        </select>
        @if($admin->is_super_admin)
            <input type="hidden" name="status" value="active">
            <p class="text-xs text-gray-400 mt-1">The primary Super Admin account cannot be deactivated.</p>
        @endif
    </div>
    <div>
        <label class="block text-sm font-medium mb-2">Role(s)</label>
        <div class="space-y-1">
            @foreach($roles as $role)
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="roles[]" value="{{ $role->id }}"
                           @checked(in_array($role->id, old('roles', $admin->roles->pluck('id')->toArray())))>
                    {{ $role->name }}
                </label>
            @endforeach
        </div>
    </div>
    <button type="submit" class="w-full bg-brand text-white rounded py-2 text-sm font-medium">Update Administrator</button>
</form>
@endsection
