<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Pajak</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #333; margin: 20px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #7C3AED; padding-bottom: 15px; }
        .header h1 { font-size: 18px; margin: 0 0 5px; color: #1F2937; }
        .header p { margin: 2px 0; color: #6B7280; font-size: 11px; }
        .header .date { color: #9CA3AF; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background: #7C3AED; color: white; font-weight: bold; padding: 8px 6px; text-align: left; font-size: 10px; text-transform: uppercase; }
        td { border: 1px solid #E5E7EB; padding: 6px; }
        tr:nth-child(even) { background: #F9FAFB; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .summary { margin-top: 20px; border-top: 2px solid #7C3AED; padding-top: 10px; display: flex; justify-content: space-between; }
        .summary-box { background: #F9FAFB; border: 1px solid #E5E7EB; border-radius: 8px; padding: 12px 20px; text-align: center; flex: 1; margin: 0 5px; }
        .summary-box .label { font-size: 10px; color: #6B7280; margin: 0; }
        .summary-box .value { font-size: 16px; font-weight: bold; margin: 5px 0 0; }
        .summary-box .value.purple { color: #7C3AED; }
        .summary-box .value.green { color: #059669; }
        .summary-box .value.blue { color: #3B82F6; }
        .footer { text-align: center; margin-top: 30px; color: #9CA3AF; font-size: 9px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN PAJAK (PPN)</h1>
        <p>{{ $store?->name ?? 'Toko' }}{{ $store?->address ? ' | ' . $store->address : '' }}{{ $store?->phone ? ' | Telp: ' . $store->phone : '' }}</p>
        <p>Periode: {{ $dateFrom ?: 'Awal' }} - {{ $dateTo ?: 'Akhir' }}</p>
        <p class="date">Tanggal cetak: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    @php
        $totalTrans = count($transactions);
        $totalTax = $transactions->sum('tax_amount');
        $totalSales = $transactions->sum('total_amount');
    @endphp

    <div class="summary">
        <div class="summary-box">
            <p class="label">Transaksi Kena Pajak</p>
            <p class="value blue">{{ $totalTrans }}</p>
        </div>
        <div class="summary-box">
            <p class="label">Total Pajak (PPN)</p>
            <p class="value purple">Rp {{ number_format($totalTax, 0, ',', '.') }}</p>
        </div>
        <div class="summary-box">
            <p class="label">Total Penjualan</p>
            <p class="value green">Rp {{ number_format($totalSales, 0, ',', '.') }}</p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Invoice</th>
                <th>Tanggal</th>
                <th>Customer</th>
                <th class="text-right">Subtotal</th>
                <th class="text-right">Tarif Pajak</th>
                <th class="text-right">Pajak</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($transactions as $t)
            <tr>
                <td>{{ $t->invoice_number }}</td>
                <td>{{ $t->created_at->format('d/m/Y H:i') }}</td>
                <td>{{ $t->customer_name ?: '-' }}</td>
                <td class="text-right">Rp {{ number_format($t->subtotal, 0, ',', '.') }}</td>
                <td class="text-right">{{ $t->items->first()?->product?->tax_rate ?? 11 }}%</td>
                <td class="text-right">Rp {{ number_format($t->tax_amount, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($t->total_amount, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr><td colspan="7" style="text-align: center; padding: 20px; color: #999;">Tidak ada data</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Dicetak dari {{ config('app.name') }} | {{ now()->format('d/m/Y H:i') }}
    </div>
</body>
</html>
