<div>
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-bold text-gray-800">Diskon & Promo</h1>
        <a href="{{ route('discounts.create') }}" class="px-4 py-2 text-white bg-blue-600 rounded-lg hover:bg-blue-700">
            + Buat Diskon
        </a>
    </div>

    <div class="mb-4">
        <input type="text" wire:model.live="search" placeholder="Cari diskon..."
            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
    </div>

    <div class="overflow-x-auto bg-white rounded-lg shadow">
        <table class="w-full">
            <thead class="text-xs font-semibold text-gray-600 uppercase bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left cursor-pointer" wire:click="sortBy('name')">Nama</th>
                    <th class="px-4 py-3 text-left cursor-pointer" wire:click="sortBy('type')">Tipe</th>
                    <th class="px-4 py-3 text-right cursor-pointer" wire:click="sortBy('value')">Nilai</th>
                    <th class="px-4 py-3 text-center">Periode</th>
                    <th class="px-4 py-3 text-center">Status</th>
                    <th class="px-4 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($discounts as $discount)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium">{{ $discount->name }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 text-xs rounded {{ $discount->type === 'percentage' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700' }}">
                                {{ $discount->type === 'percentage' ? '%' : 'Rp' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            {{ $discount->type === 'percentage' ? $discount->value . '%' : 'Rp ' . number_format($discount->value, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-center text-sm">
                            {{ $discount->start_date->format('d/m/Y') }} - {{ $discount->end_date->format('d/m/Y') }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <button wire:click="toggleActive({{ $discount->id }})"
                                class="px-2 py-1 text-xs rounded {{ $discount->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                {{ $discount->is_active ? 'Aktif' : 'Nonaktif' }}
                            </button>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <a href="{{ route('discounts.edit', $discount) }}" class="text-blue-600 hover:text-blue-800 mr-2">Edit</a>
                            <button wire:click="delete({{ $discount->id }})" wire:confirm="Yakin hapus diskon ini?"
                                class="text-red-600 hover:text-red-800">Hapus</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">Belum ada diskon.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $discounts->links() }}
    </div>
</div>
