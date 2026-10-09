@extends('layouts.admin')
@section('title', 'Death Notices')

@section('content')
<div class="flex justify-between items-center mb-4">
    <form method="GET" class="flex gap-2">
        <select name="status" class="border rounded px-3 py-1.5 text-sm">
            <option value="">All statuses</option>
            <option value="draft" @selected(request('status') === 'draft')>Draft</option>
            <option value="published" @selected(request('status') === 'published')>Published</option>
        </select>
        <button class="bg-gray-800 text-white text-sm px-3 py-1.5 rounded">Filter</button>
    </form>
    <a href="{{ route('admin.death-notices.create') }}" class="bg-brand text-white text-sm px-4 py-2 rounded">+ Add Death Notice</a>
</div>

<div class="bg-white rounded-lg shadow-sm border overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-left text-gray-500 uppercase text-xs">
            <tr>
                <th class="px-4 py-3">Photo</th>
                <th class="px-4 py-3">Name</th>
                <th class="px-4 py-3">Location</th>
                <th class="px-4 py-3">Date of Death</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3 text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y">
            @forelse($notices as $notice)
                <tr>
                    <td class="px-4 py-3">
                        <img src="{{ $notice->photo ? asset('storage/'.$notice->photo) : 'https://placehold.co/60x60?text=%F0%9F%95%AF' }}" class="w-10 h-10 rounded-full object-cover">
                    </td>
                    <td class="px-4 py-3 font-medium">{{ $notice->name }}</td>
                    <td class="px-4 py-3">{{ $notice->location ?? '—' }}</td>
                    <td class="px-4 py-3">{{ $notice->date_of_death->format('M d, Y') }}</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-0.5 rounded text-xs {{ $notice->status === 'published' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                            {{ ucfirst($notice->status) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right space-x-2">
                        <a href="{{ route('admin.death-notices.edit', $notice) }}" class="text-blue-600 hover:underline">Edit</a>
                        <form action="{{ route('admin.death-notices.destroy', $notice) }}" method="POST" class="inline" onsubmit="return confirm('Remove this notice?')">
                            @csrf @method('DELETE')
                            <button class="text-red-600 hover:underline">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-4 py-6 text-center text-gray-400">No death notices yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $notices->links() }}</div>
@endsection
