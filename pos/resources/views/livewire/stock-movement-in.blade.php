<div>
    <h1 class="text-2xl font-bold text-gray-800 mb-4">Barang Masuk</h1>
    <p class="text-gray-500 mb-4">Riwayat penerimaan barang (PO, retur, refund).</p>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-4">
        <div class="flex flex-wrap gap-2">
            <input type="text" wire:model.live.debounce="search" placeholder="Cari produk..."
                class="px-3 py-2 border border-gray-300 rounded-lg text-sm flex-1 min-w-[150px]">
            <input type="date" wire:model.live="dateFrom" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
            <input type="date" wire:model.live="dateTo" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full">
            <thead>
                <tr class="bg-gray-50 text-left text-sm text-gray-500">
                    <th class="px-4 py-3 font-medium">Tanggal</th>
                    <th class="px-4 py-3 font-medium">Produk</th>
                    <th class="px-4 py-3 font-medium">Jumlah</th>
                    <th class="px-4 py-3 font-medium">Referensi</th>
                    <th class="px-4 py-3 font-medium">User</th>
                    <th class="px-4 py-3 font-medium">Catatan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($movements as $m)
                    <tr class="hover:bg-gray-50 text-sm">
                        <td class="px-4 py-3 text-gray-500 whitespace-nowrap">{{ $m->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3 text-gray-800">{{ $m->product->name ?? '-' }}</td>
                        <td class="px-4 py-3 font-medium text-green-600">+{{ $m->quantity }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $m->reference_type }} #{{ $m->reference_id }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $m->user->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-gray-500 max-w-[200px] truncate">{{ $m->note ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-400">Belum ada barang masuk.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $movements->links() }}</div>
</div>