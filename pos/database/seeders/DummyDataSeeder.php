<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\PurchaseOrder;
use App\Models\PoItem;
use App\Models\StockMovement;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        $store1 = Store::firstOrCreate(
            ['slug' => 'toko-pusat'],
            [
                'name' => 'Toko Pusat',
                'code' => 'PST',
                'address' => 'Jl. Merdeka No. 10, Jakarta',
                'phone' => '021-12345678',
                'receipt_footer' => 'Terima kasih telah berbelanja di Toko Pusat',
                'is_active' => true,
            ]
        );

        $store2 = Store::firstOrCreate(
            ['slug' => 'toko-cabang'],
            [
                'name' => 'Toko Cabang',
                'code' => 'CBG',
                'address' => 'Jl. Sudirman No. 25, Bandung',
                'phone' => '022-87654321',
                'receipt_footer' => 'Terima kasih telah berbelanja di Toko Cabang',
                'is_active' => true,
            ]
        );

        User::firstOrCreate(
            ['email' => 'owner@pos.test'],
            [
                'store_id' => $store1->id,
                'user_id' => 'OWN001',
                'name' => 'Pemilik Utama',
                'password' => Hash::make('owner123'),
                'phone' => '081234567890',
                'role' => 'owner',
                'is_active' => true,
            ]
        );

        User::firstOrCreate(
            ['email' => 'kasir1@pos.test'],
            [
                'store_id' => $store1->id,
                'user_id' => 'KSR001',
                'name' => 'Kasir Pusat',
                'password' => Hash::make('kasir123'),
                'phone' => '081234567891',
                'role' => 'cashier',
                'is_active' => true,
            ]
        );

        User::firstOrCreate(
            ['email' => 'kasir2@pos.test'],
            [
                'store_id' => $store2->id,
                'user_id' => 'KSR002',
                'name' => 'Kasir Cabang',
                'password' => Hash::make('kasir123'),
                'phone' => '081234567892',
                'role' => 'cashier',
                'is_active' => true,
            ]
        );

        User::firstOrCreate(
            ['email' => 'owner2@pos.test'],
            [
                'store_id' => null,
                'user_id' => 'OWN002',
                'name' => 'Pemilik Cadangan',
                'password' => Hash::make('owner123'),
                'phone' => '081234567893',
                'role' => 'owner',
                'is_active' => false,
            ]
        );

        $catPulsa = Category::firstOrCreate(
            ['slug' => 'pulsa-dan-kuota', 'store_id' => $store1->id],
            [
                'name' => 'Pulsa & Kuota',
                'description' => 'Produk pulsa dan paket data',
            ]
        );

        $catMakanan = Category::firstOrCreate(
            ['slug' => 'makanan-ringan', 'store_id' => $store1->id],
            [
                'name' => 'Makanan Ringan',
                'description' => 'Camilan dan makanan ringan',
            ]
        );

        $catMinuman = Category::firstOrCreate(
            ['slug' => 'minuman', 'store_id' => $store1->id],
            [
                'name' => 'Minuman',
                'description' => 'Minuman kemasan dan botol',
            ]
        );

        $catListrik = Category::firstOrCreate(
            ['slug' => 'token-listrik', 'store_id' => $store2->id],
            [
                'name' => 'Token Listrik',
                'description' => 'Token listrik PLN',
            ]
        );

        $catPulsa2 = Category::firstOrCreate(
            ['slug' => 'pulsa', 'store_id' => $store2->id],
            [
                'name' => 'Pulsa',
                'description' => 'Pulsa all operator',
            ]
        );

        $productsStore1 = [
            ['Paket Internet 30 Hari', 'INT-30', 150000, 100000, 100, 10, true, 11, true, 30],
            ['Paket Internet 7 Hari', 'INT-7', 50000, 35000, 50, 5, false, 0, true, 7],
            ['Pulsa Telkomsel 10K', 'PLS-010', 11000, 10000, 200, 20, false, 0, false, null],
            ['Pulsa Telkomsel 20K', 'PLS-020', 21000, 20000, 150, 15, false, 0, false, null],
            ['Pulsa Indosat 10K', 'PLS-I10', 10500, 10000, 180, 18, false, 0, false, null],
            ['Kuota 1GB', 'KTA-01', 15000, 12000, 300, 30, false, 0, false, null],
            ['Kuota 5GB', 'KTA-05', 50000, 40000, 200, 20, true, 11, false, null],
            ['Keripik Singkong', 'MKN-001', 5000, 3000, 500, 50, false, 0, false, null],
            ['Cokelat Batang', 'MKN-002', 12000, 8000, 300, 30, false, 0, false, null],
            ['Biskuit', 'MKN-003', 8000, 5000, 400, 40, false, 0, false, null],
            ['Air Mineral 600ml', 'MNM-001', 3000, 2000, 1000, 100, false, 0, false, null],
            ['Teh Botol', 'MNM-002', 5000, 3500, 600, 60, false, 0, false, null],
            ['Kopi Sachet', 'MNM-003', 2000, 1500, 800, 80, false, 0, false, null],
        ];

        foreach ($productsStore1 as $p) {
            Product::firstOrCreate(
                ['sku' => $p[1], 'store_id' => $store1->id],
                [
                    'category_id' => in_array($p[1], ['MKN-001', 'MKN-002', 'MKN-003']) ? $catMakanan->id : (in_array($p[1], ['MNM-001', 'MNM-002', 'MNM-003']) ? $catMinuman->id : $catPulsa->id),
                    'name' => $p[0],
                    'slug' => str()->slug($p[0]),
                    'price' => $p[2],
                    'cost_price' => $p[3],
                    'stock' => $p[4],
                    'min_stock' => $p[5],
                    'unit' => 'pcs',
                    'is_taxed' => $p[6],
                    'tax_rate' => $p[7],
                    'is_subscription' => $p[8],
                    'subscription_days' => $p[9] ?: 30,
                    'is_active' => true,
                ]
            );
        }

        $productsStore2 = [
            ['Token Listrik 20rb', 'TKN-020', 21000, 20000, 100, 10, false, 0, false, null],
            ['Token Listrik 50rb', 'TKN-050', 51000, 50000, 80, 8, false, 0, false, null],
            ['Token Listrik 100rb', 'TKN-100', 101000, 100000, 60, 6, false, 0, false, null],
            ['Pulsa XL 10K', 'PLS-X10', 10500, 10000, 120, 12, false, 0, false, null],
            ['Pulsa XL 20K', 'PLS-X20', 20500, 20000, 100, 10, false, 0, false, null],
            ['Pulsa Three 10K', 'PLS-T10', 10500, 10000, 90, 9, false, 0, false, null],
        ];

        foreach ($productsStore2 as $p) {
            Product::firstOrCreate(
                ['sku' => $p[1], 'store_id' => $store2->id],
                [
                    'category_id' => str_starts_with($p[1], 'TKN') ? $catListrik->id : $catPulsa2->id,
                    'name' => $p[0],
                    'slug' => str()->slug($p[0]),
                    'price' => $p[2],
                    'cost_price' => $p[3],
                    'stock' => $p[4],
                    'min_stock' => $p[5],
                    'unit' => 'pcs',
                    'is_taxed' => $p[6],
                    'tax_rate' => $p[7],
                    'is_subscription' => $p[8],
                    'subscription_days' => $p[9] ?: 30,
                    'is_active' => true,
                ]
            );
        }

        $vendor1 = Vendor::firstOrCreate(
            ['email' => 'budi@telkom.co.id'],
            [
                'store_id' => $store1->id,
                'name' => 'PT Telkom Indonesia',
                'contact_person' => 'Budi Santoso',
                'phone' => '081234567001',
                'address' => 'Jl. Gatot Subroto, Jakarta',
                'is_active' => true,
            ]
        );

        $vendor2 = Vendor::firstOrCreate(
            ['email' => 'siti@makananenak.co.id'],
            [
                'store_id' => $store1->id,
                'name' => 'CV Makanan Enak',
                'contact_person' => 'Siti Rahayu',
                'phone' => '081234567002',
                'address' => 'Jl. Industri No. 5, Tangerang',
                'is_active' => true,
            ]
        );

        $vendor3 = Vendor::firstOrCreate(
            ['email' => 'agus@pln.co.id'],
            [
                'store_id' => $store2->id,
                'name' => 'PLN',
                'contact_person' => 'Agus Wijaya',
                'phone' => '081234567003',
                'address' => 'Jl. Listrik No. 1, Bandung',
                'is_active' => true,
            ]
        );

        $owner1 = User::where('user_id', 'OWN001')->first();

        $po1 = PurchaseOrder::firstOrCreate(
            ['po_number' => 'PO-20260501-001'],
            [
                'store_id' => $store1->id,
                'vendor_id' => $vendor1->id,
                'user_id' => $owner1->id,
                'status' => 'received',
                'notes' => 'PO pulsa bulan Mei',
            ]
        );

        $po2 = PurchaseOrder::firstOrCreate(
            ['po_number' => 'PO-20260501-002'],
            [
                'store_id' => $store1->id,
                'vendor_id' => $vendor2->id,
                'user_id' => $owner1->id,
                'status' => 'sent',
                'notes' => 'PO makanan ringan',
            ]
        );

        $pulsaProducts = Product::where('store_id', $store1->id)->where('sku', 'PLS-010')->first();
        if ($pulsaProducts) {
            PoItem::firstOrCreate(
                ['purchase_order_id' => $po1->id, 'product_id' => $pulsaProducts->id],
                [
                    'quantity' => 100,
                    'price' => 10000,
                    'subtotal' => 1000000,
                ]
            );
        }

        Product::where('store_id', $store1->id)->chunk(5, function ($products) use ($store1, $owner1) {
            foreach ($products as $product) {
                StockMovement::firstOrCreate(
                    ['store_id' => $store1->id, 'product_id' => $product->id, 'type' => 'in', 'reference_type' => 'initial'],
                    [
                        'user_id' => $owner1->id,
                        'quantity' => $product->stock,
                        'note' => 'Stok awal',
                    ]
                );
            }
        });

        Product::where('store_id', $store2->id)->chunk(5, function ($products) use ($store2, $owner1) {
            foreach ($products as $product) {
                StockMovement::firstOrCreate(
                    ['store_id' => $store2->id, 'product_id' => $product->id, 'type' => 'in', 'reference_type' => 'initial'],
                    [
                        'user_id' => $owner1->id,
                        'quantity' => $product->stock,
                        'note' => 'Stok awal',
                    ]
                );
            }
        });

        $this->command?->info('Dummy data seeded successfully!');
        $this->command?->info('Owner: OWN001 / owner123');
        $this->command?->info('Cashier Pusat: KSR001 / kasir123');
        $this->command?->info('Cashier Cabang: KSR002 / kasir123');
    }
}
