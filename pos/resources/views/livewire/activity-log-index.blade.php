<div>
    <div class="flex items-center gap-3 mb-6">
        <div class="w-10 h-10 rounded-xl bg-gray-100 flex items-center justify-center">
            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div>
            <h1 class="text-lg font-bold text-gray-900">Riwayat Aktivitas</h1>
            <p class="text-sm text-gray-500">Log aktivitas pengguna dalam sistem</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-4">
        <div class="flex flex-wrap gap-3">
            <div class="relative flex-1 min-w-[180px]">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" wire:model.live.debounce="search" placeholder="Cari aktivitas..." class="w-full pl-9 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none bg-white">
            </div>
            <select wire:model.live="filterUser" class="px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white min-w-[130px]">
                <option value="">Semua User</option>
                @foreach ($users as $u)
                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                @endforeach
            </select>
            <select wire:model.live="filterAction" class="px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white">
                <option value="">Semua Aksi</option>
                <option value="create">Create</option>
                <option value="update">Update</option>
                <option value="delete">Delete</option>
            </select>
            <select wire:model.live="filterType" class="px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white">
                @foreach ($typeOptions as $val => $label)
                    <option value="{{ $val }}">{{ $label }}</option>
                @endforeach
            </select>
            <div>
                <label class="block text-[10px] font-medium text-gray-500 mb-0.5">Dari</label>
                <input type="date" wire:model.live="dateFrom" class="px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white">
            </div>
            <div>
                <label class="block text-[10px] font-medium text-gray-500 mb-0.5">Sampai</label>
                <input type="date" wire:model.live="dateTo" class="px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white">
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        <th class="px-4 py-3.5">Waktu</th>
                        <th class="px-4 py-3.5">User</th>
                        <th class="px-4 py-3.5">Aksi</th>
                        <th class="px-4 py-3.5">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($logs as $log)
                        <tr class="hover:bg-gray-50 text-sm transition">
                            <td class="px-4 py-3.5 text-gray-500 whitespace-nowrap text-xs">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-3.5">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-full bg-gradient-to-br from-gray-50 to-gray-100 border border-gray-200 flex items-center justify-center text-gray-500 font-bold text-[10px]">
                                        {{ substr($log->user->name ?? 'S', 0, 1) }}
                                    </div>
                                    <span class="text-gray-800">{{ $log->user->name ?? 'System' }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-full
                                    @if($log->action == 'create') bg-emerald-50 text-emerald-700
                                    @elseif($log->action == 'update') bg-blue-50 text-blue-700
                                    @elseif($log->action == 'delete') bg-red-50 text-red-700
                                    @else bg-gray-50 text-gray-700 @endif">
                                    <span class="w-1.5 h-1.5 rounded-full
                                        @if($log->action == 'create') bg-emerald-500
                                        @elseif($log->action == 'update') bg-blue-500
                                        @elseif($log->action == 'delete') bg-red-500
                                        @else bg-gray-500 @endif">
                                    </span>
                                    {{ $log->action }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-gray-600 max-w-md truncate">{{ $log->description ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-12 text-center">
                                <div class="flex flex-col items-center text-gray-400">
                                    <svg class="w-12 h-12 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <p class="text-sm font-medium">Belum ada aktivitas</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-gray-100 bg-gray-50/50">
            {{ $logs->links() }}
        </div>
    </div>
</div>
