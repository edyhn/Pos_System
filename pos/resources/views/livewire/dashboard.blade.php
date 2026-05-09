<div>
    @php $user = auth()->user(); @endphp

    @if($user->isOwner())
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100">
                <p class="text-sm text-gray-500">Penjualan Hari Ini</p>
                <p class="text-2xl font-bold text-gray-800 mt-1">Rp {{ number_format($todaySales, 0, ',', '.') }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100">
                <p class="text-sm text-gray-500">Transaksi Hari Ini</p>
                <p class="text-2xl font-bold text-gray-800 mt-1">{{ $todayTransactions }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100">
                <p class="text-sm text-gray-500">Total Produk Aktif</p>
                <p class="text-2xl font-bold text-gray-800 mt-1">{{ $totalProducts }}</p>
            </div>
            <a href="{{ route('approvals.receipt') }}" class="bg-white rounded-xl shadow-sm p-5 border border-gray-100 hover:bg-gray-50 transition block">
                <p class="text-sm text-gray-500">Pending Approvals</p>
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
            <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100">
                <p class="text-sm text-gray-500">Penjualan Saya Hari Ini</p>
                <p class="text-2xl font-bold text-gray-800 mt-1">Rp {{ number_format($todaySales, 0, ',', '.') }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100">
                <p class="text-sm text-gray-500">Transaksi Saya Hari Ini</p>
                <p class="text-2xl font-bold text-gray-800 mt-1">{{ $todayTransactions }}</p>
            </div>
            <a href="{{ route('requests.receipt') }}" class="bg-white rounded-xl shadow-sm p-5 border border-gray-100 hover:bg-gray-50 transition block">
                <p class="text-sm text-gray-500">Request Pending</p>
                <p class="text-2xl font-bold {{ $pendingRequests > 0 ? 'text-yellow-600' : 'text-gray-800' }} mt-1">{{ $pendingRequests }}</p>
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
