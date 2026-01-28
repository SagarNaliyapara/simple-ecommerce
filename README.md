# Ecommerce Cart

A full-stack ecommerce shopping cart application built with Laravel 12, Livewire 3, and Tailwind CSS. Features real-time cart management, order processing with pessimistic locking, stock tracking, queued email notifications, and a comprehensive test suite.

---

## Table of Contents

- [Features](#features)
- [Tech Stack](#tech-stack)
- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
  - [Products](#products)
  - [Shopping Cart](#shopping-cart)
  - [Orders](#orders)
  - [Admin Notifications](#admin-notifications)
- [Architecture](#architecture)
  - [Service Layer](#service-layer)
  - [Livewire Components](#livewire-components)
  - [Jobs](#jobs)
  - [Mail](#mail)
- [Artisan Commands](#artisan-commands)
- [Testing](#testing)
- [Development](#development)

---

## Features

- **Product Catalog** — Browse products with real-time stock levels and pricing
- **Shopping Cart** — Add, remove, increment, and decrement items with stock validation
- **Guest-to-Cart Flow** — Guests can click "Add to Cart", log in or register, and the product is automatically added to their cart
- **Order Placement** — Atomic order creation using database transactions with pessimistic locking (`lockForUpdate`) to prevent race conditions
- **Stock Management** — Automatic stock decrement on order, prevents over-ordering, enforces stock limits on cart operations
- **Low Stock Alerts** — Queued email notification sent to the admin when product stock drops to or below a configurable threshold
- **Daily Sales Reports** — Artisan command dispatches a queued job that emails a summary of orders and revenue for a given date
- **Authentication** — Registration, login, logout, password reset, email verification (Laravel Breeze with Livewire/Volt)
- **Real-time UI Updates** — Cart count badge updates across components via Livewire events

---

## Tech Stack

| Layer      | Technology                       |
|------------|----------------------------------|
| Framework  | Laravel 12 (PHP 8.2+)           |
| Frontend   | Livewire 3, Volt, Tailwind CSS 3 |
| Build      | Vite 7                           |
| Database   | SQLite (default), any Laravel-supported DB |
| Queue      | Database driver (default)        |
| Testing    | PHPUnit 11                       |

---

## Requirements

- PHP >= 8.2
- Composer
- Node.js and npm
- SQLite (default) or another supported database

---

## Installation

### Quick Setup

```bash
git clone <repository-url>
cd ecommerce-cart
composer run-script setup
```

The `setup` script runs all of the following automatically:

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run build
```

### Manual Setup

1. **Clone and install PHP dependencies:**
   ```bash
   git clone <repository-url>
   cd ecommerce-cart
   composer install
   ```

2. **Configure environment:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Run migrations:**
   ```bash
   php artisan migrate
   ```

4. **Install and build frontend assets:**
   ```bash
   npm install
   npm run build
   ```

---

## Configuration

The application uses two custom environment variables beyond the standard Laravel defaults. Add these to your `.env` file:

| Variable              | Description                                          | Default           |
|-----------------------|------------------------------------------------------|--------------------|
| `ADMIN_EMAIL`         | Email address for admin notifications (low stock alerts, daily reports) | `admin@example.com` |
| `LOW_STOCK_THRESHOLD` | Stock quantity at or below which a low stock alert is triggered | `3` |

These map to the following config keys:

- `config('mail.admin_email')` — read from `ADMIN_EMAIL`
- `config('app.low_stock_threshold')` — read from `LOW_STOCK_THRESHOLD`

If `ADMIN_EMAIL` is not set (or set to `null`), notification emails are silently skipped.

### Mail

The default mail driver is `log`, which writes emails to `storage/logs/laravel.log`. To send real emails, configure the `MAIL_MAILER` and related SMTP/service variables in `.env`. Supported mailers: smtp, sendmail, mailgun, ses, postmark, resend.

### Queue

The default queue connection is `database`. Jobs are stored in the `jobs` table (created by migrations). For development, the `composer dev` script starts a queue listener automatically.

---
## Usage

### Products

The product catalog is accessible to both guests and authenticated users at `/products`. Each product displays its name, price, and current stock.

- **Authenticated users** can click "Add to Cart" to add a product. If the product is already in the cart, its quantity is incremented.
- **Guests** who click "Add to Cart" are redirected to the login page. After logging in (or registering), the product is automatically added to their cart.

Stock validation is enforced: out-of-stock products cannot be added, and quantities cannot exceed available stock.

### Shopping Cart

The cart page (`/cart`) shows all items for the logged-in user with quantity controls and a running total.

- **Increment** — Increases quantity by 1 (blocked if at stock limit)
- **Decrement** — Decreases quantity by 1; removes the item if quantity reaches 0
- **Remove** — Deletes the item from the cart entirely
- **Proceed to Order** — Places an order from all cart items

The cart count in the navigation updates in real-time via Livewire events whenever the cart changes.

### Orders

Clicking "Proceed to Order" on the cart page triggers an atomic database transaction that:

1. Locks cart items with `lockForUpdate` to prevent concurrent modifications
2. Validates that all products have sufficient stock
3. Creates an `Order` record with the calculated total
4. Bulk-inserts `OrderItem` records
5. Decrements each product's `stock_quantity`
6. Clears the user's cart
7. Dispatches a `SendLowStockNotification` job if any product's stock falls to or below the configured threshold

The order history page (`/orders`) displays all orders for the user in descending chronological order, with line-item details.

### Admin Notifications

**Low Stock Alert** — Dispatched automatically after an order when any product's remaining stock is at or below `LOW_STOCK_THRESHOLD`. The email lists each affected product and its remaining quantity.

**Daily Sales Report** — Triggered via the `report:daily-sales` artisan command. The email includes total order count, total revenue, a per-order breakdown, and a per-product quantity summary.

Both notifications are sent to the address configured in `ADMIN_EMAIL`. If that value is not set, the jobs exit silently without sending.

---

## Architecture

### Service Layer

All database queries are encapsulated in three service classes under `app/Services/`:

**CartService** — Cart item operations.

**OrderService** — Order operations (depends on `CartService`).

**ProductService** — Product queries.

### Livewire Components

| Component                         | Purpose                              |
|-----------------|--------------------------------------|
| `ProductsList`  | Product catalog with add-to-cart   |
| `Cart`          | Cart management and order placement |
| `CartCount`     | Navigation cart badge counter       |
| `Orders`        | Order history display               |

Volt anonymous components handle authentication pages:
- `resources/views/livewire/pages/auth/login.blade.php`
- `resources/views/livewire/pages/auth/register.blade.php`

### Jobs

| Job                       | Trigger                       | Action                              |
|---------------------------|-------------------------------|--------------------------------------|
| `SendLowStockNotification`| After order if stock is low   | Sends `LowStockAlert` email to admin |
| `SendDailySalesReport`    | `report:daily-sales` command  | Sends `DailySalesReport` email to admin |

Both jobs implement `ShouldQueue` and run on the `database` queue connection.

### Mail

| Mailable            | Subject                            |
|---------------------|------------------------------------|
| `LowStockAlert`     | Low Stock Alert                    |
| `DailySalesReport`  | Daily Sales Report — {date}       |

Both use Markdown mail templates.

---

## Artisan Commands

### `report:daily-sales`

Dispatches the daily sales report job for a given date.

```bash
# Report for today
php artisan report:daily-sales

# Report for a specific date
php artisan report:daily-sales --date=2026-01-15
```

The `--date` option accepts a `Y-m-d` formatted string. Defaults to today if omitted.

---

## Testing

The project includes 78 tests covering authentication, cart operations, order processing, stock notifications, daily reports, and profile management.

```bash
# Run all tests
php artisan test

# Or via composer (clears config cache first)
composer test
```

Tests use an in-memory SQLite database and the `array` mail driver for isolation.

### Test Coverage

| Test File                       | Scope                                   |
|---------------------------------|-----------------------------------------|
| `AuthenticationTest`            | Login, logout, pending cart flow        |
| `RegistrationTest`              | Registration, pending cart flow         |
| `EmailVerificationTest`         | Email verification flow                 |
| `PasswordConfirmationTest`      | Password confirmation                   |
| `PasswordResetTest`             | Password reset flow                     |
| `PasswordUpdateTest`            | Password update                         |
| `CartTest`                      | Cart CRUD, stock limits, user isolation |
| `OrderTest`                     | Order creation, stock decrement, cart clearing, user isolation |
| `ProductsListTest`              | Product display, add-to-cart, guest flow |
| `DailySalesReportTest`          | Report job, date filtering, missing config |
| `LowStockNotificationTest`     | Threshold triggers, email dispatch      |
| `ProfileTest`                   | Profile update, account deletion        |

---

## Development

Start all development services concurrently with a single command:

```bash
composer dev
```

This runs four processes in parallel:

| Process                          | Purpose                |
|----------------------------------|------------------------|
| `php artisan serve`              | Laravel dev server     |
| `npm run dev`                    | Vite HMR dev server    |
| `php artisan queue:listen`       | Queue worker           |
| `php artisan pail`               | Real-time log viewer   |

The application will be available at `http://localhost:8000`.
