# POS System (Point of Sale)

Laravel + Livewire + Tailwind CSS v4 POS system with multi-store support, thermal printing, and Midtrans payment gateway.

## Requirements

- PHP ^8.3
- MySQL/MariaDB
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
| owner   | Full access (products, categories, reports)  |
| cashier | POS checkout, transaction history, requests  |

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
