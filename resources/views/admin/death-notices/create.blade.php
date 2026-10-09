@extends('layouts.admin')
@section('title', 'Add Death Notice')
@section('content')
<form action="{{ route('admin.death-notices.store') }}" method="POST" enctype="multipart/form-data">
    @include('admin.death-notices._form')
</form>
@endsection
