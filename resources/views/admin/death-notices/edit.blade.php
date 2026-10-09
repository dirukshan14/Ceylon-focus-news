@extends('layouts.admin')
@section('title', 'Edit Death Notice')
@section('content')
<form action="{{ route('admin.death-notices.update', $notice) }}" method="POST" enctype="multipart/form-data">
    @include('admin.death-notices._form')
</form>
@endsection
