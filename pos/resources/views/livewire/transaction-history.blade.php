<div>
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold text-gray-800">Riwayat Transaksi</h1>
        @auth
            @if(auth()->user()->isOwner())
                <div class="flex gap-2">
                    <a href="{{ route('reports.sales.export-excel', request()->all()) }}" class="px-3 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm">Excel</a>
                    <a href="{{ route('reports.sales.export-pdf', request()->all()) }}" target="_blank" class="px-3 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm">PDF</a>
                </div>
            @endif
        @endauth
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-4">
        <div class="flex flex-wrap items-center gap-4">
            <input type="text" wire:model.live="search" placeholder="Cari invoice..." class="max-w-xs px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
            <div>
                <label class="block text-xs text-gray-500">Dari</label>
                <input type="date" wire:model="dateFrom" class="px-3 py-1.5 border border-gray-300 rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-xs text-gray-500">Sampai</label>
                <input type="date" wire:model="dateTo" class="px-3 py-1.5 border border-gray-300 rounded-lg text-sm">
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <table class="w-full">
            <thead>
                <tr class="bg-gray-50 text-left text-sm text-gray-500">
                    <th class="px-4 py-3 font-medium">Invoice</th>
                    <th class="px-4 py-3 font-medium">Tanggal</th>
                    <th class="px-4 py-3 font-medium">Kasir</th>
                    <th class="px-4 py-3 font-medium">Customer</th>
                    <th class="px-4 py-3 font-medium">Pembayaran</th>
                    <th class="px-4 py-3 font-medium">Status</th>
                    <th class="px-4 py-3 font-medium text-right">Total</th>
                    <th class="px-4 py-3 font-medium text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($transactions as $t)
                    <tr class="hover:bg-gray-50 text-sm">
                        <td class="px-4 py-3 font-mono font-medium text-gray-800">{{ $t->invoice_number }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $t->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $t->user?->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $t->customer_name ?? '-' }}</td>
                        <td class="px-4 py-3"><span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-700">{{ $t->payment_method }}</span></td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 text-xs rounded-full {{ $t->status == 'completed' ? 'bg-green-100 text-green-700' : ($t->status == 'refunded' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-700') }}">
                                {{ $t->status }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right font-medium">Rp {{ number_format($t->total_amount, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right space-x-2">
                            <a href="{{ route('transactions.show', $t) }}" class="text-blue-600 hover:text-blue-800">Detail</a>
                            <a href="{{ route('print.receipt', $t) }}" target="_blank" class="text-gray-600 hover:text-gray-800">Struk</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-4 py-8 text-center text-gray-400">Belum ada transaksi.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4 border-t border-gray-100">{{ $transactions->links() }}</div>
    </div>
</div>
