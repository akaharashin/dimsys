<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DIMSYS — Login</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 min-h-screen flex items-center justify-center">

    <div class="w-full max-w-md">

        {{-- Logo & Title --}}
        <div class="text-center mb-8">
            <img src="{{ asset('dimsumin.png') }}" alt="Logo"
                class="h-24 w-24 object-contain rounded-full mx-auto mb-3">
            <h1 class="text-4xl font-bold" style="color:#A51616">DIMSYS</h1>
            <p class="text-gray-500 text-sm mt-1">Dimsum In Management System</p>
        </div>

        {{-- Card --}}
        <div class="bg-white rounded-2xl shadow-lg px-8 py-8">

            <h2 class="text-xl font-semibold text-gray-700 mb-6">Masuk ke Akun</h2>

            {{-- Error --}}
            @if($errors->any())
                <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-600 rounded-lg text-sm">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-600 mb-1">Username</label>
                    <input type="text" name="username" value="{{ old('username') }}" required autofocus
                        autocomplete="username"
                        placeholder="Masukkan username Anda"
                        class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-300 focus:border-transparent transition">
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-600 mb-1">Password</label>
                    <input type="password" name="password" required placeholder="••••••••"
                        class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-300 focus:border-transparent transition">
                </div>

                <div class="flex items-center justify-between mb-6">
                    <label class="flex items-center gap-2 text-sm text-gray-500 cursor-pointer">
                        <input type="checkbox" name="remember"
                            class="rounded border-gray-300 text-red-700 focus:ring-red-300">
                        Ingat saya
                    </label>
                </div>

                <button type="submit" class="w-full text-white font-medium py-2.5 rounded-lg text-sm transition"
                    style="background-color:#A51616" onmouseover="this.style.backgroundColor='#8a1212'"
                    onmouseout="this.style.backgroundColor='#A51616'">
                    Masuk
                </button>
            </form>
        </div>

        <p class="text-center text-xs text-gray-400 mt-6">
            &copy; {{ date('Y') }} Dimsum In Management System · All rights reserved
        </p>

    </div>

</body>

</html>