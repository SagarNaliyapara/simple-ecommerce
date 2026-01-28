# Ecommerce Cart

A demo shopping cart application built with Laravel, Livewire, and Tailwind CSS.

---

## What This App Does

This is an online store where users can:

- **Browse Products** — View available products with prices and stock levels
- **Add to Cart** — Select products and add them to a shopping cart
- **Manage Cart** — Increase or decrease quantities, remove items
- **Place Orders** — Check out and place orders from the cart
- **View Order History** — See all past orders and their details

### For Store Admins

- **Low Stock Alerts** — Automatic email when products run low
- **Daily Sales Reports** — Email summary of daily orders and revenue

---

## Technical Guide

### Requirements

- PHP 8.2 or higher
- Composer
- Node.js and npm
- SQLite (default) or another database

### Installation

**Quick Setup:**

```bash
git clone git@github.com:SagarNaliyapara/simple-ecommerce.git
cd simple-ecommerce
composer run-script setup
```

**Manual Setup:**

```bash
# Install PHP dependencies
composer install

# Configure environment
cp .env.example .env
php artisan key:generate

# Run database migrations
php artisan migrate

# Install and build frontend
npm install
npm run build
```

### Running the Application

**Start development server:**

```bash
composer dev
```

This starts the web server, frontend build, queue worker, and log viewer. The app will be available at `http://localhost:8000`.

**Or start services individually:**

```bash
php artisan serve          # Web server
npm run dev                # Frontend with hot reload
php artisan queue:listen   # Process background jobs
```

### Configuration

Set `ADMIN_EMAIL` in your `.env` file to receive admin notifications:

```
ADMIN_EMAIL=admin@example.com
```

### Commands

**Generate daily sales report:**

```bash
# Report for today
php artisan report:daily-sales

# Report for a specific date
php artisan report:daily-sales --date=2026-01-15
```

**Run tests:**

```bash
php artisan test
```

**Seed database with sample products:**

```bash
php artisan db:seed
```
