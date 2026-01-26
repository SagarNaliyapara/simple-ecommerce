# E-commerce Shopping Cart - User Guide

## Overview
A complete e-commerce shopping cart system built with Laravel 12, Livewire 3, and Tailwind CSS. Users can browse products, add items to their cart, update quantities, and manage their shopping cart with real-time updates.

## Features

### ✅ User Authentication
- Laravel Breeze authentication system
- User registration and login
- Email verification support
- Password reset functionality

### ✅ Product Management
- 15 sample products pre-loaded
- Product details: name, price, stock quantity
- Out-of-stock handling
- Stock validation on add to cart

### ✅ Shopping Cart
- User-specific carts (database-backed, not session-based)
- Add products to cart
- Update item quantities (increment/decrement)
- Remove items from cart
- Real-time cart count badge in navigation
- Stock limit enforcement
- Total price calculation
- Subtotal per item

### ✅ User Experience
- Responsive design (mobile, tablet, desktop)
- Real-time updates with Livewire
- Loading states for async operations
- Success/error flash messages
- Confirmation dialogs for item removal
- Cart badge with live item count
- Clean and modern UI with Tailwind CSS

## Getting Started

### Prerequisites
- PHP 8.2 or higher
- MySQL database
- Composer
- Node.js & npm

### Installation Steps

1. **Database Configuration**
   - Create a MySQL database named `ecommerce_cart`
   - Update `.env` file with your database credentials:
     ```
     DB_DATABASE=ecommerce_cart
     DB_USERNAME=root
     DB_PASSWORD=your_password
     ```

2. **Run Migrations** (Already done)
   ```bash
   php artisan migrate
   ```

3. **Seed Products** (Already done)
   ```bash
   php artisan db:seed --class=ProductSeeder
   ```

4. **Install Frontend Dependencies** (If not done)
   ```bash
   npm install
   npm run dev
   ```

5. **Start the Development Server**
   ```bash
   php artisan serve
   ```

## Usage

### 1. Register/Login
- Visit `http://localhost:8000/register` to create an account
- Or login at `http://localhost:8000/login` if you have an account

### 2. Browse Products
- Navigate to "Products" in the main navigation
- View all available products with:
  - Product name
  - Price
  - Stock quantity
  - Add to Cart button

### 3. Add to Cart
- Click "Add to Cart" button on any product
- Out-of-stock products cannot be added
- If product already in cart, quantity increases
- Success message appears on successful addition
- Cart badge updates automatically

### 4. View Cart
- Click "Cart" in the navigation (shows item count badge)
- View all items in your cart with:
  - Product details
  - Current quantity
  - Subtotal per item
  - Total price

### 5. Manage Cart Items
- **Increase Quantity**: Click the "+" button
- **Decrease Quantity**: Click the "-" button
- **Remove Item**: Click the trash icon (with confirmation)
- Quantities cannot exceed available stock
- Decreasing to 0 removes the item

### 6. Navigation
- **Dashboard**: Home page after login
- **Products**: Browse all products
- **Cart**: View and manage cart items
- **Profile**: Update account information
- **Log Out**: End your session

## Technical Implementation

### Database Structure

#### Products Table
- `id`: Primary key
- `name`: Product name (string, 100 chars)
- `price`: Decimal price
- `stock_quantity`: Integer
- `timestamps`: Created/Updated dates

#### Cart Items Table
- `id`: Primary key
- `user_id`: Foreign key to users table (cascading delete)
- `product_id`: Foreign key to products table (cascading delete)
- `quantity`: Unsigned integer (default: 1)
- `timestamps`: Created/Updated dates
- **Unique constraint**: (user_id, product_id) - prevents duplicates

### Models & Relationships

#### User Model
- `cartItems()`: hasMany relationship to CartItem

#### Product Model
- `cartItems()`: hasMany relationship to CartItem
- Mass assignable: name, price, stock_quantity
- Casts: price (decimal:2), stock_quantity (integer)

#### CartItem Model
- `user()`: belongsTo relationship to User
- `product()`: belongsTo relationship to Product
- `getSubtotalAttribute()`: Computed subtotal (quantity × price)
- Mass assignable: user_id, product_id, quantity

### Livewire Components

#### ProductsList Component
- Displays all products in responsive grid
- Handles "Add to Cart" action
- Stock validation
- Emits 'cart-updated' event

#### Cart Component
- Displays user's cart items
- Update quantity (increment/decrement)
- Remove item functionality
- Calculates and displays total
- Listens to 'cart-updated' event

#### CartCount Component
- Displays cart item count badge
- Real-time updates via 'cart-updated' event
- Shows in navigation

### Routes
- `/products` - Product listing (authenticated)
- `/cart` - Shopping cart (authenticated)
- `/dashboard` - User dashboard (authenticated)
- `/profile` - User profile (authenticated)

## Best Practices Used

1. **Laravel Conventions**: Following Laravel naming conventions and directory structure
2. **Database Integrity**: Foreign key constraints with cascade deletes
3. **Data Validation**: Stock quantity validation before adding/updating
4. **Security**: Auth middleware protection on all routes
5. **User Isolation**: Users can only access/modify their own cart items
6. **Unique Constraints**: Preventing duplicate cart entries per user
7. **Real-time Updates**: Livewire events for instant UI updates
8. **Responsive Design**: Mobile-first Tailwind CSS approach
9. **User Feedback**: Flash messages and loading states
10. **Code Organization**: Separation of concerns with components

## Sample Products

The system comes with 15 pre-loaded products including:
- Wireless Bluetooth Headphones ($89.99)
- Smart Watch Series 5 ($299.99)
- USB-C Charging Cable ($19.99)
- Portable Power Bank 20000mAh ($45.99)
- Mechanical Gaming Keyboard ($129.99)
- And 10 more products...

Note: One product (External SSD 1TB) has 0 stock to demonstrate out-of-stock handling.

## Support

For issues or questions, check:
- Laravel documentation: https://laravel.com/docs
- Livewire documentation: https://livewire.laravel.com
- Tailwind CSS documentation: https://tailwindcss.com

## Development Notes

- Built with Laravel 12.48.1
- Livewire 3.6.4
- PHP 8.3.30
- MySQL database
- Session driver: database
- Queue connection: database
- Cache store: database

---

**Enjoy your shopping experience!** 🛒
