# RetroLoved E-Commerce

Platform e-commerce untuk penjualan fashion vintage dan preloved dengan sistem manajemen produk, pesanan, dan customer yang lengkap.

## Pembuat

- **Andre Abdilillah Ahwien**
- **Gilang Ramadhan**

## Fitur Utama

### Customer
- Registrasi dan login dengan verifikasi email
- Browse dan search produk vintage
- Shopping cart dan checkout
- Manajemen profil dan alamat pengiriman
- Tracking pesanan real-time
- Notifikasi pesanan
- Contact support

### Admin
- Dashboard dengan statistik lengkap
- Manajemen produk (CRUD dengan multiple images)
- Manajemen pesanan dan status
- Manajemen customer (block/unblock)
- Konfirmasi pembayaran
- Update tracking pesanan

## Teknologi

- **Backend:** PHP 8.x
- **Database:** MySQL/MariaDB
- **Frontend:** HTML5, CSS3, JavaScript (Vanilla)
- **Server:** Apache
- **Email:** PHPMailer

## Persyaratan Sistem

- PHP 8.0 atau lebih tinggi
- MySQL 5.7+ atau MariaDB 10.4+
- Apache Web Server
- Composer (untuk dependencies)
- Extension PHP: mysqli, mbstring, openssl

## Setup dan Instalasi

### 1. Clone Repository

```bash
git clone https://github.com/username/retroloved.git
cd retroloved
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Konfigurasi Database

**A. Buat Database**

Buka phpMyAdmin atau MySQL CLI:

```sql
CREATE DATABASE retroloved CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

**B. Import Database**

Via phpMyAdmin:
1. Pilih database `retroloved`
2. Klik tab "Import"
3. Pilih file `retroloved.sql`
4. Klik "Go"

Via Command Line:
```bash
mysql -u root -p retroloved < retroloved.sql
```

### 4. Konfigurasi Koneksi Database

Edit file `config/database.php`:

```php
$host = 'localhost';
$username = 'root';        // Sesuaikan dengan username MySQL Anda
$password = '';            // Sesuaikan dengan password MySQL Anda
$database = 'retroloved';
```

### 5. Konfigurasi Email (Opsional)

Edit file `config/email.php` untuk mengatur SMTP:

```php
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password');
define('SMTP_PORT', 587);
```

**Catatan:** Untuk Gmail, gunakan App Password, bukan password akun biasa.

### 6. Setup Folder Permissions

Pastikan folder berikut memiliki permission write:

```bash
chmod 755 assets/images/products
chmod 755 assets/images/profiles
```

Windows (via File Explorer):
- Klik kanan folder → Properties → Security
- Pastikan "Users" memiliki permission "Write"

### 7. Jalankan di Localhost

**Menggunakan XAMPP:**
1. Copy folder project ke `C:\xampp\htdocs\retroloved`
2. Start Apache dan MySQL di XAMPP Control Panel
3. Buka browser: `http://localhost/retroloved`

**Menggunakan PHP Built-in Server:**
```bash
php -S localhost:8000
```
Buka browser: `http://localhost:8000`

### 8. Login Admin

Akun admin default:
- **Username:** admin
- **Password:** 123
- **Email:** admin@retroloved.com

**PENTING:** Ganti password admin setelah login pertama kali!

## Struktur Folder

```
retroloved/
├── admin/              # Panel admin
├── assets/             # CSS, JS, Images
├── auth/               # Authentication (login, register)
├── config/             # Konfigurasi database & email
├── customer/           # Panel customer
├── database/           # Database utilities
├── includes/           # Header, footer, components
├── vendor/             # Composer dependencies
├── index.php           # Homepage
└── retroloved.sql      # Database schema
```

## Dokumentasi Tambahan

- **ERD_DIAGRAM.md** - Entity Relationship Diagram
- **DFD_DIAGRAM.md** - Data Flow Diagram

## Troubleshooting

### Error: "Connection failed"
- Pastikan MySQL/MariaDB sudah running
- Cek konfigurasi di `config/database.php`
- Pastikan database `retroloved` sudah dibuat

### Error: "Permission denied" saat upload gambar
- Pastikan folder `assets/images/products` dan `assets/images/profiles` memiliki permission write
- Windows: Cek Security settings di folder properties
- Linux/Mac: `chmod 755 assets/images/products`

### Email tidak terkirim
- Pastikan konfigurasi SMTP di `config/email.php` sudah benar
- Untuk Gmail, gunakan App Password
- Cek firewall tidak memblokir port 587

### Halaman blank/error 500
- Cek PHP error log di `php_error.log`
- Pastikan PHP version minimal 8.0
- Pastikan extension mysqli sudah aktif

## Keamanan

Untuk production, pastikan:
1. Ganti password admin default
2. Ganti database password
3. Aktifkan HTTPS
4. Update `config/database.php` dengan credentials yang aman
5. Set `display_errors = Off` di php.ini
6. Backup database secara berkala

## Lisensi

Project ini dibuat untuk keperluan pembelajaran dan portfolio.

## Kontak

Untuk pertanyaan atau dukungan, silakan hubungi:
- Andre Abdilillah Ahwien   | andreabdilillah67@gmail.com
- Gilang Ramadhan           | gilangg.rmdhn189@gmail.com

---

**RetroLoved** - Vintage Fashion, Modern Style
