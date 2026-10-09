@extends('layouts.admin')
@section('title', 'Administrators')

@section('content')
<div class="flex justify-end mb-4">
    <a href="{{ route('admin.administrators.create') }}" class="bg-brand text-white text-sm px-4 py-2 rounded">+ Add Administrator</a>
</div>

<div class="bg-white rounded-lg shadow-sm border overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-left text-gray-500 uppercase text-xs">
            <tr>
                <th class="px-4 py-3">Name</th>
                <th class="px-4 py-3">Email</th>
                <th class="px-4 py-3">Roles</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3">Last Login</th>
                <th class="px-4 py-3 text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y">
            @forelse($admins as $admin)
                <tr>
                    <td class="px-4 py-3 font-medium">
                        {{ $admin->name }}
                        @if($admin->is_super_admin)
                            <span class="ml-1 px-1.5 py-0.5 bg-purple-100 text-purple-700 text-xs rounded">Super Admin</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">{{ $admin->email }}</td>
                    <td class="px-4 py-3">{{ $admin->roles->pluck('name')->join(', ') ?: '—' }}</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-0.5 rounded text-xs {{ $admin->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                            {{ ucfirst($admin->status) }}
                        </span>
                    </td>
                    <td class="px-4 py-3">{{ $admin->last_login_at?->diffForHumans() ?? 'Never' }}</td>
                    <td class="px-4 py-3 text-right space-x-2">
                        <a href="{{ route('admin.administrators.edit', $admin) }}" class="text-blue-600 hover:underline">Edit</a>
                        <form action="{{ route('admin.administrators.reset-password', $admin) }}" method="POST" class="inline" onsubmit="return confirm('Reset this admin\'s password?')">
                            @csrf
                            <button class="text-amber-600 hover:underline">Reset Password</button>
                        </form>
                        @can('delete', $admin)
                            <form action="{{ route('admin.administrators.destroy', $admin) }}" method="POST" class="inline" onsubmit="return confirm('Remove this administrator?')">
                                @csrf @method('DELETE')
                                <button class="text-red-600 hover:underline">Remove</button>
                            </form>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-4 py-6 text-center text-gray-400">No administrators yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $admins->links() }}</div>
@endsection
