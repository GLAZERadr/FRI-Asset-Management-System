# FRI Asset Management System

Welcome to the **FRI Asset Management System** – a Laravel-based web application designed to manage asset in Fakultas Rekayasa Industri. This document will guide you through setting up the project on your local development environment.

---

## 📦 Requirements

- PHP >= 8.1
- Composer
- Node.js & NPM
- Laravel 12+
- XAMPP or any LAMP stack with MySQL
- ImageMagick + PHP Imagick extension

---

## 🛠️ Installing ImageMagick and the Imagick PHP Extension

The application uses the Imagick extension for image processing. Below are the steps to install it on both Linux (Ubuntu/Debian) and Windows (XAMPP).

### Ubuntu / Debian (LAMP)

1. Update package lists

```bash
sudo apt update
```

2. Install ImageMagick and the PHP Imagick extension

```bash
sudo apt install imagemagick php-imagick
```

3. Verify installation

```bash
php -m | grep imagick
```

You should see imagick listed in the output.

4. Restart your web server

    * If you’re using Apache:

    ```bash
    sudo service apache2 restart
    ```

    * If you’re using Nginx + PHP-FPM:

    ```bash
    sudo service php8.1-fpm restart
    sudo service nginx restart
    ```

---

### Windows (XAMPP)

1. Download ImageMagick

    * Go to: https://imagemagick.org/script/download.php#windows

    * Download the dynamic Win64 (or Win32) binary that matches your PHP architecture (e.g., Q16 HDRI DLL).

    * Install ImageMagick (e.g., to C:\Program Files\ImageMagick-7.1.0-Q16-HDRI).

2. Add ImageMagick to PATH

    * Open System Properties → Environment Variables → under “System variables” find and edit Path.

    * Add the directory where convert.exe is located. For example:

    ```bash
    C:\Program Files\ImageMagick-7.1.0-Q16-HDRI\
    ```

3. Enable php_imagick.dll in XAMPP

    * Locate your PHP folder inside XAMPP (e.g., C:\xampp\php\ext).

    * Copy the correct php_imagick.dll into C:\xampp\php\ext.
        * You can download the matching DLL from PECL: https://pecl.php.net/package/imagick
        
        * Choose the DLL that matches your PHP version (e.g., php_imagick-3.7.0-8.1-ts-win64.zip → unzip → copy php_imagick.dll).
    
    * Open C:\xampp\php\php.ini and add (or uncomment) the following line:

    ```bash
    extension=imagick
    ```

    * Save php.ini.

4. Restart Apache

    * Open the XAMPP Control Panel.

    * Stop and then Start Apache.

5. Verify installation

    * Create a phpinfo.php in your htdocs folder with:

    ```bash
    <?php
    phpinfo();
    ```

    * Visit http://localhost/phpinfo.php in your browser.

    * Search for “imagick” on that page; if installed correctly, you’ll see an “Imagick” section.

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
