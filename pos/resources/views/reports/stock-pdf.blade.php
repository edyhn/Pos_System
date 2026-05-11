<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Stok</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #333; margin: 20px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #3B82F6; padding-bottom: 15px; }
        .header h1 { font-size: 18px; margin: 0 0 5px; color: #1F2937; }
        .header p { margin: 2px 0; color: #6B7280; font-size: 11px; }
        .header .date { color: #9CA3AF; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background: #3B82F6; color: white; font-weight: bold; padding: 8px 6px; text-align: left; font-size: 10px; text-transform: uppercase; }
        td { border: 1px solid #E5E7EB; padding: 6px; }
        tr:nth-child(even) { background: #F9FAFB; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .summary { margin-top: 20px; border-top: 2px solid #3B82F6; padding-top: 10px; }
        .summary table { width: auto; margin-left: auto; }
        .summary td { border: none; padding: 3px 10px; font-size: 12px; }
        .summary .label { font-weight: bold; text-align: right; }
        .summary .value { font-weight: bold; color: #3B82F6; text-align: right; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 10px; }
        .badge-danger { background: #FEE2E2; color: #DC2626; }
        .badge-success { background: #D1FAE5; color: #059669; }
        .footer { text-align: center; margin-top: 30px; color: #9CA3AF; font-size: 9px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN STOK PRODUK</h1>
        <p>{{ $store->name ?? 'Toko' }}{{ $store->address ? ' | ' . $store->address : '' }}{{ $store->phone ? ' | Telp: ' . $store->phone : '' }}</p>
        <p class="date">Tanggal cetak: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Produk</th>
                <th>SKU</th>
                <th>Kategori</th>
                <th class="text-right">Stok</th>
                <th class="text-right">Min Stok</th>
                <th class="text-right">Harga</th>
                <th class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($products as $p)
            <tr>
                <td>{{ $p->name }}</td>
                <td>{{ $p->sku ?: '-' }}</td>
                <td>{{ $p->category?->name ?: '-' }}</td>
                <td class="text-right">{{ $p->stock }}</td>
                <td class="text-right">{{ $p->min_stock }}</td>
                <td class="text-right">Rp {{ number_format($p->price, 0, ',', '.') }}</td>
                <td class="text-center">
                    <span class="badge {{ $p->isLowStock() ? 'badge-danger' : 'badge-success' }}">
                        {{ $p->isLowStock() ? 'Stok Minim' : 'Normal' }}
                    </span>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" style="text-align: center; padding: 20px; color: #999;">Tidak ada produk</td></tr>
            @endforelse
        </tbody>
    </table>

    @if(count($products) > 0)
    <div class="summary">
        <table>
            <tr><td class="label">Total Produk:</td><td class="value">{{ count($products) }}</td></tr>
            @php $lowStockCount = $products->filter(fn($p) => $p->isLowStock())->count(); @endphp
            <tr><td class="label">Produk Stok Minim:</td><td class="value" style="color: #DC2626;">{{ $lowStockCount }}</td></tr>
            @php $totalValue = $products->sum(fn($p) => $p->stock * $p->price); @endphp
            <tr><td class="label">Total Nilai Stok:</td><td class="value">Rp {{ number_format($totalValue, 0, ',', '.') }}</td></tr>
        </table>
    </div>
    @endif

    <div class="footer">
        Dicetak dari {{ config('app.name') }} | {{ now()->format('d/m/Y H:i') }}
    </div>
</body>
</html>
