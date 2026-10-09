@extends('layouts.admin')
@section('title', 'Edit Article')

@section('content')
<form action="{{ route('admin.articles.update', $article) }}" method="POST" enctype="multipart/form-data">
    @include('admin.articles._form')
</form>
@endsection
