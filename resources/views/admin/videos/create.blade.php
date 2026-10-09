@extends('layouts.admin')
@section('title', 'Add Video')
@section('content')
<form action="{{ route('admin.videos.store') }}" method="POST" enctype="multipart/form-data">
    @include('admin.videos._form')
</form>
@endsection
