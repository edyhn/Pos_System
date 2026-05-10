<div>
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold text-gray-800">Laporan Penjualan</h1>
        <div class="flex gap-2">
            <a href="{{ route('reports.sales.export-excel', request()->query()) }}" class="px-3 py-2 bg-green-600 text-white rounded-lg text-sm hover:bg-green-700">Excel</a>
            <a href="{{ route('reports.sales.export-pdf', request()->query()) }}" class="px-3 py-2 bg-red-600 text-white rounded-lg text-sm hover:bg-red-700">PDF</a>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-4">
        <div class="flex items-center gap-4">
            <div>
                <label class="block text-xs text-gray-500 mb-1">Dari</label>
                <input type="date" wire:model="dateFrom" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Sampai</label>
                <input type="date" wire:model="dateTo" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            @if($stores)
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Cabang</label>
                    <select wire:model="storeFilter" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="">Semua Cabang</option>
                        @foreach ($stores as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-4" wire:ignore>
        <h2 class="text-lg font-semibold text-gray-800 mb-3">Penjualan Per Hari</h2>
        <canvas data-chart='{!! json_encode([
            "type" => "line",
            "data" => [
                "labels" => $dailyChartData["labels"],
                "datasets" => [[
                    "label" => "Penjualan",
                    "data" => $dailyChartData["values"],
                    "borderColor" => "#3b82f6",
                    "backgroundColor" => "rgba(59, 130, 246, 0.1)",
                    "fill" => true,
                    "tension" => 0.3
                ]]
            ],
            "options" => [
                "responsive" => true,
                "plugins" => ["legend" => ["display" => false]],
                "scales" => ["y" => ["beginAtZero" => true, "ticks" => ["callback" => "formatRupiah"]]]
            ]
        ]) !!}'></canvas>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-sm text-gray-500">Total Transaksi</p>
            <p class="text-2xl font-bold text-gray-800">{{ $summary->total_transactions }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-sm text-gray-500">Total Pendapatan</p>
            <p class="text-2xl font-bold text-green-600">Rp {{ number_format($summary->total_revenue, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-sm text-gray-500">Total Pajak</p>
            <p class="text-2xl font-bold text-blue-600">Rp {{ number_format($summary->total_tax, 0, ',', '.') }}</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <table class="w-full">
            <thead>
                <tr class="bg-gray-50 text-left text-sm text-gray-500">
                    <th class="px-4 py-3 font-medium">Invoice</th>
                    <th class="px-4 py-3 font-medium">Tanggal</th>
                    <th class="px-4 py-3 font-medium">Cabang</th>
                    <th class="px-4 py-3 font-medium">Kasir</th>
                    <th class="px-4 py-3 font-medium">Pembayaran</th>
                    <th class="px-4 py-3 font-medium text-right">Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($transactions as $t)
                    <tr class="hover:bg-gray-50 text-sm">
                        <td class="px-4 py-3 font-medium text-gray-800">{{ $t->invoice_number }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $t->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $t->store?->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $t->user?->name ?? '-' }}</td>
                        <td class="px-4 py-3"><span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-700">{{ $t->payment_method }}</span></td>
                        <td class="px-4 py-3 text-right font-medium">Rp {{ number_format($t->total_amount, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">Belum ada transaksi.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4 border-t border-gray-100">{{ $transactions->links() }}</div>
    </div>
</div>
