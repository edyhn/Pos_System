<div>
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold text-gray-800">Purchase Order</h1>
        <a href="{{ route('purchase-orders.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm">+ Buat PO</a>
    </div>

    @if (session('message'))
        <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-green-700 text-sm">{{ session('message') }}</div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="p-4 border-b border-gray-100 flex items-center gap-4">
            <input type="text" wire:model.live="search" placeholder="Cari PO..." class="max-w-xs px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
            <select wire:model.live="statusFilter" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                <option value="">Semua Status</option>
                <option value="draft">Draft</option>
                <option value="sent">Sent</option>
                <option value="received">Received</option>
                <option value="cancelled">Cancelled</option>
            </select>
        </div>

        <table class="w-full">
            <thead>
                <tr class="bg-gray-50 text-left text-sm text-gray-500">
                    <th class="px-4 py-3 font-medium">No. PO</th>
                    <th class="px-4 py-3 font-medium">Vendor</th>
                    <th class="px-4 py-3 font-medium">Tanggal</th>
                    <th class="px-4 py-3 font-medium">Status</th>
                    <th class="px-4 py-3 font-medium">Pembuat</th>
                    <th class="px-4 py-3 font-medium text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($orders as $po)
                    <tr class="hover:bg-gray-50 text-sm">
                        <td class="px-4 py-3 font-medium text-gray-800">{{ $po->po_number }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $po->vendor?->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $po->created_at->format('d/m/Y') }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 text-xs rounded-full
                                @if($po->status == 'draft') bg-yellow-100 text-yellow-700
                                @elseif($po->status == 'sent') bg-blue-100 text-blue-700
                                @elseif($po->status == 'received') bg-green-100 text-green-700
                                @else bg-red-100 text-red-700 @endif">
                                {{ $po->status }}
                            </span>
                            @if($po->is_auto_draft)
                                <span class="ml-1 text-xs text-gray-400">(auto)</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-500">{{ $po->user?->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-right space-x-1">
                            <a href="{{ route('purchase-orders.show', $po) }}" class="text-blue-600 hover:text-blue-800">Detail</a>
                            @if($po->status == 'draft')
                                <button wire:click="updateStatus({{ $po->id }}, 'sent')" class="text-green-600 hover:text-green-800">Kirim</button>
                            @endif
                            @if($po->status == 'sent')
                                <button wire:click="updateStatus({{ $po->id }}, 'received')" class="text-green-600 hover:text-green-800">Terima</button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">Belum ada PO.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4 border-t border-gray-100">{{ $orders->links() }}</div>
    </div>
</div>
