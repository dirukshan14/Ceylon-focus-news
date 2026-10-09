@extends('layouts.admin')
@section('title', 'Edit Advertisement')
@section('content')
<form action="{{ route('admin.advertisements.update', $ad) }}" method="POST" enctype="multipart/form-data">
    @include('admin.advertisements._form')
</form>
@endsection
