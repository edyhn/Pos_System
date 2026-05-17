<div wire:poll.30s="refreshCount" class="relative" x-data="{ open: false }">
    <button @click="open = ! open; if(open) $wire.refreshCount()" class="relative p-2 text-gray-600 hover:text-gray-900 transition">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>
        @if($count > 0)
            <span class="absolute -top-1 -right-1 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white bg-red-500 rounded-full min-w-[20px]">{{ $count > 99 ? '99+' : $count }}</span>
        @endif
    </button>

    <div x-show="open" x-cloak class="absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-lg border border-gray-200 z-50 max-h-96 overflow-y-auto">
        <div class="p-3 border-b border-gray-100">
            <p class="text-sm font-semibold text-gray-800">Notifikasi</p>
        </div>

        @if($count === 0)
            <div class="p-6 text-center text-gray-500 text-sm">Tidak ada notifikasi</div>
        @else
            @if(count($pendingApprovals) > 0 || count($pendingRequests) > 0)
                @php $items = $user->isOwner() ? $pendingApprovals : $pendingRequests; @endphp
                @foreach($items as $item)
                    <a href="{{ $item instanceof \App\Models\ReceiptReprintRequest ? route('approvals.receipt') : route('approvals.refund') }}" class="flex items-start gap-3 p-3 hover:bg-gray-50 transition border-b border-gray-50">
                        <div class="w-8 h-8 rounded-full bg-yellow-100 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-gray-800 truncate">{{ $item instanceof \App\Models\ReceiptReprintRequest ? 'Cetak ulang struk' : 'Refund' }} oleh {{ $item->user?->name ?? '-' }}</p>
                            <p class="text-xs text-gray-500">{{ $item->created_at->diffForHumans() }}</p>
                        </div>
                    </a>
                @endforeach
            @endif

            @foreach($lowStockProducts as $product)
                @php $isOutOfStock = $product->stock <= 0; @endphp
                <a href="{{ route('stock.index') }}" class="flex items-start gap-3 p-3 hover:bg-gray-50 transition border-b border-gray-50">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 {{ $isOutOfStock ? 'bg-red-100' : 'bg-amber-100' }}">
                        <svg class="w-4 h-4 {{ $isOutOfStock ? 'text-red-600' : 'text-amber-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-gray-800 truncate">Stok <strong>{{ $product->name }}</strong> {{ $isOutOfStock ? 'habis' : 'menipis' }}</p>
                        <p class="text-xs {{ $isOutOfStock ? 'text-red-500' : 'text-gray-500' }}">{{ $isOutOfStock ? 'Stok habis' : $product->stock . ' / ' . $product->min_stock }}</p>
                    </div>
                </a>
            @endforeach

            @foreach($draftPos as $po)
                <a href="{{ route('purchase-orders.edit', $po) }}" class="flex items-start gap-3 p-3 hover:bg-gray-50 transition border-b border-gray-50">
                    <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-gray-800 truncate">PO {{ $po->po_number }} menunggu</p>
                        <p class="text-xs text-gray-500">{{ $po->created_at->diffForHumans() }}</p>
                    </div>
                </a>
            @endforeach
        @endif

        <div class="p-2 border-t border-gray-100 text-center">
            <button wire:click="markAllRead" @click="open = false" class="text-xs text-blue-600 hover:underline">Tandai sudah dibaca</button>
        </div>
    </div>

    <div x-show="open" x-cloak class="fixed inset-0 z-40" @click="open = false"></div>
</div>
