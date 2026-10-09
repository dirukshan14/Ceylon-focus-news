@extends('layouts.admin')
@section('title', 'News Articles')

@section('content')
<div class="flex justify-between items-center mb-4">
    <form method="GET" class="flex gap-2">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Search title..."
               class="border rounded px-3 py-1.5 text-sm">
        <select name="status" class="border rounded px-3 py-1.5 text-sm">
            <option value="">All statuses</option>
            @foreach(['draft','pending_review','scheduled','published','archived'] as $status)
                <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst(str_replace('_',' ',$status)) }}</option>
            @endforeach
        </select>
        <select name="category_id" class="border rounded px-3 py-1.5 text-sm">
            <option value="">All categories</option>
            @foreach($categories as $cat)
                <option value="{{ $cat->id }}" @selected((int) request('category_id') === $cat->id)>{{ $cat->name }}</option>
            @endforeach
        </select>
        <button class="bg-gray-800 text-white text-sm px-3 py-1.5 rounded">Filter</button>
    </form>

    <a href="{{ route('admin.articles.create') }}" class="bg-brand text-white text-sm px-4 py-2 rounded">+ New Article</a>
</div>

<div class="bg-white rounded-lg shadow-sm border overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-left text-gray-500 uppercase text-xs">
            <tr>
                <th class="px-4 py-3">Title</th>
                <th class="px-4 py-3">Category</th>
                <th class="px-4 py-3">Author</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3">Views</th>
                <th class="px-4 py-3">Published</th>
                <th class="px-4 py-3 text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y">
            @forelse($articles as $article)
                <tr>
                    <td class="px-4 py-3 font-medium max-w-xs truncate">{{ $article->title }}</td>
                    <td class="px-4 py-3">{{ $article->category?->name }}</td>
                    <td class="px-4 py-3">{{ $article->author?->name }}</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-0.5 rounded text-xs
                            {{ $article->status === 'published' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                            {{ ucfirst(str_replace('_',' ', $article->status)) }}
                        </span>
                    </td>
                    <td class="px-4 py-3">{{ number_format($article->views_count) }}</td>
                    <td class="px-4 py-3">{{ $article->published_at?->format('M d, Y') ?? '—' }}</td>
                    <td class="px-4 py-3 text-right space-x-2">
                        <a href="{{ route('admin.articles.edit', $article) }}" class="text-blue-600 hover:underline">Edit</a>
                        <form action="{{ route('admin.articles.destroy', $article) }}" method="POST" class="inline"
                              onsubmit="return confirm('Delete this article?');">
                            @csrf @method('DELETE')
                            <button class="text-red-600 hover:underline">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-4 py-6 text-center text-gray-400">No articles found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $articles->links() }}</div>
@endsection
