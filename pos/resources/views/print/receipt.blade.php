<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Struk {{ $transaction->invoice_number }}</title>
    <style>
        @page { margin: 0; size: 80mm auto; }
        body { font-family: 'Courier New', monospace; font-size: 12px; width: 80mm; margin: 0 auto; padding: 10px 5px; }
        .header { text-align: center; margin-bottom: 10px; }
        .header h2 { margin: 0; font-size: 16px; }
        .header p { margin: 2px 0; font-size: 10px; }
        .divider { border-top: 1px dashed #000; margin: 6px 0; }
        .items { width: 100%; }
        .items td { padding: 2px 0; }
        .items td:last-child { text-align: right; }
        .total { font-weight: bold; font-size: 14px; }
        .footer { text-align: center; margin-top: 10px; font-size: 10px; }
    </style>
</head>
<body>
    @php $store = $transaction->store; @endphp
    <div class="header">
        <h2>{{ $store?->name ?? 'Toko' }}</h2>
        @if($store?->address) <p>{{ $store->address }}</p> @endif
        @if($store?->phone) <p>Telp: {{ $store->phone }}</p> @endif
        <p>Invoice: {{ $transaction->invoice_number }}</p>
        <p>{{ $transaction->created_at->format('d/m/Y H:i') }}</p>
        <p>Kasir: {{ $transaction->user?->name ?? '-' }}</p>
        @if($transaction->customer_name)
            <p>Customer: {{ $transaction->customer_name }}</p>
        @endif
    </div>

    <div class="divider"></div>

    <table class="items">
        <thead>
            <tr>
                <td><strong>Item</strong></td>
                <td><strong>Qty</strong></td>
                <td><strong>Harga</strong></td>
            </tr>
        </thead>
        <tbody>
            @foreach($transaction->items as $item)
                <tr>
                    <td>{{ $item->product_name }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="divider"></div>

    <table style="width:100%">
        <tr><td>Subtotal</td><td style="text-align:right">Rp {{ number_format($transaction->subtotal, 0, ',', '.') }}</td></tr>
        @if($transaction->tax_amount > 0)
            <tr><td>Pajak</td><td style="text-align:right">Rp {{ number_format($transaction->tax_amount, 0, ',', '.') }}</td></tr>
        @endif
        <tr class="total"><td>Total</td><td style="text-align:right">Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</td></tr>
        <tr><td>Bayar ({{ strtoupper($transaction->payment_method) }})</td><td style="text-align:right">Rp {{ number_format($transaction->payment_amount, 0, ',', '.') }}</td></tr>
        @if($transaction->change_amount > 0)
            <tr><td>Kembali</td><td style="text-align:right">Rp {{ number_format($transaction->change_amount, 0, ',', '.') }}</td></tr>
        @endif
    </table>

    @if($store?->receipt_footer)
        <div class="footer">
            <div class="divider"></div>
            <p>{{ $store->receipt_footer }}</p>
        </div>
    @endif

    <script>
        window.print();
    </script>
</body>
</html>