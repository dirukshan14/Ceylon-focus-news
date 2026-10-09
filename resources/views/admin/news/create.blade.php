@extends('layouts.admin')
@section('title', 'Add News')

@section('content')
<h1>Add News Article</h1>

<form method="POST" action="{{ route('admin.news.store') }}" enctype="multipart/form-data" class="admin-form">
    @csrf
    @include('admin.news._form')
    <button type="submit" class="btn btn-primary">Publish</button>
</form>
@endsection
