@extends('layouts.admin')
@section('title', 'Edit News')

@section('content')
<h1>Edit News Article</h1>

<form method="POST" action="{{ route('admin.news.update', $news) }}" enctype="multipart/form-data" class="admin-form">
    @csrf
    @method('PUT')
    @include('admin.news._form')
    <button type="submit" class="btn btn-primary">Save Changes</button>
</form>
@endsection
