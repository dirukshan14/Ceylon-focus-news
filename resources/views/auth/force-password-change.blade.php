<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Set Your Password</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-900 min-h-screen flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-sm p-8">
        <h1 class="text-lg font-bold mb-1">Set a new password</h1>
        <p class="text-sm text-gray-500 mb-6">For security, you must set your own password before continuing.</p>

        @if($errors->any())
            <div class="mb-4 rounded bg-red-100 border border-red-300 text-red-800 px-4 py-2 text-sm">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('admin.password.force') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium mb-1">New Password</label>
                <input type="password" name="password" required class="w-full border rounded px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Confirm Password</label>
                <input type="password" name="password_confirmation" required class="w-full border rounded px-3 py-2 text-sm">
            </div>
            <button type="submit" class="w-full bg-slate-900 text-white rounded py-2 text-sm font-medium hover:bg-slate-800">
                Save &amp; Continue
            </button>
        </form>
    </div>
</body>
</html>
