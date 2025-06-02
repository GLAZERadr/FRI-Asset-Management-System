# FRI Asset Management System

Welcome to the **FRI Asset Management System** – a Laravel-based web application designed to manage asset in Fakultas Rekayasa Industri. This document will guide you through setting up the project on your local development environment.

---

## 📦 Requirements

- PHP >= 8.1
- Composer
- Node.js & NPM
- Laravel 12+
- XAMPP or any LAMP stack with MySQL

---

## ⚙️ Setup Instructions

### 1. Install Laravel Dependencies

```bash
composer install
```

### 2. Copy Environment File

```bash
cp .env.example .env
```

Then generate the application key:

```bash
php artisan key:generate
```

### 3. Create Database

* Open XAMPP Control Panel and start MySQL.

* Go to http://localhost/phpmyadmin and create a new database named:

```bash
fri_asset_management
```

### 4. Configure Environment Variables

Open the .env file and set the database section like this:

```bash
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=fri_asset_management
DB_USERNAME=root
DB_PASSWORD=
```

(Adjust DB_USERNAME and DB_PASSWORD based on your XAMPP settings)

### 5. Migrate and Seed Database

```bash
php artisan migrate:fresh --seed
```

This will drop all tables, re-run all migrations, and populate the database with initial data.

### 6. Install Frontend Dependencies

```bash
npm install
```

Then compile the assets:

```bash
npm run dev
```

### 7. Start the Development Server

```bash
php artisan serve
```

Visit the application at:
👉 http://localhost:8000

---

## 📁 Project Assets
Inalum logo is located at:
public/images/logo fri.png

---

## 🛠 Development Notes
* Use npm run build for production-ready asset bundling.
* Use php artisan migrate:refresh --seed to refresh data during testing.
* Vite is used as the frontend build tool.
