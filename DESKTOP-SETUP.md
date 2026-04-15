# 🖥️ POS Kasir MU - Desktop App Setup Guide

Panduan lengkap untuk menjalankan dan mendistribusikan aplikasi POS Kasir MU sebagai aplikasi desktop native.

---

## 📋 **Prerequisites**

Sebelum memulai, pastikan Anda sudah menginstall:

- ✅ **PHP 8.2+** dengan SQLite extension
- ✅ **Composer 2.0+**
- ✅ **Node.js 22+** (wajib untuk Electron)
- ✅ **npm 9.0+**

---

## 🚀 **Quick Start**

### **1. Development Mode**

Jalankan aplikasi desktop dalam mode development:

```bash
# Install dependencies (jika belum)
composer install
npm install

# Run desktop app (development)
php artisan native:run
```

Aplikasi akan terbuka dalam window Electron dengan Laravel development server.

### **2. Build untuk Production**

Build aplikasi menjadi installer native:

```bash
# Build untuk Windows (.exe)
php artisan native:build win

# Build untuk macOS (.dmg)
php artisan native:build mac

# Build untuk Linux (.AppImage)
php artisan native:build linux
```

Output files akan ada di:
```
nativephp/electron/dist/
├── POS Kasir MU Setup 1.0.0.exe      # Windows installer
├── POS Kasir MU-1.0.0.dmg            # macOS installer
└── POS Kasir MU-1.0.0.AppImage       # Linux AppImage
```

---

## 💾 **Database & Storage**

### **Database Location**

Aplikasi desktop menyimpan database SQLite di lokasi OS-specific:

**Windows:**
```
%APPDATA%/PosKasir/database.sqlite
# Contoh: C:\Users\YourName\AppData\Roaming\PosKasir\database.sqlite
```

**macOS:**
```
~/Library/Application Support/PosKasir/database.sqlite
```

**Linux:**
```
~/.local/share/PosKasir/database.sqlite
```

### **Backup & Restore**

Backup database secara manual:

```bash
# Windows - Copy database file
copy %APPDATA%\PosKasir\database.sqlite backup.sqlite

# macOS/Linux
cp ~/Library/Application\ Support/PosKasir/database.sqlite backup.sqlite
```

Restore database:

```bash
# Stop aplikasi dulu, lalu copy balik
copy backup.sqlite %APPDATA%\PosKasir\database.sqlite
```

### **Logs Location**

Log aplikasi tersimpan di:
```
%APPDATA%/PosKasir/logs/native-php-error.log
storage/logs/*.log                  # Laravel logs
```

---

## 🎛️ **Configuration**

### **Environment Variables (`.env`)**

Beberapa konfigurasi penting di `.env`:

```env
# App Settings
APP_ENV=production
APP_DEBUG=false  # Jangan true di production build!

# Database (SQLite - local storage)
DB_CONNECTION=sqlite

# Performance (optimized for desktop)
CACHE_STORE=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync

# Logging
LOG_STACK=daily
LOG_LEVEL=error
```

### **Window Configuration**

Edit `routes/native.php` untuk mengubah window settings:

```php
Window::open('main')
    ->title('POS Kasir MU')
    ->url('/dashboard')
    ->width(1400)    // Window width
    ->height(900)    // Window height
    ->minWidth(1024) // Minimum width
    ->minHeight(768) // Minimum height
    ->center()
    ->rememberState(); // Remember position & size
```

---

## 🔧 **Troubleshooting**

### **1. Aplikasi tidak bisa start**

**Error:** `PHP not found` atau `Failed to start PHP`

**Solusi:**
```bash
# Pastikan PHP ada di PATH
php -v

# Kalau tidak ada, install PHP:
# Windows: https://windows.php.net/download/
# macOS: brew install php
# Linux: sudo apt install php
```

### **2. Node.js version error**

**Error:** `Node.js version must be >=22`

**Solusi:**
```bash
# Check version
node -v

# Update Node.js:
# Download dari: https://nodejs.org
# Atau pakai nvm (Node Version Manager)
```

### **3. Database locked / busy**

**Error:** `database is locked` atau `SQLITE_BUSY`

**Solusi:**
1. Pastikan tidak ada instance lain yang sedang berjalan
2. Check file permissions:
   ```bash
   # Windows
   icacls %APPDATA%\PosKasir\database.sqlite
   
   # macOS/Linux
   ls -l ~/Library/Application\ Support/PosKasir/database.sqlite
   chmod 644 ~/Library/Application\ Support/PosKasir/database.sqlite
   ```

### **4. Printer tidak terdeteksi**

**Masalah:** Tombol print tidak bekerja atau printer tidak muncul

**Solusi:**
1. Pastikan printer sudah terinstall di OS
2. Set printer sebagai default
3. Restart aplikasi
4. Check log: `%APPDATA%/PosKasir/logs/native-php-error.log`

### **5. Build gagal**

**Error:** `electron-builder failed` atau `build error`

**Solusi:**
```bash
# Clean build files
php artisan native:reset

# Install dependencies ulang
cd nativephp/electron
npm install
cd ../..

# Build lagi
php artisan native:build win
```

---

## 📦 **Distribution**

### **Windows Distribution**

Setelah build, Anda akan dapat file:
```
POS Kasir MU Setup 1.0.0.exe
```

**Cara distribute:**
1. Copy file `.exe` ke USB/Cloud/Network share
2. User tinggal double-click untuk install
3. Installer akan:
   - Install aplikasi ke `%LOCALAPPDATA%/Programs/POSKasirMU`
   - Create desktop shortcut
   - Register di Start Menu

### **Auto-Update Setup**

Untuk enable auto-update via GitHub Releases:

1. **Buat GitHub repository**
2. **Set environment variables:**
   ```env
   NATIVEPHP_UPDATER_ENABLED=true
   NATIVEPHP_UPDATER_PROVIDER=github
   GITHUB_REPO=PosKasir
   GITHUB_OWNER=YourGitHubUsername
   GITHUB_TOKEN=your_github_token
   GITHUB_PRIVATE=false
   ```

3. **Build dan publish:**
   ```bash
   php artisan native:publish win
   ```

Aplikasi akan otomatis check for updates setiap start.

---

## 🔐 **Security**

### **Production Checklist:**

- ✅ `APP_DEBUG=false` di `.env`
- ✅ Jangan commit `.env` ke git
- ✅ Hapus sensitive keys sebelum build
- ✅ Gunakan HTTPS untuk API calls
- ✅ Enable session encryption
- ✅ Set proper file permissions

### **Environment Keys Cleanup**

NativePHP automatically removes sensitive keys dari `.env` saat build. Config ada di `config/nativephp.php`:

```php
'cleanup_env_keys' => [
    'AWS_*',
    '*_SECRET',
    'GITHUB_TOKEN',
    // Add more patterns...
],
```

---

## 🎯 **Commands Reference**

| Command | Deskripsi |
|---------|-----------|
| `php artisan native:run` | Run desktop app (development) |
| `php artisan native:build win` | Build Windows installer |
| `php artisan native:build mac` | Build macOS installer |
| `php artisan native:build linux` | Build Linux installer |
| `php artisan native:reset` | Clean all build files |
| `php artisan native:install` | Install Electron dependencies |
| `php artisan native:migrate` | Run database migrations |
| `php artisan native:seed` | Seed database |
| `php artisan native:migrate:fresh` | Drop & re-run migrations |

---

## 📞 **Support**

Jika mengalami masalah:

1. **Check logs:** `%APPDATA%/PosKasir/logs/`
2. **Debug mode:** Set `'show_dev_tools' => true` di `routes/native.php`
3. **Generate debug info:** `php artisan native:debug`
4. **Check documentation:** [NativePHP Docs](https://nativephp.com)

---

<div align="center">

**POS Kasir MU** - _Solusi Digital untuk Bisnis Anda_

Dibuat dengan ❤️ menggunakan [NativePHP](https://nativephp.com)

</div>
