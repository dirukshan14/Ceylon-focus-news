@extends('layouts.admin')
@section('title', 'Videos')

@section('content')
<div class="flex justify-between items-center mb-4">
    <form method="GET" class="flex gap-2">
        <select name="status" class="border rounded px-3 py-1.5 text-sm">
            <option value="">All statuses</option>
            @foreach(['draft','published','archived'] as $status)
                <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
            @endforeach
        </select>
        <button class="bg-gray-800 text-white text-sm px-3 py-1.5 rounded">Filter</button>
    </form>
    <a href="{{ route('admin.videos.create') }}" class="bg-brand text-white text-sm px-4 py-2 rounded">+ Add Video</a>
</div>

<div class="bg-white rounded-lg shadow-sm border overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-left text-gray-500 uppercase text-xs">
            <tr>
                <th class="px-4 py-3">Title</th>
                <th class="px-4 py-3">Provider</th>
                <th class="px-4 py-3">Category</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3">Views</th>
                <th class="px-4 py-3 text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y">
            @forelse($videos as $video)
                <tr>
                    <td class="px-4 py-3 font-medium max-w-xs truncate">{{ $video->title }}</td>
                    <td class="px-4 py-3 capitalize">{{ $video->provider }}</td>
                    <td class="px-4 py-3">{{ $video->category?->name ?? '—' }}</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-0.5 rounded text-xs {{ $video->status === 'published' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                            {{ ucfirst($video->status) }}
                        </span>
                    </td>
                    <td class="px-4 py-3">{{ number_format($video->views_count) }}</td>
                    <td class="px-4 py-3 text-right space-x-2">
                        <a href="{{ route('admin.videos.edit', $video) }}" class="text-blue-600 hover:underline">Edit</a>
                        <form action="{{ route('admin.videos.destroy', $video) }}" method="POST" class="inline" onsubmit="return confirm('Delete this video?')">
                            @csrf @method('DELETE')
                            <button class="text-red-600 hover:underline">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-4 py-6 text-center text-gray-400">No videos yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $videos->links() }}</div>
@endsection
