# 🛍️ RetroLoved E-Commerce

Platform e-commerce modern untuk penjualan fashion vintage dan preloved dengan sistem manajemen produk, pesanan, dan customer yang lengkap.

[![PHP Version](https://img.shields.io/badge/PHP-8.0%2B-blue)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-5.7%2B-orange)](https://www.mysql.com/)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

---

## 👥 Tim Pengembang

- **Andre Abdilillah Ahwien** - andreabdilillah67@gmail.com
- **Gilang Ramadhan** - gilangg.rmdhn189@gmail.com

---

## 📋 Daftar Isi

- [Fitur Utama](#-fitur-utama)
- [Teknologi](#-teknologi)
- [Persyaratan Sistem](#-persyaratan-sistem)
- [Instalasi & Setup](#-instalasi--setup)
- [Konfigurasi](#-konfigurasi)
- [Penggunaan](#-penggunaan)
- [Struktur Folder](#-struktur-folder)
- [Troubleshooting](#-troubleshooting)
- [Keamanan](#-keamanan)
- [Lisensi](#-lisensi)

---

## ✨ Fitur Utama

### 🛒 Customer Features
- ✅ Registrasi dan login dengan verifikasi email OTP
- ✅ Browse dan search produk vintage/preloved
- ✅ Shopping cart dengan validasi stok real-time
- ✅ Checkout dengan multiple payment methods
- ✅ Manajemen profil dan multiple shipping addresses
- ✅ Real-time order tracking dengan timeline
- ✅ Notifikasi pesanan (in-app notifications)
- ✅ Recently viewed products
- ✅ Contact support system

### 👨‍💼 Admin Features
- ✅ Dashboard dengan statistik lengkap (revenue, orders, customers)
- ✅ Manajemen produk (CRUD dengan multiple images upload)
- ✅ Manajemen pesanan dan update status
- ✅ Manajemen customer (view, block/unblock)
- ✅ Konfirmasi pembayaran dengan preview bukti transfer
- ✅ Update tracking pesanan dengan detail lokasi
- ✅ Auto-generate tracking number dan courier assignment
- ✅ Export reports (coming soon)

### 🎨 UI/UX Features
- ✅ Responsive design (mobile, tablet, desktop)
- ✅ Modern toast notifications (no alert popups)
- ✅ Custom confirmation modals
- ✅ Smooth animations dan transitions
- ✅ Loading states dan skeleton screens
- ✅ Image lazy loading
- ✅ Form validation real-time

---

## 🛠️ Teknologi

### Backend
- **PHP** 8.0+ (OOP & Procedural)
- **MySQL/MariaDB** 5.7+ (Relational Database)
- **PHPMailer** 6.x (Email Service)
- **Composer** (Dependency Management)

### Frontend
- **HTML5** (Semantic Markup)
- **CSS3** (Custom Design System)
- **JavaScript** (Vanilla JS, ES6+)
- **SVG Icons** (Custom Icons)

### Server
- **Apache** 2.4+ (Web Server)
- **.htaccess** (URL Rewriting)

---

## 📦 Persyaratan Sistem

### Minimum Requirements
- **PHP:** 8.0 atau lebih tinggi
- **MySQL:** 5.7+ atau MariaDB 10.4+
- **Apache:** 2.4+ dengan mod_rewrite enabled
- **Composer:** 2.x
- **RAM:** 512 MB minimum
- **Storage:** 500 MB minimum

### PHP Extensions Required
```
- mysqli
- mbstring
- openssl
- json
- curl
- gd (untuk image processing)
```

### Recommended
- **PHP:** 8.1+
- **MySQL:** 8.0+
- **RAM:** 1 GB+
- **Storage:** 1 GB+

---

## 🚀 Instalasi & Setup

### Metode 1: Setup Otomatis (Recommended)

#### Step 1: Clone Repository
```bash
git clone https://github.com/ahwine/retroloved.git
cd retroloved
```

#### Step 2: Install Dependencies
```bash
composer install
```

#### Step 3: Buat Database
Buka phpMyAdmin atau MySQL CLI:
```sql
CREATE DATABASE retroloved CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

#### Step 4: Import Database
**Via phpMyAdmin:**
1. Pilih database `retroloved`
2. Klik tab "Import"
3. Pilih file `backup/retroloved.sql`
4. Klik "Go"

**Via Command Line:**
```bash
mysql -u root -p retroloved < backup/retroloved.sql
```

#### Step 5: Konfigurasi Database
Edit file `config/database.php`:
```php
$host = 'localhost';
$username = 'root';          // Ganti dengan username MySQL Anda
$password = '';              // Ganti dengan password MySQL Anda
$database = 'retroloved';
```

#### Step 6: Konfigurasi Email (Opsional)
Edit file `config/email.php`:
```php
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password');  // Gunakan App Password untuk Gmail
define('SMTP_PORT', 587);
```

**Cara Membuat Gmail App Password:**
1. Buka Google Account Settings
2. Security → 2-Step Verification
3. App passwords → Generate new password
4. Copy password dan paste ke config

#### Step 7: Set Folder Permissions
**Windows (XAMPP):**
- Folder sudah otomatis memiliki permission yang benar

**Linux/Mac:**
```bash
chmod 755 assets/images/products
chmod 755 assets/images/profiles
chmod 755 backup
```

#### Step 8: Jalankan Website
**Menggunakan XAMPP:**
1. Copy folder `retroloved` ke `C:\xampp\htdocs\`
2. Start Apache dan MySQL di XAMPP Control Panel
3. Buka browser: `http://localhost/retroloved`

**Menggunakan PHP Built-in Server:**
```bash
php -S localhost:8000
```
Buka browser: `http://localhost:8000`

#### Step 9: Login Admin
```
URL: http://localhost/retroloved/admin
Username: admin
Password: 123
Email: admin@retroloved.com
```

**⚠️ PENTING:** Ganti password admin setelah login pertama kali!

---

### Metode 2: Setup Manual

Jika Anda ingin setup manual, ikuti langkah berikut:

#### 1. Download/Clone Project
```bash
git clone https://github.com/ahwine/retroloved.git
```

#### 2. Install Composer Dependencies
```bash
cd retroloved
composer install
```

#### 3. Setup Database
```bash
# Buat database
mysql -u root -p -e "CREATE DATABASE retroloved CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Import database
mysql -u root -p retroloved < backup/retroloved.sql
```

#### 4. Copy Config Files
```bash
# Jika menggunakan example files
cp config/database.example.php config/database.php
cp config/email.example.php config/email.php
```

#### 5. Edit Config Files
Edit `config/database.php` dan `config/email.php` sesuai environment Anda.

#### 6. Set Permissions
```bash
chmod -R 755 assets/images
chmod -R 755 backup
```

#### 7. Start Server
```bash
php -S localhost:8000
```

---

## ⚙️ Konfigurasi

### Database Configuration
File: `config/database.php`
```php
<?php
// Database connection settings
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'retroloved';

// Timezone
date_default_timezone_set('Asia/Jakarta');
```

### Email Configuration
File: `config/email.php`
```php
<?php
// SMTP Configuration
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password');
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls');

// Email Settings
define('FROM_EMAIL', 'noreply@retroloved.com');
define('FROM_NAME', 'RetroLoved');
```

### Shipping Configuration
File: `config/shipping.php`
- Konfigurasi kurir dan layanan pengiriman
- Auto-generate tracking number
- Auto-assign courier driver

---

## 📖 Penggunaan

### Akun Default

#### Admin Account
```
URL: http://localhost/retroloved/admin
Username: admin
Password: 123
Email: admin@retroloved.com
```

#### Test Customer Account (Opsional)
Anda bisa membuat akun customer baru melalui halaman register.

### Workflow Customer

1. **Register** → Verifikasi email dengan OTP
2. **Browse Products** → Lihat katalog produk
3. **Add to Cart** → Tambahkan produk ke keranjang
4. **Checkout** → Isi data pengiriman dan pilih payment
5. **Upload Payment Proof** → Upload bukti transfer
6. **Track Order** → Pantau status pesanan real-time

### Workflow Admin

1. **Login** → Akses admin panel
2. **Dashboard** → Lihat statistik dan overview
3. **Manage Products** → Tambah/edit/hapus produk
4. **Manage Orders** → Konfirmasi payment, update status
5. **Manage Customers** → Lihat data customer, block jika perlu
6. **Update Tracking** → Update lokasi dan status pengiriman

---

## 📁 Struktur Folder

```
retroloved/
├── admin/                      # Admin panel
│   ├── dashboard.php          # Dashboard dengan statistik
│   ├── products.php           # Manajemen produk
│   ├── product-add.php        # Tambah produk baru
│   ├── product-edit.php       # Edit produk
│   ├── orders.php             # Manajemen pesanan
│   ├── order-detail.php       # Detail pesanan
│   └── customers.php          # Manajemen customer
├── assets/                     # Static assets
│   ├── css/                   # Stylesheets
│   │   ├── style.css         # Main stylesheet
│   │   ├── admin.css         # Admin styles
│   │   └── toast.css         # Toast notification styles
│   ├── js/                    # JavaScript files
│   │   ├── script.js         # Main script
│   │   ├── toast.js          # Toast notifications
│   │   ├── modal.js          # Modal dialogs
│   │   └── confirm-modal.js  # Confirmation modals
│   └── images/                # Images
│       ├── products/         # Product images
│       └── profiles/         # Profile pictures
├── auth/                       # Authentication
│   ├── process-auth.php      # Login/Register handler
│   ├── logout.php            # Logout handler
│   └── check-availability.php # Username/email checker
├── backup/                     # Database backups
│   └── retroloved.sql        # Database schema & data
├── config/                     # Configuration files
│   ├── database.php          # Database config
│   ├── email.php             # Email config
│   └── shipping.php          # Shipping config
├── customer/                   # Customer panel
│   ├── cart.php              # Shopping cart
│   ├── checkout.php          # Checkout page
│   ├── orders.php            # Order history
│   ├── order-detail.php      # Order detail & tracking
│   ├── product-detail.php    # Product detail page
│   ├── profile.php           # Customer profile
│   └── notifications.php     # Notifications
├── includes/                   # Reusable components
│   ├── header.php            # Header component
│   ├── footer.php            # Footer component
│   ├── shipping-selection.php # Shipping selector
│   └── tracking-timeline.php  # Order tracking timeline
├── vendor/                     # Composer dependencies
├── .gitignore                 # Git ignore file
├── .htaccess                  # Apache config
├── composer.json              # Composer config
├── index.php                  # Homepage
├── shop.php                   # Shop page
├── faq.php                    # FAQ page
├── how-it-works.php           # How it works page
├── privacy-policy.php         # Privacy policy
├── terms-conditions.php       # Terms & conditions
├── shipping-delivery.php      # Shipping info
├── size-guide.php             # Size guide
├── process-contact-support.php # Contact form handler
├── cleanup-files.bat          # Cleanup utility (Windows)
├── reset-database.bat         # Database reset utility (Windows)
├── reset-database-safe.sql    # Database reset script
├── INSTALL.md                 # Installation guide
├── PRE-LAUNCH-CHECKLIST.md    # Pre-launch checklist
└── README.md                  # This file
```

---

## 🐛 Troubleshooting

### Error: "Connection failed"
**Penyebab:** Database tidak terkoneksi

**Solusi:**
1. Pastikan MySQL/MariaDB sudah running
2. Cek konfigurasi di `config/database.php`
3. Pastikan database `retroloved` sudah dibuat
4. Cek username dan password MySQL

```bash
# Cek MySQL status (Linux/Mac)
sudo systemctl status mysql

# Cek MySQL status (Windows XAMPP)
# Buka XAMPP Control Panel, pastikan MySQL running
```

### Error: "Permission denied" saat upload gambar
**Penyebab:** Folder tidak memiliki write permission

**Solusi:**

**Windows:**
1. Klik kanan folder `assets/images/products`
2. Properties → Security
3. Edit → Users → Allow "Write"

**Linux/Mac:**
```bash
chmod -R 755 assets/images
chown -R www-data:www-data assets/images  # Untuk Apache
```

### Email tidak terkirim
**Penyebab:** Konfigurasi SMTP salah atau firewall blocking

**Solusi:**
1. Pastikan konfigurasi di `config/email.php` benar
2. Untuk Gmail, gunakan **App Password** bukan password biasa
3. Cek firewall tidak memblokir port 587
4. Test dengan script sederhana:

```php
<?php
require 'vendor/autoload.php';
require 'config/email.php';

$result = EmailHelper::send(
    'test@example.com',
    'Test Email',
    'This is a test email'
);

echo $result ? 'Email sent!' : 'Email failed!';
```

### Halaman blank/error 500
**Penyebab:** PHP error atau missing extension

**Solusi:**
1. Enable error display di `php.ini`:
```ini
display_errors = On
error_reporting = E_ALL
```

2. Cek PHP error log:
```bash
# Linux/Mac
tail -f /var/log/apache2/error.log

# Windows XAMPP
# Lihat di: C:\xampp\apache\logs\error.log
```

3. Pastikan PHP extensions aktif:
```bash
php -m | grep mysqli
php -m | grep mbstring
php -m | grep openssl
```

### Composer install gagal
**Penyebab:** Composer tidak terinstall atau network issue

**Solusi:**
1. Install Composer: https://getcomposer.org/download/
2. Update Composer:
```bash
composer self-update
```

3. Clear cache dan retry:
```bash
composer clear-cache
composer install
```

### Database import error
**Penyebab:** SQL syntax error atau charset issue

**Solusi:**
1. Pastikan MySQL version minimal 5.7
2. Set charset saat import:
```bash
mysql -u root -p --default-character-set=utf8mb4 retroloved < backup/retroloved.sql
```

3. Atau via phpMyAdmin, pilih charset: `utf8mb4_unicode_ci`

---

## 🔒 Keamanan

### Untuk Production

#### 1. Ganti Password Default
```sql
-- Login ke MySQL
UPDATE users SET password = MD5('password_baru_yang_kuat') WHERE username = 'admin';
```

#### 2. Update Database Credentials
Edit `config/database.php`:
```php
$username = 'retroloved_user';  // Jangan gunakan root
$password = 'password_yang_kuat_dan_random';
```

#### 3. Disable Error Display
Edit `php.ini` atau `.htaccess`:
```ini
display_errors = Off
log_errors = On
error_log = /path/to/error.log
```

#### 4. Enable HTTPS
```apache
# .htaccess
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

#### 5. Secure File Permissions
```bash
# Linux/Mac
chmod 644 config/*.php
chmod 755 assets/images
```

#### 6. Backup Database Regularly
```bash
# Setup cron job untuk backup otomatis
0 2 * * * mysqldump -u root -p retroloved > /backup/retroloved_$(date +\%Y\%m\%d).sql
```

#### 7. Update Dependencies
```bash
composer update
```

#### 8. Enable Security Headers
Tambahkan di `.htaccess`:
```apache
Header set X-Content-Type-Options "nosniff"
Header set X-Frame-Options "SAMEORIGIN"
Header set X-XSS-Protection "1; mode=block"
```

---

## 🧪 Testing

### Manual Testing
1. Register akun baru → Cek email OTP
2. Login → Cek session
3. Browse products → Cek pagination
4. Add to cart → Cek cart count
5. Checkout → Cek order creation
6. Upload payment → Cek file upload
7. Admin: Confirm payment → Cek status update
8. Track order → Cek timeline

### Database Reset (Untuk Testing)
```bash
# Windows
reset-database.bat

# Manual via phpMyAdmin
# Import file: reset-database-safe.sql
```

---

## 📝 Changelog

### Version 1.0.0 (Current)
- ✅ Initial release
- ✅ Customer registration dengan email OTP
- ✅ Product management dengan multiple images
- ✅ Shopping cart dan checkout
- ✅ Order tracking dengan timeline
- ✅ Admin dashboard dengan statistik
- ✅ Payment confirmation system
- ✅ Notification system
- ✅ Responsive design

---

## 🤝 Contributing

Contributions are welcome! Jika Anda ingin berkontribusi:

1. Fork repository ini
2. Buat branch baru (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add some AmazingFeature'`)
4. Push ke branch (`git push origin feature/AmazingFeature`)
5. Buat Pull Request

---

## 📄 Lisensi

Project ini dibuat untuk keperluan pembelajaran dan portfolio.

---

## 📞 Kontak & Support

Untuk pertanyaan, bug report, atau feature request:

- **Andre Abdilillah Ahwien**
  - Email: andreabdilillah67@gmail.com
  - GitHub: [@ahwine](https://github.com/ahwine)

- **Gilang Ramadhan**
  - Email: gilangg.rmdhn189@gmail.com

---

## 🙏 Acknowledgments

- PHPMailer untuk email service
- Font Awesome untuk icons (jika digunakan)
- Inspiration dari berbagai e-commerce platforms

---

<div align="center">

**RetroLoved** - Vintage Fashion, Modern Style 🛍️

Made with ❤️ by Andre & Gilang

[⬆ Back to Top](#-retroloved-e-commerce)

</div>
