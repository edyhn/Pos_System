<div>
    <h1 class="text-2xl font-bold text-gray-800 mb-4">Cek Langganan Aktif</h1>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-4 max-w-lg">
        <div class="flex gap-2">
            <input type="text" wire:model="search" placeholder="Masukkan nomor HP / ID customer" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
            <button wire:click="check" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm">Cari</button>
        </div>
    </div>

    @if($searched)
        <div class="bg-white rounded-xl shadow-sm border border-gray-100">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 text-left text-sm text-gray-500">
                        <th class="px-4 py-3 font-medium">Customer</th>
                        <th class="px-4 py-3 font-medium">Produk</th>
                        <th class="px-4 py-3 font-medium">Invoice</th>
                        <th class="px-4 py-3 font-medium">Mulai</th>
                        <th class="px-4 py-3 font-medium">Berakhir</th>
                        <th class="px-4 py-3 font-medium">Sisa Hari</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($subscriptions as $sub)
                        @php
                            $daysLeft = now()->diffInDays(\Carbon\Carbon::parse($sub->end_date), false);
                        @endphp
                        <tr class="hover:bg-gray-50 text-sm">
                            <td class="px-4 py-3 text-gray-800">{{ $sub->customer_identifier ?? '-' }}</td>
                            <td class="px-4 py-3 text-gray-800">{{ $sub->product?->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $sub->transaction?->invoice_number ?? '-' }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $sub->start_date->format('d/m/Y') }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $sub->end_date->format('d/m/Y') }}</td>
                            <td class="px-4 py-3 font-bold {{ $daysLeft <= 0 ? 'text-red-600' : ($daysLeft <= 3 ? 'text-yellow-600' : 'text-green-600') }}">
                                {{ $daysLeft > 0 ? $daysLeft . ' hari' : 'Expired' }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 text-xs rounded-full {{ $sub->status == 'active' && $daysLeft > 0 ? 'bg-green-100 text-green-700' : ($sub->status == 'refunded' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-700') }}">
                                    {{ $sub->status }}@if($sub->status == 'active' && $daysLeft <= 0) (expired)@endif
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-8 text-center text-gray-400">Data tidak ditemukan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif
</div>
