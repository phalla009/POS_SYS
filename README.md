<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="300" alt="Laravel Logo"></a></p>

<h1 align="center">POS_SYS</h1>

<p align="center">
A Point of Sale (POS) system for managing store inventory, sales, and customers — built on the <a href="https://laravel.com">Laravel</a> framework.
</p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Laravel Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About PhonestoreSystem

POS_SYS is a Point of Sale application designed for retail stores. It handles the day-to-day operations of a store — selling products, tracking stock, managing customers and staff — through a simple, fast interface built on Laravel's ecosystem.

### Key Features

- **Sales & Checkout** — Process sales transactions quickly with an intuitive POS interface.
- **Inventory Management** — Track product stock levels, receive new stock, and get low-stock alerts.
- **Product Catalog** — Manage products, categories, brands, and pricing.
- **Customer Management** — Store customer records and purchase history.
- **User Roles & Permissions** — Separate access for admins, cashiers, and staff.
- **Sales Reports** — View daily, weekly, and monthly sales summaries.
- **Receipts & Invoicing** — Generate printable receipts and invoices for each transaction.

## Tech Stack

- **Framework:** [Laravel](https://laravel.com)
- **Database:** MySQL (or your configured driver)
- **Frontend:** Blade / [add your frontend stack, e.g. Livewire, Vue, React]

## Getting Started

### Requirements

- PHP >= 8.1
- Composer
- MySQL or another supported database
- Node.js & NPM (for compiling frontend assets)

### Installation

```bash
# Clone the repository
git clone https://github.com/phalla009/POS_SYS
cd POS_SYS

# Install PHP dependencies
composer install

# Install JS dependencies
npm install

# Copy environment file and set your config
cp .env.example .env
php artisan key:generate

# Run database migrations (and seeders, if available)
php artisan migrate --seed

# Build frontend assets
npm run dev

# Serve the application
php artisan serve
```

Update your `.env` file with your database credentials and any store-specific configuration before running migrations.

## Usage

Once installed, log in with your admin account to:

1. Set up your product catalog and categories.
2. Add initial stock and inventory counts.
3. Create cashier/staff accounts with appropriate permissions.
4. Start processing sales from the POS screen.

## Contributing

Contributions are welcome. Please open an issue to discuss significant changes before submitting a pull request.

## License

This project is open-sourced software. See the [LICENSE](LICENSE) file for details.
