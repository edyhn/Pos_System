<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>Laporan Stok</title></head>
<body style="font-family: sans-serif; font-size: 12px;">
    <h2 style="text-align: center;">Laporan Stok Produk</h2>
    <table style="width: 100%; border-collapse: collapse; margin-top: 15px;">
        <thead>
            <tr style="background: #f3f4f6;">
                <th style="border: 1px solid #ddd; padding: 6px; text-align: left;">Produk</th>
                <th style="border: 1px solid #ddd; padding: 6px; text-align: left;">SKU</th>
                <th style="border: 1px solid #ddd; padding: 6px; text-align: right;">Stok</th>
                <th style="border: 1px solid #ddd; padding: 6px; text-align: right;">Min Stok</th>
                <th style="border: 1px solid #ddd; padding: 6px; text-align: right;">Harga</th>
                <th style="border: 1px solid #ddd; padding: 6px; text-align: center;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($products as $p)
            <tr>
                <td style="border: 1px solid #ddd; padding: 6px;">{{ $p->name }}</td>
                <td style="border: 1px solid #ddd; padding: 6px;">{{ $p->sku ?: '-' }}</td>
                <td style="border: 1px solid #ddd; padding: 6px; text-align: right;">{{ $p->stock }}</td>
                <td style="border: 1px solid #ddd; padding: 6px; text-align: right;">{{ $p->min_stock }}</td>
                <td style="border: 1px solid #ddd; padding: 6px; text-align: right;">Rp {{ number_format($p->price, 0, ',', '.') }}</td>
                <td style="border: 1px solid #ddd; padding: 6px; text-align: center;">{{ $p->isLowStock() ? 'Stok Minim' : 'Normal' }}</td>
            </tr>
            @empty
            <tr><td colspan="6" style="text-align: center; padding: 20px; color: #999;">Tidak ada produk</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
