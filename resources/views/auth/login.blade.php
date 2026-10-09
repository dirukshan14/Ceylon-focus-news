@extends('layouts.app')
@section('title', 'Login')

@section('content')
<div class="max-w-sm mx-auto px-4 py-16">
    <h1 class="text-xl font-bold mb-6 text-center">Login</h1>

    @if($errors->any())
        <div class="mb-4 rounded bg-red-100 border border-red-300 text-red-800 px-4 py-2 text-sm">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-4 bg-white border rounded-lg p-6">
        @csrf
        <div>
            <label class="block text-sm font-medium mb-1">Email</label>
            <input type="email" name="email" required class="w-full border rounded px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Password</label>
            <input type="password" name="password" required class="w-full border rounded px-3 py-2 text-sm">
        </div>
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="remember"> Remember me</label>
        <button class="w-full bg-brand text-white rounded py-2 text-sm font-medium">Login</button>
        <p class="text-center text-sm text-gray-500">No account? <a href="{{ route('register') }}" class="text-brand hover:underline">Register</a></p>
    </form>
</div>
@endsection
