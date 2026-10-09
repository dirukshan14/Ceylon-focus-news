@extends('layouts.admin')
@section('title', 'News')

@section('content')
<div class="page-header">
    <h1>News Articles</h1>
    <a href="{{ route('admin.news.create') }}" class="btn btn-primary">+ Add News</a>
</div>

<form method="GET" class="filter-bar">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search title...">
    <select name="category_id">
        <option value="">All categories</option>
        @foreach($categories as $cat)
            <option value="{{ $cat->id }}" @selected(request('category_id') == $cat->id)>{{ $cat->name }}</option>
        @endforeach
    </select>
    <button type="submit" class="btn">Filter</button>
</form>

<table class="admin-table">
    <thead>
        <tr><th>Title</th><th>Category</th><th>Status</th><th>Views</th><th>Published</th><th>Actions</th></tr>
    </thead>
    <tbody>
        @foreach($news as $item)
            <tr>
                <td>{{ $item->title }}</td>
                <td>{{ $item->category->name }}</td>
                <td>{{ ucfirst($item->status) }}</td>
                <td>{{ $item->views }}</td>
                <td>{{ $item->published_at?->format('M d, Y') }}</td>
                <td>
                    <a href="{{ route('news.show', $item->slug) }}" target="_blank">View</a>
                    <a href="{{ route('admin.news.edit', $item) }}">Edit</a>
                    <form method="POST" action="{{ route('admin.news.destroy', $item) }}" style="display:inline"
                          onsubmit="return confirm('Delete this article?')">
                        @csrf @method('DELETE')
                        <button type="submit">Delete</button>
                    </form>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

{{ $news->links() }}
@endsection
