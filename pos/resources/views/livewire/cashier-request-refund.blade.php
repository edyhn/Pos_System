<div>
    <h1 class="text-2xl font-bold text-gray-800 mb-4">Request Refund</h1>

    @if (session('message'))
        <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-green-700 text-sm">{{ session('message') }}</div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6 max-w-lg">
        <h2 class="font-semibold text-gray-700 mb-3">Buat Request Refund Baru</h2>
        <div class="space-y-3">
            <div>
                <label class="block text-sm text-gray-600 mb-1">Pilih Transaksi</label>
                <select wire:model="selectedTransaction" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="">Pilih Transaksi</option>
                    @foreach ($transactions as $t)
                        <option value="{{ $t->id }}">{{ $t->invoice_number }} - Rp {{ number_format($t->total_amount, 0, ',', '.') }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">Informasi Kondisi Barang</label>
                <textarea wire:model="conditionInfo" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Jelaskan kondisi barang yang diretur..."></textarea>
                @error('conditionInfo') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <button wire:click="requestRefund" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm">Kirim Request</button>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <h2 class="font-semibold text-gray-700 p-4 border-b border-gray-100">Riwayat Request Saya</h2>
        <table class="w-full">
            <thead>
                <tr class="bg-gray-50 text-left text-sm text-gray-500">
                    <th class="px-4 py-3 font-medium">Invoice</th>
                    <th class="px-4 py-3 font-medium">Kondisi</th>
                    <th class="px-4 py-3 font-medium">Tanggal</th>
                    <th class="px-4 py-3 font-medium">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($myRequests as $req)
                    <tr class="hover:bg-gray-50 text-sm">
                        <td class="px-4 py-3 text-gray-800">{{ $req->transaction->invoice_number }}</td>
                        <td class="px-4 py-3 text-gray-500 max-w-xs truncate">{{ $req->condition_info }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $req->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 text-xs rounded-full
                                @if($req->status == 'pending') bg-yellow-100 text-yellow-700
                                @elseif($req->status == 'approved') bg-green-100 text-green-700
                                @else bg-red-100 text-red-700 @endif">
                                {{ $req->status }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-8 text-center text-gray-400">Belum ada request.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4 border-t border-gray-100">{{ $myRequests->links() }}</div>
    </div>
</div>
