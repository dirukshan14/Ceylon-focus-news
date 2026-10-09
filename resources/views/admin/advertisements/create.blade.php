@extends('layouts.admin')
@section('title', 'New Advertisement')
@section('content')
<form action="{{ route('admin.advertisements.store') }}" method="POST" enctype="multipart/form-data">
    @include('admin.advertisements._form')
</form>
@endsection
