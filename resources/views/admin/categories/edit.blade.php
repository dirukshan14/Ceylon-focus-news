@extends('layouts.admin')
@section('title', 'Edit Category')

@section('content')
<form action="{{ route('admin.categories.update', $category) }}" method="POST" enctype="multipart/form-data">
    @include('admin.categories._form')
</form>
@endsection
