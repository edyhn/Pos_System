<div>
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold text-gray-800">Stock Opname</h1>
        @if(!$showForm)
            <button wire:click="startOpname" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm">+ Stock Opname Baru</button>
        @endif
    </div>

    @if (session('message'))
        <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-green-700 text-sm">{{ session('message') }}</div>
    @endif

    @if($showForm)
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
            <div class="flex items-center gap-4 mb-4">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Tanggal</label>
                    <input type="date" wire:model="date" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div class="flex-1">
                    <label class="block text-xs text-gray-500 mb-1">Catatan</label>
                    <input type="text" wire:model="notes" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Catatan opname">
                </div>
            </div>

            <div class="overflow-y-auto max-h-96">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 text-left text-xs text-gray-500">
                            <th class="px-3 py-2 font-medium">Produk</th>
                            <th class="px-3 py-2 font-medium text-right">Sistem</th>
                            <th class="px-3 py-2 font-medium text-right">Aktual</th>
                            <th class="px-3 py-2 font-medium text-right">Selisih</th>
                            <th class="px-3 py-2 font-medium">Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($opnameItems as $index => $item)
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-2 text-gray-800">{{ $item['product_name'] }}</td>
                                <td class="px-3 py-2 text-right">{{ $item['system_stock'] }}</td>
                                <td class="px-3 py-2">
                                    <input type="number" wire:model="opnameItems.{{ $index }}.actual_stock" class="w-20 text-right px-2 py-1 border border-gray-300 rounded text-sm">
                                </td>
                                <td class="px-3 py-2 text-right font-medium {{ $item['difference'] != 0 ? ($item['difference'] > 0 ? 'text-green-600' : 'text-red-600') : '' }}">
                                    {{ $item['difference'] > 0 ? '+' : '' }}{{ $item['difference'] }}
                                </td>
                                <td class="px-3 py-2">
                                    <input type="text" wire:model="opnameItems.{{ $index }}.note" class="w-full px-2 py-1 border border-gray-200 rounded text-xs" placeholder="-">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4 flex gap-2">
                <button wire:click="saveOpname" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm">Simpan Opname</button>
                <button wire:click="$set('showForm', false)" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm">Batal</button>
            </div>
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <table class="w-full">
            <thead>
                <tr class="bg-gray-50 text-left text-sm text-gray-500">
                    <th class="px-4 py-3 font-medium">Tanggal</th>
                    <th class="px-4 py-3 font-medium">Status</th>
                    <th class="px-4 py-3 font-medium">Oleh</th>
                    <th class="px-4 py-3 font-medium">Catatan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($opnames as $opname)
                    <tr class="hover:bg-gray-50 text-sm">
                        <td class="px-4 py-3 text-gray-800">{{ $opname->date }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 text-xs rounded-full {{ $opname->status === 'completed' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                {{ $opname->status }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-500">{{ $opname->user?->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $opname->notes ?? '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-8 text-center text-gray-400">Belum ada stock opname.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4 border-t border-gray-100">{{ $opnames->links() }}</div>
    </div>
</div>
