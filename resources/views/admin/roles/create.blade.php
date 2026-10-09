@extends('layouts.admin')
@section('title', 'New Role')
@section('content')
<form action="{{ route('admin.roles.store') }}" method="POST">
    @include('admin.roles._form')
</form>
@endsection
