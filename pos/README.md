# POS System (Point of Sale)

Laravel + Livewire + Tailwind CSS v4 POS system with multi-store support, thermal printing, discount/promo management, dashboard analytics, and forecasting.

## Requirements

- PHP ^8.3
- SQLite (default) or MySQL/MariaDB
- Composer
- Node.js + npm
- Web server (Apache/Nginx) or `php artisan serve`

## Quick Setup

```bash
cp .env.example .env
# Edit .env - set APP_URL, database path

composer install
npm install

php artisan key:generate
php artisan migrate --seed
npm run build
```

## Running Development

```bash
composer run dev
```

This runs 5 processes concurrently:
| Service     | Description                    |
|-------------|--------------------------------|
| `server`    | PHP built-in server            |
| `queue`     | Queue worker (background jobs) |
| `logs`      | Laravel Pail (log viewer)      |
| `scheduler` | Laravel Scheduler (cron tasks) |
| `vite`      | Vite HMR (hot reload)          |

## Production

### Queue Worker
Run queue worker as a daemon:
```bash
php artisan queue:work --sleep=3 --tries=3
```

Or on Windows, use Task Scheduler:
```powershell
.\setup-windows-service.ps1
```

### Scheduler
Add to system cron every minute:
```cron
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

On Windows, the scheduler is included in `composer run dev`, or use:
```bash
.\scheduler.bat
```

### Docker

```bash
docker compose up -d
```

Nginx + PHP 8.3, serves on port 80.

## Configuration

### Database

SQLite is the default. The database file is at `database/database.sqlite`.

To switch to MySQL, update `.env`:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pos
DB_USERNAME=root
DB_PASSWORD=
```

### Auto Backup

Database is automatically backed up daily at 02:00. Backups older than 30 days are cleaned up.

Manual backup:
```bash
php artisan db:backup
```

### Email Notifications

Set mail driver in `.env`:
```
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your@email.com
MAIL_PASSWORD=your-app-password
MAIL_FROM_ADDRESS=your@email.com
```

For development, use `MAIL_MAILER=log` to view emails in storage/logs.

### Printer (ESC/POS Thermal)

Configure in Pengaturan Toko:
- Network: IP + Port (default 9100)
- USB: auto-detected

## API

Sanctum-based API available at `/api/`:

```
POST /api/login          # Login (returns token)
POST /api/logout         # Logout (revoke token)
GET  /api/user           # Current user info

GET    /api/products              # List products (owner)
POST   /api/products              # Create product (owner)
GET    /api/products/{id}         # Show product (owner)
PUT    /api/products/{id}         # Update product (owner)
DELETE /api/products/{id}         # Deactivate product (owner)

GET    /api/categories            # List categories (owner)
POST   /api/categories            # Create category (owner)
PUT    /api/categories/{id}       # Update category (owner)
DELETE /api/categories/{id}       # Delete category (owner)

GET    /api/transactions          # List transactions
GET    /api/transactions/today    # Today's transactions
GET    /api/transactions/{id}     # Show transaction
```

All endpoints except `/api/login` require header: `Authorization: Bearer {token}`

## Roles

| Role    | Access                                       |
|---------|----------------------------------------------|
| owner   | Full access (products, categories, reports, discounts, approvals) |
| cashier | POS checkout, transaction history, refund/receipt requests |

## Features

### POS / Cashier
- Product grid with search, category filter & **barcode scanner** (F2 shortcut)
- Cart management (add, remove, quantity)
- Multi-payment: **Cash, QRIS, Transfer Bank, Kartu Debit**
- **Auto discount calculation** — priority-based, stackable, min-purchase rules
- Diskon ditampilkan di banner, badge produk, breakdown keranjang, dan struk
- Tax toggle (PPN)
- Automatic stock deduction
- Subscription product support
- Thermal receipt printing (browser HTML + direct ESC/POS)
- **Realtime browser notification** on completed transaction

### Dashboard
- Stat cards: today's sales, transactions, products, pending approvals
- Chart: weekly & monthly sales (bar/line chart)
- **Top 5 products** by quantity (horizontal bar)
- **Category sales breakdown** (doughnut chart)
- Draft PO & low stock alerts

### Inventory
- Stock view with color-coded alerts (red=out, orange=low, green=ok)
- Stock Opname (create session, input physical, auto-diff, finalize)
- Stock Movement In (manual / PO reference)
- Stock Movement Out (rusak / expired / lainnya)
- Export Excel & PDF

### Purchase Orders
- CRUD with workflow: Draft → Sent → Received → Cancelled
- Auto-draft PO for low-stock products (scheduled daily at 06:00)
- **Forecast-based auto PO** from stock runout predictions

### Diskon & Promo
- Tipe: Persentase (%) atau Nominal (Rp)
- Date range auto-activation
- Manual toggle on/off
- **Priority & stackable rules** (prioritas lebih tinggi didahulukan)
- Minimum purchase threshold
- Apply to specific products or all products
- **Diskon otomatis terhitung di kasir** & tersimpan di transaksi

### Reports
- **Sales report** — filter by date & payment method, average transaction, breakdown per method, reference number, chart + export Excel/PDF
- **Tax report** — with chart + export PDF
- Stock export Excel/PDF

### Forecasting
- **Sales**: Moving Average, WMA, Exponential Smoothing, Linear Regression, Seasonal, Future Prediction
- **Stock**: Daily average, runout prediction (status: habis/kritis/menipis/aman), reorder recommendations

### Approvals
- Receipt Reprint approval workflow
- Refund Request approval with notes
- **Refund respects discounts** — amount auto-calculated from discounted price, validated on approval

### Subscription
- Subscription-enabled products with auto-expire (scheduled daily)

### Activity Logs
- Track all create/update/delete actions with filterable viewer

### Dark Mode
- Toggle in sidebar (persisted to localStorage)
- Comprehensive CSS overrides covering all pages

## Dummy Data

```bash
php artisan db:seed --class=DummyDataSeeder
```

Creates: 2 stores, 4 users, 5 categories, ~19 products, 3 vendors, POs, stock movements.

### Test Credentials

| User ID | Password  | Role    | Store        |
|---------|-----------|---------|--------------|
| OWN001  | owner123  | owner   | All stores   |
| KSR001  | kasir123  | cashier | Toko Pusat   |
| KSR002  | kasir123  | cashier | Toko Cabang  |

## Testing

```bash
composer test
```

Runs 120+ tests covering:
- Authentication & role middleware
- Service layer (CheckoutService, StockService, ForecastService)
- Checkout flow (cart validation, transaction creation, stock deduction, subscriptions, discounts)
- Product, Category, User CRUD access control
- Stock movements, stock opname
- Purchase orders
- Forecast calculations
- Approvals & refunds

## Architecture

### Service Layer
Business logic is extracted into dedicated service classes:
- `CheckoutService` — checkout validation, invoice generation, transaction creation, discount application
- `StockService` — stock in/out movements with validation
- `PrintService` — ESC/POS thermal printing
- `ActivityLogger` — activity logging
- `ForecastService` — sales & stock forecasting algorithms
- `DatabaseBackup` (Command) — automated SQLite backup

### Key Design Decisions
- **SQLite** as default database (portable, zero-config)
- **Vanilla JS** for dark mode (more reliable than Alpine on `<html>`)
- **CSS overrides** for dark mode (covers hundreds of elements without editing each view)
- **No Midtrans/ewallet** — online payment removed; QRIS retained as offline QR scan
- **Discount stored per transaction item** — `discount_amount` + `discount_name` on each item for auditability

## Changelog

### 2026-05-11 — Performance & Security Fixes

- **Fix: Race condition** — Invoice number generation uses cache lock
- **Fix: SQLite compatibility** — Monthly chart is database-agnostic
- **Fix: XSS prevention** — Export links param whitelist
- **Soft Deletes** — Category, Product, Vendor, User, Store
- **Indexing** — Composite indexes on stock_movements
- **Rate Limiting** — API routes (60/min auth, 10/min login)
- **Image Optimization** — Product upload resize to max 600px width

### 2026-05-12 — Discount Integration, Dark Mode, Backup, Docker

- **Feature:** Discount auto-calculation in cashier — priority, stackable, min-purchase applied to cart
- **Feature:** Discount displayed in cart breakdown (per-item & total), receipt (HTML & thermal), and stored in DB
- **Feature:** Dark mode toggle (vanilla JS + localStorage + comprehensive CSS)
- **Feature:** Auto backup database (`db:backup` command, scheduled daily 02:00, 30-day cleanup)
- **Feature:** Realtime browser notification on transaction complete
- **Feature:** Docker setup (PHP 8.3 + Nginx)
- **Feature:** CI/CD via GitHub Actions (`tests.yml`)
- **Enhancement:** Refund now respects discounts (amount auto-calculated from net price)
- **Enhancement:** Priority field uses dropdown (Rendah/Normal/Tinggi/Sangat Tinggi)
- **Enhancement:** Dynamic "Nilai" label (%)/(Rp) based on discount type
- **Enhancement:** Per-item netto price shown in cart + receipt
- **Fix:** Dashboard cache keys mismatch (CheckoutService now clears correct keys)
- **Fix:** Dashboard `scopeToday()` uses range query for index utilization
- **Fix:** Dashboard category sales joins on `product_id` instead of `product_name`
- **Fix:** Forecast `linearRegression` division-by-zero guard
- **Fix:** Forecast `predictFuture` removes double-counting of trend
- **Fix:** Forecast date range off-by-one between `getDailySales` and `fillMissingDates`
- **Fix:** Store settings slug not updated on name change
- **Fix:** Store settings printer address accepts hostnames (not just IP)
- **Optimization:** Dashboard polling reduced 30s→300s, all data points cached
- **Midtrans removed** — online payment gateway fully removed

### 2026-05-16 — Sidebar Accordion, Cache Fixes, Locale, Artifacts Cleanup

- **Feature:** Sidebar navigation grouped into collapsible accordion sections with auto-expand on active group
- **Feature:** Sidebar auto-scrolls to active menu item on page load
- **Fix:** Sidebar not scrollable on small screens — added `min-h-0` to nav element
- **Fix:** Dashboard "incomplete object" error — cached Eloquent models converted to arrays (`toArray()`) to prevent unserialization failure
- **Feature:** Registered missing `POST /midtrans/webhook` route for payment gateway callback
- **Config:** Changed default locale `en` → `id`, fallback `en` → `id`, faker `en_US` → `id_ID`
- **Fix:** Removed redundant `$request->is('api/*')` check in RoleMiddleware (API already uses `auth:sanctum`)
- **Cleanup:** Removed development artifacts `test_app.php` and `check_data.php`

