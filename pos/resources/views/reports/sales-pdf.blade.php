<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>Laporan Penjualan</title></head>
<body style="font-family: sans-serif; font-size: 12px;">
    <h2 style="text-align: center; margin-bottom: 5px;">Laporan Penjualan</h2>
    <p style="text-align: center; margin-top: 0; color: #666;">{{ $dateFrom ?: 'Awal' }} - {{ $dateTo ?: 'Akhir' }}</p>
    <table style="width: 100%; border-collapse: collapse; margin-top: 15px;">
        <thead>
            <tr style="background: #f3f4f6;">
                <th style="border: 1px solid #ddd; padding: 6px; text-align: left;">Invoice</th>
                <th style="border: 1px solid #ddd; padding: 6px; text-align: left;">Tanggal</th>
                <th style="border: 1px solid #ddd; padding: 6px; text-align: left;">Customer</th>
                <th style="border: 1px solid #ddd; padding: 6px; text-align: right;">Total</th>
                <th style="border: 1px solid #ddd; padding: 6px; text-align: left;">Metode</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($transactions as $t)
            <tr>
                <td style="border: 1px solid #ddd; padding: 6px;">{{ $t->invoice_number }}</td>
                <td style="border: 1px solid #ddd; padding: 6px;">{{ $t->created_at->format('d/m/Y') }}</td>
                <td style="border: 1px solid #ddd; padding: 6px;">{{ $t->customer_name ?: '-' }}</td>
                <td style="border: 1px solid #ddd; padding: 6px; text-align: right;">Rp {{ number_format($t->total_amount, 0, ',', '.') }}</td>
                <td style="border: 1px solid #ddd; padding: 6px;">{{ $t->payment_method }}</td>
            </tr>
            @empty
            <tr><td colspan="5" style="text-align: center; padding: 20px; color: #999;">Tidak ada data</td></tr>
            @endforelse
        </tbody>
    </table>
    @if(count($transactions) > 0)
    <div style="margin-top: 15px; text-align: right;">
        <strong>Total Transaksi:</strong> {{ count($transactions) }}<br>
        <strong>Total Pendapatan:</strong> Rp {{ number_format($transactions->sum('total_amount'), 0, ',', '.') }}
    </div>
    @endif
</body>
</html>
