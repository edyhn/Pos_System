<div>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            </div>
            <div>
                <h1 class="text-lg font-bold text-gray-900">Laporan Penjualan</h1>
                <p class="text-sm text-gray-500">Analisis data penjualan toko Anda</p>
            </div>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('reports.sales.export-excel', request()->query()) }}" class="inline-flex items-center gap-1.5 px-3 py-2 bg-emerald-600 text-white rounded-xl hover:bg-emerald-700 transition text-sm font-medium shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Excel
            </a>
            <a href="{{ route('reports.sales.export-pdf', request()->query()) }}" class="inline-flex items-center gap-1.5 px-3 py-2 bg-red-600 text-white rounded-xl hover:bg-red-700 transition text-sm font-medium shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                PDF
            </a>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-4">
        <div class="flex flex-wrap items-end gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Dari</label>
                <input type="date" wire:model="dateFrom" class="px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Sampai</label>
                <input type="date" wire:model="dateTo" class="px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white">
            </div>
            @if($stores)
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Cabang</label>
                    <select wire:model="storeFilter" class="px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white">
                        <option value="">Semua Cabang</option>
                        @foreach ($stores as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-4" wire:ignore>
        <div class="flex items-center gap-2 mb-4">
            <div class="w-2 h-2 rounded-full bg-blue-500"></div>
            <h2 class="text-sm font-semibold text-gray-800">Penjualan Per Hari</h2>
        </div>
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
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 hover:shadow-md transition">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-9 h-9 rounded-lg bg-blue-50 flex items-center justify-center">
                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
                <span class="text-sm font-medium text-gray-500">Total Transaksi</span>
            </div>
            <p class="text-2xl font-bold text-gray-800">{{ $summary->total_transactions }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 hover:shadow-md transition">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-9 h-9 rounded-lg bg-emerald-50 flex items-center justify-center">
                    <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <span class="text-sm font-medium text-gray-500">Total Pendapatan</span>
            </div>
            <p class="text-2xl font-bold text-emerald-600">Rp{{ number_format($summary->total_revenue, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 hover:shadow-md transition">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-9 h-9 rounded-lg bg-purple-50 flex items-center justify-center">
                    <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <span class="text-sm font-medium text-gray-500">Total Pajak</span>
            </div>
            <p class="text-2xl font-bold text-purple-600">Rp{{ number_format($summary->total_tax, 0, ',', '.') }}</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        <th class="px-4 py-3.5">Invoice</th>
                        <th class="px-4 py-3.5">Tanggal</th>
                        <th class="px-4 py-3.5">Cabang</th>
                        <th class="px-4 py-3.5">Kasir</th>
                        <th class="px-4 py-3.5">Pembayaran</th>
                        <th class="px-4 py-3.5 text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($transactions as $t)
                        <tr class="hover:bg-gray-50 text-sm transition">
                            <td class="px-4 py-3.5 font-mono text-xs font-medium text-gray-800">{{ $t->invoice_number }}</td>
                            <td class="px-4 py-3.5 text-gray-500 whitespace-nowrap">{{ $t->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-3.5 text-gray-500">{{ $t->store?->name ?? '-' }}</td>
                            <td class="px-4 py-3.5 text-gray-500">{{ $t->user?->name ?? '-' }}</td>
                            <td class="px-4 py-3.5">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-full bg-blue-50 text-blue-700">
                                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                    {{ $t->payment_method }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-right font-semibold text-gray-800">Rp{{ number_format($t->total_amount, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center">
                                <div class="flex flex-col items-center text-gray-400">
                                    <svg class="w-12 h-12 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                                    <p class="text-sm font-medium">Belum ada transaksi</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-gray-100 bg-gray-50/50">
            {{ $transactions->links() }}
        </div>
    </div>
</div>
