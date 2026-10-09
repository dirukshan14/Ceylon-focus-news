@extends('layouts.admin')
@section('title', 'Social Media Links')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 bg-white rounded-lg border overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left text-gray-500 uppercase text-xs">
                <tr><th class="px-4 py-3">Platform</th><th class="px-4 py-3">URL</th><th class="px-4 py-3">Active</th><th class="px-4 py-3 text-right">Actions</th></tr>
            </thead>
            <tbody class="divide-y">
                @forelse($links as $link)
                    <tr>
                        <form action="{{ route('admin.social-links.update', $link) }}" method="POST">
                            @csrf @method('PUT')
                            <td class="px-4 py-3">
                                <select name="platform" class="border rounded px-2 py-1 text-sm">
                                    @foreach(['facebook','youtube','instagram','tiktok','twitter_x','telegram','whatsapp','other'] as $platform)
                                        <option value="{{ $platform }}" @selected($link->platform === $platform)>{{ ucfirst(str_replace('_',' ',$platform)) }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="px-4 py-3"><input type="url" name="url" value="{{ $link->url }}" class="border rounded px-2 py-1 text-sm w-full"></td>
                            <td class="px-4 py-3"><input type="checkbox" name="is_active" value="1" @checked($link->is_active)></td>
                            <td class="px-4 py-3 text-right space-x-2">
                                <button class="text-blue-600 hover:underline">Save</button>
                        </form>
                                <form action="{{ route('admin.social-links.destroy', $link) }}" method="POST" class="inline" onsubmit="return confirm('Remove this link?')">
                                    @csrf @method('DELETE')
                                    <button class="text-red-600 hover:underline">Remove</button>
                                </form>
                            </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-6 text-center text-gray-400">No social links yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="bg-white rounded-lg border p-4 h-fit">
        <h3 class="font-semibold text-sm mb-3">Add Social Link</h3>
        <form action="{{ route('admin.social-links.store') }}" method="POST" class="space-y-3">
            @csrf
            <div>
                <label class="block text-sm font-medium mb-1">Platform</label>
                <select name="platform" class="w-full border rounded px-3 py-2 text-sm">
                    @foreach(['facebook','youtube','instagram','tiktok','twitter_x','telegram','whatsapp','other'] as $platform)
                        <option value="{{ $platform }}">{{ ucfirst(str_replace('_',' ',$platform)) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">URL</label>
                <input type="url" name="url" required class="w-full border rounded px-3 py-2 text-sm" placeholder="https://facebook.com/yourpage">
            </div>
            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="is_active" value="1" checked> Active
            </label>
            <button type="submit" class="w-full bg-brand text-white rounded py-2 text-sm font-medium">Add Link</button>
        </form>
    </div>
</div>
@endsection
