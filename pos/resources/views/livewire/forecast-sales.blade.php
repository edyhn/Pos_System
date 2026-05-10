<div>
    <h1 class="text-2xl font-bold text-gray-800 mb-4">Forecast Penjualan</h1>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
        <div class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs text-gray-500 mb-1">Rentang Data (hari)</label>
                <select wire:model.live="dateRange" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="30">30 Hari</option>
                    <option value="60">60 Hari</option>
                    <option value="90">90 Hari</option>
                    <option value="180">180 Hari</option>
                    <option value="365">1 Tahun</option>
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Periode MA</label>
                <select wire:model.live="period" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="3">3</option>
                    <option value="7">7</option>
                    <option value="14">14</option>
                    <option value="30">30</option>
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Alpha (SES)</label>
                <select wire:model.live="alpha" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="0.1">0.1 (halus)</option>
                    <option value="0.3">0.3</option>
                    <option value="0.5">0.5</option>
                    <option value="0.7">0.7 (responsif)</option>
                    <option value="0.9">0.9</option>
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Prediksi (hari)</label>
                <select wire:model.live="futureDays" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="7">7 Hari</option>
                    <option value="14">14 Hari</option>
                    <option value="30">30 Hari</option>
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Filter Produk</label>
                <select wire:model.live="productFilter" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="">Semua Produk</option>
                    @foreach ($products as $p)
                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs text-gray-500">Total Penjualan</p>
            <p class="text-lg font-bold text-gray-800">Rp {{ number_format($stats['total_sales'] ?? 0, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs text-gray-500">Rata-rata Harian</p>
            <p class="text-lg font-bold text-gray-800">Rp {{ number_format($stats['avg_daily'] ?? 0, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs text-gray-500">Tren</p>
            <p class="text-lg font-bold {{ ($stats['trend'] ?? 'stabil') == 'naik' ? 'text-green-600' : (($stats['trend'] ?? 'stabil') == 'turun' ? 'text-red-600' : 'text-gray-800') }}">
                {{ ucfirst($stats['trend'] ?? 'Stabil') }}
                @if(($stats['slope'] ?? 0) != 0)
                    ({{ $stats['slope'] > 0 ? '+' : '' }}{{ number_format($stats['slope'] ?? 0, 0, ',', '.') }}/hari)
                @endif
            </p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <p class="text-xs text-gray-500">Prediksi {{ $futureDays }} Hari</p>
            <p class="text-lg font-bold text-blue-600">Rp {{ number_format($stats['future_total'] ?? 0, 0, ',', '.') }}</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-6">
        <div class="flex gap-1 border-b border-gray-200 mb-4">
            <button wire:click="$set('activeTab', 'sma')" class="px-4 py-2 text-sm font-medium {{ $activeTab == 'sma' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-500 hover:text-gray-700' }}">
                Simple MA ({{ $period }})
            </button>
            <button wire:click="$set('activeTab', 'wma')" class="px-4 py-2 text-sm font-medium {{ $activeTab == 'wma' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-500 hover:text-gray-700' }}">
                Weighted MA ({{ $period }})
            </button>
            <button wire:click="$set('activeTab', 'ses')" class="px-4 py-2 text-sm font-medium {{ $activeTab == 'ses' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-500 hover:text-gray-700' }}">
                Exponential Smoothing
            </button>
            <button wire:click="$set('activeTab', 'regression')" class="px-4 py-2 text-sm font-medium {{ $activeTab == 'regression' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-500 hover:text-gray-700' }}">
                Regresi Linear
            </button>
            <button wire:click="$set('activeTab', 'seasonal')" class="px-4 py-2 text-sm font-medium {{ $activeTab == 'seasonal' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-500 hover:text-gray-700' }}">
                Seasonal (7 Hari)
            </button>
            <button wire:click="$set('activeTab', 'future')" class="px-4 py-2 text-sm font-medium {{ $activeTab == 'future' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-500 hover:text-gray-700' }}">
                Prediksi {{ $futureDays }} Hari
            </button>
        </div>

        @if($activeTab == 'sma')
            <div wire:ignore>
                <canvas data-chart='{!! $smaChartJson !!}'></canvas>
            </div>
            <p class="text-xs text-gray-400 mt-2">Simple Moving Average: rata-rata {{ $period }} hari terakhir untuk menghaluskan fluktuasi.</p>
        @endif

        @if($activeTab == 'wma')
            <div wire:ignore>
                <canvas data-chart='{!! $wmaChartJson !!}'></canvas>
            </div>
            <p class="text-xs text-gray-400 mt-2">Weighted Moving Average: memberi bobot lebih besar pada data terbaru.</p>
        @endif

        @if($activeTab == 'ses')
            <div wire:ignore>
                <canvas data-chart='{!! $sesChartJson !!}'></canvas>
            </div>
            <p class="text-xs text-gray-400 mt-2">Single Exponential Smoothing: alpha={{ $alpha }} — semakin tinggi alpha, semakin responsif terhadap perubahan.</p>
        @endif

        @if($activeTab == 'regression')
            <div wire:ignore>
                <canvas data-chart='{!! $regressionChartJson !!}'></canvas>
            </div>
            <p class="text-xs text-gray-400 mt-2">Regresi Linear: garis tren jangka panjang. Slope: {{ $stats['slope'] ?? 0 }} ({{ $stats['trend'] ?? 'stabil' }}).</p>
        @endif

        @if($activeTab == 'seasonal')
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div wire:ignore>
                    <canvas data-chart='{!! $seasonalChartJson !!}'></canvas>
                </div>
                <div class="flex flex-col justify-center">
                    <h3 class="font-semibold text-gray-700 mb-3">Pola Musiman 7 Hari</h3>
                    <div class="space-y-2">
                        @php $days = ['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu']; @endphp
                        @foreach($seasonalValues as $i => $val)
                            <div class="flex items-center gap-2">
                                <span class="w-16 text-sm text-gray-600">{{ $days[$i] ?? 'Hari ' . ($i+1) }}</span>
                                <div class="flex-1 bg-gray-100 rounded-full h-4">
                                    @php $maxVal = !empty($seasonalValues) ? max($seasonalValues) : 1; @endphp
                                    <div class="bg-blue-500 h-4 rounded-full" style="width: {{ ($val / $maxVal) * 100 }}%"></div>
                                </div>
                                <span class="text-sm font-medium text-gray-700 w-24 text-right">Rp {{ number_format($val, 0, ',', '.') }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <p class="text-xs text-gray-400 mt-2">Pola musiman per hari dalam seminggu berdasarkan rata-rata historis.</p>
        @endif

        @if($activeTab == 'future')
            <div wire:ignore>
                <canvas data-chart='{!! $futureChartJson !!}'></canvas>
            </div>
            <div class="mt-4 grid grid-cols-2 md:grid-cols-7 gap-2">
                @foreach($futureValues as $i => $val)
                    <div class="bg-gray-50 rounded-lg p-2 text-center">
                        <p class="text-xs text-gray-500">{{ $forecastLabels[$i] ?? 'Hari ' . ($i+1) }}</p>
                        <p class="text-sm font-bold text-blue-600">Rp {{ number_format($val, 0, ',', '.') }}</p>
                    </div>
                @endforeach
            </div>
            <p class="text-xs text-gray-400 mt-2">Prediksi {{ $futureDays }} hari ke depan berdasarkan SMA + tren regresi linear.</p>
        @endif
    </div>
</div>
