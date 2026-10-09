@extends('layouts.admin')
@section('title', 'Advertisements')

@section('content')
<div class="flex justify-end mb-4">
    <a href="{{ route('admin.advertisements.create') }}" class="bg-brand text-white text-sm px-4 py-2 rounded">+ New Advertisement</a>
</div>

<div class="bg-white rounded-lg shadow-sm border overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-left text-gray-500 uppercase text-xs">
            <tr>
                <th class="px-4 py-3">Name</th>
                <th class="px-4 py-3">Position</th>
                <th class="px-4 py-3">Device</th>
                <th class="px-4 py-3">Impressions</th>
                <th class="px-4 py-3">Clicks</th>
                <th class="px-4 py-3">CTR</th>
                <th class="px-4 py-3">Active</th>
                <th class="px-4 py-3 text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y">
            @forelse($ads as $ad)
                <tr>
                    <td class="px-4 py-3 font-medium">{{ $ad->name }}</td>
                    <td class="px-4 py-3">{{ ucwords(str_replace('_',' ',$ad->position)) }}</td>
                    <td class="px-4 py-3 capitalize">{{ $ad->device_target }}</td>
                    <td class="px-4 py-3">{{ number_format($ad->impressions_count) }}</td>
                    <td class="px-4 py-3">{{ number_format($ad->clicks_count) }}</td>
                    <td class="px-4 py-3">{{ $ad->ctr() }}%</td>
                    <td class="px-4 py-3">
                        <form action="{{ route('admin.advertisements.toggle', $ad) }}" method="POST">
                            @csrf
                            <button class="px-2 py-0.5 rounded text-xs {{ $ad->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                {{ $ad->is_active ? 'Active' : 'Inactive' }}
                            </button>
                        </form>
                    </td>
                    <td class="px-4 py-3 text-right space-x-2">
                        <a href="{{ route('admin.advertisements.edit', $ad) }}" class="text-blue-600 hover:underline">Edit</a>
                        <form action="{{ route('admin.advertisements.destroy', $ad) }}" method="POST" class="inline" onsubmit="return confirm('Delete this ad?')">
                            @csrf @method('DELETE')
                            <button class="text-red-600 hover:underline">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="px-4 py-6 text-center text-gray-400">No advertisements yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $ads->links() }}</div>
@endsection
