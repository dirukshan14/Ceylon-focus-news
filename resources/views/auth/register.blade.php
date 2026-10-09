@extends('layouts.app')
@section('title', 'Register')

@section('content')
<div class="max-w-sm mx-auto px-4 py-16">
    <h1 class="text-xl font-bold mb-6 text-center">Create an Account</h1>

    @if($errors->any())
        <div class="mb-4 rounded bg-red-100 border border-red-300 text-red-800 px-4 py-2 text-sm">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}" class="space-y-4 bg-white border rounded-lg p-6">
        @csrf
        <div>
            <label class="block text-sm font-medium mb-1">Name</label>
            <input type="text" name="name" required value="{{ old('name') }}" class="w-full border rounded px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Email</label>
            <input type="email" name="email" required value="{{ old('email') }}" class="w-full border rounded px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Password</label>
            <input type="password" name="password" required class="w-full border rounded px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Confirm Password</label>
            <input type="password" name="password_confirmation" required class="w-full border rounded px-3 py-2 text-sm">
        </div>
        <button class="w-full bg-brand text-white rounded py-2 text-sm font-medium">Register</button>
        <p class="text-center text-sm text-gray-500">Already have an account? <a href="{{ route('login') }}" class="text-brand hover:underline">Login</a></p>
    </form>
</div>
@endsection
