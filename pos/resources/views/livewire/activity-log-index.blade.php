<div>
    <h1 class="text-2xl font-bold text-gray-800 mb-4">Riwayat Aktivitas</h1>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-4">
        <div class="flex flex-wrap gap-2">
            <input type="text" wire:model.live.debounce="search" placeholder="Cari aktivitas..." class="px-3 py-2 border border-gray-300 rounded-lg text-sm flex-1 min-w-[150px]">
            <select wire:model.live="filterUser" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                <option value="">Semua User</option>
                @foreach ($users as $u)
                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                @endforeach
            </select>
            <select wire:model.live="filterAction" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                <option value="">Semua Aksi</option>
                <option value="create">Create</option>
                <option value="update">Update</option>
                <option value="delete">Delete</option>
            </select>
            <select wire:model.live="filterType" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                @foreach ($typeOptions as $val => $label)
                    <option value="{{ $val }}">{{ $label }}</option>
                @endforeach
            </select>
            <input type="date" wire:model.live="dateFrom" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
            <input type="date" wire:model.live="dateTo" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
        </div>
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
                            <span class="px-2 py-1 text-xs rounded-full
                                @if($log->action == 'create') bg-green-100 text-green-700
                                @elseif($log->action == 'update') bg-blue-100 text-blue-700
                                @elseif($log->action == 'delete') bg-red-100 text-red-700
                                @else bg-gray-100 text-gray-700 @endif">
                                {{ $log->action }}
                            </span>
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
