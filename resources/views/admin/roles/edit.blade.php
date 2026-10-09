@extends('layouts.admin')
@section('title', 'Edit Role')
@section('content')
<form action="{{ route('admin.roles.update', $role) }}" method="POST">
    @include('admin.roles._form')
</form>
@endsection
