# 📚 Dokumentasi Lengkap Aplikasi POS Kasir

Dokumen ini menjelaskan secara komprehensif tentang arsitektur, alur kerja, struktur database, keamanan, dan cara pengembangan aplikasi POS Kasir.

---

## 📋 Daftar Isi

1. [Persyaratan Sistem](#1-persyaratan-sistem)
2. [Instalasi](#2-instalasi)
3. [Arsitektur Sistem](#3-arsitektur-sistem)
4. [Struktur Database (ERD)](#4-struktur-database-erd)
5. [Alur Kerja Aplikasi](#5-alur-kerja-aplikasi)
6. [Sistem Keamanan](#6-sistem-keamanan)
7. [Manajemen Role](#7-manajemen-role)
8. [Cara Kerja Full-Stack](#8-cara-kerja-full-stack)
9. [Panduan Development](#9-panduan-development)
10. [Seeder & Factory](#10-seeder--factory)
11. [Fitur Lengkap](#11-fitur-lengkap)

---

## 1. Persyaratan Sistem

| Komponen        | Versi Minimum |
| --------------- | ------------- |
| PHP             | >= 8.2        |
| MySQL / MariaDB | >= 8.0 / 10.4 |
| Composer        | >= 2.0        |
| Node.js         | >= 18.0       |
| NPM             | >= 9.0        |

---

## 2. Instalasi

```bash
# 1. Clone repository
git clone <repository-url>
cd kasir

# 2. Install dependencies
composer install
npm install

# 3. Build assets
npm run build

# 4. Setup environment
cp .env.example .env
php artisan key:generate

# 5. Konfigurasi database di .env
# DB_DATABASE=pos_kasir
# DB_USERNAME=root
# DB_PASSWORD=

# 6. Jalankan migrasi + seeder
php artisan migrate --seed

# 7. Jalankan aplikasi
php artisan serve
```

**Akun Default:** `admin@admin.com` / `password`

---

## 3. Arsitektur Sistem

Aplikasi ini menggunakan **Clean Architecture** dengan pola **Repository Pattern** untuk memisahkan tanggung jawab setiap layer.

```
┌─────────────────────────────────────────────────────────────┐
│                     PRESENTATION LAYER                      │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────────────┐  │
│  │   Blade     │  │  Alpine.js  │  │  Tailwind CSS       │  │
│  │   Views     │  │  (Frontend) │  │  (Styling)          │  │
│  └─────────────┘  └─────────────┘  └─────────────────────┘  │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                     APPLICATION LAYER                       │
│  ┌─────────────────┐  ┌─────────────────┐                   │
│  │   Controllers   │  │   Form Requests │                   │
│  │   (Admin/Kasir) │  │   (Validation)  │                   │
│  └─────────────────┘  └─────────────────┘                   │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                      DOMAIN LAYER                           │
│  ┌─────────────────┐  ┌─────────────────┐                   │
│  │    Services     │  │   Repositories  │                   │
│  │  (Business      │  │   (Data Access) │                   │
│  │   Logic)        │  │                 │                   │
│  └─────────────────┘  └─────────────────┘                   │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                   INFRASTRUCTURE LAYER                      │
│  ┌─────────────────┐  ┌─────────────────┐                   │
│  │   Eloquent      │  │    MySQL/       │                   │
│  │   Models        │  │    MariaDB      │                   │
│  └─────────────────┘  └─────────────────┘                   │
└─────────────────────────────────────────────────────────────┘
```

### Struktur Folder Utama

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Admin/           # Controller untuk Admin
│   │   │   ├── ProductController.php
│   │   │   ├── UserController.php
│   │   │   ├── TransactionController.php
│   │   │   ├── StockAdjustmentController.php
│   │   │   ├── ReportController.php
│   │   │   ├── ActivityLogController.php
│   │   │   └── SettingController.php
│   │   └── Kasir/           # Controller untuk Kasir
│   │       └── PosController.php
│   ├── Middleware/
│   │   └── RoleMiddleware.php
│   └── Requests/            # Form Request Validation
│       ├── StoreProductRequest.php
│       └── UpdateProductRequest.php
├── Models/                  # Eloquent Models
│   ├── User.php
│   ├── Product.php
│   ├── Transaction.php
│   ├── TransactionItem.php
│   ├── StockAdjustment.php
│   ├── ActivityLog.php
│   └── Setting.php
├── Repositories/            # Data Access Layer
│   ├── ProductRepository.php
│   ├── UserRepository.php
│   ├── TransactionRepository.php
│   └── StockAdjustmentRepository.php
└── Services/                # Business Logic Layer
    ├── ProductService.php
    ├── TransactionService.php
    └── StockAdjustmentService.php
```

---

## 4. Struktur Database (ERD)

```
┌──────────────────┐       ┌──────────────────┐
│      users       │       │    products      │
├──────────────────┤       ├──────────────────┤
│ id (PK)          │       │ id (PK)          │
│ name             │       │ name             │
│ email (UNIQUE)   │       │ barcode (UNIQUE) │
│ password         │       │ purchase_price   │  ← Harga Beli/Modal
│ role (admin/kasir)       │ price            │  ← Harga Jual
│ created_at       │       │ stock            │
│ updated_at       │       │ image            │
└────────┬─────────┘       │ deleted_at       │  ← Soft Delete
         │                 │ created_at       │
         │                 └────────┬─────────┘
         │                          │
         ▼                          ▼
┌──────────────────┐       ┌──────────────────┐
│  transactions    │       │transaction_items │
├──────────────────┤       ├──────────────────┤
│ id (PK)          │◄──────│ id (PK)          │
│ user_id (FK)     │       │ transaction_id(FK)
│ invoice_code     │       │ product_id (FK)  │───►
│ total            │       │ quantity         │
│ cash             │       │ price            │  ← Harga saat beli
│ change           │       │ purchase_price   │  ← Modal saat beli
│ created_at       │       │ subtotal         │
└──────────────────┘       └──────────────────┘

┌──────────────────┐       ┌──────────────────┐
│stock_adjustments │       │  activity_logs   │
├──────────────────┤       ├──────────────────┤
│ id (PK)          │       │ id (PK)          │
│ product_id (FK)  │       │ user_id (FK)     │
│ user_id (FK)     │       │ action           │
│ type (in/out)    │       │ description      │
│ quantity         │       │ model_type       │
│ notes            │       │ model_id         │
│ created_at       │       │ ip_address       │
└──────────────────┘       │ created_at       │
                           └──────────────────┘

┌──────────────────┐
│    settings      │
├──────────────────┤
│ id (PK)          │
│ key (UNIQUE)     │  ← store_name, store_address, dll.
│ value            │
│ created_at       │
└──────────────────┘
```

### Relasi Antar Tabel

| Tabel             | Relasi    | Tabel Tujuan                                   |
| ----------------- | --------- | ---------------------------------------------- |
| users             | hasMany   | transactions, stock_adjustments, activity_logs |
| products          | hasMany   | transaction_items, stock_adjustments           |
| transactions      | belongsTo | users                                          |
| transactions      | hasMany   | transaction_items                              |
| transaction_items | belongsTo | transactions, products                         |

---

## 5. Alur Kerja Aplikasi

### 5.1 Alur Transaksi POS

```
┌─────────┐    ┌─────────┐    ┌─────────────┐    ┌──────────┐
│  Kasir  │───►│  Scan/  │───►│   Keranjang │───►│  Bayar   │
│  Login  │    │  Klik   │    │   (Alpine)  │    │  (POST)  │
└─────────┘    │ Produk  │    └─────────────┘    └────┬─────┘
               └─────────┘                            │
                                                      ▼
┌─────────┐    ┌─────────┐    ┌─────────────┐    ┌──────────┐
│  Cetak  │◄───│ Success │◄───│  Kurangi    │◄───│ Validasi │
│  Struk  │    │  Modal  │    │  Stok       │    │  Stok    │
└─────────┘    └─────────┘    └─────────────┘    └──────────┘
```

### 5.2 Alur Data Transaksi (Backend)

```php
// 1. Request masuk ke PosController
PosController::checkout(Request $request)
    │
    ▼
// 2. Validasi oleh TransactionService
TransactionService::createTransaction($items, $cash)
    │
    ├── Validasi stok setiap item
    ├── Hitung total
    ├── Simpan transaction + items
    ├── Update stok produk (decrement)
    └── Return transaction data
    │
    ▼
// 3. Response JSON ke frontend
{
    "success": true,
    "transaction": { ... }
}
```

---

## 6. Sistem Keamanan

### 6.1 Authentication

- Menggunakan **Laravel Breeze** dengan session-based auth
- Password di-hash menggunakan **bcrypt**

### 6.2 Authorization

- **Role-based Access Control (RBAC)** via `RoleMiddleware`
- Middleware mendefinisikan route mana yang bisa diakses role tertentu

### 6.3 CSRF Protection

- Setiap form memiliki `@csrf` token
- AJAX request menyertakan `X-CSRF-TOKEN` header

### 6.4 Input Validation

- Semua input divalidasi via **Form Request** (`StoreProductRequest`, dll.)
- Mencegah SQL Injection dan XSS

### 6.5 Audit Trail

- Setiap aksi penting dicatat di tabel `activity_logs`
- Mencatat: User, Aksi, Deskripsi, IP Address, Waktu

```php
// Contoh logging
ActivityLog::log('Tambah Produk', "Menambahkan produk: {$product->name}", $product);
```

### 6.6 Rate Limiting

- Route POS search dibatasi **60 request/menit**
- Route checkout dibatasi **30 request/menit**
- Mencegah brute force dan spam

```php
// routes/web.php
Route::post('/pos/checkout', [PosController::class, 'checkout'])
    ->middleware('throttle:30,1');
```

### 6.7 Pessimistic Locking

- Transaksi menggunakan `lockForUpdate()` untuk mencegah **race conditions**
- Mencegah **overselling** saat checkout bersamaan

```php
// TransactionService.php
$products = Product::query()
    ->whereIn('id', $productIds)
    ->lockForUpdate()  // Lock database rows
    ->get();
```

### 6.8 Slow Query Logging

- Query yang lebih dari 1 detik otomatis dicatat di log
- Hanya aktif di mode debug (development)

---

## 7. Manajemen Role

### Role yang Tersedia

| Role      | Akses                                              |
| --------- | -------------------------------------------------- |
| **admin** | Semua fitur (Produk, User, Laporan, Setting, dll.) |
| **kasir** | POS, Transaksi (lihat), Dashboard                  |

### Implementasi Middleware ```php

// app/Http/Middleware/RoleMiddleware.php
public function handle($request, Closure $next, ...$roles)
{
if (!in_array(auth()->user()->role, $roles)) {
        abort(403);
    }
    return $next($request);
}

````

### Penggunaan di Routes
```php
// routes/web.php

// Admin only
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('products', ProductController::class);
    Route::resource('users', UserController::class);
});

// Admin & Kasir
Route::middleware(['auth', 'role:admin,kasir'])->group(function () {
    Route::get('/pos', [PosController::class, 'index']);
});
````

---

## 8. Cara Kerja Full-Stack

### 8.1 Frontend Stack

| Teknologi           | Fungsi                              |
| ------------------- | ----------------------------------- |
| **Blade**           | Templating engine Laravel           |
| **Tailwind CSS v4** | Styling utility-first               |
| **Alpine.js**       | Reactive frontend (POS, Cart, dll.) |

### 8.2 Backend Stack

| Teknologi              | Fungsi               |
| ---------------------- | -------------------- |
| **Laravel 12**         | PHP Framework        |
| **Eloquent ORM**       | Database Abstraction |
| **Repository Pattern** | Data Access Layer    |
| **Service Layer**      | Business Logic       |

### 8.3 Contoh Alur Full-Stack (POS)

**1. View (Blade + Alpine.js)**

```html
<!-- resources/views/kasir/pos/index.blade.php -->
<div x-data="posApp()">
    <button @click="addToCart(product)">Tambah</button>
    <button @click="checkout">Bayar</button>
</div>

<script>
    function posApp() {
        return {
            cart: [],
            async checkout() {
                const response = await fetch("/kasir/pos/checkout", {
                    method: "POST",
                    body: JSON.stringify({
                        items: this.cart,
                        cash: this.cashReceived,
                    }),
                });
                // Handle response...
            },
        };
    }
</script>
```

**2. Controller**

```php
// app/Http/Controllers/Kasir/PosController.php
public function checkout(Request $request)
{
    $transaction = $this->transactionService->createTransaction(
        $request->items,
        $request->cash
    );

    return response()->json(['success' => true, 'transaction' => $transaction]);
}
```

**3. Service (Business Logic)**

```php
// app/Services/TransactionService.php
public function createTransaction(array $items, int $cash): Transaction
{
    return DB::transaction(function () use ($items, $cash) {
        // Validasi stok
        // Buat transaction
        // Buat transaction_items
        // Kurangi stok produk
        // Return transaction
    });
}
```

**4. Repository (Data Access)**

```php
// app/Repositories/TransactionRepository.php
public function create(array $data): Transaction
{
    return $this->model->create($data);
}
```

---

## 9. Panduan Development

### 9.1 Menjalankan Development Server

```bash
# Terminal 1: Laravel + Vite
composer run dev

# Atau jalankan terpisah:
# Terminal 1: Laravel
php artisan serve

# Terminal 2: Vite (Hot Reload)
npm run dev
```

### 9.2 Membuat Fitur Baru

**Contoh: Menambah Modul Supplier**

```bash
# 1. Buat Model + Migration + Controller + Factory + Seeder
php artisan make:model Supplier -mfsc

# 2. Buat Repository
# app/Repositories/SupplierRepository.php

# 3. Buat Service
# app/Services/SupplierService.php

# 4. Buat Form Request
php artisan make:request StoreSupplierRequest

# 5. Buat Views
# resources/views/admin/suppliers/index.blade.php
# resources/views/admin/suppliers/create.blade.php
# resources/views/admin/suppliers/edit.blade.php

# 6. Tambahkan Routes
# routes/web.php
```

### 9.3 Code Style

```bash
# Format kode dengan Laravel Pint
vendor/bin/pint

# Format hanya file yang berubah
vendor/bin/pint --dirty
```

### 9.4 Testing

```bash
# Jalankan semua test
php artisan test

# Jalankan test tertentu
php artisan test --filter=ProductTest
```

---

## 10. Seeder & Factory

### 10.1 Struktur Seeder

```
database/seeders/
├── DatabaseSeeder.php      # Seeder utama (PRODUKSI)
├── DemoSeeder.php          # Seeder demo (PRESENTASI)
└── SettingSeeder.php       # Seeder pengaturan toko
```

### 10.2 DatabaseSeeder (Produksi)

Digunakan untuk instalasi baru ke klien. Hanya membuat **1 akun Admin**.

```php
// database/seeders/DatabaseSeeder.php
public function run(): void
{
    User::create([
        'name' => 'Admin Utama',
        'email' => 'admin@admin.com',
        'password' => Hash::make('password'),
        'role' => 'admin',
    ]);

    $this->call(SettingSeeder::class);
}
```

**Cara Menjalankan:**

```bash
php artisan migrate:fresh --seed
```

### 10.3 DemoSeeder (Presentasi)

Digunakan untuk demo ke calon pembeli. Berisi data produk realistis.

```php
// database/seeders/DemoSeeder.php
public function run(): void
{
    // Buat Admin + Kasir
    User::create([...]);

    // Buat 20+ produk dengan harga beli & jual realistis
    $products = [
        ['name' => 'Indomie Goreng', 'purchase_price' => 2500, 'price' => 3500, ...],
        ['name' => 'Aqua 600ml', 'purchase_price' => 2500, 'price' => 4000, ...],
        // ... dst
    ];

    foreach ($products as $product) {
        Product::create($product);
    }
}
```

**Cara Menjalankan:**

```bash
php artisan migrate:fresh
php artisan db:seed --class=DemoSeeder
```

### 10.4 SettingSeeder

Mengisi pengaturan default toko.

```php
// database/seeders/SettingSeeder.php
$settings = [
    'store_name' => 'POS KASIR MU',
    'store_address' => 'Jl. Merdeka No. 123, Jakarta',
    'store_phone' => '081234567890',
    'receipt_footer' => 'Terima kasih atas kunjungan Anda!',
];
```

### 10.5 Factory (Opsional)

Untuk generate data acak saat testing.

```php
// database/factories/ProductFactory.php
public function definition(): array
{
    $products = [
        ['name' => 'Indomie Goreng', 'price' => 3500],
        ['name' => 'Aqua 600ml', 'price' => 4000],
        // ...
    ];

    $product = fake()->randomElement($products);

    return [
        'name' => $product['name'],
        'purchase_price' => (int)($product['price'] * 0.7), // Modal 70%
        'price' => $product['price'],
        'stock' => fake()->numberBetween(10, 100),
        'barcode' => fake()->unique()->ean13(),
    ];
}
```

**Penggunaan di Test/Tinker:**

```php
Product::factory()->count(50)->create();
```

---

## 11. Fitur Lengkap

| Modul            | Fitur                                                                          |
| ---------------- | ------------------------------------------------------------------------------ |
| **Dashboard**    | Statistik harian/bulanan, Grafik omzet 7 hari, Produk terlaris, Stok rendah    |
| **Produk**       | CRUD, Barcode, Harga Beli/Jual, Gambar, Soft Delete, Trash, Search, Export CSV |
| **POS**          | Scan barcode, Quick cash buttons, Format angka otomatis, Cetak struk thermal   |
| **Transaksi**    | Riwayat, Filter tanggal, Cari invoice, Export CSV                              |
| **Laporan**      | Laba bersih per item, Export detail ke CSV                                     |
| **Stok**         | Penyesuaian masuk/keluar, Search, Filter, Export CSV                           |
| **Users**        | CRUD, Role admin/kasir, Search, Export CSV                                     |
| **Activity Log** | Audit trail, Search, Filter tanggal, Export CSV                                |
| **Pengaturan**   | Nama toko, Alamat, Telepon, Footer struk (dinamis)                             |

---

## 12. NativePHP - Konversi ke Desktop App

Dokumen ini menjelaskan cara mengkonversi aplikasi POS Kasir dari web app menjadi aplikasi desktop native menggunakan NativePHP, sehingga dapat diinstal langsung di Windows dan Linux.

### 12.1 Apa Itu NativePHP?

NativePHP adalah framework untuk membangun aplikasi desktop native menggunakan PHP/Laravel. Berbeda dengan web app yang jalan di browser, NativePHP membungkus aplikasi PHP bersama **Electron runtime** menjadi executable native untuk Windows, macOS, dan Linux.

**Arsitektur Web App vs Desktop:**

```
┌─────────────────────────────────────────────────────────┐
│                    LARAVEL WEB APP                       │
├─────────────────────────────────────────────────────────┤
│   Browser ←→ Apache/Nginx ←→ Laravel ←→ MySQL/SQLite  │
│   (Memerlukan internet/server untuk akses)             │
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│                   NATIVEPHP DESKTOP                     │
├─────────────────────────────────────────────────────────┤
│  ┌───────────────────────────────────────────────────┐  │
│  │              Electron Runtime                      │  │
│  │  ┌─────────────────────────────────────────────┐  │  │
│  │  │           Aplikasi Laravel Anda              │  │  │
│  │  │  Routes, Controllers, Services, Database   │  │  │
│  │  └─────────────────────────────────────────────┘  │  │
│  │              SQLite Bundled (Offline)            │  │
│  └───────────────────────────────────────────────────┘  │
│                    Windows / Linux / macOS              │
└─────────────────────────────────────────────────────────┘
```

### 12.2 Kelebihan & Kekurangan

**Kelebihan NativePHP:**
```
✅ Tidak perlu belajar bahasa pemrograman baru
✅ 1 codebase untuk 3 sistem operasi
✅ Akses fitur native (file dialog, notifications, system tray)
✅ SQLite ter-bundle otomatis
✅ Fully offline (tidak memerlukan internet)
✅ Installer standalone (.exe, .AppImage, .dmg)
✅ Auto-update built-in
✅ Laravel ecosystem sepenuhnya didukung
```

**Kekurangan NativePHP:**
```
❌ Ukuran aplikasi besar (~150-300 MB) karena bundel PHP + Electron
❌ Tidak secepat aplikasi native murni (C++, C#)
❌ Memory usage lebih tinggi dibanding web app
❌ Build time lama (~10-30 menit per platform)
```

**Perbandingan Ukuran Aplikasi:**

| Framework | Ukuran Estimasi |
|-----------|----------------|
| NativePHP | 150-300 MB |
| Electron | 100-250 MB |
| Qt/C++ | 10-50 MB |
| C# (.NET) | 20-100 MB |

### 12.3 Cara Kerja NativePHP

```
Source Code (Laravel)
        │
        ▼
┌───────────────────┐
│  1. Prebuild      │  ← npm run build, php artisan optimize
└───────────────────┘
        │
        ▼
┌───────────────────┐
│  2. Bundle PHP     │  ← Embed PHP runtime + extensions
└───────────────────┘
        │
        ▼
┌───────────────────┐
│  3. Package        │  ← Bundel Electron + Laravel app
└───────────────────┘
        │
        ▼
┌───────────────────┐     ┌───────────────────┐
│  4. Signing        │     │  5. Output        │
│     (Windows/      │     │  Windows: .exe    │
│      macOS)        │     │  Linux: .AppImage │
└───────────────────┘     │  macOS: .dmg       │
                         └───────────────────┘
```

### 12.4 Fitur Native yang Tersedia

| Fitur | Deskripsi |
|-------|-----------|
| **Native Windows** | File dialog, notification, menu, system tray |
| **Auto-update** | Built-in updater, upload ke S3/GitHub releases |
| **Database bundling** | SQLite otomatis ter-bundle |
| **Cross-platform** | 1 codebase → Windows, macOS, Linux |
| **Laravel full support** | Semua fitur Laravel bekerja |

### 12.5 Implementasi Langkah demi Langkah

#### Step 1: Install NativePHP

```bash
# Install NativePHP Electron package
composer require nativephp/electron --dev

# Jalankan installer
php artisan native:install
```

#### Step 2: Konfigurasi Environment

Ubah file `.env` untuk desktop:

```env
# Ubah dari web ke desktop
APP_ENV=desktop
APP_DEBUG=false
APP_URL=http://localhost

# Session & Queue tetap database
SESSION_DRIVER=database
QUEUE_CONNECTION=database

# Cache database untuk offline
CACHE_DRIVER=database

# Mail disable untuk desktop
MAIL_MAILER=log
```

#### Step 3: Buat Konfigurasi NativePHP

Buat file `config/nativephp.php`:

```php
<?php

return [
    'app_id' => 'com.poskasir.app',
    'name' => 'PosKasir',
    'shortcode' => 'poskasir',
    'description' => 'Aplikasi Kasir Point of Sale',
    'version' => '1.0.0',
    
    'company_name' => 'Nama Perusahaan Anda',
    'copyright' => '© 2025 Nama Perusahaan Anda',
    
    'executables' => [
        'poskasir' => 'PosKasir',
    ],
    
    'window' => [
        'title' => 'PosKasir',
        'width' => 1400,
        'height' => 900,
        'min_width' => 1200,
        'min_height' => 700,
        'resizable' => true,
        'fullscreen' => false,
        'always_on_top' => false,
        'skip_taskbar' => false,
        'decorated' => true,
        'hidden' => false,
        'maximizable' => true,
        'minimizable' => true,
        'closable' => true,
    ],
    
    'databases' => [
        'sqlite' => [
            'driver' => 'sqlite',
            'database' => 'database/database.sqlite',
        ],
    ],
    
    'system_tray' => [
        'icon' => resource_path('images/tray-icon.png'),
        'tooltip' => 'PosKasir',
    ],
    
    'prebuild' => [
        'npm run build',
        'php artisan optimize',
        'php artisan migrate --force',
        'php artisan db:seed --class=DatabaseSeeder',
    ],
    
    'publish' => [
        'provider' => 'github',
        'repo' => 'username/poskasir',
    ],
];
```

#### Step 4: Buat Desktop Service Provider

Buat `app/Providers/DesktopServiceProvider.php`:

```php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Native\Laravel\Native;
use Native\Laravel\Facades\Window;
use Native\Laravel\Facades\Menu;
use Native\Laravel\Menu\MenuItem;

class DesktopServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Konfigurasi menu aplikasi
        Menu::new()
            ->submenu('File', Menu::new()
                ->item('Transaksi Baru', 'Ctrl+N')
                ->separator()
                ->quit('Keluar', 'Ctrl+Q')
            )
            ->submenu('View', Menu::new()
                ->fullscreen('Layar Penuh', 'F11')
            )
            ->submenu('Bantuan', Menu::new()
                ->about('Tentang PosKasir')
            )
            ->register();

        // Konfigurasi window utama
        Window::route('dashboard', function () {
            return view('dashboard');
        })
        ->width(1400)
        ->height(900)
        ->resizable();

        // Notifikasi startup
        Native::notification()
            ->title('PosKasir')
            ->body('Aplikasi siap digunakan')
            ->show();
    }
}
```

#### Step 5: Update Routes untuk Desktop

Modify `routes/web.php` untuk deteksi desktop:

```php
<?php

use Illuminate\Support\Facades\Route;
use Native\Laravel\Facades\NativeApp;

Route::get('/', function () {
    if (NativeApp::isRunningOnDesktop()) {
        return redirect('/dashboard');
    }
    return view('welcome');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
    
    // Route lainnya...
});
```

### 12.6 Perubahan dari Web App ke Desktop

| Aspek | Web App (Sekarang) | Desktop App (NativePHP) |
|-------|---------------------|------------------------|
| Entry point | `public/index.php` | `artisan native:serve` |
| Session | Database | Database (tetap sama) |
| Cache | Database/File | Database (disarankan) |
| Queue | Database | Database (tetap sama) |
| Mail | SMTP/Log | Comment out / log only |
| Storage | Local/Cloud | Local only (ter-bundle) |
| Auth | Breeze/Jetstream | Breeze OK |
| Assets | Vite dev server | Vite build (production) |
| Database | MySQL/SQLite | SQLite (recommended) |

### 12.7 Database Strategy untuk Desktop

```
┌─────────────────────────────────────────────────────────┐
│               DATABASE STRATEGY                          │
├─────────────────────────────────────────────────────────┤
│                                                         │
│   Development: storage/database/database.sqlite         │
│                                                         │
│   Build Time:                                           │
│   ├─ Copy DB ke build output                            │
│   ├─ Include migrations (auto-run saat first run)      │
│   └─ Seed initial data                                  │
│                                                         │
│   Runtime:                                              │
│   ├─ App menggunakan SQLite bundled                    │
│   ├─ Lokasi: %APPDATA%/poskasir/database.sqlite        │
│   │   (Windows: C:\Users\[user]\AppData\Roaming\)     │
│   ├─ Linux: ~/.local/share/poskasir/                   │
│   └─ macOS: ~/Library/Application Support/poskasir/     │
│                                                         │
│   Updates:                                              │
│   ├─ Auto-migrate saat version upgrade                 │
│   └─ Backup otomatis sebelum migrate                   │
│                                                         │
└─────────────────────────────────────────────────────────┘
```

### 12.8 Menangani Fitur Web-Specific

| Fitur Web | Solusi Desktop |
|-----------|----------------|
| Routes web | Sama, tapi modify welcome page |
| Asset serving | Vite build, assets ter-bundle |
| File uploads | Local storage only (`storage/app/public`) |
| External APIs | HTTP client tetap bekerja |
| Real-time | WebSocket tetap bekerja |
| Mail | Disable atau use log driver |
| Queue | Queue worker jalan di background |

### 12.9 Build untuk Windows & Linux

```bash
# Build untuk Development (testing)
php artisan native:serve

# Build Windows (di Windows atau cross-compile)
php artisan native:build --platform=windows

# Build Linux
php artisan native:build --platform=linux

# Build macOS (di macOS)
php artisan native:build --platform=macos

# Build semua platform
php artisan native:build --platform=all
```

**Output Build:**

| Platform | Output | Ukuran (estimasi) |
|----------|--------|-------------------|
| **Windows** | `poskasir Setup 1.0.0.exe` | ~150-200 MB |
| **macOS** | `poskasir-1.0.0.dmg` | ~150-200 MB |
| **Linux** | `poskasir-1.0.0.AppImage` | ~100-150 MB |

### 12.10 Struktur Folder Setelah NativePHP

```
PosKasir/
├── .env                           ← Ubah APP_ENV=desktop
├── config/
│   ├── app.php
│   ├── database.php
│   └── nativephp.php              ← BARU
├── database/
│   ├── database.sqlite
│   └── migrations/
├── resources/
│   ├── css/
│   ├── js/
│   └── views/
├── routes/
│   ├── web.php
│   └── desktop.php                ← Optional
├── storage/
│   ├── app/
│   ├── framework/
│   └── logs/
├── app/
│   ├── Console/
│   │   └── Kernel.php            ← Queue worker config
│   ├── Http/
│   │   └── Kernel.php            ← API routes
│   ├── Models/
│   ├── Providers/
│   │   ├── AppServiceProvider.php
│   │   └── DesktopServiceProvider.php  ← BARU
│   └── Services/
├── artisan
├── composer.json
├── package.json
└── vite.config.js
```

### 12.11 Checklist Persiapan Production

```bash
# 1. Environment
✅ APP_ENV=desktop
✅ APP_DEBUG=false
✅ APP_URL=http://localhost

# 2. Database
✅ SESSION_DRIVER=database
✅ QUEUE_CONNECTION=database
✅ CACHE_DRIVER=database

# 3. Optimasi
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 4. Permission
chmod -R 775 storage bootstrap/cache

# 5. Build
php artisan native:build --platform=windows
php artisan native:build --platform=linux
```

### 12.12 Troubleshooting Umum

| Masalah | Solusi |
|---------|--------|
| App tidak start | Jalankan `php artisan native:serve` untuk debug |
| Database error | Pastikan `database.sqlite` ada di folder yang benar |
| Assets tidak load | Jalankan `npm run build` sebelum build |
| Build gagal | Periksa `php artisan native:install` sudah dijalankan |
| Memory error | Tambah `memory_limit` di php.ini |

### 12.53 Referensi Resmi

- **Dokumentasi NativePHP:** https://nativephp.com/docs
- **NativePHP GitHub:** https://github.com/nativephp
- **NativePHP Discord:** https://discord.gg/nativephp

---

## 📞 Dukungan

Untuk pertanyaan teknis atau permintaan fitur baru, hubungi tim pengembang.

---

_Dokumen ini diperbarui terakhir: Februari 2026_
