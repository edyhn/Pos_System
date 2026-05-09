<div>
    <h1 class="text-2xl font-bold text-gray-800 mb-4">Approval Cetak Ulang Struk</h1>

    @if (session('message'))
        <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-green-700 text-sm">{{ session('message') }}</div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <table class="w-full">
            <thead>
                <tr class="bg-gray-50 text-left text-sm text-gray-500">
                    <th class="px-4 py-3 font-medium">Invoice</th>
                    <th class="px-4 py-3 font-medium">Diminta Oleh</th>
                    <th class="px-4 py-3 font-medium">Alasan</th>
                    <th class="px-4 py-3 font-medium">Tanggal</th>
                    <th class="px-4 py-3 font-medium">Status</th>
                    <th class="px-4 py-3 font-medium text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($requests as $req)
                    <tr class="hover:bg-gray-50 text-sm">
                        <td class="px-4 py-3 font-medium text-gray-800">{{ $req->transaction->invoice_number }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $req->user->name }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $req->reason ?? '-' }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $req->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 text-xs rounded-full
                                @if($req->status == 'pending') bg-yellow-100 text-yellow-700
                                @elseif($req->status == 'approved') bg-green-100 text-green-700
                                @else bg-red-100 text-red-700 @endif">
                                {{ $req->status }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right space-x-2">
                            @if($req->status == 'pending')
                                <button wire:click="approve({{ $req->id }})" class="text-green-600 hover:text-green-800">Setuju</button>
                                <button wire:click="reject({{ $req->id }})" class="text-red-600 hover:text-red-800">Tolak</button>
                            @endif
                            <a href="{{ route('print.receipt', $req->transaction_id) }}" target="_blank" class="text-blue-600 hover:text-blue-800">Cetak</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">Belum ada request.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4 border-t border-gray-100">{{ $requests->links() }}</div>
    </div>
</div>
