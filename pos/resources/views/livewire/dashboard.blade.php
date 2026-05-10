<div>
    @php $user = auth()->user(); @endphp

    @if($user->isOwner())
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100 hover:shadow-md transition">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-sm text-gray-500">Penjualan Hari Ini</p>
                    <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <p class="text-2xl font-bold text-gray-800">Rp {{ number_format($todaySales, 0, ',', '.') }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100 hover:shadow-md transition">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-sm text-gray-500">Transaksi Hari Ini</p>
                    <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
                </div>
                <p class="text-2xl font-bold text-gray-800">{{ $todayTransactions }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100 hover:shadow-md transition">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-sm text-gray-500">Total Produk Aktif</p>
                    <svg class="w-8 h-8 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
                <p class="text-2xl font-bold text-gray-800">{{ $totalProducts }}</p>
            </div>
            <a href="{{ route('approvals.receipt') }}" class="bg-white rounded-xl shadow-sm p-5 border border-gray-100 hover:shadow-md transition block">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-sm text-gray-500">Pending Approvals</p>
                    <svg class="w-8 h-8 {{ $pendingApprovals > 0 ? 'text-yellow-500' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <p class="text-2xl font-bold {{ $pendingApprovals > 0 ? 'text-yellow-600' : 'text-gray-800' }} mt-1">{{ $pendingApprovals }}</p>
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6" wire:ignore>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h2 class="text-lg font-semibold text-gray-800 mb-3">Penjualan 7 Hari</h2>
                <canvas data-chart='{!! json_encode([
                    "type" => "line",
                    "data" => [
                        "labels" => $weeklyChartData["labels"],
                        "datasets" => [[
                            "label" => "Penjualan",
                            "data" => $weeklyChartData["values"],
                            "borderColor" => "#3b82f6",
                            "backgroundColor" => "rgba(59, 130, 246, 0.1)",
                            "fill" => true,
                            "tension" => 0.3
                        ]]
                    ],
                    "options" => [
                        "responsive" => true,
                        "plugins" => ["legend" => ["display" => false]],
                        "scales" => [
                            "y" => ["beginAtZero" => true, "ticks" => ["callback" => "formatRupiah"]]
                        ]
                    ]
                ]) !!}'></canvas>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h2 class="text-lg font-semibold text-gray-800 mb-3">Penjualan Bulanan</h2>
                <canvas data-chart='{!! json_encode([
                    "type" => "bar",
                    "data" => [
                        "labels" => $monthlyChartData["labels"],
                        "datasets" => [[
                            "label" => "Penjualan",
                            "data" => $monthlyChartData["values"],
                            "backgroundColor" => "rgba(16, 185, 129, 0.7)",
                            "borderColor" => "#10b981",
                            "borderWidth" => 1
                        ]]
                    ],
                    "options" => [
                        "responsive" => true,
                        "plugins" => ["legend" => ["display" => false]],
                        "scales" => [
                            "y" => ["beginAtZero" => true, "ticks" => ["callback" => "formatRupiah"]]
                        ]
                    ]
                ]) !!}'></canvas>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h2 class="text-lg font-semibold text-gray-800 mb-3">Draft PO Menunggu</h2>
                @if($draftPos->count())
                    <div class="space-y-2">
                        @foreach($draftPos as $po)
                            <a href="{{ route('purchase-orders.edit', $po) }}" class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                                <div>
                                    <p class="text-sm font-medium text-gray-800">#{{ $po->po_number }}</p>
                                    <p class="text-xs text-gray-500">{{ $po->vendor?->name ?? '-' }} | {{ $po->items_count ?? 0 }} item</p>
                                </div>
                                <span class="text-xs {{ $po->is_auto_draft ? 'text-orange-500' : 'text-gray-400' }}">{{ $po->is_auto_draft ? 'Auto' : 'Manual' }}</span>
                            </a>
                        @endforeach
                    </div>
                    <a href="{{ route('purchase-orders.index') }}" class="mt-3 inline-block text-sm text-blue-600 hover:underline">Lihat semua PO</a>
                @else
                    <p class="text-gray-500 text-sm">Tidak ada draft PO.</p>
                @endif
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h2 class="text-lg font-semibold text-gray-800 mb-3">Stok Menipis</h2>
                @if($lowStockProducts->count())
                    <div class="space-y-2">
                        @foreach($lowStockProducts as $product)
                            <div class="flex items-center justify-between p-3 bg-red-50 rounded-lg">
                                <div>
                                    <p class="text-sm font-medium text-gray-800">{{ $product->name }}</p>
                                    <p class="text-xs text-gray-500">SKU: {{ $product->sku }}</p>
                                </div>
                                <span class="text-sm font-bold {{ $product->stock == 0 ? 'text-red-600' : 'text-orange-500' }}">
                                    {{ $product->stock }} / {{ $product->min_stock }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                    <a href="{{ route('stock.index') }}" class="mt-3 inline-block text-sm text-blue-600 hover:underline">Lihat semua stok</a>
                @else
                    <p class="text-gray-500 text-sm">Semua stok aman.</p>
                @endif
            </div>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100 hover:shadow-md transition">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-sm text-gray-500">Penjualan Saya Hari Ini</p>
                    <svg class="w-8 h-8 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <p class="text-2xl font-bold text-gray-800">Rp {{ number_format($todaySales, 0, ',', '.') }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100 hover:shadow-md transition">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-sm text-gray-500">Transaksi Saya Hari Ini</p>
                    <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
                </div>
                <p class="text-2xl font-bold text-gray-800">{{ $todayTransactions }}</p>
            </div>
            <a href="{{ route('requests.receipt') }}" class="bg-white rounded-xl shadow-sm p-5 border border-gray-100 hover:shadow-md transition block">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-sm text-gray-500">Request Pending</p>
                    <svg class="w-8 h-8 {{ $pendingRequests > 0 ? 'text-yellow-500' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <p class="text-2xl font-bold {{ $pendingRequests > 0 ? 'text-yellow-600' : 'text-gray-800' }}">{{ $pendingRequests }}</p>
            </a>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-6" wire:ignore>
            <h2 class="text-lg font-semibold text-gray-800 mb-3">Penjualan Saya 7 Hari</h2>
            <canvas data-chart='{!! json_encode([
                "type" => "line",
                "data" => [
                    "labels" => $weeklyChartData["labels"],
                    "datasets" => [[
                        "label" => "Penjualan",
                        "data" => $weeklyChartData["values"],
                        "borderColor" => "#8b5cf6",
                        "backgroundColor" => "rgba(139, 92, 246, 0.1)",
                        "fill" => true,
                        "tension" => 0.3
                    ]]
                ],
                "options" => [
                    "responsive" => true,
                    "plugins" => ["legend" => ["display" => false]],
                    "scales" => [
                        "y" => ["beginAtZero" => true, "ticks" => ["callback" => "formatRupiah"]]
                    ]
                ]
            ]) !!}'></canvas>
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <h2 class="text-lg font-semibold text-gray-800 mb-3">Cek Langganan Aktif</h2>
        @livewire('subscription-check')
    </div>
</div>
