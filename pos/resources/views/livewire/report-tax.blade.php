<div>
    <h1 class="text-2xl font-bold text-gray-800 mb-4">Laporan Pajak (PPN)</h1>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-4 flex gap-4">
        <div>
            <label class="block text-xs text-gray-500 mb-1">Dari</label>
            <input type="date" wire:model="dateFrom" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">Sampai</label>
            <input type="date" wire:model="dateTo" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
        </div>
    </div>

    <div class="grid grid-cols-3 gap-4 mb-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-sm text-gray-500">Total Transaksi Kena Pajak</p>
            <p class="text-2xl font-bold text-gray-800">{{ $summary->total }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-sm text-gray-500">Total PPN</p>
            <p class="text-2xl font-bold text-blue-600">Rp {{ number_format($summary->total_tax, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-sm text-gray-500">Total Penjualan (Include PPN)</p>
            <p class="text-2xl font-bold text-green-600">Rp {{ number_format($summary->total_sales, 0, ',', '.') }}</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <table class="w-full">
            <thead><tr class="bg-gray-50 text-left text-sm text-gray-500"><th class="px-4 py-3 font-medium">Invoice</th><th class="px-4 py-3 font-medium">Tanggal</th><th class="px-4 py-3 font-medium">Cabang</th><th class="px-4 py-3 font-medium text-right">Total</th><th class="px-4 py-3 font-medium text-right">PPN</th></tr></thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($transactions as $t)
                    <tr class="hover:bg-gray-50 text-sm">
                        <td class="px-4 py-3 text-gray-800">{{ $t->invoice_number }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $t->created_at->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $t->store->name }}</td>
                        <td class="px-4 py-3 text-right">Rp {{ number_format($t->total_amount, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right text-blue-600 font-medium">Rp {{ number_format($t->tax_amount, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-gray-400">Belum ada transaksi kena pajak.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4 border-t border-gray-100">{{ $transactions->links() }}</div>
    </div>
</div>
