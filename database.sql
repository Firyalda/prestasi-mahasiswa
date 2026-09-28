-- ======================================================
-- DATABASE SISTEM PENGAJUAN PRESTASI MAHASISWA
-- VERSI BARU - SESUAI GAMBAR (LOKAL, PROVINSI, NASIONAL, INTERNASIONAL)
-- ======================================================

-- ======================================================
-- TABEL 1: users (untuk login)
-- ======================================================
CREATE TABLE users (
    id INT(11) NOT NULL AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    nim VARCHAR(20) DEFAULT NULL,
    role ENUM('admin', 'mahasiswa') DEFAULT 'mahasiswa',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ======================================================
-- TABEL 2: kategori_reward (SESUAI GAMBAR ANDA)
-- ======================================================
CREATE TABLE kategori_reward (
    id INT(11) NOT NULL AUTO_INCREMENT,
    tingkat ENUM('Lokal', 'Provinsi', 'Nasional', 'Internasional') NOT NULL,
    kategori ENUM('Individu', 'Tim') NOT NULL,
    raihan VARCHAR(30) NOT NULL,
    nominal INT(11) NOT NULL,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ======================================================
-- DATA KATEGORI REWARD (28 DATA LENGKAP)
-- ======================================================

-- KATEGORI INDIVIDU (14 data)
INSERT INTO kategori_reward (tingkat, kategori, raihan, nominal) VALUES
('Lokal', 'Individu', 'Juara 1', 750000),
('Lokal', 'Individu', 'Juara 2', 500000),
('Lokal', 'Individu', 'Juara 3', 250000),
('Provinsi', 'Individu', 'Juara 1', 1250000),
('Provinsi', 'Individu', 'Juara 2', 1000000),
('Provinsi', 'Individu', 'Juara 3', 750000),
('Nasional', 'Individu', 'Juara 1', 1750000),
('Nasional', 'Individu', 'Juara 2', 1500000),
('Nasional', 'Individu', 'Juara 3', 1000000),
('Nasional', 'Individu', 'Finalis', 500000),
('Internasional', 'Individu', 'Juara 1', 2250000),
('Internasional', 'Individu', 'Juara 2', 2000000),
('Internasional', 'Individu', 'Juara 3', 1750000),
('Internasional', 'Individu', 'Finalis', 750000);

-- KATEGORI TIM (14 data)
INSERT INTO kategori_reward (tingkat, kategori, raihan, nominal) VALUES
('Lokal', 'Tim', 'Juara 1', 1000000),
('Lokal', 'Tim', 'Juara 2', 750000),
('Lokal', 'Tim', 'Juara 3', 500000),
('Provinsi', 'Tim', 'Juara 1', 1750000),
('Provinsi', 'Tim', 'Juara 2', 1250000),
('Provinsi', 'Tim', 'Juara 3', 750000),
('Nasional', 'Tim', 'Juara 1', 2500000),
('Nasional', 'Tim', 'Juara 2', 2000000),
('Nasional', 'Tim', 'Juara 3', 1500000),
('Nasional', 'Tim', 'Finalis', 750000),
('Internasional', 'Tim', 'Juara 1', 3500000),
('Internasional', 'Tim', 'Juara 2', 2500000),
('Internasional', 'Tim', 'Juara 3', 1500000),
('Internasional', 'Tim', 'Finalis', 1000000);

-- ======================================================
-- TABEL 3: pengajuan (form pengajuan prestasi)
-- ======================================================
CREATE TABLE pengajuan (
    id INT(11) NOT NULL AUTO_INCREMENT,
    user_id INT(11) NOT NULL,
    jenis_peserta ENUM('Individu', 'Tim') DEFAULT 'Individu',
    level_kompetisi ENUM('Lokal', 'Provinsi', 'Nasional', 'Internasional') NOT NULL,
    raihan_prestasi VARCHAR(30) NOT NULL,
    nama_ketua VARCHAR(100) NOT NULL,
    nim_ketua VARCHAR(20) NOT NULL,
    no_hp VARCHAR(15) NOT NULL,
    prodi VARCHAR(50) NOT NULL,
    nama_kejuaraan VARCHAR(200) NOT NULL,
    kategori_prestasi ENUM('Akademik', 'Non-Akademik', 'Olahraga', 'Seni', 'Lainnya') DEFAULT 'Akademik',
    bulan_raihan VARCHAR(20) NOT NULL,
    tahun_raihan YEAR NOT NULL,
    url_penyelenggara TEXT DEFAULT NULL,
    nama_tim VARCHAR(100) DEFAULT NULL,
    anggota_tim TEXT DEFAULT NULL,
    dosen_pembimbing VARCHAR(100) DEFAULT NULL,
    sertifikat VARCHAR(255) NOT NULL,
    foto_dokumentasi VARCHAR(255) NOT NULL,
    surat_tugas VARCHAR(255) NOT NULL,
    status ENUM('diproses', 'disetujui', 'ditolak') DEFAULT 'diproses',
    reward_nominal INT(11) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ======================================================
-- DATA SAMPLE USERS (admin & mahasiswa untuk login)
-- ======================================================
INSERT INTO users (username, password, nama_lengkap, nim, role) VALUES
('admin', MD5('admin123'), 'Administrator', NULL, 'admin'),
('mahasiswa1', MD5('mhs123'), 'Budi Santoso', '20210001', 'mahasiswa'),
('mahasiswa2', MD5('mhs123'), 'Siti Aminah', '20210002', 'mahasiswa');

-- ======================================================
-- DATA CONTOH PENGAJUAN (untuk testing halaman admin)
-- ======================================================
INSERT INTO pengajuan (
    user_id, jenis_peserta, level_kompetisi, raihan_prestasi,
    nama_ketua, nim_ketua, no_hp, prodi, nama_kejuaraan,
    kategori_prestasi, bulan_raihan, tahun_raihan,
    sertifikat, foto_dokumentasi, surat_tugas, status
) VALUES 
(
    2, 'Individu', 'Nasional', 'Juara 1',
    'Budi Santoso', '20210001', '081234567890', 'Teknik Informatika',
    'Lomba Debat Mahasiswa Nasional 2025', 'Akademik', 'Januari', 2025,
    'dummy.pdf', 'dummy.png', 'dummy.pdf', 'diproses'
),
(
    3, 'Tim', 'Provinsi', 'Juara 2',
    'Siti Aminah', '20210002', '081234567891', 'Sistem Informasi',
    'Inovasi Teknologi 2025', 'Non-Akademik', 'Februari', 2025,
    'dummy.pdf', 'dummy.png', 'dummy.pdf', 'diproses'
);

-- ======================================================
-- SELESAI
-- ======================================================

-- Cek hasil (opsional)
-- SELECT * FROM users;
-- SELECT * FROM kategori_reward;
-- SELECT * FROM pengajuan;