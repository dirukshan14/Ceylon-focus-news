@extends('layouts.admin')
@section('title', 'Edit Video')
@section('content')
<form action="{{ route('admin.videos.update', $video) }}" method="POST" enctype="multipart/form-data">
    @include('admin.videos._form')
</form>
@endsection
