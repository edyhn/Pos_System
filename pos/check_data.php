<?php
$db = new SQLite3(__DIR__ . '/database/database.sqlite');

echo "=== PRODUCTS (store=1, active) ===\n";
$res = $db->query('SELECT id, name, stock, min_stock, store_id, is_active FROM products WHERE store_id = 1 AND is_active = 1');
while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
    echo json_encode($row) . "\n";
}

echo "\n=== COMPLETED TRANSACTIONS (last 30 days) ===\n";
$res = $db->query("SELECT COUNT(*) as cnt FROM transactions WHERE store_id = 1 AND status = 'completed' AND date(created_at) >= date('now', '-30 days')");
echo json_encode($res->fetchArray(SQLITE3_ASSOC)) . "\n";

echo "\n=== TRANSACTION ITEMS (store=1, completed, last 30 days) ===\n";
$res = $db->query("SELECT ti.product_id, SUM(ti.quantity) as qty FROM transaction_items ti JOIN transactions t ON t.id = ti.transaction_id WHERE t.store_id = 1 AND t.status = 'completed' AND date(t.created_at) >= date('now', '-30 days') GROUP BY ti.product_id");
while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
    echo json_encode($row) . "\n";
}

echo "\n=== PURCHASE ORDERS (auto draft) ===\n";
$res = $db->query("SELECT id, po_number, vendor_id, status, is_auto_draft FROM purchase_orders WHERE store_id = 1");
while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
    echo json_encode($row) . "\n";
}

echo "\n=== PO ITEMS ===\n";
$res = $db->query("SELECT pi.id, pi.purchase_order_id, pi.product_id, pi.quantity FROM po_items pi JOIN purchase_orders po ON po.id = pi.purchase_order_id WHERE po.store_id = 1");
while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
    echo json_encode($row) . "\n";
}
