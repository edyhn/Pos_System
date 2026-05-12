<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'POS') - {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>[x-cloak] { display: none !important; }</style>
    <script>
        if (localStorage.getItem('darkMode') === 'true') {
            document.documentElement.classList.add('dark');
        }
        function toggleDark() {
            const isDark = document.documentElement.classList.toggle('dark');
            localStorage.setItem('darkMode', isDark);
            const icons = document.querySelectorAll('[data-dark-icon]');
            if (icons.length >= 2) {
                icons[0].classList.toggle('hidden', !isDark);
                icons[1].classList.toggle('hidden', isDark);
            }
        }
        document.addEventListener('DOMContentLoaded', () => {
            const isDark = document.documentElement.classList.contains('dark');
            const icons = document.querySelectorAll('[data-dark-icon]');
            if (icons.length >= 2) {
                icons[0].classList.toggle('hidden', !isDark);
                icons[1].classList.toggle('hidden', isDark);
            }
        });
    </script>
</head>
<body class="bg-gray-50 dark:bg-gray-950 min-h-screen" x-data="{ sidebarOpen: true }">
    @auth
        @php
            $user = auth()->user();
            $store = $user->store;
            $isOwner = $user->isOwner();
            $route = fn($p) => request()->routeIs($p);

            $navUmum = [
                ['route' => 'dashboard', 'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>', 'label' => 'Dashboard'],
                ['route' => 'cashier', 'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>', 'label' => 'Kasir'],
                ['route' => 'transactions.*', 'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>', 'label' => 'Riwayat Transaksi'],
            ];
            $navMaster = [
                ['route' => 'products.*', 'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>', 'label' => 'Produk'],
                ['route' => 'categories.*', 'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>', 'label' => 'Kategori'],
                ['route' => 'vendors.*', 'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>', 'label' => 'Vendor'],
                ['route' => 'users.*', 'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/></svg>', 'label' => 'Pengguna'],
                ['route' => 'discounts.*', 'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4m2 8l2-4m8 4l-2-4m-6 0a2 2 0 01-2-2V8a2 2 0 012-2h4l4-2v10m-8 0h8"/></svg>', 'label' => 'Diskon & Promo'],
            ];
            $navInventory = [
                ['route' => 'stock.index', 'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>', 'label' => 'Stok Produk'],
                ['route' => 'stock-opname.*', 'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>', 'label' => 'Stock Opname'],
                ['route' => 'stock-movement.in', 'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3"/></svg>', 'label' => 'Barang Masuk'],
                ['route' => 'stock-movement.out', 'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 14l5-5 5 5M12 4v12"/></svg>', 'label' => 'Barang Keluar'],
                ['route' => 'purchase-orders.*', 'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>', 'label' => 'Purchase Order'],
            ];
            $navReports = [
                ['route' => 'reports.sales', 'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>', 'label' => 'Penjualan'],
                ['route' => 'reports.tax', 'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>', 'label' => 'Pajak'],
            ];
            $navForecast = [
                ['route' => 'forecast.sales', 'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>', 'label' => 'Forecast Penjualan'],
                ['route' => 'forecast.stock', 'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>', 'label' => 'Forecast Stok'],
            ];
            $navApprovals = [
                ['route' => 'approvals.refund', 'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-8 0v1m0 0a4 4 0 00-4 4h12a4 4 0 00-4-4z"/></svg>', 'label' => 'Refund'],
                ['route' => 'approvals.receipt', 'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>', 'label' => 'Cetak Ulang'],
            ];
            $navSettings = [
                ['route' => 'settings.store', 'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>', 'label' => 'Pengaturan Toko'],
                ['route' => 'activity-logs.index', 'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>', 'label' => 'Riwayat Aktivitas'],
                ['route' => 'subscriptions.check', 'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>', 'label' => 'Cek Langganan'],
            ];
            $navCashierRequests = [
                ['route' => 'requests.receipt', 'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>', 'label' => 'Cetak Ulang'],
                ['route' => 'requests.refund', 'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-8 0v1m0 0a4 4 0 00-4 4h12a4 4 0 00-4-4z"/></svg>', 'label' => 'Refund'],
            ];
        @endphp

        {{-- ===== SIDEBAR ===== --}}
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
               class="fixed top-0 left-0 z-40 w-64 bg-slate-900 h-screen flex flex-col transition-transform duration-300 ease-in-out shadow-2xl">

            {{-- Logo & close button --}}
            <div class="h-16 flex items-center gap-3 px-4 border-b border-white/10 shrink-0">
                <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center shadow-lg shadow-blue-500/20">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                </div>
                <div class="flex-1 min-w-0">
                    <h2 class="text-sm font-bold text-white leading-tight truncate">{{ $store?->name ?? 'POS' }}</h2>
                    <p class="text-[10px] text-white/50 truncate">{{ optional($store)->address ?? 'Management System' }}</p>
                </div>
                <button @@click="sidebarOpen = false" class="lg:hidden p-1.5 text-white/50 hover:text-white hover:bg-white/5 rounded-lg transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Navigation --}}
            <nav class="flex-1 sidebar-scroll px-3 py-4 space-y-0.5">
                <p class="text-[10px] font-semibold text-white/40 uppercase tracking-wider px-3 mb-1.5">UMUM</p>
                @foreach($navUmum as $item)
                    <x-nav-item :active="$route($item['route'])"
                                href="{{ route(str_replace('.*', '.index', $item['route'])) }}"
                                :icon="$item['icon']">
                        {{ $item['label'] }}
                    </x-nav-item>
                @endforeach

                @if($isOwner)
                <p class="text-[10px] font-semibold text-white/40 uppercase tracking-wider px-3 mb-1.5 mt-5">MASTER DATA</p>
                @foreach($navMaster as $item)
                    <x-nav-item :active="$route($item['route'])"
                                href="{{ route(str_replace('.*', '.index', $item['route'])) }}"
                                :icon="$item['icon']">
                        {{ $item['label'] }}
                    </x-nav-item>
                @endforeach

                <p class="text-[10px] font-semibold text-white/40 uppercase tracking-wider px-3 mb-1.5 mt-5">INVENTORY</p>
                @foreach($navInventory as $item)
                    <x-nav-item :active="$route($item['route'])"
                                href="{{ route(str_replace('.*', '.index', $item['route'])) }}"
                                :icon="$item['icon']">
                        {{ $item['label'] }}
                    </x-nav-item>
                @endforeach

                <p class="text-[10px] font-semibold text-white/40 uppercase tracking-wider px-3 mb-1.5 mt-5">LAPORAN</p>
                @foreach($navReports as $item)
                    <x-nav-item :active="$route($item['route'])"
                                href="{{ route(str_replace('.*', '.index', $item['route'])) }}"
                                :icon="$item['icon']">
                        {{ $item['label'] }}
                    </x-nav-item>
                @endforeach

                <p class="text-[10px] font-semibold text-white/40 uppercase tracking-wider px-3 mb-1.5 mt-5">FORECAST & ANALISIS</p>
                @foreach($navForecast as $item)
                    <x-nav-item :active="$route($item['route'])"
                                href="{{ route(str_replace('.*', '.index', $item['route'])) }}"
                                :icon="$item['icon']">
                        {{ $item['label'] }}
                    </x-nav-item>
                @endforeach

                <p class="text-[10px] font-semibold text-white/40 uppercase tracking-wider px-3 mb-1.5 mt-5">PERSETUJUAN</p>
                @foreach($navApprovals as $item)
                    <x-nav-item :active="$route($item['route'])"
                                href="{{ route(str_replace('.*', '.index', $item['route'])) }}"
                                :icon="$item['icon']">
                        {{ $item['label'] }}
                    </x-nav-item>
                @endforeach

                <p class="text-[10px] font-semibold text-white/40 uppercase tracking-wider px-3 mb-1.5 mt-5">PENGATURAN</p>
                @foreach($navSettings as $item)
                    <x-nav-item :active="$route($item['route'])"
                                href="{{ route(str_replace('.*', '.index', $item['route'])) }}"
                                :icon="$item['icon']">
                        {{ $item['label'] }}
                    </x-nav-item>
                @endforeach
                @endif

                @if($user->isCashier())
                <p class="text-[10px] font-semibold text-white/40 uppercase tracking-wider px-3 mb-1.5 mt-5">REQUEST</p>
                @foreach($navCashierRequests as $item)
                    <x-nav-item :active="$route($item['route'])"
                                href="{{ route(str_replace('.*', '.index', $item['route'])) }}"
                                :icon="$item['icon']">
                        {{ $item['label'] }}
                    </x-nav-item>
                @endforeach
                @endif
            </nav>

            {{-- User footer --}}
            <div class="shrink-0 border-t border-white/10 p-4">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center text-white font-bold text-sm overflow-hidden ring-2 ring-white/20 shadow-lg">
                        @if($user->photo)
                            <img src="{{ Storage::url($user->photo) }}" class="w-full h-full object-cover">
                        @else
                            {{ substr($user->name, 0, 1) }}
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-white truncate">{{ $user->name }}</p>
                        <p class="text-[11px] text-white/50 truncate">
                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 bg-white/10 rounded text-[10px] font-medium">
                                <span class="w-1 h-1 rounded-full {{ $user->role === 'owner' ? 'bg-amber-400' : 'bg-blue-400' }}"></span>
                                {{ ucfirst($user->role) }}
                            </span>
                        </p>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="mt-2.5">
                    @csrf
                    <button type="submit" class="w-full flex items-center justify-center gap-2 text-sm text-red-400 hover:text-white px-3 py-2 rounded-lg hover:bg-red-500/10 transition group">
                        <svg class="w-4 h-4 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        Logout
                    </button>
                </form>
            </div>
        </aside>

        {{-- Overlay (mobile only) --}}
        <div x-show="sidebarOpen" x-cloak class="fixed inset-0 z-30 bg-black/60 lg:hidden" @@click="sidebarOpen = false"></div>

        {{-- ===== MAIN CONTENT ===== --}}
        <div class="flex-1 flex flex-col min-h-screen transition-all duration-300" :class="sidebarOpen ? 'lg:ml-64' : 'lg:ml-0'">
            {{-- Top bar --}}
            <header class="bg-white border-b border-gray-200 px-4 lg:px-6 h-16 flex items-center justify-between gap-4 sticky top-0 z-20 shrink-0">
                <button @@click="sidebarOpen = !sidebarOpen" class="p-2 text-gray-500 hover:text-gray-700 rounded-lg hover:bg-gray-100 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.75 5.25h16.5m-16.5 6.75h16.5m-16.5 6.75h16.5"/></svg>
                </button>

                <div class="hidden lg:flex items-center gap-2 text-sm text-gray-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    <span>{{ $store?->name ?? 'POS' }}</span>
                    <span class="text-gray-300">/</span>
                    <span class="text-gray-800 font-medium">@yield('title', 'Dashboard')</span>
                </div>

                <div class="flex items-center gap-3 ml-auto">
                    @livewire('notification-bell')

                    <button onclick="toggleDark()" class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition" title="Toggle Dark Mode">
                        <svg data-dark-icon class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></svg>
                        <svg data-dark-icon class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></svg>
                    </button>

                    <div class="hidden lg:flex items-center gap-3 pl-3 border-l border-gray-200 dark:border-gray-700">
                        <div class="text-right">
                            <p class="text-sm font-medium text-gray-800">{{ $user->name }}</p>
                            <p class="text-xs text-gray-400">{{ $user->email }}</p>
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
                            <button type="submit" class="p-1.5 text-gray-400 hover:text-red-500 rounded-lg hover:bg-red-50 transition" title="Logout">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            </button>
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
                            <button type="submit" class="text-xs text-gray-400 hover:text-red-500 p-1" title="Logout">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            {{-- Page content --}}
            <main class="flex-1 p-4 lg:p-6">
                @yield('content')
            </main>
        </div>
    @endauth

    @livewireScripts
</body>
</html>
