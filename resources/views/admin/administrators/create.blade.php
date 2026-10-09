@extends('layouts.admin')
@section('title', 'Add Administrator')

@section('content')
<form action="{{ route('admin.administrators.store') }}" method="POST" class="max-w-lg bg-white rounded-lg border p-4 space-y-4">
    @csrf
    <div>
        <label class="block text-sm font-medium mb-1">Name</label>
        <input type="text" name="name" required value="{{ old('name') }}" class="w-full border rounded px-3 py-2 text-sm">
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Email</label>
        <input type="email" name="email" required value="{{ old('email') }}" class="w-full border rounded px-3 py-2 text-sm">
    </div>
    <div>
        <label class="block text-sm font-medium mb-2">Assign Role(s)</label>
        <div class="space-y-1">
            @foreach($roles as $role)
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="roles[]" value="{{ $role->id }}" @checked(in_array($role->id, old('roles', [])))>
                    {{ $role->name }} <span class="text-gray-400 text-xs">— {{ $role->description }}</span>
                </label>
            @endforeach
        </div>
    </div>
    <p class="text-xs text-gray-500">A secure temporary password will be generated. The new administrator must change it on first login.</p>
    <button type="submit" class="w-full bg-brand text-white rounded py-2 text-sm font-medium">Create Administrator</button>
</form>
@endsection
