<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Penjualan</title>
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
        .summary-section { margin-top: 20px; border-top: 2px solid #3B82F6; padding-top: 12px; }
        .summary-title { font-size: 12px; font-weight: bold; color: #1F2937; margin: 0 0 10px; text-transform: uppercase; letter-spacing: 1px; }
        .summary-table { width: 100%; border-collapse: collapse; }
        .summary-table td { border: none; padding: 5px 8px; font-size: 11px; }
        .summary-table .label { color: #6B7280; width: 60%; }
        .summary-table .value { font-weight: bold; color: #1F2937; text-align: right; width: 40%; }
        .summary-table .divider td { border-bottom: 1px solid #E5E7EB; padding: 0; }
        .footer { text-align: center; margin-top: 30px; color: #9CA3AF; font-size: 9px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN PENJUALAN</h1>
        <p>{{ $store?->name ?? 'Toko' }}{{ $store?->address ? ' | ' . $store->address : '' }}{{ $store?->phone ? ' | Telp: ' . $store->phone : '' }}</p>
        <p>Periode: {{ $dateFrom ?: 'Awal' }} - {{ $dateTo ?: 'Akhir' }}</p>
        <p class="date">Tanggal cetak: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    @php
        $totalTrans = count($transactions);
        $totalRevenue = $transactions->sum('total_amount');
        $totalTax = $transactions->sum('tax_amount');
        $avgPerTrans = $totalTrans > 0 ? $totalRevenue / $totalTrans : 0;
    @endphp

    <div class="summary-section">
        <h3 class="summary-title">Ringkasan Penjualan</h3>
        <table class="summary-table">
            <tr>
                <td class="label">Total Transaksi</td>
                <td class="value">{{ $totalTrans }}</td>
            </tr>
            <tr class="divider"><td colspan="2"></td></tr>
            <tr>
                <td class="label">Total Pendapatan</td>
                <td class="value">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</td>
            </tr>
            <tr class="divider"><td colspan="2"></td></tr>
            <tr>
                <td class="label">Total Pajak (PPN)</td>
                <td class="value">Rp {{ number_format($totalTax, 0, ',', '.') }}</td>
            </tr>
            <tr class="divider"><td colspan="2"></td></tr>
            <tr>
                <td class="label">Rata-rata per Transaksi</td>
                <td class="value">Rp {{ number_format($avgPerTrans, 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    <table>
        <thead>
            <tr>
                <th>Invoice</th>
                <th>Tanggal</th>
                <th>Customer</th>
                <th class="text-right">Subtotal</th>
                <th class="text-right">Pajak</th>
                <th class="text-right">Total</th>
                <th>Metode</th>
                <th>Kasir</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($transactions as $t)
            <tr>
                <td>{{ $t->invoice_number }}</td>
                <td>{{ $t->created_at->format('d/m/Y H:i') }}</td>
                <td>{{ $t->customer_name ?: '-' }}</td>
                <td class="text-right">Rp {{ number_format($t->subtotal, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($t->tax_amount, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($t->total_amount, 0, ',', '.') }}</td>
                <td>{{ $t->payment_method }}</td>
                <td>{{ $t->user?->name ?: '-' }}</td>
            </tr>
            @empty
            <tr><td colspan="8" style="text-align: center; padding: 20px; color: #999;">Tidak ada data</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Dicetak dari {{ config('app.name') }} | {{ now()->format('d/m/Y H:i') }}
    </div>
</body>
</html>
