<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - {{ \App\Models\Setting::get('site_name', config('app.name')) }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>.site-logo { -webkit-user-drag: none; user-select: none; }</style>
</head>
<body class="bg-slate-900 min-h-screen flex items-center justify-center">
    @php
        $loginLogo = \App\Models\Setting::get('logo')
            ? asset('storage/'.\App\Models\Setting::get('logo'))
            : asset('images/logo-full.png');
    @endphp
    <div class="bg-white rounded-lg shadow-lg w-full max-w-sm p-8">
        <img src="{{ $loginLogo }}" alt="{{ \App\Models\Setting::get('site_name', config('app.name')) }}"
             draggable="false" class="site-logo h-16 w-auto object-contain mx-auto mb-3">
        <p class="text-center text-sm text-gray-500 mb-6">Admin Panel Login</p>

        @if($errors->any())
            <div class="mb-4 rounded bg-red-100 border border-red-300 text-red-800 px-4 py-2 text-sm">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('admin.login') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium mb-1">Email</label>
                <input type="email" name="email" required autofocus value="{{ old('email') }}"
                       class="w-full border rounded px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Password</label>
                <input type="password" name="password" required class="w-full border rounded px-3 py-2 text-sm">
            </div>
            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="remember"> Remember me
            </label>
            <button type="submit" class="w-full bg-slate-900 text-white rounded py-2 text-sm font-medium hover:bg-slate-800">
                Sign in
            </button>
        </form>
    </div>
</body>
</html>
