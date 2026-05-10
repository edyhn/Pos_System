<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>POS System - {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gradient-to-br from-blue-50 to-white min-h-screen flex flex-col">
    @php $now = now(); @endphp
    <header class="w-full px-6 py-4 flex items-center justify-end">
        @if (Route::has('login'))
            @auth
                <a href="{{ url('/dashboard') }}" class="px-5 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm font-medium">Dashboard</a>
            @else
                <a href="{{ route('login') }}" class="px-5 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm font-medium">Masuk / Login</a>
            @endauth
        @endif
    </header>

    <main class="flex-1 flex items-center justify-center px-4">
        <div class="text-center max-w-lg">
            <div class="w-20 h-20 bg-blue-100 rounded-2xl flex items-center justify-center mx-auto mb-6">
                <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/>
                </svg>
            </div>
            <h1 class="text-4xl font-bold text-gray-900 mb-2">POS System</h1>
            <p class="text-lg text-gray-500 mb-8">Sistem Kasir & Manajemen Toko</p>

            @guest
                <a href="{{ route('login') }}" class="inline-block px-8 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium shadow-lg shadow-blue-200">
                    Mulai Sekarang
                </a>
            @endguest
        </div>
    </main>

    <footer class="text-center py-4 text-sm text-gray-400">
        &copy; {{ $now->year }} POS System. All rights reserved.
    </footer>
</body>
</html>
