<div>
    <h1 class="text-2xl font-bold text-gray-800 mb-4">Riwayat Aktivitas</h1>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-4">
        <input type="text" wire:model.live.debounce="search" placeholder="Cari aktivitas..." class="max-w-xs px-3 py-2 border border-gray-300 rounded-lg text-sm">
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full">
            <thead>
                <tr class="bg-gray-50 text-left text-sm text-gray-500">
                    <th class="px-4 py-3 font-medium">Waktu</th>
                    <th class="px-4 py-3 font-medium">User</th>
                    <th class="px-4 py-3 font-medium">Aksi</th>
                    <th class="px-4 py-3 font-medium">Keterangan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($logs as $log)
                    <tr class="hover:bg-gray-50 text-sm">
                        <td class="px-4 py-3 text-gray-500 whitespace-nowrap">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3 text-gray-800">{{ $log->user->name ?? 'System' }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-700">{{ $log->action }}</span>
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ $log->description ?? '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-8 text-center text-gray-400">Belum ada aktivitas.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4 border-t border-gray-100">{{ $logs->links() }}</div>
    </div>
</div>
