<div>
    @if (session('message'))
        <div class="mb-4 flex items-center gap-2 px-4 py-3 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-700 text-sm">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('message') }}</span>
        </div>
    @endif

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-cyan-50 flex items-center justify-center">
                <svg class="w-5 h-5 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
            </div>
            <div>
                <h1 class="text-lg font-bold text-gray-900">Stock Opname</h1>
                <p class="text-sm text-gray-500">Cocokkan stok fisik dengan sistem</p>
            </div>
        </div>
        @if(!$showForm)
            <button wire:click="startOpname" class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition text-sm font-medium shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Stock Opname Baru
            </button>
        @endif
    </div>

    @if($showDetail && $viewOpnameId)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6">
            <div class="flex items-center justify-between mb-5">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-cyan-50 flex items-center justify-center">
                        <svg class="w-4 h-4 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    </div>
                    <div>
                        <h2 class="font-semibold text-gray-800">Detail Stock Opname</h2>
                        <p class="text-xs text-gray-500">{{ $viewOpname->date }} &middot; oleh {{ $viewOpname->user?->name ?? '-' }} &middot; {{ $viewOpname->notes ?? 'Tanpa catatan' }}</p>
                    </div>
                </div>
                <button wire:click="closeDetail" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="overflow-x-auto border border-gray-200 rounded-lg">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            <th class="px-3 py-2.5">Produk</th>
                            <th class="px-3 py-2.5 text-right">Stok Sistem</th>
                            <th class="px-3 py-2.5 text-right">Stok Aktual</th>
                            <th class="px-3 py-2.5 text-right">Selisih</th>
                            <th class="px-3 py-2.5">Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @php
                            $totalDiff = 0;
                        @endphp
                        @foreach($viewItems as $item)
                            @php $totalDiff += $item->difference; @endphp
                            <tr class="hover:bg-gray-50 transition {{ $item->difference != 0 ? ($item->difference > 0 ? 'bg-emerald-50/50' : 'bg-red-50/50') : '' }}">
                                <td class="px-3 py-2 text-gray-800 font-medium">{{ $item->product?->name ?? 'Produk dihapus' }}</td>
                                <td class="px-3 py-2 text-right text-gray-600">{{ $item->system_stock }}</td>
                                <td class="px-3 py-2 text-right font-medium {{ $item->difference != 0 ? ($item->difference > 0 ? 'text-emerald-600' : 'text-red-600') : 'text-gray-800' }}">
                                    {{ $item->actual_stock }}
                                </td>
                                <td class="px-3 py-2 text-right font-semibold {{ $item->difference != 0 ? ($item->difference > 0 ? 'text-emerald-600' : 'text-red-600') : 'text-gray-400' }}">
                                    {{ $item->difference > 0 ? '+' : '' }}{{ $item->difference }}
                                </td>
                                <td class="px-3 py-2 text-gray-500 text-xs">{{ $item->note ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-gray-50 font-semibold text-sm">
                            <td class="px-3 py-2.5 text-gray-700" colspan="2">Total Produk: {{ $viewItems->count() }}</td>
                            <td class="px-3 py-2.5 text-right text-gray-700" colspan="2">
                                Total Selisih:
                                <span class="{{ $totalDiff != 0 ? ($totalDiff > 0 ? 'text-emerald-600' : 'text-red-600') : 'text-gray-700' }}">
                                    {{ $totalDiff > 0 ? '+' : '' }}{{ $totalDiff }}
                                </span>
                            </td>
                            <td class="px-3 py-2.5"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    @endif

    @if($showForm)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6">
            <div class="flex items-center gap-2 mb-5">
                <div class="w-8 h-8 rounded-lg bg-cyan-50 flex items-center justify-center">
                    <svg class="w-4 h-4 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                </div>
                <h2 class="font-semibold text-gray-800">Form Opname Baru</h2>
            </div>
            <div class="flex items-center gap-4 mb-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Tanggal</label>
                    <input type="date" wire:model="date" class="px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white">
                </div>
                <div class="flex-1">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Catatan</label>
                    <input type="text" wire:model="notes" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white" placeholder="Catatan opname">
                </div>
            </div>

            <div class="overflow-x-auto max-h-96 border border-gray-200 rounded-lg">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            <th class="px-3 py-2.5">Produk</th>
                            <th class="px-3 py-2.5 text-right">Sistem</th>
                            <th class="px-3 py-2.5 text-right">Aktual</th>
                            <th class="px-3 py-2.5 text-right">Selisih</th>
                            <th class="px-3 py-2.5">Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($opnameItems as $index => $item)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-3 py-2 text-gray-800 font-medium">{{ $item['product_name'] }}</td>
                                <td class="px-3 py-2 text-right text-gray-600">{{ $item['system_stock'] }}</td>
                                <td class="px-3 py-2">
                                    <input type="number" wire:model="opnameItems.{{ $index }}.actual_stock" class="w-20 text-right px-2 py-1.5 border border-gray-300 rounded-lg text-sm bg-white">
                                </td>
                                <td class="px-3 py-2 text-right font-semibold {{ $item['difference'] != 0 ? ($item['difference'] > 0 ? 'text-emerald-600' : 'text-red-600') : 'text-gray-400' }}">
                                    {{ $item['difference'] > 0 ? '+' : '' }}{{ $item['difference'] }}
                                </td>
                                <td class="px-3 py-2">
                                    <input type="text" wire:model="opnameItems.{{ $index }}.note" class="w-full px-2 py-1.5 border border-gray-200 rounded text-xs bg-white" placeholder="-">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4 flex gap-2">
                <button wire:click="saveOpname" class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-600 text-white rounded-xl hover:bg-emerald-700 transition text-sm font-medium shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Simpan Opname
                </button>
                <button wire:click="$set('showForm', false)" class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition text-sm font-medium">Batal</button>
            </div>
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        <th class="px-4 py-3.5">Tanggal</th>
                        <th class="px-4 py-3.5">Status</th>
                        <th class="px-4 py-3.5">Oleh</th>
                        <th class="px-4 py-3.5">Catatan</th>
                        <th class="px-4 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($opnames as $opname)
                        <tr class="hover:bg-gray-50 text-sm transition">
                            <td class="px-4 py-3.5 text-gray-800 font-medium">{{ $opname->date }}</td>
                            <td class="px-4 py-3.5">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-full {{ $opname->status === 'completed' ? 'bg-emerald-50 text-emerald-700' : 'bg-yellow-50 text-yellow-700' }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $opname->status === 'completed' ? 'bg-emerald-500' : 'bg-yellow-500' }}"></span>
                                    {{ $opname->status }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-gray-500">{{ $opname->user?->name ?? '-' }}</td>
                            <td class="px-4 py-3.5 text-gray-500 max-w-xs truncate">{{ $opname->notes ?? '-' }}</td>
                            <td class="px-4 py-3.5 text-right">
                                <button wire:click="viewOpname({{ $opname->id }})" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    Lihat
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-12 text-center">
                                <div class="flex flex-col items-center text-gray-400">
                                    <svg class="w-12 h-12 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                                    <p class="text-sm font-medium">Belum ada stock opname</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-gray-100 bg-gray-50/50">
            {{ $opnames->links() }}
        </div>
    </div>
</div>
