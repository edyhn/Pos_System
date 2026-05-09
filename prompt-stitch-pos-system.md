# Prompt Google Stitch — POS System (Lengkap 35 Halaman)

Dibuat berdasarkan analisis kode: 65 Blade views, 27 Livewire components, 17 Models, 23 Controllers, 3 Export files.

---

## A. AUTH & LAYOUT

### 1. Login Page
```
Buat halaman login untuk POS System dengan desain modern profesional.
- Background: gradient subtle dari biru ke putih, dengan ilustrasi POS/kasir di sisi kiri (desktop)
- Form login di sisi kanan dengan card putih, shadow halus, border-radius 2xl
- Input: User ID (text field dengan icon user) dan Password (dengan toggle show/hide)
- Tombol "Login" biru solid (bg-blue-600) dengan hover effect, full width
- Judul "POS System" dengan subtitle "Masukkan User ID dan Password"
- Tampilkan error message di atas form jika login gagal (alert red)
- Desain responsive: di mobile form full width tanpa ilustrasi
- Font: Inter atau system sans-serif, bersih dan modern
```

### 2. Welcome / Landing Page
```
Buat halaman landing page sebelum login (untuk route /):
- Tampilan bersih dengan brand POS System
- Hero section: judul "POS System" (bold), subtitle "Sistem Kasir & Manajemen Toko"
- Ilustrasi atau icon POS/kasir di tengah (vector style, warna biru)
- Tombol "Masuk / Login" (bg-blue-600 text-white, rounded-lg) mengarah ke halaman login
- Jika sudah login: langsung redirect ke dashboard
- Footer: copyright "POS System" tahun sekarang
- Desain: modern, minimalis, dengan gradient bg atau pattern subtle
- Optional dark mode
```

### 3. Main Layout (Sidebar + Header)
```
Buat layout utama POS System dengan:
- Sidebar kiri width 260px, bg-white, border-right shadow-sm, min-h-screen, flex flex-col
- Sidebar header: logo + nama toko "POS System" (bold), badge "Cabang: Nama Toko"
- Menu sidebar dengan icon (emoji) dan label, hover bg-blue-50 text-blue-700, transition
- Grup menu dengan label grup uppercase kecil:
  • Dashboard: 🏠 Dashboard
  • Master Data (Owner): 📦 Produk, 📂 Kategori, 🏢 Vendor
  • Inventory (Owner): 📊 Stok Produk, 📋 Stock Opname, 📥 Barang Masuk, 📤 Barang Keluar
  • Pembelian (Owner): 📋 Purchase Order
  • Laporan (Owner): 📈 Penjualan, 💰 Pajak
  • Transaksi: 🛒 Kasir, 📄 Riwayat Transaksi, 🔍 Cek Langganan
  • Request (Cashier): 🖨️ Cetak Ulang, ↩️ Refund
  • Pengaturan (Owner): 👥 Pengguna, ✅ Approval Cetak Ulang, ✅ Approval Refund, ⚙️ Pengaturan Toko, 📋 Riwayat Aktivitas
- Active menu: bg-blue-50 text-blue-700 font-semibold, dengan strip biru di kiri
- Sidebar footer: avatar bulat (inisial/foto) + nama + role badge + tombol logout merah
- Main content area bg-gray-50, p-6, overflow-y-auto
- Top header bar: breadcrumb kiri, notification bell (livewire) + user info + logout kanan
- Responsive: hamburger button di mobile, sidebar slide
- Font: Inter/system sans-serif, clean professional
```

---

## B. DASHBOARD

### 4. Dashboard — Owner View
```
Buat dashboard untuk role Owner:
- 4 card statistik grid 1/2/4 kolom:
  (1) "Penjualan Hari Ini" — Rp amount (hijau), icon receipt
  (2) "Transaksi Hari Ini" — angka, icon shopping-cart
  (3) "Total Produk Aktif" — angka, icon box
  (4) "Pending Approvals" — angka, kuning jika >0, link ke halaman approval
- Setiap card: bg-white rounded-xl shadow-sm border p-5, hover effect
- Baris 2 grid 2 kolom:
  • Card "Penjualan 7 Hari" — line chart (Chart.js), borderColor blue, fill blue 0.1
  • Card "Penjualan Bulanan" — bar chart, backgroundColor green 0.7
  Setiap chart: canvas dalam card putih, responsive
- Baris 3 grid 2 kolom:
  • Card "Draft PO Menunggu" — list PO, setiap item: no PO, vendor, jumlah item, 
    link "Lihat semua PO", bg-gray-50 hover:bg-gray-100
  • Card "Stok Menipis" — list produk stok minim, setiap item: nama, SKU, 
    stock/min_stock, merah jika stock 0, orange jika di bawah min, bg-red-50
- Bagian "Cek Langganan Aktif" di bawah (include livewire component)
- Loading skeleton animation saat data dimuat
- Empty state: "Belum ada data" dengan icon
```

### 5. Dashboard — Cashier View
```
Buat dashboard untuk role Cashier:
- 3 card statistik grid 1/3 kolom:
  (1) "Penjualan Saya Hari Ini" — Rp amount (purple)
  (2) "Transaksi Saya Hari Ini" — angka
  (3) "Request Pending" — angka, kuning jika >0, link ke halaman request
- Card "Penjualan Saya 7 Hari" — line chart warna purple/violet, responsive
- Bagian "Cek Langganan Aktif" di bawah
- Desain: bg-white rounded-xl shadow-sm border p-5
- Lebih sederhana dari owner dashboard
```

---

## C. MASTER DATA

### 6. Products — List Page
```
Buat halaman daftar produk dengan:
- Header: judul "Produk" kiri, tombol "+ Tambah Produk" (bg-blue-600) kanan
- Search bar "Cari produk..." + dropdown filter kategori + checkbox "Tampilkan nonaktif"
- Table dalam white card rounded-xl shadow-sm border:
  Kolom: Nama (dengan badge purple "Langganan" jika subscription), 
  Kategori, SKU, Harga (right-align Rp), Stok (right-align, merah jika low stock),
  Status (toggle pill: Aktif=green, Nonaktif=red), Aksi (link Edit)
- Row hover: bg-gray-50, row low stock: bg-yellow-50
- Pagination di footer table (laravel-style links)
- Empty state: "Belum ada produk."
```

### 7. Products — Form (Create / Edit)
```
Buat halaman form produk:
- Header: "Tambah Produk" / "Edit Produk" + tombol back
- Layout 2 kolom desktop, 1 kolom mobile, dalam white card rounded-xl shadow-sm
- Kolom kiri: Nama (required), Kategori (dropdown), SKU, Barcode
- Kolom kanan: Harga Jual (Rp prefix), Harga Modal/Cost
- Baris penuh: Stok (number), Min Stok, Satuan/Unit
- Checkbox: "Kena Pajak" — jika dicentang, muncul field Tarif Pajak (%)
- Checkbox: "Produk Langganan" — jika dicentang, muncul field "Hari Langganan" (number)
- Textarea: Deskripsi
- File upload: Gambar (drag & drop area, preview image)
- Status toggle: Aktif / Nonaktif
- Tombol: "Simpan" (biru) dan "Batal" (abu-abu)
- Validasi: required fields, format harga number
- Label: text-sm font-medium text-gray-700
- Input: border rounded-lg focus:ring-2 focus:ring-blue-500
```

### 8. Products — Detail Page
```
Buat halaman detail produk:
- Header: nama produk (large) + badge status Aktif/Nonaktif + badge "Langganan X hr" jika subscription
- Info grid 2 kolom 3 baris:
  Baris 1: Kategori, SKU, Barcode
  Baris 2: Harga Jual (Rp), Harga Modal (Rp), Stok / Min Stok
  Baris 3: Satuan, Kena Pajak (Ya/Tidak + rate), Subscription Days (jika ada)
- Gambar produk preview besar (max-h-64 object-contain)
- Deskripsi produk
- Tab navigation (3 tab):
  Tab 1: "Riwayat Stok" — table: Tanggal, Tipe (Masuk/Keluar badge), Qty (+/- warna), Referensi, User, Catatan
  Tab 2: "Transaksi" — table: Invoice, Tanggal, Qty, Total
  Tab 3: "Stock Opname" — table: Tanggal Opname, Stok Sistem, Stok Fisik, Selisih
- Tombol: "Edit" (biru), "Kembali" (abu-abu)
- Empty state per tab: "Belum ada data"
```

### 9. Categories — List & Form
```
Buat halaman kategori:
- LIST: Header "Kategori" + tombol "+ Tambah Kategori"
- Card grid atau table: Nama, Jumlah Produk, Status (Aktif/Nonaktif pill), Aksi Edit
- CREATE/EDIT modal/drawer: Nama (required), Deskripsi (textarea), Status
- Konfirmasi hapus dengan modal (alert jika masih ada produk terkait)
- Search bar
- Empty state: "Belum ada kategori."
```

### 10. Vendors — List & Form
```
Buat halaman vendor/supplier:
- LIST: Header "Vendor" + tombol "+ Tambah Vendor"
- Table: Nama Vendor, Kontak Person, Telepon, Email, Alamat, Status (Aktif/Nonaktif), Aksi Edit
- Search bar + pagination
- FORM (modal/separate): Nama Vendor (required), Kontak Person, Telepon (required), 
  Email, Alamat (textarea), Catatan (textarea), Status Aktif/Nonaktif
- Empty state: "Belum ada vendor."
```

---

## D. USERS

### 11. Users — List & Form (Owner only)
```
Buat halaman manajemen pengguna:
- LIST: Header "Pengguna" + tombol "+ Tambah Pengguna"
- Table: User ID, Nama, Email, Telepon, Role (Owner=purple badge, Cashier=blue badge),
  Status (Aktif=green pill, Nonaktif=red pill), Aksi Edit
- Search bar
- FORM: User ID, Nama (required), Email, Telepon, 
  Password (input with toggle show/hide, strength indicator), 
  Role (dropdown: Owner/Cashier), Foto (upload), 
  Status Aktif, Toko (dropdown store jika multi-store)
```

---

## E. CASHIER / POS

### 12. Cashier / POS (Point of Sale)
```
Buat halaman kasir/POS layout split:
- KIRI (flex-1, overflow-y-auto):
  • Search bar + dropdown kategori filter di atas (flex gap-2, mb-3)
  • Grid produk 3 kolom: card produk = nama (truncate), harga (blue bold), 
    stok (text-xs gray), badge "Langganan X hr" (purple) jika subscription
  • Produk habis (stock <= 0): opacity-50, cursor not-allowed
  • Hover: border-blue-300 shadow-sm transition
  • Scroll area: h-[calc(100vh-8rem)]

- KANAN (w-96, bg-white rounded-xl shadow-sm border, flex flex-col):
  • Header "Keranjang" (p-4 border-bottom)
  • Alert messages (success/error) dari session
  • List item keranjang (overflow-y-auto, p-3 space-y-2):
    Setiap item: nama, harga satuan, qty (- button | angka | + button), 
    subtotal right-align, tombol hapus (× merah)
    Qty controls: w-6 h-6 bg-gray-200 rounded hover:bg-gray-300
  • Input "Nama customer" (optional, px-3 py-2 border rounded-lg)
  • Checkbox "Pajak PPN" (toggle tax_enabled)
  • Summary: Subtotal, Pajak (jika enabled), Total (bold, border-top)
  • Dropdown metode bayar: Tunai, QRIS, Transfer Bank, Kartu Debit, Midtrans
  • Jika Tunai: input "Jumlah Bayar" + hitung "Kembalian" (text-green-600 bold)
  • Jika QRIS: tampilkan gambar QRIS dari Storage (jika ada)
    Jika belum upload: text "QRIS belum diupload. Atur di Pengaturan Toko."
  • Jika Midtrans: proses via Snap popup (trigger JS)
  • Tombol "Bayar" (bg-blue-600, full width, disabled jika cart kosong)

- Setelah bayar:
  • Auto-open window print struk di tab baru (/print/receipt/{id})
  • Loading state saat checkout
  • Validasi: bayar >= total, cart tidak kosong
  • Midtrans: popup Snap muncul, handle onSuccess/onPending/onError/onClose
```

### 13. Midtrans Payment Flow
```
Buat tampilan alur pembayaran Midtrans:
- Loading state: overlay/spinner saat memproses token Midtrans
- Midtrans Snap popup otomatis muncul (snap.pay via snap.js CDN)
- Success: dispatch event completeMidtransPayment, simpan transaksi,
  tampilkan alert hijau "Pembayaran berhasil! Invoice: ...", auto-print struk
- Pending: alert info "Pembayaran sedang diproses. Silakan tunggu konfirmasi."
- Error: alert merah "Pembayaran gagal. Silakan coba lagi."
- Close: popup ditutup user — tidak ada aksi, cart tetap tersimpan
- Setup: script src="https://app.sandbox.midtrans.com/snap/snap.js" 
  data-client-key="{{ midtrans_client_key }}"
- Event listener Livewire: on midtransReady(token), jalankan snap.pay
```

---

## F. TRANSAKSI

### 14. Transaction History
```
Buat halaman riwayat transaksi:
- Header: "Riwayat Transaksi"
- Filter bar (white card rounded-xl p-4 mb-4):
  Search invoice, filter tanggal (Dari - Sampai date input)
- Table (white card rounded-xl shadow-sm):
  Kolom: Invoice (monospace bold), Tanggal (d/m/Y H:i), Kasir, Customer,
  Pembayaran (blue badge: cash/qris/transfer/dc/midtrans),
  Status (badge: completed=green, refunded=red, pending=gray),
  Total (right-align Rp), Aksi (Detail link, Struk link)
- Row hover: bg-gray-50
- Pagination footer
- Empty state: "Belum ada transaksi."
- Export button (Excel/PDF) untuk role owner
```

### 15. Transaction Detail
```
Buat halaman detail transaksi:
- Header: Invoice # (monospace bold) + badge status
- Info grid 4 kolom: Tanggal, Kasir, Customer, Metode Bayar
- Jika ada refund: badge merah "Refunded"
- Table items: Nama Produk, Harga Satuan, Qty, Subtotal
- Summary card: Subtotal, Pajak (jika > 0), Total (bold large),
  Bayar, Kembali (jika > 0)
- Tombol: "Cetak Struk" (blue, target _blank), "Kembali" (gray)
- Jika status refund: info refund amount dan tipe
- Desain: white card, clean layout, mb-4 per section
```

---

## G. INVENTORY

### 16. Stock View
```
Buat halaman stok produk (Owner only):
- Header: "Stok Produk" + dropdown export (Excel/PDF) dengan icon
- Search bar + filter kategori + checkbox "Tampilkan nonaktif"
- Table: Nama Produk, SKU, Kategori, Stok (warna: merah jika stok 0,
  orange jika < min_stok, hijau jika aman), Min Stok, Selisih,
  Harga Modal, Harga Jual
- Low stock: bg-red-50, text-red-600 bold
- Out of stock: bg-red-100
- Sorting by stok (click header asc/desc)
- Pagination
- Empty state: "Tidak ada produk."
```

### 17. Stock Opname
```
Buat halaman stock opname:
- LIST (default view):
  Header "Stock Opname" + tombol "+ Buat Stock Opname"
  Table: Tanggal, Status (Draft=yellow, Completed=green badge),
  User, Jumlah Item, Total Selisih (+ hijau / - merah), Aksi (Detail)
  Pagination, empty state

- FORM (setelah klik "+ Buat Stock Opname"):
  Pilih Tanggal (date input), Catatan (textarea)
  Table daftar semua produk aktif:
  Kolom: Produk, Stok Sistem (readonly, dari DB), Stok Fisik (input number),
  Selisih (auto-hitung: fisik - sistem, hijau jika >0, merah jika <0),
  Catatan (text input per item)
  Filter/search produk dalam form
  Tombol: "Simpan Draft" (gray), "Selesai" (green solid)
  Validasi: stok fisik >= 0, required
  Scroll area untuk banyak produk
```

### 18. Stock Movement — Barang Masuk
```
Buat halaman barang masuk (Stock In):
- Header: "Barang Masuk" + tombol "+ Tambah Barang Masuk" (modal trigger)
- Filter bar (white card p-4): search produk, tanggal dari-sampai
- Table: Tanggal, Produk, User, Qty (+ hijau), Referensi (PO# / Manual),
  Catatan
- Pagination

- FORM MODAL "Tambah Barang Masuk":
  Pilih Produk (searchable dropdown dengan nama + SKU + stok saat ini)
  Qty (number input, min 1)
  Referensi (dropdown: Manual / PO)
  Jika PO: pilih Purchase Order dari daftar PO yang sudah dikirim
  Catatan (textarea)
  Tombol: "Simpan" (biru), "Batal" (abu-abu)
  Validasi: produk wajib, qty > 0
```

### 19. Stock Movement — Barang Keluar
```
Buat halaman barang keluar (Stock Out):
- Header: "Barang Keluar" + tombol "+ Tambah Barang Keluar" (modal trigger)
- Filter bar: search produk, tanggal dari-sampai
- Table: Tanggal, Produk, User, Qty (- merah), Referensi (Penjualan/Rusak/Expired/Lainnya),
  Catatan
- Pagination

- FORM MODAL "Tambah Barang Keluar":
  Pilih Produk (searchable dropdown dengan stok saat ini)
  Qty (number input, min 1)
  Peringatan jika qty > stok: alert merah "Stok tidak mencukupi"
  Referensi (dropdown: Penjualan, Rusak, Expired, Lainnya)
  Catatan/Alasan (textarea required)
  Tombol: "Simpan" (biru), "Batal" (abu-abu)
```

---

## H. PURCHASING

### 20. Purchase Orders — List
```
Buat halaman daftar Purchase Order:
- Header: "Purchase Order" + tombol "+ Buat PO" (biru)
- Filter: search, status (dropdown: Semua/Draft/Sent/Received/Cancelled), vendor (dropdown)
- Table: No PO (monospace bold), Vendor, Tanggal, Status (badge: Draft=yellow,
  Sent=blue, Received=green, Cancelled=red), Jumlah Item, Total (Rp),
  User pembuat, Aksi (Edit/Detail)
- Auto-draft: badge "Auto" orange
- Row clickable menuju detail
- Pagination
```

### 21. Purchase Orders — Form (Create / Edit)
```
Buat form Purchase Order:
- Header: "Buat Purchase Order" / "Edit PO #xxxxx"
- Pilih Vendor (dropdown searchable, required)
- Catatan (textarea optional)
- Dynamic items table:
  Setiap baris: Pilih Produk (dropdown/searchable), Qty (number), 
  Harga Satuan (auto dari produk, bisa diedit manual), Subtotal (auto: qty × harga)
  Tombol hapus baris (× merah)
  Tombol "+ Tambah Item" (blue outline)
- Footer: Total Item (count), Total Harga (sum semua subtotal, bold)
- Tombol: "Simpan Draft" (gray), "Submit / Kirim ke Vendor" (biru)
- Konfirmasi modal sebelum submit
- Unique product validation
- Jika edit: load items dari data existing
```

### 22. Purchase Orders — Detail Page
```
Buat halaman detail Purchase Order:
- Header: No PO (monospace bold, text-lg) + badge status + badge "Auto" jika auto-draft
- Info grid 4-5 kolom: Vendor (nama + kontak), Tanggal, Pembuat, Status, Catatan
- Table items: Produk, Qty, Harga Satuan, Subtotal
- Tfoot: Total (bold, border-top double)
- Jika status "draft": tombol "Edit" (biru) dan "Submit" (hijau)
- Jika status "sent": tombol "Terima" (green) untuk mark as received
- Jika "received": info diterima oleh siapa dan kapan
- Jika "cancelled": alasan pembatalan
- Tombol "Kembali ke list"
```

---

## I. REPORTS

### 23. Reports — Sales
```
Buat halaman laporan penjualan:
- Header: "Laporan Penjualan" + dropdown export (Excel / PDF) dengan icon
- Filter bar (white card p-4 mb-4):
  Tanggal Dari - Sampai (date inputs)
  Pilih Toko (dropdown, hanya untuk multi-store)
  Tombol "Filter" (biru)
- Summary cards (3 card sejajar):
  (1) "Total Transaksi" — icon receipt, angka
  (2) "Total Revenue" — icon money, Rp amount (hijau)
  (3) "Total Pajak" — icon calculator, Rp amount
- Line chart penjualan per hari dalam range
- Table: Invoice, Tanggal, Toko, Kasir, Customer, Metode Bayar, Status, Total
- Pagination
```

### 24. Reports — Tax
```
Buat halaman laporan pajak:
- Header: "Laporan Pajak" + export button
- Filter: Bulan (dropdown), Tahun (dropdown), Toko (jika multi-store)
- Summary card: "Total Pajak Periode Ini" — icon kalkulator, Rp amount (large)
- Bar chart: pajak per bulan dalam 1 tahun
- Table: Invoice, Tanggal, Subtotal Kena Pajak, Tarif Pajak, Jumlah Pajak, Total (include pajak)
- Pagination
```

### 25. PDF Export — Laporan Penjualan (Print-friendly)
```
Buat template PDF untuk laporan penjualan:
- Standalone HTML, font sans-serif 12px
- Header center: "Laporan Penjualan" (bold), periode tanggal di bawahnya
- Table border-collapse: Invoice, Tanggal, Customer, Total (Rp), Metode Bayar
- Thead: bg-gray-100
- Border: 1px solid #ddd
- Footer: Total Transaksi (jumlah), Total Pendapatan (Rp sum)
- Empty: "Tidak ada data"
- CSS @media print
- Desain monokrom, print-friendly
```

### 26. PDF Export — Laporan Stok (Print-friendly)
```
Buat template PDF untuk laporan stok:
- Standalone HTML, font sans-serif 12px
- Header center: "Laporan Stok Produk"
- Table: Produk, SKU, Stok, Min Stok, Harga (Rp), Status (Normal / Stok Minim)
- Thead: bg-gray-100
- Border: 1px solid #ddd
- Empty: "Tidak ada produk"
- CSS @media print
- Desain monokrom, print-friendly
```

---

## J. APPROVALS

### 27. Approvals — Receipt Reprint (Owner)
```
Buat halaman approval cetak ulang struk (Owner):
- Header: "Approval Cetak Ulang Struk"
- Filter status tabs/dropdown: Semua, Pending, Approved, Rejected
- Table/list: Request ID, Invoice (link), Kasir (pengaju), Tanggal, Alasan,
  Status (badge: Pending=yellow, Approved=green, Rejected=red)
- Untuk Pending: tombol "Setujui" (bg-green-500) dan "Tolak" (bg-red-500)
  Setiap klik: modal konfirmasi
- Approved/Rejected: tampilkan info approved_by + approved_at
- Modal Setujui: "Yakin menyetujui request ini?" — tombol Ya/Tidak
- Modal Tolak: textarea alasan penolakan (optional) — tombol Tolak/Tidak
- Flash message setelah aksi
```

### 28. Approvals — Refund (Owner)
```
Buat halaman approval refund (Owner):
- Header: "Approval Refund"
- Filter status tabs: Pending, Approved, Rejected
- List cards (bukan table) untuk setiap request:
  Informasi: Invoice #, Kasir pengaju, Tanggal request
  Detail: Item yang diretur (nama produk + qty), Kondisi barang (textarea), 
  Jumlah refund diminta (Rp), Tipe refund (Uang Kembali / Tukar Barang)
- Untuk Pending: tombol "Setujui" + "Tolak"
  Setujui: buka form dengan input:
    • Jumlah Refund (input number, pre-filled dengan amount diminta)
    • Tipe Refund (dropdown: Full / Prorata)
    • Catatan Owner (textarea)
    Tombol: "Konfirmasi Setujui" (hijau)
  Tolak: modal dengan catatan, tombol "Tolak" (merah)
- Jika approved: update status transaction jadi "refunded", 
  stock dikembalikan, subscription dicancel
- Loading state saat proses
```

---

## K. CASHIER REQUESTS

### 29. Cashier Request — Receipt Reprint
```
Buat halaman request cetak ulang struk (Cashier):
- Header: "Request Cetak Ulang Struk"
- Tombol "+ Request Cetak Ulang" (modal trigger)
- FORM MODAL:
  Cari transaksi: input search invoice, tampilkan hasil dropdown/list
  Pilih transaksi dari hasil pencarian (tampilkan invoice + tanggal + total)
  Alasan (textarea, min 5 karakter, required)
  Tombol "Kirim Request" (biru)
- History table: Invoice, Tanggal Request, Status (Pending/Approved/Rejected pill),
  Aksi "Cetak" (link jika status approved)
- Empty state: "Belum ada request."
- Notifikasi ke owner via database notification saat submit
```

### 30. Cashier Request — Refund
```
Buat halaman request refund (Cashier):
- Header: "Request Refund"
- Tombol "+ Request Refund" (modal trigger)
- FORM MODAL:
  Cari transaksi: input search, pilih dari hasil
  Pilih item dari transaksi yang diretur (radio/checkbox per item)
  Kondisi barang (textarea, required, min 5)
  Upload foto kondisi (optional, drag & drop)
  Tipe refund (dropdown: Uang Kembali / Tukar Barang)
  Jumlah refund (auto-calculate atau input manual)
  Tombol "Kirim Request Refund"
- History table: Invoice, Tanggal, Status, Alasan
- Validasi: tidak bisa refund transaksi yang sudah direfund
- Notifikasi ke owner via database notification
```

---

## L. SETTINGS

### 31. Store Settings
```
Buat halaman pengaturan toko (Owner only):
- Header: "Pengaturan Toko"
- FORM dalam white card rounded-xl shadow-sm, dibagi section:

  SECTION 1 — Informasi Toko (icon building):
  Nama Toko (required), Kode Toko, Telepon,
  Alamat (textarea), Footer Struk (textarea kecil)

  SECTION 2 — Printer Thermal (icon printer):
  Tipe Printer (dropdown: Network / USB)
  Alamat IP Printer (text input, muncul jika Network)
  Port (number, default 9100, muncul jika Network)

  SECTION 3 — Pembayaran QRIS (icon qrcode):
  Upload Gambar QRIS (drag & drop, preview image)
  Jika sudah ada gambar: tampilkan preview + tombol "Hapus"

  Divider antar section (border-t border-gray-200 my-6)
  Tombol "Simpan Pengaturan" (bg-blue-600, full width atau right-align)
  Alert sukses hijau setelah simpan
```

---

## M. OTHER

### 32. Activity Logs
```
Buat halaman riwayat aktivitas (Owner only):
- Header: "Riwayat Aktivitas"
- Filter bar: User (dropdown), Aksi (dropdown: Create/Update/Delete),
  Tipe (dropdown: Product/Transaction/User/dll), Date range
- Table/list: Timestamp (relative: "2 jam lalu"), User, Aksi (badge warna),
  Tipe, Deskripsi, IP Address
- Badge warna: Create=green, Update=blue, Delete=red
- Pagination
- Search by deskripsi
- Icon per tipe aksi
- Empty state: "Belum ada aktivitas."
```

### 33. Subscription Check
```
Buat halaman pengecekan langganan:
- Header: "Cek Langganan"
- Search input "Cari customer..." + tombol "Cari" (biru)
- Hasil pencarian hanya muncul setelah klik tombol Cari
- Table: Invoice #, Produk (nama subscription), Customer,
  Tanggal Mulai, Tanggal Berakhir, Sisa Hari (badge: 
  hijau >30hr, kuning 7-30hr, merah <7hr), Status (Aktif=green, Expired=red)
- Summary card: "Total Langganan Aktif"
- Filter: Aktif / Semua
- Empty state: "Tidak ada data langganan." / "Cari customer untuk memulai."
```

### 34. Notification Bell
```
Buat komponen notification bell di header:
- Icon bell (SVG/lucide) dengan badge merah bulat (jumlah unread)
- Jika 0 unread: badge tidak muncul
- Dropdown panel saat diklik (absolute, right-0, w-80, bg-white, 
  rounded-xl, shadow-lg, border)
- Header dropdown: "Notifikasi" + "Tandai semua dibaca" (link kecil)
- List notifikasi (max-5, scroll jika lebih):
  Setiap item: icon sesuai tipe, pesan teks, timestamp relative
  Notifikasi baru: bg-blue-50
  Notifikasi lama: bg-white
  Hover: bg-gray-50
- Footer: "Lihat Semua" (link)
- Empty state: "Tidak ada notifikasi"
- Click notifikasi: mark as read, dispatch event
- Livewire event listener: check-notifications
```

---

## N. PRINT

### 35. Print Receipt (Browser — Thermal 80mm)
```
Buat halaman cetak struk thermal (standalone, tanpa layout/ sidebar):
- CSS: @page { margin: 0; size: 80mm auto; }
- Body: font-family 'Courier New' monospace, font-size: 12px, 
  width: 80mm, margin auto, padding 10px 5px
- Header center:
  Nama Toko (bold 16px) — dari $store->name
  Alamat (10px)
  Telp: ... (10px)
  Invoice: #... (monospace)
  Tanggal (d/m/Y H:i)
  Kasir: nama
  Customer: nama (jika ada)
- Divider: border-top 1px dashed #000, margin 6px 0
- Table items (width 100%):
  Thead: Item, Qty, Harga
  Tbody per item: product_name, quantity, subtotal (Rp)
- Divider dashed
- Summary (table width 100%):
  Subtotal (Rp)
  Pajak (Rp) — jika > 0
  Total (bold, font-size 14px) — Rp
  Bayar (metode) — Rp
  Kembali — Rp (jika > 0)
- Footer center:
  Divider dashed
  Footer text dari $store->receipt_footer (jika ada)
  "Terima Kasih"
- window.print() otomatis saat load
```

### 36. Direct Print (via Printer Thermal Network/USB)
```
Buat tombol/aksi cetak langsung ke printer thermal:
- Tombol "Cetak ke Printer" di:
  • Halaman detail transaksi
  • Halaman riwayat transaksi (per row)
  • Setelah transaksi kasir berhasil
- Loading state (spinner) saat mengirim perintah cetak
- Alert sukses: "Struk berhasil dicetak" (bg-green-50, auto-hide 3 detik)
- Alert error: "Gagal mencetak: [error message]" (bg-red-50)
  Error umum: printer offline, IP tidak terhubung, port salah
- Konfigurasi printer ada di halaman Pengaturan Toko:
  Tipe: Network / USB, IP Address, Port (default 9100)
- Backend menggunakan library Mike42 ESC/POS untuk komunikasi printer
- Tidak perlu preview visual, cukup tombol aksi + feedback alert
```

---

## RINGKASAN

| Modul | Jumlah Prompt |
|-------|:------------:|
| A. Auth & Layout | 3 |
| B. Dashboard | 2 |
| C. Master Data | 5 |
| D. Users | 1 |
| E. Cashier / POS | 2 |
| F. Transaksi | 2 |
| G. Inventory | 4 |
| H. Purchasing | 3 |
| I. Reports | 4 |
| J. Approvals | 2 |
| K. Cashier Requests | 2 |
| L. Settings | 1 |
| M. Other | 3 |
| N. Print | 2 |
| **TOTAL** | **36 Prompt** |

---

## CARA PAKAI DI STITCH

1. Buka https://stitch.withgoogle.com, login dengan Google account
2. Generate bertahap mulai dari **Layout → Login → Dashboard**
3. Copy-paste **satu prompt per generate** untuk hasil maksimal
4. Gunakan fitur **DESIGN.md** untuk menjaga konsistensi warna/tipografi
5. Export sebagai **HTML/CSS (Tailwind)** atau **Figma**
6. Integrasikan ke Blade + Livewire views Laravel Anda

> **Tips:** Gunakan **Vibe Design** di Stitch untuk tiap modul agar style konsisten antar halaman.
