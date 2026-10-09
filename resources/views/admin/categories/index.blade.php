@extends('layouts.admin')
@section('title', 'Categories')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 bg-white rounded-lg border overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left text-gray-500 uppercase text-xs">
                <tr><th class="px-4 py-3">Name</th><th class="px-4 py-3">Order</th><th class="px-4 py-3">Active</th><th class="px-4 py-3 text-right">Actions</th></tr>
            </thead>
            <tbody class="divide-y">
                @foreach($categories as $category)
                    <tr class="font-medium">
                        <td class="px-4 py-3">{{ $category->name }}</td>
                        <td class="px-4 py-3">{{ $category->order }}</td>
                        <td class="px-4 py-3">{{ $category->is_active ? 'Yes' : 'No' }}</td>
                        <td class="px-4 py-3 text-right space-x-2">
                            <a href="{{ route('admin.categories.edit', $category) }}" class="text-blue-600 hover:underline">Edit</a>
                            <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" class="inline" onsubmit="return confirm('Delete this category?')">
                                @csrf @method('DELETE')
                                <button class="text-red-600 hover:underline">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @foreach($category->children as $child)
                        <tr class="text-gray-600">
                            <td class="px-4 py-3 pl-8">— {{ $child->name }}</td>
                            <td class="px-4 py-3">{{ $child->order }}</td>
                            <td class="px-4 py-3">{{ $child->is_active ? 'Yes' : 'No' }}</td>
                            <td class="px-4 py-3 text-right space-x-2">
                                <a href="{{ route('admin.categories.edit', $child) }}" class="text-blue-600 hover:underline">Edit</a>
                                <form action="{{ route('admin.categories.destroy', $child) }}" method="POST" class="inline" onsubmit="return confirm('Delete this category?')">
                                    @csrf @method('DELETE')
                                    <button class="text-red-600 hover:underline">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="bg-white rounded-lg border p-4 h-fit">
        <h3 class="font-semibold text-sm mb-3">Add Category</h3>
        <a href="{{ route('admin.categories.create') }}" class="block text-center bg-brand text-white rounded py-2 text-sm">+ New Category</a>
    </div>
</div>
@endsection
