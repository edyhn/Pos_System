<div wire:poll.300s>
    @php $user = auth()->user(); @endphp

    @if($user->isOwner())
        {{-- Stat Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 hover:shadow-md transition">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-sm font-medium text-gray-500">Penjualan Hari Ini</span>
                    <div class="w-10 h-10 rounded-lg bg-emerald-50 flex items-center justify-center">
                        <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
                <p class="text-2xl font-bold text-gray-900">Rp {{ number_format($todaySales, 0, ',', '.') }}</p>
                <p class="text-xs text-gray-400 mt-1">Pendapatan hari ini</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 hover:shadow-md transition">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-sm font-medium text-gray-500">Transaksi Hari Ini</span>
                    <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
                    </div>
                </div>
                <p class="text-2xl font-bold text-gray-900">{{ $todayTransactions }}</p>
                <p class="text-xs text-gray-400 mt-1">Total transaksi</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 hover:shadow-md transition">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-sm font-medium text-gray-500">Produk Aktif</span>
                    <div class="w-10 h-10 rounded-lg bg-purple-50 flex items-center justify-center">
                        <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </div>
                </div>
                <p class="text-2xl font-bold text-gray-900">{{ $totalProducts }}</p>
                <p class="text-xs text-gray-400 mt-1">Produk tersedia</p>
            </div>
            <a href="{{ route('approvals.receipt') }}" class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 hover:shadow-md transition block">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-sm font-medium text-gray-500">Pending Approvals</span>
                    <div class="w-10 h-10 rounded-lg {{ $pendingApprovals > 0 ? 'bg-amber-50' : 'bg-gray-50' }} flex items-center justify-center">
                        <svg class="w-5 h-5 {{ $pendingApprovals > 0 ? 'text-amber-500' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
                <p class="text-2xl font-bold {{ $pendingApprovals > 0 ? 'text-amber-600' : 'text-gray-900' }}">{{ $pendingApprovals }}</p>
                <p class="text-xs {{ $pendingApprovals > 0 ? 'text-amber-500' : 'text-gray-400' }} mt-1">Menunggu persetujuan</p>
            </a>
        </div>

        {{-- Charts --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-sm font-semibold text-gray-800">Penjualan 7 Hari</h2>
                    <span class="text-xs text-gray-400">Line Chart</span>
                </div>
                <canvas data-chart='{!! json_encode([
                    "type" => "line",
                    "data" => [
                        "labels" => $weeklyChartData["labels"],
                        "datasets" => [[
                            "label" => "Penjualan",
                            "data" => $weeklyChartData["values"],
                            "borderColor" => "#3b82f6",
                            "backgroundColor" => "rgba(59, 130, 246, 0.08)",
                            "fill" => true,
                            "tension" => 0.3,
                            "pointRadius" => 4,
                            "pointBackgroundColor" => "#3b82f6",
                            "borderWidth" => 2
                        ]]
                    ],
                    "options" => [
                        "responsive" => true,
                        "plugins" => ["legend" => ["display" => false]],
                        "scales" => ["y" => ["beginAtZero" => true, "ticks" => ["callback" => "formatRupiah"]]]
                    ]
                ]) !!}'></canvas>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-sm font-semibold text-gray-800">Penjualan Bulanan</h2>
                    <span class="text-xs text-gray-400">Bar Chart</span>
                </div>
                <canvas data-chart='{!! json_encode([
                    "type" => "bar",
                    "data" => [
                        "labels" => $monthlyChartData["labels"],
                        "datasets" => [[
                            "label" => "Penjualan",
                            "data" => $monthlyChartData["values"],
                            "backgroundColor" => "rgba(16, 185, 129, 0.6)",
                            "borderColor" => "#10b981",
                            "borderWidth" => 1,
                            "borderRadius" => 4
                        ]]
                    ],
                    "options" => [
                        "responsive" => true,
                        "plugins" => ["legend" => ["display" => false]],
                        "scales" => ["y" => ["beginAtZero" => true, "ticks" => ["callback" => "formatRupiah"]]]
                    ]
                ]) !!}'></canvas>
            </div>
        </div>

        {{-- More Charts: Top Products & Category Sales --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-sm font-semibold text-gray-800">Top 5 Produk Terlaris (30 hari)</h2>
                    <span class="text-xs text-gray-400">Horizontal Bar</span>
                </div>
                <canvas data-chart='{!! json_encode([
                    "type" => "bar",
                    "data" => [
                        "labels" => $topProductsChart["labels"],
                        "datasets" => [[
                            "label" => "Terjual",
                            "data" => $topProductsChart["values"],
                            "backgroundColor" => ["rgba(59, 130, 246, 0.7)", "rgba(16, 185, 129, 0.7)", "rgba(245, 158, 11, 0.7)", "rgba(139, 92, 246, 0.7)", "rgba(239, 68, 68, 0.7)"],
                            "borderRadius" => 4
                        ]]
                    ],
                    "options" => [
                        "indexAxis" => "y",
                        "responsive" => true,
                        "plugins" => ["legend" => ["display" => false]],
                        "scales" => ["x" => ["beginAtZero" => true, "ticks" => ["precision" => 0]]]
                    ]
                ]) !!}'></canvas>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-sm font-semibold text-gray-800">Penjualan per Kategori (30 hari)</h2>
                    <span class="text-xs text-gray-400">Doughnut</span>
                </div>
                <div class="flex justify-center">
                    <canvas data-chart='{!! json_encode([
                        "type" => "doughnut",
                        "data" => [
                            "labels" => $categorySalesChart["labels"],
                            "datasets" => [[
                                "data" => $categorySalesChart["values"],
                                "backgroundColor" => ["#3b82f6", "#10b981", "#f59e0b", "#8b5cf6", "#ef4444", "#06b6d4", "#f97316", "#ec4899"],
                                "borderWidth" => 0
                            ]]
                        ],
                        "options" => [
                            "responsive" => true,
                            "plugins" => ["legend" => ["position" => "bottom", "labels" => ["padding" => 12, "usePointStyle" => true, "pointStyle" => "circle"]]]
                        ]
                    ]) !!}' style="max-height: 260px;">
                    </canvas>
                </div>
            </div>
        </div>

        {{-- Bottom sections --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-sm font-semibold text-gray-800">Draft Purchase Order</h2>
                    <span class="text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full">{{ count($draftPos) }}</span>
                </div>
                @if(count($draftPos))
                    <div class="space-y-2">
                        @foreach($draftPos as $po)
                            <a href="{{ route('purchase-orders.edit', $po['id']) }}" class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition group">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-white border border-gray-200 flex items-center justify-center">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-800 group-hover:text-blue-600 transition">#{{ $po['po_number'] }}</p>
                                        <p class="text-xs text-gray-400">{{ $po['vendor']['name'] ?? '-' }} · {{ $po['items_count'] ?? 0 }} item</p>
                                    </div>
                                </div>
                                <span class="text-[10px] font-medium px-2 py-0.5 rounded-full {{ ($po['is_auto_draft'] ?? false) ? 'bg-orange-50 text-orange-600' : 'bg-gray-100 text-gray-500' }}">
                                    {{ ($po['is_auto_draft'] ?? false) ? 'Otomatis' : 'Manual' }}
                                </span>
                            </a>
                        @endforeach
                    </div>
                    <a href="{{ route('purchase-orders.index') }}" class="mt-3 inline-flex items-center gap-1 text-xs text-blue-600 hover:text-blue-700 font-medium">
                        Lihat semua PO
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                @else
                    <div class="flex flex-col items-center py-8 text-gray-400">
                        <svg class="w-10 h-10 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <p class="text-sm">Tidak ada draft PO</p>
                    </div>
                @endif
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-sm font-semibold text-red-700">Stok Habis</h2>
                    <span class="text-xs bg-red-50 text-red-500 px-2 py-0.5 rounded-full">{{ count($outOfStockProducts) }}</span>
                </div>
                @if(count($outOfStockProducts))
                    <div class="space-y-2">
                        @foreach($outOfStockProducts as $product)
                            <div class="flex items-center justify-between p-3 rounded-lg bg-red-50">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-white border border-red-200 flex items-center justify-center">
                                        <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-800">{{ $product['name'] }}</p>
                                        <p class="text-xs text-gray-400">SKU: {{ $product['sku'] }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-bold text-red-600">{{ $product['stock'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <a href="{{ route('stock.index') }}" class="mt-3 inline-flex items-center gap-1 text-xs text-blue-600 hover:text-blue-700 font-medium">
                        Lihat semua stok
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                @else
                    <div class="flex flex-col items-center py-3 text-gray-400">
                        <p class="text-sm">Tidak ada stok habis</p>
                    </div>
                @endif
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-sm font-semibold text-amber-700">Stok Menipis</h2>
                    <span class="text-xs bg-amber-50 text-amber-500 px-2 py-0.5 rounded-full">{{ count($lowStockProducts) }}</span>
                </div>
                @if(count($lowStockProducts))
                    <div class="space-y-2">
                        @foreach($lowStockProducts as $product)
                            <div class="flex items-center justify-between p-3 rounded-lg bg-amber-50">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-white border border-amber-200 flex items-center justify-center">
                                        <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-800">{{ $product['name'] }}</p>
                                        <p class="text-xs text-gray-400">SKU: {{ $product['sku'] }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-bold text-amber-600">{{ $product['stock'] }}</p>
                                    <p class="text-[10px] text-gray-400">min: {{ $product['min_stock'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <a href="{{ route('stock.index') }}" class="mt-3 inline-flex items-center gap-1 text-xs text-blue-600 hover:text-blue-700 font-medium">
                        Lihat semua stok
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                @else
                    <div class="flex flex-col items-center py-3 text-gray-400">
                        <p class="text-sm">Semua stok aman</p>
                    </div>
                @endif
            </div>
        </div>
    @else
        {{-- Cashier Dashboard --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 hover:shadow-md transition">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-sm font-medium text-gray-500">Penjualan Saya Hari Ini</span>
                    <div class="w-10 h-10 rounded-lg bg-purple-50 flex items-center justify-center">
                        <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
                <p class="text-2xl font-bold text-gray-900">Rp {{ number_format($todaySales, 0, ',', '.') }}</p>
                <p class="text-xs text-gray-400 mt-1">Penjualan Anda hari ini</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 hover:shadow-md transition">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-sm font-medium text-gray-500">Transaksi Saya Hari Ini</span>
                    <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
                    </div>
                </div>
                <p class="text-2xl font-bold text-gray-900">{{ $todayTransactions }}</p>
                <p class="text-xs text-gray-400 mt-1">Total transaksi Anda</p>
            </div>
            <a href="{{ route('requests.receipt') }}" class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 hover:shadow-md transition block">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-sm font-medium text-gray-500">Request Pending</span>
                    <div class="w-10 h-10 rounded-lg {{ $pendingRequests > 0 ? 'bg-amber-50' : 'bg-gray-50' }} flex items-center justify-center">
                        <svg class="w-5 h-5 {{ $pendingRequests > 0 ? 'text-amber-500' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
                <p class="text-2xl font-bold {{ $pendingRequests > 0 ? 'text-amber-600' : 'text-gray-900' }}">{{ $pendingRequests }}</p>
                <p class="text-xs {{ $pendingRequests > 0 ? 'text-amber-500' : 'text-gray-400' }} mt-1">Menunggu persetujuan</p>
            </a>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-semibold text-gray-800">Penjualan Saya 7 Hari</h2>
                <span class="text-xs text-gray-400">Line Chart</span>
            </div>
            <canvas data-chart='{!! json_encode([
                "type" => "line",
                "data" => [
                    "labels" => $weeklyChartData["labels"],
                    "datasets" => [[
                        "label" => "Penjualan",
                        "data" => $weeklyChartData["values"],
                        "borderColor" => "#8b5cf6",
                        "backgroundColor" => "rgba(139, 92, 246, 0.08)",
                        "fill" => true,
                        "tension" => 0.3,
                        "pointRadius" => 4,
                        "pointBackgroundColor" => "#8b5cf6",
                        "borderWidth" => 2
                    ]]
                ],
                "options" => [
                    "responsive" => true,
                    "plugins" => ["legend" => ["display" => false]],
                    "scales" => ["y" => ["beginAtZero" => true, "ticks" => ["callback" => "formatRupiah"]]]
                ]
            ]) !!}'></canvas>
        </div>
    @endif

    {{-- Subscription Check --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-sm font-semibold text-gray-800">Cek Langganan Aktif</h2>
            <svg class="w-5 h-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        @livewire('subscription-check')
    </div>
</div>