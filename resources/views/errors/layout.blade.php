<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $code }} - {{ $message }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen flex items-center justify-center bg-gray-50">
    <div class="text-center px-4">
        <p class="text-6xl font-extrabold text-brand">{{ $code }}</p>
        <p class="text-xl font-semibold mt-2">{{ $message }}</p>
        <p class="text-gray-500 mt-1">{{ $description }}</p>
        <a href="{{ route('home') }}" class="inline-block mt-6 bg-slate-900 text-white px-5 py-2 rounded text-sm">Back to Homepage</a>
    </div>
</body>
</html>
