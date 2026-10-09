@extends('layouts.admin')
@section('title', 'New Article')

@section('content')
<form action="{{ route('admin.articles.store') }}" method="POST" enctype="multipart/form-data">
    @include('admin.articles._form')
</form>
@endsection
