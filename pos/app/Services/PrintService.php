<?php

namespace App\Services;

use App\Models\StoreSetting;
use App\Models\Transaction;
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\PrintConnectors\UsbPrintConnector;

class PrintService
{
    public function printReceipt(Transaction $transaction): bool
    {
        $transaction->load('items', 'store', 'user');

        $storeId = $transaction->store_id;

        $settings = StoreSetting::where('store_id', $storeId)
            ->whereIn('key', ['printer_type', 'printer_address', 'printer_port'])
            ->get()
            ->keyBy('key');

        $printerType = $settings->get('printer_type')?->value;
        $printerAddress = $settings->get('printer_address')?->value;
        $printerPort = $settings->get('printer_port')?->value ?? '9100';

        try {
            $connector = match ($printerType) {
                'network' => new NetworkPrintConnector($printerAddress, (int) $printerPort),
                'usb' => new UsbPrintConnector(),
                default => throw new \Exception('Tipe printer tidak dikenal. Atur printer di Pengaturan Toko.'),
            };

            $printer = new Printer($connector);
            $this->buildReceipt($printer, $transaction);
            $printer->close();

            return true;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    private function buildReceipt(Printer $printer, Transaction $transaction): void
    {
        $printer->initialize();
        $printer->selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->text($transaction->store->name . "\n");
        $printer->selectPrintMode();
        $printer->setEmphasis(false);
        $printer->text($transaction->store->address . "\n");
        if ($transaction->store->phone) {
            $printer->text("Telp: " . $transaction->store->phone . "\n");
        }
        $printer->feed();
        $printer->text("Invoice: " . $transaction->invoice_number . "\n");
        $printer->text($transaction->created_at->format('d/m/Y H:i') . "\n");
        $printer->text("Kasir: " . $transaction->user->name . "\n");
        if ($transaction->customer_name) {
            $printer->text("Customer: " . $transaction->customer_name . "\n");
        }

        $printer->feed();
        $printer->setJustification(Printer::JUSTIFY_LEFT);
        $printer->text(str_repeat('-', 32) . "\n");

        $printer->setEmphasis(true);
        $printer->text(str_pad("Item", 16) . str_pad("Qty", 6) . "Harga\n");
        $printer->setEmphasis(false);
        $printer->text(str_repeat('-', 32) . "\n");

        foreach ($transaction->items as $item) {
            $name = mb_substr($item->product_name, 0, 16);
            $qty = str_pad((string) $item->quantity, 6, ' ', STR_PAD_LEFT);
            $price = "Rp" . number_format($item->subtotal, 0, ',', '.');
            $printer->text($name . "\n");
            $printer->text(str_pad($qty, 16, ' ', STR_PAD_LEFT) . " " . $price . "\n");
            if ($item->discount_amount > 0) {
                $discLabel = $item->discount_name ? "Diskon ({$item->discount_name})" : "Diskon";
                $printer->text("  $discLabel: -Rp" . number_format($item->discount_amount, 0, ',', '.') . "\n");
            }
        }

        $printer->text(str_repeat('-', 32) . "\n");

        $printer->setJustification(Printer::JUSTIFY_RIGHT);
        $printer->text("Subtotal: Rp" . number_format($transaction->subtotal, 0, ',', '.') . "\n");
        if ($transaction->discount_amount > 0) {
            $printer->text("Diskon: -Rp" . number_format($transaction->discount_amount, 0, ',', '.') . "\n");
        }
        if ($transaction->tax_amount > 0) {
            $printer->text("Pajak: Rp" . number_format($transaction->tax_amount, 0, ',', '.') . "\n");
        }
        $printer->setEmphasis(true);
        $printer->selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
        $printer->text("Total: Rp" . number_format($transaction->total_amount, 0, ',', '.') . "\n");
        $printer->selectPrintMode();
        $printer->setEmphasis(false);
        $printer->text("Bayar (" . strtoupper($transaction->payment_method) . "): Rp" . number_format($transaction->payment_amount, 0, ',', '.') . "\n");
        if ($transaction->change_amount > 0) {
            $printer->text("Kembali: Rp" . number_format($transaction->change_amount, 0, ',', '.') . "\n");
        }

        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->feed(2);

        if ($transaction->store->receipt_footer) {
            $printer->text($transaction->store->receipt_footer . "\n");
        }

        $printer->feed(3);
        $printer->cut();
    }
}
