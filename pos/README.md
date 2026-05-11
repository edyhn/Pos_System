# POS System (Point of Sale)

Laravel + Livewire + Tailwind CSS v4 POS system with multi-store support, thermal printing, Midtrans payment gateway, and discount/promo management.

## Requirements

- PHP ^8.3
- MySQL/MariaDB or SQLite
- Composer
- Node.js + npm
- Web server (Apache/Nginx) or `php artisan serve`

## Quick Setup

```bash
cp .env.example .env
# Edit .env - set DB credentials, APP_URL, Midtrans keys

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

## Configuration

### Midtrans (Payment Gateway)

1. Register at [Midtrans](https://midtrans.com)
2. Get Server Key & Client Key from Dashboard
3. Set in `.env`:
```
MIDTRANS_SERVER_KEY=your-server-key
MIDTRANS_CLIENT_KEY=your-client-key
MIDTRANS_IS_PRODUCTION=false
MIDTRANS_MERCHANT_ID=your-merchant-id
MIDTRANS_SANDBOX=true
```
4. Set webhook URL in Midtrans Dashboard: `https://your-domain.com/midtrans/webhook`

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
| owner   | Full access (products, categories, reports, discounts) |
| cashier | POS checkout, transaction history, requests  |

## Features

### POS / Cashier
- Product grid with search & category filter
- Cart management (add, remove, quantity)
- Multi-payment: Cash, QRIS, Transfer, Debit Card, Midtrans
- Tax toggle (PPN)
- Automatic stock deduction
- Subscription product support
- Thermal receipt printing (browser + direct ESC/POS)

### Inventory
- Stock view with color-coded alerts (red=out, orange=low, green=ok)
- Stock Opname (create session, input physical, auto-diff, finalize)
- Stock Movement In (manual / PO reference)
- Stock Movement Out (rusak / expired / lainnya)
- Export Excel & PDF

### Purchase Orders
- CRUD with workflow: Draft → Sent → Received → Cancelled
- Auto-draft PO for low-stock products (scheduled daily at 06:00)

### Diskon & Promo
- Tipe: Persentase (%) atau Nominal (Rp)
- Date range auto-activation
- Manual toggle on/off
- Priority & stackable rules
- Minimum purchase threshold
- Apply to specific products or all products

### Reports
- Sales report with chart + export Excel/PDF
- Tax report with chart + export PDF
- Stock export Excel/PDF

### Forecasting
- Sales: Moving Average, WMA, Exponential Smoothing, Linear Regression, Seasonal
- Stock: Daily average, runout prediction, reorder recommendations

### Approvals
- Receipt Reprint approval workflow
- Refund Request approval with notes

### Subscription
- Subscription-enabled products with auto-expire (scheduled daily)

### Activity Logs
- Track all create/update/delete actions with filterable viewer

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
- Service layer (CheckoutService, StockService)
- Checkout flow (cart validation, transaction creation, stock deduction, subscriptions)
- Product, Category, User CRUD access control
- Stock movements, stock opname
- Purchase orders
- Approvals & refunds

## Architecture

### Service Layer
Business logic is extracted into dedicated service classes:
- `CheckoutService` — checkout validation, invoice generation, transaction creation
- `StockService` — stock in/out movements with validation
- `MidtransService` — Midtrans Snap API + signature verification
- `PrintService` — ESC/POS thermal printing
- `DiscountService` — discount eligibility & calculation
- `ActivityLogger` — activity logging
- `ForecastService` — sales & stock forecasting algorithms

### FormRequest Validation
Reusable validation classes for API and web:
- `StoreProductRequest` / `UpdateProductRequest`
- `StoreCategoryRequest`
- `StoreTransactionRequest`

## Changelog

### 2026-05-12 — Service Layer, Discounts & Testing

- **Refactor:** Extract `CheckoutService` from bloated Cashier Livewire component (448→262 lines)
- **Refactor:** Extract `StockService` for centralized stock management
- **Refactor:** `MidtransWebhookController` now uses constructor injection
- **Feature:** Discount & Promo system with CRUD, date-based auto-activation, toggle, priority, stackable rules, and per-product targeting
- **Feature:** `DiscountService` for calculating applicable discounts at checkout
- **Quality:** Blade directives `@currency` and `@formatNumber`
- **Quality:** Global helper functions `formatNumber()`, `formatCurrency()`, `formatDate()`
- **Quality:** FormRequest validation classes (Product, Category, Transaction)
- **Quality:** `PurchaseOrder` now uses SoftDeletes (consistency with other models)
- **Testing:** 30 new unit tests for CheckoutService (9) and StockService (7)
- **Testing:** Full test suite expanded from 103 to 120 tests
- **Optimization:** Dashboard queries cached (60s owner, 30s cashier), invalidated on new transaction
- **Optimization:** Cache dashboard charts already cached (weekly: 5min, monthly: 1hr)

### 2026-05-11 — Performance & Security Fixes

- **Fix: Race condition** — Invoice number generation uses cache lock
- **Fix: Midtrans double-processing** — Status verification before completing
- **Fix: SQLite compatibility** — Monthly chart is database-agnostic
- **Fix: XSS prevention** — Export links param whitelist
- **Soft Deletes** — Category, Product, Vendor, User, Store
- **Indexing** — Composite indexes on stock_movements
- **Rate Limiting** — API routes (60/min auth, 10/min login)
- **Image Optimization** — Product upload resize to max 600px width
