<div>
    <h1 class="text-2xl font-bold text-gray-800 mb-4">Approval Refund</h1>

    @if (session('message'))
        <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-green-700 text-sm">{{ session('message') }}</div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-4 flex flex-wrap items-center gap-4">
        <input type="text" wire:model.live="search" placeholder="Cari invoice..." class="max-w-xs px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
        <select wire:model.live="statusFilter" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
            <option value="">Semua Status</option>
            <option value="pending">Pending</option>
            <option value="approved">Disetujui</option>
            <option value="rejected">Ditolak</option>
        </select>
    </div>

    @if($showApproveForm)
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-4 max-w-lg">
            <h2 class="font-semibold text-gray-800 mb-3">Konfirmasi Refund</h2>
            <div class="space-y-3">
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Jumlah Refund</label>
                    <input type="number" wire:model="refundAmount" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    @error('refundAmount') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Tipe Refund</label>
                    <select wire:model="refundType" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="prorata">Prorata</option>
                        <option value="full">Full</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Catatan Owner</label>
                    <textarea wire:model="ownerNote" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"></textarea>
                </div>
                <div class="flex gap-2">
                    <button wire:click="approve" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm">Setujui</button>
                    <button wire:click="reject" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm">Tolak</button>
                    <button wire:click="$set('showApproveForm', false)" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm">Batal</button>
                </div>
            </div>
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full">
            <thead>
                <tr class="bg-gray-50 text-left text-sm text-gray-500">
                    <th class="px-4 py-3 font-medium">Invoice</th>
                    <th class="px-4 py-3 font-medium">Kasir</th>
                    <th class="px-4 py-3 font-medium">Kondisi Barang</th>
                    <th class="px-4 py-3 font-medium">Total Transaksi</th>
                    <th class="px-4 py-3 font-medium">Status</th>
                    <th class="px-4 py-3 font-medium text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($requests as $req)
                    <tr class="hover:bg-gray-50 text-sm">
                        <td class="px-4 py-3 font-medium text-gray-800">{{ $req->transaction?->invoice_number ?? '-' }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $req->user?->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-gray-500 max-w-xs truncate">{{ $req->condition_info }}</td>
                        <td class="px-4 py-3 text-gray-800">Rp {{ number_format($req->transaction?->total_amount ?? 0, 0, ',', '.') }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 text-xs rounded-full
                                @if($req->status == 'pending') bg-yellow-100 text-yellow-700
                                @elseif($req->status == 'approved') bg-green-100 text-green-700
                                @else bg-red-100 text-red-700 @endif">
                                {{ $req->status }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            @if($req->status == 'pending')
                                <button wire:click="showForm({{ $req->id }}, {{ $req->transaction?->total_amount ?? 0 }})" class="text-blue-600 hover:text-blue-800">Proses</button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">Belum ada request refund.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4 border-t border-gray-100">{{ $requests->links() }}</div>
    </div>
</div>