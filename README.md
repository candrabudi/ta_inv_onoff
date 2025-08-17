# 🖥️ TA Inventory On/Off

Project ini adalah aplikasi berbasis **Laravel** untuk manajemen inventaris.
Dokumentasi ini menjelaskan cara **installasi dan setup** menggunakan **Laragon** dengan **PHP 8.3**.

---

## 🚀 Persyaratan Sistem

Sebelum memulai, pastikan sudah terpasang:

* [Laragon](https://laragon.org/download/) (disarankan versi terbaru)
* **PHP 8.3** (pastikan aktif di Laragon → `Menu > PHP > Version`)
* **Composer** (sudah include di Laragon)
* Database: **MySQL/MariaDB** (sudah include di Laragon)
* Git (opsional, untuk clone repo langsung)

---

## 📥 Instalasi

### 1. Clone Repository

```bash
git clone https://github.com/candrabudi/ta_inv_onoff.git
cd ta_inv_onoff
```

### 2. Install Dependencies Laravel

```bash
composer install
```

### 3. Setup Environment

* Copy file `.env.example` jadi `.env`:

```bash
cp .env.example .env
```

* Buka `.env`, lalu sesuaikan konfigurasi database sesuai Laragon (default: `root` tanpa password).

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ta_inv_onoff
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Generate Key

```bash
php artisan key:generate
```

### 5. Migrasi Database & Seeder (jika ada)

```bash
php artisan migrate --seed
```

---

## ▶️ Menjalankan Project

### 1. Jalankan Laragon

* Start **Apache/Nginx** dan **MySQL**.

### 2. Jalankan Laravel Server (opsional jika tidak pakai virtual host)

```bash
php artisan serve
```

Akses via: [http://127.0.0.1:8000](http://127.0.0.1:8000)

### 3. Jika pakai Laragon Virtual Host

* Pindahkan folder project ke `C:\laragon\www\`
* Restart Laragon
* Akses via: `http://ta_inv_onoff.test`

---

## 🔧 Troubleshooting

* **Composer error** → pastikan PHP 8.3 sudah aktif di Laragon.
* **Database error** → cek kembali konfigurasi `.env`.
* **Port bentrok** → ubah port di `.env` atau stop service lain yang bentrok.

---

## 📚 Teknologi

* Laravel 10+
* PHP 8.3
* MySQL/MariaDB
* Bootstrap/Tailwind (cek folder `resources`)

---

⚡ Selamat ngoding!
