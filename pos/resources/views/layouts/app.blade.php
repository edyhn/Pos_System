<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'POS') - {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="bg-gray-50 min-h-screen flex" x-data="{ sidebarOpen: false }">
    @auth
        @php
            $user = auth()->user();
            $store = $user->store;
        @endphp

        {{-- Mobile overlay --}}
        <div x-show="sidebarOpen" x-cloak class="fixed inset-0 z-30 bg-black/50 lg:hidden" @@click="sidebarOpen = false"></div>

        {{-- Sidebar --}}
        {{-- Mobile sidebar --}}
        <aside x-show="sidebarOpen" x-cloak
               x-transition:enter="transform transition-transform duration-300"
               x-transition:enter-start="-translate-x-full"
               x-transition:enter-end="translate-x-0"
               x-transition:leave="transform transition-transform duration-300"
               x-transition:leave-start="translate-x-0"
               x-transition:leave-end="-translate-x-full"
               class="fixed inset-y-0 left-0 z-40 w-64 bg-white border-r border-gray-200 flex flex-col h-screen lg:hidden">
            <div class="p-4 border-b border-gray-200 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold text-gray-800">{{ $store?->name ?? 'POS' }}</h2>
                    <p class="text-sm text-gray-500">{{ $store ? 'Cabang: ' . $store->name : 'Multi Cabang' }}</p>
                </div>
                <button @@click="sidebarOpen = false" class="lg:hidden p-1 text-gray-500 hover:text-gray-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <nav class="flex-1 overflow-y-auto p-3 space-y-1">
                <p class="text-xs font-semibold text-gray-400 uppercase px-3 mt-2 mb-2">Menu</p>

                <a href="{{ route('dashboard') }}" @@click="sidebarOpen = false" class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition {{ request()->routeIs('dashboard') ? 'bg-blue-50 text-blue-700' : '' }}">
                    <span>🏠</span>
                    <span class="text-sm">Dashboard</span>
                </a>

                @if($user->isOwner())
                    <div class="mt-4">
                        <p class="text-xs font-semibold text-gray-400 uppercase px-3 mb-2">Master Data</p>
                        <a href="{{ route('products.index') }}" @@click="sidebarOpen = false" class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition">📦 Produk</a>
                        <a href="{{ route('categories.index') }}" @@click="sidebarOpen = false" class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition">📂 Kategori</a>
                        <a href="{{ route('vendors.index') }}" @@click="sidebarOpen = false" class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition">🏢 Vendor</a>
                    </div>

                    <div class="mt-4">
                        <p class="text-xs font-semibold text-gray-400 uppercase px-3 mb-2">Inventory</p>
                        <a href="{{ route('stock.index') }}" @@click="sidebarOpen = false" class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition">📊 Stok Produk</a>
                        <a href="{{ route('stock-opname.index') }}" @@click="sidebarOpen = false" class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition">📋 Stock Opname</a>
                        <a href="{{ route('stock-movement.in') }}" @@click="sidebarOpen = false" class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition">📥 Barang Masuk</a>
                        <a href="{{ route('stock-movement.out') }}" @@click="sidebarOpen = false" class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition">📤 Barang Keluar</a>
                    </div>

                    <div class="mt-4">
                        <p class="text-xs font-semibold text-gray-400 uppercase px-3 mb-2">Pembelian</p>
                        <a href="{{ route('purchase-orders.index') }}" @@click="sidebarOpen = false" class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition">📋 Purchase Order</a>
                    </div>

                    <div class="mt-4">
                        <p class="text-xs font-semibold text-gray-400 uppercase px-3 mb-2">Laporan</p>
                        <a href="{{ route('reports.sales') }}" @@click="sidebarOpen = false" class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition">📈 Penjualan</a>
                        <a href="{{ route('reports.tax') }}" @@click="sidebarOpen = false" class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition">💰 Pajak</a>
                    </div>

                    <div class="mt-4">
                        <p class="text-xs font-semibold text-gray-400 uppercase px-3 mb-2">Forecast & Analisis</p>
                        <a href="{{ route('forecast.sales') }}" @@click="sidebarOpen = false" class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition">📈 Forecast Penjualan</a>
                        <a href="{{ route('forecast.stock') }}" @@click="sidebarOpen = false" class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition">📊 Forecast Stok</a>
                    </div>

                    <div class="mt-4">
                        <p class="text-xs font-semibold text-gray-400 uppercase px-3 mb-2">Pengaturan</p>
                        <a href="{{ route('users.index') }}" @@click="sidebarOpen = false" class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition">👥 Pengguna</a>
                        <a href="{{ route('approvals.receipt') }}" @@click="sidebarOpen = false" class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition">✅ Approval Cetak Ulang</a>
                        <a href="{{ route('approvals.refund') }}" @@click="sidebarOpen = false" class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition">✅ Approval Refund</a>
                        <a href="{{ route('settings.store') }}" @@click="sidebarOpen = false" class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition">⚙️ Pengaturan</a>
                        <a href="{{ route('activity-logs.index') }}" @@click="sidebarOpen = false" class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition">📋 Riwayat Aktivitas</a>
                    </div>
                @endif

                <div class="mt-4">
                    <p class="text-xs font-semibold text-gray-400 uppercase px-3 mb-2">Transaksi</p>
                    <a href="{{ route('cashier') }}" @@click="sidebarOpen = false" class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition">🛒 Kasir</a>
                    <a href="{{ route('transactions.index') }}" @@click="sidebarOpen = false" class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition">📄 Riwayat Transaksi</a>
                    <a href="{{ route('subscriptions.check') }}" @@click="sidebarOpen = false" class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition">🔍 Cek Langganan</a>
                </div>

                @if($user->isCashier())
                    <div class="mt-4">
                        <p class="text-xs font-semibold text-gray-400 uppercase px-3 mb-2">Request</p>
                        <a href="{{ route('requests.receipt') }}" @@click="sidebarOpen = false" class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition">🖨️ Cetak Ulang</a>
                        <a href="{{ route('requests.refund') }}" @@click="sidebarOpen = false" class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition">↩️ Refund</a>
                    </div>
                @endif
            </nav>

            <div class="p-4 border-t border-gray-200">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center text-blue-600 font-bold text-sm overflow-hidden">
                        @if($user->photo)
                            <img src="{{ Storage::url($user->photo) }}" class="w-full h-full object-cover">
                        @else
                            {{ substr($user->name, 0, 1) }}
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-800 truncate">{{ $user->name }}</p>
                        <p class="text-xs text-gray-500">{{ $user->user_id }} ({{ $user->role }})</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="mt-2">
                    @csrf
                    <button type="submit" class="w-full text-left text-sm text-red-600 hover:text-red-700 px-3 py-1.5 rounded-lg hover:bg-red-50 transition">
                        Logout
                    </button>
                </form>
            </div>
        </aside>

        {{-- Desktop sidebar --}}
        <aside class="hidden lg:flex w-64 bg-white border-r border-gray-200 h-screen flex-col">
            <div class="p-4 border-b border-gray-200">
                <h2 class="text-lg font-bold text-gray-800">{{ $store?->name ?? 'POS' }}</h2>
                <p class="text-sm text-gray-500">{{ $store ? 'Cabang: ' . $store->name : 'Multi Cabang' }}</p>
            </div>

            <nav class="flex-1 overflow-y-auto p-3 space-y-1">
                <p class="text-xs font-semibold text-gray-400 uppercase px-3 mt-2 mb-2">Menu</p>

                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition {{ request()->routeIs('dashboard') ? 'bg-blue-50 text-blue-700' : '' }}">
                    <span>🏠</span>
                    <span class="text-sm">Dashboard</span>
                </a>

                @if($user->isOwner())
                    <div class="mt-4">
                        <p class="text-xs font-semibold text-gray-400 uppercase px-3 mb-2">Master Data</p>
                        <a href="{{ route('products.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition">📦 Produk</a>
                        <a href="{{ route('categories.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition">📂 Kategori</a>
                        <a href="{{ route('vendors.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition">🏢 Vendor</a>
                    </div>

                    <div class="mt-4">
                        <p class="text-xs font-semibold text-gray-400 uppercase px-3 mb-2">Inventory</p>
                        <a href="{{ route('stock.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition">📊 Stok Produk</a>
                        <a href="{{ route('stock-opname.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition">📋 Stock Opname</a>
                        <a href="{{ route('stock-movement.in') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition">📥 Barang Masuk</a>
                        <a href="{{ route('stock-movement.out') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition">📤 Barang Keluar</a>
                    </div>

                    <div class="mt-4">
                        <p class="text-xs font-semibold text-gray-400 uppercase px-3 mb-2">Pembelian</p>
                        <a href="{{ route('purchase-orders.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition">📋 Purchase Order</a>
                    </div>

                    <div class="mt-4">
                        <p class="text-xs font-semibold text-gray-400 uppercase px-3 mb-2">Laporan</p>
                        <a href="{{ route('reports.sales') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition">📈 Penjualan</a>
                        <a href="{{ route('reports.tax') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition">💰 Pajak</a>
                    </div>

                    <div class="mt-4">
                        <p class="text-xs font-semibold text-gray-400 uppercase px-3 mb-2">Forecast & Analisis</p>
                        <a href="{{ route('forecast.sales') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition">📈 Forecast Penjualan</a>
                        <a href="{{ route('forecast.stock') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition">📊 Forecast Stok</a>
                    </div>

                    <div class="mt-4">
                        <p class="text-xs font-semibold text-gray-400 uppercase px-3 mb-2">Pengaturan</p>
                        <a href="{{ route('users.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition">👥 Pengguna</a>
                        <a href="{{ route('approvals.receipt') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition">✅ Approval Cetak Ulang</a>
                        <a href="{{ route('approvals.refund') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition">✅ Approval Refund</a>
                        <a href="{{ route('settings.store') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition">⚙️ Pengaturan</a>
                        <a href="{{ route('activity-logs.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition">📋 Riwayat Aktivitas</a>
                    </div>
                @endif

                <div class="mt-4">
                    <p class="text-xs font-semibold text-gray-400 uppercase px-3 mb-2">Transaksi</p>
                    <a href="{{ route('cashier') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition">🛒 Kasir</a>
                    <a href="{{ route('transactions.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition">📄 Riwayat Transaksi</a>
                    <a href="{{ route('subscriptions.check') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition">🔍 Cek Langganan</a>
                </div>

                @if($user->isCashier())
                    <div class="mt-4">
                        <p class="text-xs font-semibold text-gray-400 uppercase px-3 mb-2">Request</p>
                        <a href="{{ route('requests.receipt') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition">🖨️ Cetak Ulang</a>
                        <a href="{{ route('requests.refund') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition">↩️ Refund</a>
                    </div>
                @endif
            </nav>

            <div class="p-4 border-t border-gray-200">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center text-blue-600 font-bold text-sm overflow-hidden">
                        @if($user->photo)
                            <img src="{{ Storage::url($user->photo) }}" class="w-full h-full object-cover">
                        @else
                            {{ substr($user->name, 0, 1) }}
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-800 truncate">{{ $user->name }}</p>
                        <p class="text-xs text-gray-500">{{ $user->user_id }} ({{ $user->role }})</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="mt-2">
                    @csrf
                    <button type="submit" class="w-full text-left text-sm text-red-600 hover:text-red-700 px-3 py-1.5 rounded-lg hover:bg-red-50 transition">
                        Logout
                    </button>
                </form>
            </div>
        </aside>

        <main class="flex-1 overflow-y-auto min-h-screen flex flex-col">
            <div class="bg-white border-b border-gray-200 px-4 lg:px-6 py-2 flex items-center justify-between lg:justify-end gap-4 sticky top-0 z-20">
                <button @@click="sidebarOpen = true" class="lg:hidden p-2 text-gray-600 hover:text-gray-900 rounded-lg hover:bg-gray-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>

                <div class="flex items-center gap-4">
                    @livewire('notification-bell')

                    <div class="hidden lg:flex items-center gap-3">
                        <div class="text-right">
                            <p class="text-sm font-medium text-gray-800">{{ $user->name }}</p>
                            <p class="text-xs text-gray-500">{{ $user->user_id }} ({{ $user->role }})</p>
                        </div>
                        <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center text-blue-600 font-bold text-sm overflow-hidden">
                            @if($user->photo)
                                <img src="{{ Storage::url($user->photo) }}" class="w-full h-full object-cover">
                            @else
                                {{ substr($user->name, 0, 1) }}
                            @endif
                        </div>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-sm text-red-600 hover:text-red-700 px-2 py-1 rounded hover:bg-red-50 transition">Logout</button>
                        </form>
                    </div>

                    <div class="lg:hidden flex items-center gap-2">
                        <div class="w-7 h-7 bg-blue-100 rounded-full flex items-center justify-center text-blue-600 font-bold text-xs overflow-hidden">
                            @if($user->photo)
                                <img src="{{ Storage::url($user->photo) }}" class="w-full h-full object-cover">
                            @else
                                {{ substr($user->name, 0, 1) }}
                            @endif
                        </div>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-xs text-red-600 hover:text-red-700">Logout</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="p-4 lg:p-6 flex-1">
                @yield('content')
            </div>
        </main>
    @endauth

    @livewireScripts
</body>
</html>
