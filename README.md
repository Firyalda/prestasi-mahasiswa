# Sistem Pengajuan Mahasiswa Berprestasi

Sistem Pengajuan Mahasiswa Berprestasi adalah aplikasi berbasis web yang dibuat untuk membantu proses pengajuan, pengelolaan, dan verifikasi data prestasi mahasiswa secara digital.

## Persyaratan

Sebelum menjalankan aplikasi, pastikan sudah terinstal:

* PHP
* MySQL
* Web Browser
* Laragon atau XAMPP

## Instalasi

### 1. Clone Repository

Buka Terminal / CMD, lalu jalankan:

```bash
git clone https://github.com/Firyalda/prestasi-mahasiswa.git
```

Masuk ke folder project:

```bash
cd prestasi-mahasiswa
```

### 2. Pindahkan ke Folder Web Server

Jika menggunakan **Laragon**, letakkan folder project di:

```text
C:\laragon\www\
```

Sehingga menjadi:

```text
C:\laragon\www\prestasi-mahasiswa
```

Jika menggunakan XAMPP, letakkan di:

```text
C:\xampp\htdocs\
```

### 3. Buat Database

1. Jalankan Laragon/XAMPP.
2. Aktifkan **Apache** dan **MySQL**.
3. Buka phpMyAdmin.
4. Buat database baru, misalnya:

```text
prestasi_mahasiswa
```

5. Import file database `.sql` yang tersedia di dalam project bernama **db_coba (2) (1).sql**.

### 4. Atur Koneksi Database

Buka file konfigurasi database di dalam folder **config/database.php** pada project.

Sesuaikan konfigurasi dengan database lokal:

```php
$host = "localhost";
$user = "root";
$password = "";
$database = "prestasi_mahasiswa";
```

Sesuaikan nama database jika berbeda.

### 5. Jalankan Aplikasi

Jika menggunakan Laragon, buka browser dan akses:

```text
http://localhost/prestasi-mahasiswa
```

Jika menggunakan XAMPP, gunakan alamat yang sama:

```text
http://localhost/prestasi-mahasiswa
```
