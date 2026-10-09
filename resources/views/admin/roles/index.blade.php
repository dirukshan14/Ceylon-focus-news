@extends('layouts.admin')
@section('title', 'Roles & Permissions')

@section('content')
<div class="flex justify-end mb-4">
    <a href="{{ route('admin.roles.create') }}" class="bg-brand text-white text-sm px-4 py-2 rounded">+ New Role</a>
</div>

<div class="bg-white rounded-lg shadow-sm border overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-left text-gray-500 uppercase text-xs">
            <tr><th class="px-4 py-3">Role</th><th class="px-4 py-3">Permissions</th><th class="px-4 py-3">Users</th><th class="px-4 py-3 text-right">Actions</th></tr>
        </thead>
        <tbody class="divide-y">
            @foreach($roles as $role)
                <tr>
                    <td class="px-4 py-3 font-medium">{{ $role->name }}
                        @if($role->slug === 'super-admin')
                            <span class="ml-1 px-1.5 py-0.5 bg-purple-100 text-purple-700 text-xs rounded">Built-in</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-gray-500">{{ $role->permissions->count() }} permissions</td>
                    <td class="px-4 py-3">{{ $role->users_count }}</td>
                    <td class="px-4 py-3 text-right space-x-2">
                        @if($role->slug !== 'super-admin')
                            <a href="{{ route('admin.roles.edit', $role) }}" class="text-blue-600 hover:underline">Edit</a>
                            <form action="{{ route('admin.roles.destroy', $role) }}" method="POST" class="inline" onsubmit="return confirm('Delete this role?')">
                                @csrf @method('DELETE')
                                <button class="text-red-600 hover:underline">Delete</button>
                            </form>
                        @else
                            <span class="text-gray-400 text-xs">Locked</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
