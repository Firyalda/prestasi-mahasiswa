-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Sep 28, 2026 at 09:16 AM
-- Server version: 8.0.30
-- PHP Version: 8.2.19

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_coba`
--

-- --------------------------------------------------------

--
-- Table structure for table `achievement_periods`
--

CREATE TABLE `achievement_periods` (
  `id` int NOT NULL,
  `nama_periode` varchar(100) NOT NULL,
  `tahun_akademik` varchar(20) NOT NULL,
  `semester` enum('Ganjil','Genap','Pendek') NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `description` text,
  `status` enum('upcoming','active','ended','cancelled') NOT NULL DEFAULT 'upcoming',
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `achievement_periods`
--

INSERT INTO `achievement_periods` (`id`, `nama_periode`, `tahun_akademik`, `semester`, `start_date`, `end_date`, `description`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Periode Genap 2026/2027', '2026/2027', 'Genap', '2026-06-15', '2026-07-02', NULL, 'active', '2026-07-02 11:41:08', '2026-07-02 13:56:41'),
(2, 'Periode 2026/2027', '2026/2027', 'Ganjil', '2026-06-15', '2026-07-02', NULL, 'active', '2026-07-02 12:46:31', NULL),
(3, 'Periode 2026/2027', '2026/2027', 'Ganjil', '2026-06-15', '2026-07-02', NULL, 'ended', '2026-07-02 12:46:54', NULL),
(4, 'Periode 2026/2027', '2026/2027', 'Ganjil', '2026-06-15', '2026-07-02', NULL, 'upcoming', '2026-07-02 14:09:22', NULL),
(5, 'Periode 2026/2027', '2026/2027', 'Ganjil', '2026-06-15', '2026-07-02', NULL, 'upcoming', '2026-07-02 14:09:35', NULL),
(6, 'Periode 2026/2027', '2026/2027', 'Ganjil', '2026-06-15', '2026-07-03', NULL, 'upcoming', '2026-07-02 14:09:54', NULL),
(7, 'Periode 2026/2027', '2026/2027', 'Ganjil', '2026-06-01', '2026-07-03', NULL, 'upcoming', '2026-07-02 14:12:17', NULL),
(8, 'Periode 2026/2027', '2026/2027', 'Ganjil', '2026-06-01', '2026-07-27', NULL, 'upcoming', '2026-07-02 14:24:07', NULL),
(9, 'Periode 2026/2027', '2026/2027', 'Ganjil', '2026-06-01', '2026-07-27', NULL, 'upcoming', '2026-07-02 14:24:19', NULL),
(10, 'Periode 2026/2027', '2026/2027', 'Ganjil', '2026-06-01', '2026-07-27', NULL, 'upcoming', '2026-07-02 14:26:54', NULL),
(11, 'Periode 2026/2027', '2026/2027', 'Ganjil', '2026-06-01', '2026-07-27', NULL, 'upcoming', '2026-07-02 14:28:07', NULL),
(12, 'Periode 2026/2027', '2026/2027', 'Ganjil', '2026-06-01', '2026-07-04', NULL, 'upcoming', '2026-07-02 14:29:09', NULL),
(13, 'Periode 2026/2027', '2026/2027', 'Ganjil', '2026-06-01', '2026-07-04', NULL, 'upcoming', '2026-07-02 15:48:14', NULL),
(14, 'Periode 2026/2027', '2026/2027', 'Ganjil', '2026-06-01', '2026-07-04', NULL, 'upcoming', '2026-07-02 15:49:44', NULL),
(15, 'Periode 2026/2027', '2026/2027', 'Ganjil', '2026-06-01', '2026-07-30', NULL, 'upcoming', '2026-07-05 23:07:28', NULL),
(16, 'Periode 2026/2027', '2026/2027', 'Ganjil', '2026-07-30', '2026-09-30', NULL, 'upcoming', '2026-08-02 10:08:49', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `kategori_prestasi`
--

CREATE TABLE `kategori_prestasi` (
  `id` int NOT NULL,
  `nama_kategori` varchar(100) NOT NULL,
  `deskripsi` text,
  `bobot_nilai` decimal(5,2) DEFAULT '0.00',
  `kriteria_penilaian` text,
  `aktif` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kategori_reward`
--

CREATE TABLE `kategori_reward` (
  `id` int NOT NULL,
  `tingkat` enum('Lokal','Provinsi','Nasional','Internasional') NOT NULL,
  `kategori` enum('Individu','Tim') NOT NULL,
  `raihan` varchar(30) NOT NULL,
  `nominal` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `kategori_reward`
--

INSERT INTO `kategori_reward` (`id`, `tingkat`, `kategori`, `raihan`, `nominal`) VALUES
(1, 'Lokal', 'Individu', 'Juara 1', 750000),
(2, 'Lokal', 'Individu', 'Juara 2', 500000),
(3, 'Lokal', 'Individu', 'Juara 3', 250000),
(4, 'Provinsi', 'Individu', 'Juara 1', 1250000),
(5, 'Provinsi', 'Individu', 'Juara 2', 1000000),
(6, 'Provinsi', 'Individu', 'Juara 3', 750000),
(7, 'Nasional', 'Individu', 'Juara 1', 1750000),
(8, 'Nasional', 'Individu', 'Juara 2', 1500000),
(9, 'Nasional', 'Individu', 'Juara 3', 1000000),
(10, 'Nasional', 'Individu', 'Finalis', 500000),
(11, 'Internasional', 'Individu', 'Juara 1', 2250000),
(12, 'Internasional', 'Individu', 'Juara 2', 2000000),
(13, 'Internasional', 'Individu', 'Juara 3', 1750000),
(14, 'Internasional', 'Individu', 'Finalis', 750000),
(15, 'Lokal', 'Tim', 'Juara 1', 1000000),
(16, 'Lokal', 'Tim', 'Juara 2', 750000),
(17, 'Lokal', 'Tim', 'Juara 3', 500000),
(18, 'Provinsi', 'Tim', 'Juara 1', 1750000),
(19, 'Provinsi', 'Tim', 'Juara 2', 1250000),
(20, 'Provinsi', 'Tim', 'Juara 3', 750000),
(21, 'Nasional', 'Tim', 'Juara 1', 2500000),
(22, 'Nasional', 'Tim', 'Juara 2', 2000000),
(23, 'Nasional', 'Tim', 'Juara 3', 1500000),
(24, 'Nasional', 'Tim', 'Finalis', 750000),
(25, 'Internasional', 'Tim', 'Juara 1', 3500000),
(26, 'Internasional', 'Tim', 'Juara 2', 2500000),
(27, 'Internasional', 'Tim', 'Juara 3', 1500000),
(28, 'Internasional', 'Tim', 'Finalis', 1000000);

-- --------------------------------------------------------

--
-- Table structure for table `notifikasi`
--

CREATE TABLE `notifikasi` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `role` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `judul` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `pesan` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `link` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_read` tinyint DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifikasi`
--

INSERT INTO `notifikasi` (`id`, `user_id`, `role`, `judul`, `pesan`, `link`, `is_read`, `created_at`) VALUES
(1, 1, 'admin', 'Hasil Verifikasi dari Tim Verifikator', 'Verifikator telah menyelesaikan verifikasi pengajuan atas nama salmanafisa (NIM: 22205036) dengan status: DISETUJUI.\n\nSilakan cek dan tentukan nominal reward.', '../admin/detail_pengajuan.php?id=16', 1, '2026-07-03 13:35:57'),
(2, 1, 'admin', 'Hasil Verifikasi dari Tim Verifikator', 'Verifikator telah menyelesaikan verifikasi pengajuan atas nama fatihmuzaki (NIM: 22205026) dengan status: DITOLAK.\n\nSilakan cek dan tentukan nominal reward.', '../admin/detail_pengajuan.php?id=15', 0, '2026-07-03 15:50:12'),
(3, 1, 'admin', 'Hasil Verifikasi dari Tim Verifikator', 'Verifikator telah menyelesaikan verifikasi pengajuan atas nama rizka yuniarti (NIM: 22205051) dengan status: REVISI.\n\nSilakan cek dan tentukan nominal reward.', '../admin/detail_pengajuan.php?id=13', 1, '2026-07-03 16:32:54'),
(4, 1, 'admin', 'Hasil Verifikasi dari Tim Verifikator', 'Verifikator telah menyelesaikan verifikasi pengajuan atas nama rizka yuniarti (NIM: 22205051) dengan status: DIPROSES.\n\nSilakan cek dan tentukan nominal reward.', '../admin/detail_pengajuan.php?id=13', 1, '2026-07-03 16:59:20'),
(5, 12, 'mahasiswa', 'Permintaan Revisi Pengajuan', 'Pengajuan prestasi Anda perlu direvisi. Silakan cek status pengajuan Anda di dashboard.\n\nCatatan Verifikator:\nHASIL VERIFIKASI: REVISI\r\nHASIL VERIFIKASI: REVISI', '../mahasiswa/detail_pengajuan.php?id=13', 0, '2026-07-03 17:43:51'),
(6, 12, 'mahasiswa', 'Permintaan Revisi Pengajuan', 'Pengajuan prestasi Anda perlu direvisi. Silakan cek status pengajuan Anda di dashboard.\n\nCatatan Verifikator:\nHASIL VERIFIKASI: REVISI\r\nHASIL VERIFIKASI: REVISI', '../mahasiswa/detail_pengajuan.php?id=13', 0, '2026-07-03 17:44:18'),
(7, 12, 'mahasiswa', 'Permintaan Revisi Pengajuan', 'Pengajuan prestasi Anda perlu direvisi. Silakan cek status pengajuan Anda di dashboard.\n\nCatatan Verifikator:\nHASIL VERIFIKASI: REVISI\r\n\r\n1. Sertifikat: Tidak Terdeteksi / Tidak Terbaca\r\n- File sertifikat tidak terbaca oleh sistem OCR\r\n- Mohon upload ulang file sertifikat dengan kualitas yang lebih baik (resolusi tinggi, jelas)\r\n\r\n2. Foto: Valid\r\n- 1 foto dokumentasi terupload dan sesuai\r\n\r\n3. Surat Tugas: Valid\r\n- Surat tugas dari kampus terupload dan valid\r\n\r\nREKOMENDASI: REVISI\r\nCatatan:\r\nMohon upload ulang file sertifikat dengan kualitas yang lebih baik agar dapat diverifikasi.\r\nPastikan file sertifikat:\r\n- Berformat PDF\r\n- Resolusi gambar jelas (minimal 300 dpi)\r\n- Nama dan tanggal terbaca dengan baik\r\n\r\nBatas waktu revisi: 7 hari setelah notifikasi ini.', '../mahasiswa/detail_pengajuan.php?id=13', 0, '2026-07-03 17:59:37'),
(8, 12, 'mahasiswa', 'Permintaan Revisi Pengajuan', 'Pengajuan prestasi Anda perlu direvisi. Silakan cek status pengajuan Anda di dashboard.\n\nCatatan Verifikator:\nHASIL VERIFIKASI: REVISI\r\n\r\n1. Sertifikat: Tidak Terdeteksi / Tidak Terbaca\r\n- File sertifikat tidak terbaca oleh sistem OCR\r\n- Mohon upload ulang file sertifikat dengan kualitas yang lebih baik (resolusi tinggi, jelas)\r\n\r\n2. Foto: Valid\r\n- 1 foto dokumentasi terupload dan sesuai\r\n\r\n3. Surat Tugas: Valid\r\n- Surat tugas dari kampus terupload dan valid\r\n\r\nREKOMENDASI: REVISI\r\nCatatan:\r\nMohon upload ulang file sertifikat dengan kualitas yang lebih baik agar dapat diverifikasi.\r\nPastikan file sertifikat:\r\n- Berformat PDF\r\n- Resolusi gambar jelas (minimal 300 dpi)\r\n- Nama dan tanggal terbaca dengan baik\r\n\r\nBatas waktu revisi: 7 hari setelah notifikasi ini.', '../mahasiswa/detail_pengajuan.php?id=13', 0, '2026-07-03 18:00:33'),
(9, 1, 'admin', 'Hasil Verifikasi dari Tim Verifikator', 'Verifikator telah menyelesaikan verifikasi pengajuan atas nama alfaro (NIM: 22205067) dengan status: DIPROSES.\n\nSilakan cek dan tentukan nominal reward.', '../admin/detail_pengajuan.php?id=26', 0, '2026-07-04 11:37:57'),
(10, 1, 'admin', 'Hasil Verifikasi dari Tim Verifikator', 'Verifikator telah menyelesaikan verifikasi pengajuan atas nama alfaro (NIM: 22205067) dengan status: DISETUJUI.\n\nSilakan cek dan tentukan nominal reward.', '../admin/detail_pengajuan.php?id=26', 0, '2026-07-04 11:43:10'),
(11, 1, 'admin', 'Hasil Verifikasi dari Tim Verifikator', 'Verifikator telah menyelesaikan verifikasi pengajuan atas nama alfaro (NIM: 22205067) dengan status: DITOLAK.\n\nPengajuan ini ditolak. Silakan cek detail pengajuan untuk melihat alasan penolakan.', '../admin/detail_pengajuan.php?id=25', 0, '2026-07-04 12:05:08'),
(12, 1, 'admin', 'Hasil Verifikasi dari Tim Verifikator', 'Verifikator telah menyelesaikan verifikasi pengajuan atas nama alfaro (NIM: 22205067) dengan status: DISETUJUI.\n\nSilakan cek dan tentukan nominal reward.', '../admin/detail_pengajuan.php?id=27', 0, '2026-07-04 12:14:39'),
(13, 19, 'mahasiswa', 'Permintaan Revisi Pengajuan', 'Pengajuan prestasi Anda perlu direvisi. Silakan cek status pengajuan Anda di dashboard.\n\nCatatan Verifikator:', '../mahasiswa/detail_pengajuan.php?id=24', 0, '2026-07-06 17:32:14'),
(14, 1, 'admin', 'Hasil Verifikasi dari Tim Verifikator', 'Verifikator telah menyelesaikan verifikasi pengajuan atas nama lutfanalwafi (NIM: 22205024) dengan status: DITOLAK.\n\nPengajuan ini ditolak. Silakan cek detail pengajuan untuk melihat alasan penolakan.', '../admin/detail_pengajuan.php?id=28', 0, '2026-07-09 07:53:51'),
(15, 19, 'mahasiswa', 'Selamat! Anda Ditetapkan sebagai Mahasiswa Berprestasi', 'Selamat! Anda telah ditetapkan sebagai Mahasiswa Berprestasi periode 2026.\n\nNama: alfaro\nNIM: 22205067\nPrestasi: melukis\nReward: Rp 1.000.000', '../mahasiswa/detail_pengajuan.php?id=26', 0, '2026-07-09 09:02:17'),
(16, 1, 'admin', 'Hasil Verifikasi dari Tim Verifikator', 'Verifikator telah menyelesaikan verifikasi pengajuan atas nama lutfanalwafi (NIM: 22205024) dengan status: DISETUJUI.\n\nSilakan cek dan tentukan nominal reward.', '../admin/detail_pengajuan.php?id=29', 0, '2026-07-09 09:25:00'),
(17, 1, 'admin', 'Hasil Verifikasi dari Tim Verifikator', 'Verifikator telah menyelesaikan verifikasi pengajuan atas nama fajarnusantara (NIM: 22205028) dengan status: DISETUJUI.\n\nSilakan cek dan tentukan nominal reward.', '../admin/detail_pengajuan.php?id=31', 0, '2026-07-09 10:17:11'),
(18, 21, 'mahasiswa', 'Selamat! Anda Ditetapkan sebagai Mahasiswa Berprestasi', 'Selamat! Anda telah ditetapkan sebagai Mahasiswa Berprestasi periode 2026.\n\nNama: fajarnusantara\nNIM: 22205028\nPrestasi: kewirausahaan pelajar\nReward: Rp 1.750.000', '../mahasiswa/detail_pengajuan.php?id=31', 0, '2026-07-09 10:21:08'),
(19, 1, 'admin', 'Hasil Verifikasi dari Tim Verifikator', 'Verifikator telah menyelesaikan verifikasi pengajuan atas nama fajarnusantara (NIM: 22205028) dengan status: DITOLAK.\n\nPengajuan ini ditolak. Silakan cek detail pengajuan untuk melihat alasan penolakan.', '../admin/detail_pengajuan.php?id=30', 0, '2026-07-12 10:15:29'),
(20, 1, 'admin', 'Hasil Verifikasi dari Tim Verifikator', 'Verifikator telah menyelesaikan verifikasi pengajuan atas nama cahaya dewi (NIM: 22205089) dengan status: DISETUJUI.\n\nSilakan cek dan tentukan nominal reward.', '../admin/detail_pengajuan.php?id=32', 0, '2026-08-04 06:54:18'),
(21, 22, 'mahasiswa', 'Selamat! Anda Ditetapkan sebagai Mahasiswa Berprestasi', 'Selamat! Anda telah ditetapkan sebagai Mahasiswa Berprestasi periode 2026.\n\nNama: cahaya dewi\nNIM: 22205089\nPrestasi: balap karung\nReward: Rp 750.000', '../mahasiswa/detail_pengajuan.php?id=32', 0, '2026-08-04 06:56:31'),
(22, 1, 'admin', 'Hasil Verifikasi dari Tim Verifikator', 'Verifikator telah menyelesaikan verifikasi pengajuan atas nama alfaro (NIM: 22205067) dengan status: DISETUJUI.\n\nSilakan cek dan tentukan nominal reward.', '../admin/detail_pengajuan.php?id=23', 0, '2026-08-05 01:26:34');

-- --------------------------------------------------------

--
-- Table structure for table `pengajuan`
--

CREATE TABLE `pengajuan` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `mahasiswa_id` int DEFAULT NULL,
  `kategori_id` int DEFAULT NULL,
  `judul_prestasi` varchar(200) DEFAULT NULL,
  `deskripsi` text,
  `tanggal_prestasi` date DEFAULT NULL,
  `level_kompetisi` enum('Lokal','Provinsi','Nasional','Internasional') NOT NULL,
  `penyelenggara` varchar(100) DEFAULT NULL,
  `lokasi` varchar(100) DEFAULT NULL,
  `raihan_prestasi` varchar(50) DEFAULT NULL,
  `dokumen_path` varchar(255) DEFAULT NULL,
  `status` enum('diproses','disetujui','ditolak','revisi') NOT NULL DEFAULT 'diproses',
  `tanggal_disetujui` datetime DEFAULT NULL,
  `reward_nominal` int DEFAULT '0',
  `nilai_akhir` decimal(5,2) DEFAULT NULL,
  `admin_catatan` text,
  `admin_id` int DEFAULT NULL,
  `validated_at` datetime DEFAULT NULL,
  `periode_id` int DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL,
  `fakultas` varchar(100) DEFAULT NULL,
  `jenis_peserta` enum('Individu','Tim') DEFAULT 'Individu',
  `nama_ketua` varchar(100) DEFAULT NULL,
  `nim_ketua` varchar(20) DEFAULT NULL,
  `no_hp` varchar(15) DEFAULT NULL,
  `prodi` varchar(50) DEFAULT NULL,
  `bulan_raihan` varchar(20) DEFAULT NULL,
  `tahun_raihan` year DEFAULT NULL,
  `url_penyelenggara` text,
  `nama_tim` varchar(100) DEFAULT NULL,
  `anggota_tim` text,
  `sertifikat` varchar(255) DEFAULT NULL,
  `foto_dokumentasi` text,
  `surat_tugas` varchar(255) DEFAULT NULL,
  `nama_kejuaraan` varchar(255) DEFAULT NULL,
  `kategori_prestasi` varchar(100) DEFAULT NULL,
  `tanggal_raihan` date DEFAULT NULL,
  `ocr_text` text,
  `ocr_accuracy` decimal(5,4) DEFAULT '0.0000',
  `file_hash` varchar(64) DEFAULT NULL,
  `digital_signature` text,
  `penetapan` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `pengajuan`
--

INSERT INTO `pengajuan` (`id`, `user_id`, `mahasiswa_id`, `kategori_id`, `judul_prestasi`, `deskripsi`, `tanggal_prestasi`, `level_kompetisi`, `penyelenggara`, `lokasi`, `raihan_prestasi`, `dokumen_path`, `status`, `tanggal_disetujui`, `reward_nominal`, `nilai_akhir`, `admin_catatan`, `admin_id`, `validated_at`, `periode_id`, `created_at`, `updated_at`, `fakultas`, `jenis_peserta`, `nama_ketua`, `nim_ketua`, `no_hp`, `prodi`, `bulan_raihan`, `tahun_raihan`, `url_penyelenggara`, `nama_tim`, `anggota_tim`, `sertifikat`, `foto_dokumentasi`, `surat_tugas`, `nama_kejuaraan`, `kategori_prestasi`, `tanggal_raihan`, `ocr_text`, `ocr_accuracy`, `file_hash`, `digital_signature`, `penetapan`) VALUES
(6, 9, NULL, NULL, NULL, NULL, NULL, 'Nasional', NULL, NULL, 'Silver Medal', NULL, 'disetujui', NULL, 0, NULL, NULL, NULL, NULL, NULL, '2026-06-01 11:12:52', NULL, 'Fakultas Sains dan Teknologi', 'Individu', 'intankomalasari', '22205031', '083156789876', 'S-1 Teknik Informatika', NULL, NULL, 'https://www.instagram.com/p/DV0S0Mpkasd/?img_index=1', '', '', '1780287153_6a1d06b1d0bb6.pdf', '1780287153_6a1d06b1d1d4f.jpg', '1780287153_6a1d06b1d23d3.pdf', 'olimpiade sains', 'Akademik', '2026-02-01', 'bhm FOSNAS isR SERTIFIKAT No: 2481443/BHM-FOSNAS/ISO/S/11/2026 Festival OlimpiadeSainsNasional(FOsNAS)dibawahnaunganYayasanBerlianHatiMulia denganNomorlzin:AHU-0007789-AH.01.04Tahun2021memberikanpenghargaankepada: Nama :INTANKOMALASARI Asal Instansi:UNIVERSITASHARKATNEGERI Provinsi ：Prov.Jawa Tengah Bidang :Fisika Jenjang ：PerguruanTinggi Ataspartisipasinyadalam ajangOlimpiadeSainsTingkatNasionalpada tanggal 01Februari2026diSumateraUtara,yangbertajuk INDONESIANSCIENCEOLYMPIAD(ISO)2026,Sebagai: PESERTAAKTIF SumateraUtara,03Februari2026 Mengetahui, bhm FOSNAS BERLIANHATIMULL Prof.Dr.Ir.TriMaftial,MP Drs.M.Isa Ansari,M.Si PembinaIlYayasanBerlianHatiMulia DirekturFestival OlimpiadeSainsNasional Verifikasi KeaslianDokumen Disini (FOSNAS)', '0.9507', 'f783dd66c2becebf533fd82cd0a8fa5664c3c19c21e6845c47731498c0395076', NULL, 1),
(7, 9, NULL, NULL, NULL, NULL, NULL, 'Nasional', NULL, NULL, 'Silver Medal', NULL, 'diproses', NULL, 0, NULL, NULL, NULL, NULL, NULL, '2026-06-01 11:21:33', NULL, 'Fakultas Sains dan Teknologi', 'Individu', 'intankomalasari', '22205031', '083156789876', 'S-1 Teknik Informatika', NULL, NULL, 'https://www.instagram.com/p/DV0S0Mpkasd/?img_index=1', '', '', '1780287676_6a1d08bc12b45.pdf', '1780287676_6a1d08bc139ba.jpg', '1780287676_6a1d08bc13e43.pdf', 'olimpiade sains', 'Akademik', '2026-03-01', 'bhm FOSNAS isR SERTIFIKAT No: 2481443/BHM-FOSNAS/ISO/S/11/2026 Festival OlimpiadeSainsNasional(FOsNAS)dibawahnaunganYayasanBerlianHatiMulia denganNomorlzin:AHU-0007789-AH.01.04Tahun2021memberikanpenghargaankepada: Nama :INTANKOMALASARI Asal Instansi:UNIVERSITASHARKATNEGERI Provinsi ：Prov.Jawa Tengah Bidang :Fisika Jenjang ：PerguruanTinggi Ataspartisipasinyadalam ajangOlimpiadeSainsTingkatNasionalpada tanggal 01Februari2026diSumateraUtara,yangbertajuk INDONESIANSCIENCEOLYMPIAD(ISO)2026,Sebagai: PESERTAAKTIF SumateraUtara,03Februari2026 Mengetahui, bhm FOSNAS BERLIANHATIMULL Prof.Dr.Ir.TriMaftial,MP Drs.M.Isa Ansari,M.Si PembinaIlYayasanBerlianHatiMulia DirekturFestival OlimpiadeSainsNasional Verifikasi KeaslianDokumen Disini (FOSNAS)', '0.9507', NULL, NULL, 0),
(8, 9, NULL, NULL, NULL, NULL, NULL, 'Nasional', NULL, NULL, 'Silver Medal', NULL, 'diproses', NULL, 0, NULL, NULL, NULL, NULL, NULL, '2026-06-01 11:21:51', NULL, 'Fakultas Sains dan Teknologi', 'Individu', 'intankomalasari', '22205031', '083156789876', 'S-1 Teknik Informatika', NULL, NULL, 'https://www.instagram.com/p/DV0S0Mpkasd/?img_index=1', '', '', '1780287693_6a1d08cdb1d44.pdf', '1780287693_6a1d08cdb3496.jpg', '1780287693_6a1d08cdb3fc9.pdf', 'olimpiade sains', 'Akademik', '2026-02-01', 'bhm FOSNAS isR SERTIFIKAT No: 2481443/BHM-FOSNAS/ISO/S/11/2026 Festival OlimpiadeSainsNasional(FOsNAS)dibawahnaunganYayasanBerlianHatiMulia denganNomorlzin:AHU-0007789-AH.01.04Tahun2021memberikanpenghargaankepada: Nama :INTANKOMALASARI Asal Instansi:UNIVERSITASHARKATNEGERI Provinsi ：Prov.Jawa Tengah Bidang :Fisika Jenjang ：PerguruanTinggi Ataspartisipasinyadalam ajangOlimpiadeSainsTingkatNasionalpada tanggal 01Februari2026diSumateraUtara,yangbertajuk INDONESIANSCIENCEOLYMPIAD(ISO)2026,Sebagai: PESERTAAKTIF SumateraUtara,03Februari2026 Mengetahui, bhm FOSNAS BERLIANHATIMULL Prof.Dr.Ir.TriMaftial,MP Drs.M.Isa Ansari,M.Si PembinaIlYayasanBerlianHatiMulia DirekturFestival OlimpiadeSainsNasional Verifikasi KeaslianDokumen Disini (FOSNAS)', '0.9507', NULL, NULL, 0),
(9, 12, NULL, NULL, NULL, NULL, NULL, 'Lokal', NULL, NULL, 'Juara 1', NULL, 'diproses', NULL, 0, NULL, NULL, NULL, NULL, NULL, '2026-06-10 20:35:48', NULL, 'Fakultas Sains dan Teknologi', 'Tim', 'rizka yuniarti', '22205051', '08123456789', 'S-1 Teknik Informatika', NULL, NULL, 'https://www.instagram.com/p/C4zTugRhxrQ/', 'seni mural', '1. alvin\r\n2. adi\r\n3. januar\r\n4. ayu\r\n5. rizka', '1781098494_6a2967feab3c5.pdf', '1781098494_6a2967fead466.png', '1781098494_6a2967feaecb6.pdf', 'seni mural', 'Seni', '2026-06-01', 'SERTIFIKAT PENGHARGAAN Denganbanggadiberikankepada GretaJMae Gvamy Sebagai: JUARA1 DalamLombaKewirausahaanPelajartingkatsekolahmenengahatas. Semogakeberhasilaninisemakinmemacusemangatbelajar,berinovasi,danmenumbuhkan generasi wirausahamuda yangkreatif danberdayasaing tinggi. LARSPEETERS HARPERRUSSO Ketua Organisasi KetuaPenyelenggara', '0.9503', NULL, NULL, 0),
(10, 12, NULL, NULL, NULL, NULL, NULL, 'Lokal', NULL, NULL, 'Juara 1', NULL, 'diproses', NULL, 0, NULL, NULL, NULL, NULL, NULL, '2026-06-10 21:59:52', NULL, 'Fakultas Sains dan Teknologi', 'Tim', 'rizka yuniarti', '22205051', '08895909590', 'S-1 Teknik Informatika', NULL, NULL, 'https://www.instagram.com/p/DV0S0Mpkasd/?img_index=1', 'seni mural', '1. ayu\r\n2 Rizka \r\n3. alfin\r\n4. januar\r\n5. elsa', '1781103540_6a297bb49975d.pdf', '1781103540_6a297bb49aac1.jpg', '1781103540_6a297bb49b3ac.pdf', 'mumural seni', 'Seni', '2026-04-01', 'SERTIFIKAT PENGHARGAAN Denganbanggadiberikankepada GretaJMae Gvamy Sebagai: JUARA1 DalamLombaKewirausahaanPelajartingkatsekolahmenengahatas. Semogakeberhasilaninisemakinmemacusemangatbelajar,berinovasi,danmenumbuhkan generasi wirausahamuda yangkreatif danberdayasaing tinggi. LARSPEETERS HARPERRUSSO Ketua Organisasi KetuaPenyelenggara', '0.9503', NULL, NULL, 0),
(11, 13, NULL, NULL, NULL, NULL, NULL, 'Lokal', NULL, NULL, 'Silver Medal', NULL, 'diproses', NULL, 0, NULL, NULL, NULL, NULL, NULL, '2026-06-11 00:05:53', NULL, 'Fakultas Sains dan Teknologi', 'Individu', 'elsafebiana', '22205010', '08896789092', 'S-1 Teknik Informatika', NULL, NULL, 'https://www.instagram.com/p/DV0S0Mpkasd/?img_index=1', '', '', '1781111140_6a29996496bc8.pdf', '1781111140_6a29996497c7a.jpg', '1781111140_6a29996498572.pdf', 'olimpiade sains', 'Akademik', '2026-04-01', 'bhm FOSNAS isR SERTIFIKAT No: 2481443/BHM-FOSNAS/ISO/S/11/2026 Festival OlimpiadeSainsNasional(FOsNAS)dibawahnaunganYayasanBerlianHatiMulia denganNomorlzin:AHU-0007789-AH.01.04Tahun2021memberikanpenghargaankepada: Nama :INTANKOMALASARI Asal Instansi:UNIVERSITASHARKATNEGERI Provinsi ：Prov.Jawa Tengah Bidang :Fisika Jenjang ：PerguruanTinggi Ataspartisipasinyadalam ajangOlimpiadeSainsTingkatNasionalpada tanggal 01Februari2026diSumateraUtara,yangbertajuk INDONESIANSCIENCEOLYMPIAD(ISO)2026,Sebagai: PESERTAAKTIF SumateraUtara,03Februari2026 Mengetahui, bhm FOSNAS BERLIANHATIMULL Prof.Dr.Ir.TriMaftial,MP Drs.M.Isa Ansari,M.Si PembinaIlYayasanBerlianHatiMulia DirekturFestival OlimpiadeSainsNasional Verifikasi KeaslianDokumen Disini (FOSNAS)', '0.9507', 'f783dd66c2becebf533fd82cd0a8fa5664c3c19c21e6845c47731498c0395076', '', 0),
(12, 13, NULL, NULL, NULL, NULL, NULL, 'Lokal', NULL, NULL, 'Silver Medal', NULL, 'diproses', NULL, 0, NULL, NULL, NULL, NULL, NULL, '2026-06-11 00:31:15', NULL, 'Fakultas Sains dan Teknologi', 'Individu', 'elsafebiana', '22205010', '08895909590', 'S-1 Teknik Informatika', NULL, NULL, 'https://www.instagram.com/p/DV0S0Mpkasd/?img_index=1', '', '', '1781112661_6a299f55d1ff4.pdf', '1781112661_6a299f55d2ed2.jpg', '1781112661_6a299f55d3880.pdf', 'olimpiade sains', 'Akademik', '2026-04-01', 'bhm FOSNAS isR SERTIFIKAT No: 2481443/BHM-FOSNAS/ISO/S/11/2026 Festival OlimpiadeSainsNasional(FOsNAS)dibawahnaunganYayasanBerlianHatiMulia denganNomorlzin:AHU-0007789-AH.01.04Tahun2021memberikanpenghargaankepada: Nama :INTANKOMALASARI Asal Instansi:UNIVERSITASHARKATNEGERI Provinsi ：Prov.Jawa Tengah Bidang :Fisika Jenjang ：PerguruanTinggi Ataspartisipasinyadalam ajangOlimpiadeSainsTingkatNasionalpada tanggal 01Februari2026diSumateraUtara,yangbertajuk INDONESIANSCIENCEOLYMPIAD(ISO)2026,Sebagai: PESERTAAKTIF SumateraUtara,03Februari2026 Mengetahui, bhm FOSNAS BERLIANHATIMULL Prof.Dr.Ir.TriMaftial,MP Drs.M.Isa Ansari,M.Si PembinaIlYayasanBerlianHatiMulia DirekturFestival OlimpiadeSainsNasional Verifikasi KeaslianDokumen Disini (FOSNAS)', '0.9507', 'f783dd66c2becebf533fd82cd0a8fa5664c3c19c21e6845c47731498c0395076', 'JYQvF2gGRkHY6+Y1LXefZ5zFsneChugcmS70dmXAbYNU7ZL0gmpeTOckbeVeM9LGLNsz5eN9w+xzVPgIUULvnFHRRWqm1VmSeRBznN51iHNxuC9MJbGOEC4B4ENKOrSZhDe7i2vR6b6OB2SMwJUddgyAkpQx1GXxxxcayTxq4/X8kU58IqSQRWiJiUdkeNGOYRsDZle2SAB4UZkqHDoRGzX3QH09RfklX4fP02Ktz/yGSSLUZMAzRk3n38Ll9Ari6IGdDRMlCIrV/Rwy+LpJtWLvI/7/9CmwMbEU46dTVUAysNJzw14vuoYrh3bBUVtCrGopWtkWs8nKuWJFKKnvMw==', 0),
(13, 12, NULL, NULL, NULL, NULL, NULL, 'Lokal', NULL, NULL, 'Juara 1', NULL, 'ditolak', NULL, 0, NULL, 'Admin: Dokumen pendukung sudah lengkap. Silakan verifikasi keaslian dokumen.\r\nVerifikator: HASIL VERIFIKASI: REVISI\r\n\r\n1. Sertifikat: Tidak Terdeteksi / Tidak Terbaca\r\n- File sertifikat tidak terbaca oleh sistem OCR\r\n- Mohon upload ulang file sertifikat dengan kualitas yang lebih baik (resolusi tinggi, jelas)\r\n\r\n2. Foto: Valid\r\n- 1 foto dokumentasi terupload dan sesuai\r\n\r\n3. Surat Tugas: Valid\r\n- Surat tugas dari kampus terupload dan valid\r\n\r\nREKOMENDASI: REVISI\r\nCatatan:\r\nMohon upload ulang file sertifikat dengan kualitas yang lebih baik agar dapat diverifikasi.\r\nPastikan file sertifikat:\r\n- Berformat PDF\r\n- Resolusi gambar jelas (minimal 300 dpi)\r\n- Nama dan tanggal terbaca dengan baik\r\n\r\nBatas waktu revisi: 7 hari setelah notifikasi ini.', NULL, NULL, NULL, '2026-06-11 00:55:15', NULL, 'Fakultas Sains dan Teknologi', 'Individu', 'rizka yuniarti', '22205051', '08895909590', 'S-1 Teknik Informatika', NULL, NULL, 'https://www.instagram.com/p/DV0S0Mpkasd/?img_index=1', '', '', '1781114106_6a29a4fa2cabf.pdf', '1781114106_6a29a4fa2cfd7.jpg', '1781114106_6a29a4fa2d3ca.pdf', 'kompetensi rpl', 'Akademik', '2026-11-10', '', '0.0000', '328c1458514f356a1c18e879f078e6c61050666de09c8b1d8a3254b018db1770', 'hU5EJ+tMIJTTatEH4qrqJTHo3/74+IURY1Z1brj7bDr3OBeTBvhyiovtthDIkWg4sFslzfd3dk9aG38/OKhqrd2txj1WkQVrgha9BNBM96PE7TrLFUrEHhHBQqXVfd50HsoB1fWJ1GDTepiiWp3ejwPGxRM2VGk9SG2ZiATVPys4rOJbqO/f7+sW5hhhvdC7P+ZqWdKBkwmtNpyzNy0j4VTo3G6FPfRAFaLLnIjRwMMkY939cch1RRSvFypNjY50ByHOfV7Wt59tcUqFOHBRAGWrvYFz66KHDeMlLSv2RqhfmLjH3H+kjqahfnysUpu9POaxep+smdZD9GttxNVVXQ==', 0),
(14, 9, NULL, NULL, NULL, NULL, NULL, 'Provinsi', NULL, NULL, 'Juara 1', NULL, 'ditolak', NULL, 0, NULL, NULL, NULL, NULL, NULL, '2026-06-11 15:40:16', NULL, 'Fakultas Sains dan Teknologi', 'Individu', 'Intan Komalasari', '22205031', '08895909590', 'S-1 Teknik Informatika', NULL, NULL, 'https://kemenag.go.id', '', '', '1781167192_6a2a745871d72.pdf', '1781167192_6a2a745873a98.jpg', '1781167192_6a2a74587479c.pdf', 'olimpiade sains', 'Akademik', '2026-03-01', '', '0.0000', '328c1458514f356a1c18e879f078e6c61050666de09c8b1d8a3254b018db1770', 'hU5EJ+tMIJTTatEH4qrqJTHo3/74+IURY1Z1brj7bDr3OBeTBvhyiovtthDIkWg4sFslzfd3dk9aG38/OKhqrd2txj1WkQVrgha9BNBM96PE7TrLFUrEHhHBQqXVfd50HsoB1fWJ1GDTepiiWp3ejwPGxRM2VGk9SG2ZiATVPys4rOJbqO/f7+sW5hhhvdC7P+ZqWdKBkwmtNpyzNy0j4VTo3G6FPfRAFaLLnIjRwMMkY939cch1RRSvFypNjY50ByHOfV7Wt59tcUqFOHBRAGWrvYFz66KHDeMlLSv2RqhfmLjH3H+kjqahfnysUpu9POaxep+smdZD9GttxNVVXQ==', 0),
(15, 14, NULL, NULL, NULL, NULL, NULL, 'Nasional', NULL, NULL, 'Juara 1', NULL, 'ditolak', NULL, 0, NULL, '\nVerifikator: HASIL VERIFIKASI: TIDAK SESUAI\r\n\r\n1. Sertifikat: [Tulis alasan]\r\n2. Foto: [Tulis alasan]\r\n3. Surat Tugas: [Tulis alasan]\r\n\r\nREKOMENDASI: DITOLAK', NULL, NULL, NULL, '2026-06-24 14:49:48', NULL, 'Fakultas Sains dan Teknologi', 'Individu', 'fatihmuzaki', '22205026', '08896789092', 'S-1 Teknik Informatika', NULL, NULL, 'https://kemenag.go.id', '', '', '1782287343_6a3b8befa5af4.pdf', '1782287343_6a3b8befa702a.jpeg', '1782287343_6a3b8befa7f49.pdf', 'juara 1 membaca alquran', 'Akademik', '2026-06-24', '', '0.0000', '328c1458514f356a1c18e879f078e6c61050666de09c8b1d8a3254b018db1770', 'hU5EJ+tMIJTTatEH4qrqJTHo3/74+IURY1Z1brj7bDr3OBeTBvhyiovtthDIkWg4sFslzfd3dk9aG38/OKhqrd2txj1WkQVrgha9BNBM96PE7TrLFUrEHhHBQqXVfd50HsoB1fWJ1GDTepiiWp3ejwPGxRM2VGk9SG2ZiATVPys4rOJbqO/f7+sW5hhhvdC7P+ZqWdKBkwmtNpyzNy0j4VTo3G6FPfRAFaLLnIjRwMMkY939cch1RRSvFypNjY50ByHOfV7Wt59tcUqFOHBRAGWrvYFz66KHDeMlLSv2RqhfmLjH3H+kjqahfnysUpu9POaxep+smdZD9GttxNVVXQ==', 0),
(16, 15, NULL, NULL, NULL, NULL, NULL, 'Provinsi', NULL, NULL, 'Juara 2', NULL, 'disetujui', NULL, 1000000, NULL, '\nVerifikator: HASIL VERIFIKASI: SESUAI\n\n1. Sertifikat: Valid\n2. Foto: Lengkap\n3. Surat Tugas: Valid\n\nREKOMENDASI: DISETUJUI\nReward: Rp 1.000.000 (Level: Provinsi | Raihan: Juara 2)\nPimpinan: Mahasiswa ini telah melalui proses verifikasi oleh Belum diverifikasi dan dinyatakan layak menerima predikat Mahasiswa Berprestasi periode 2026 dengan skor akhir 0 dan reward Rp 1.000.000.', NULL, NULL, NULL, '2026-06-24 15:28:06', NULL, 'Fakultas Psikologi dan Pendidikan', 'Individu', 'salmanafisa', '22205036', '08896789092', 'S-1 Psikologi', NULL, NULL, 'https://kemenag.go.id', '', '', '1782289667_6a3b95038957b.pdf', '1782289667_6a3b95038aa35.jpeg', '1782289667_6a3b95038b275.pdf', 'kewirausahaan pelajar', 'Kewirausahaan', '2026-06-23', 'SERTIFIKAT PENGHARGAAN Denganbanggadiberikankepada GretaJMae Gvamy Sebagai: JUARA1 DalamLombaKewirausahaanPelajartingkatsekolahmenengahatas. Semogakeberhasilaninisemakinmemacusemangatbelajar,berinovasi,danmenumbuhkan generasi wirausahamuda yangkreatif danberdayasaing tinggi. LARSPEETERS HARPERRUSSO Ketua Organisasi KetuaPenyelenggara', '0.9503', '029fdbaa3cb3a90e2cf198d8204b9f769ebf7ab2cec6bfc05a8bfb0dc13b4b03', 'pd3vwQhfe8Q/jLFltToEt90scqaUxx9xIHMbOkEK2eAMTd6U5R2EYMC6s/uUIl4ae7oN4/yKDFJN5CA3cg6DI2PA46lgfy5zNqVtNLjr2SZsFI67+Lp4PAA5h/FfFXt7vw+uE5jGK7lO+IVyLBlxU/5h3YP+/0gbfyyjy4B6lHCjZXfW8bWf7ejF0iTYE6h1ZilaLYNGPDm52uezIE8sQRuLsj96lGXDzReRjIvSHwDb3NmU7gsKtPNHtpzEQvqRJwa4KVYcENqQzBgtQXRly66t7knYMEEwAOpmiGtdvZGGwVM0OvcJuf9lFNGmnxAmxgLMnzYLpMCMVzKsb+yafw==', 1),
(17, 9, NULL, NULL, NULL, NULL, NULL, 'Internasional', NULL, NULL, 'Juara 1', NULL, 'disetujui', NULL, 2250000, NULL, '\nVerifikator:  HASIL VERIFIKASI: SESUAI\r\n\r\n1. Sertifikat:  Valid\r\n2. Foto:  Lengkap\r\n3. Surat Tugas:  Valid\r\n\r\n REKOMENDASI: DISETUJUI\r\n Reward: Rp 2.250.000 (Level: Internasional | Raihan: Juara 1)\nPimpinan: Mahasiswa ini memenuhi semua kriteria dan layak ditetapkan sebagai Mahasiswa Berprestasi periode 2026/2027.', NULL, NULL, NULL, '2026-07-01 13:24:03', NULL, 'Fakultas Sosial dan Humaniora', 'Individu', 'intankomalasari', '22205031', '08895909590', 'S-1 Ilmu Komunikasi', NULL, NULL, 'https://www.instagram.com/p/DV0S0Mpkasd/?img_index=1', '', '', '1782887004_6a44b25c86c0d.pdf', '1782887004_6a44b25c883a6.jpeg', '1782887004_6a44b25c89adc.pdf', 'kewirausahaan pelajar', 'Kewirausahaan', '2026-06-09', 'SERTIFIKAT PENGHARGAAN Diberikan kepada: Cahaya Dewi Sebagai peserta dari lombamewarnai antarsekolah padatanggal9November2022. MuhammadPatel Benjamin Shah', '0.9793', 'd35d7ada380aec3595c00d390c402e74e57b71fcd2884ababa2c4ddf8b01cf5f', 'c7bR6I1MkaBJj4Q5nBWEmzY7K6uNuu6YrtfbA8wY4KJm+bYjzqfyMIdNXw0IIyXdwOvxv/tpzEF75DuqBV4nkw5sjwTFRwTsIuyuNW1Usd8ANyf+ZqclbWLw69jc5/Tu5jMTMftqHH9uhm7cSPjKKtfhDePOmwISUXHahlXZMW/0L7Pbk4E5nDOipilxsR4CO5cwpTheoz3hIdoVJrkxYpQoV5ojRTp2lFgiHQnFj267aWwVjD9Bq9+yN3i9t7H9vNq0GvKrpP+78hfKfWC9tyQLAZ/JJnlPJTNwTUioYpZc4daoYvo3dIiqevK+4Xvw+BsjW6c1BBJEaVpVEM7hOQ==', 1),
(18, 8, NULL, NULL, NULL, NULL, NULL, 'Lokal', NULL, NULL, 'Juara 1', NULL, 'diproses', NULL, 0, NULL, NULL, NULL, NULL, NULL, '2026-07-04 15:27:48', NULL, 'Fakultas Sains dan Teknologi', 'Individu', 'syakir firdaus', '22205020', '08896789092', 'S-1 Teknik Informatika', NULL, NULL, 'https://kemenag.go.id', '', '', '1783153622_6a48c3d6f3ce7.pdf', '1783153623_6a48c3d701335.png', '1783153623_6a48c3d701ff4.pdf', 'juara 1 membaca alquran', 'Akademik', '2024-03-25', 'SERTIFIKAT No:01234 Diberikan kepada: SyakirCtirdaus Sebagai: Juara 1 PadaKegiatan LombaTahfidzQuranyangdiselenggarakanoleh DewanPengembanganMasjidBorcellepada25Maret 2024 KetuaPanitiaPelaksana, (Dr.MuradNaser,M.Pd.)', '0.9650', '8ee365736186027a5bfd0ece3726241f3fedbf323390313fa02334ddd54642b2', 'evGCKVUI0pO+623YdtWX96J5XqPAvMO4d/3u8RFeCDVdR6GJJd3bLFi4WoUnEjkjoGldePjmUX4IDJcboL+yap5zn+JjkYVP+wxuvH04HBiRKi8A3IU/U1nW+VA+Sxlj4te1EIlwoZzjnP2DOVr+Mk/3/dobi8TmxpMafhIjK97TjZhzLSbAQHJ9BoMQFa5UnWmbMOMrLWCQlo2qto+OVO803RMeivAG9utX5i36y4oaO0KEzD7gAfuTGNeQnkiPPzhkJwO0EG8ZKJMM2bR9jBC1lRgUi9NVUFwfVE5rjFB8PE7+pz1Cb6OJxWGp/LcXHvlvVnJhjYlhHUEHGi7I5w==', 0),
(19, 11, NULL, NULL, NULL, NULL, NULL, 'Nasional', NULL, NULL, 'Juara 1', NULL, 'diproses', NULL, 0, NULL, NULL, NULL, NULL, NULL, '2026-07-04 15:57:49', NULL, 'Fakultas Sains dan Teknologi', 'Individu', 'lutfi laeli nur afiah', '22205067', '08123456789', 'S-1 Teknik Informatika', NULL, NULL, 'https://kemenag.go.id', '', '', '1783155450_6a48cafa3e8c4.pdf', '1783155450_6a48cafa3fdcc.jpg', '1783155450_6a48cafa40295.pdf', 'Program Kreativitas Mahasiswa (PKM) ', 'Akademik', '2025-12-04', 'Kementerian Pendidikan Tinggi, Belm.w. Sains,danTeknologi Republik Indonesia SERTIEIKAT pkm ProgramKreativitas Nom0r:5108/B2/DT.01.00/2025 Jakarta,4Desember2025 Diberikan kepada LUTFILAELINURAFIYAH SEPTINAASTINABILA AFSANIWAHYUMAWARDI FELDISANJAYA Beny Bandanadjaja ILHAMRAMADHANI DirekturPembelajaran STMIK YMI Tegal danKemahasiswaan peraih insentif bidang PKM-GFT Program Kreativitas Mahasiswa (PKM) Tahun 2025 NusaVoice2045:EkosistemAlBahasaNusantaraBerbasisDesentralisasiDataSuaradan 0 Partisipasi Digital-Komunal sebagai Pilar Pelestarian Budaya Masa Depan', '0.9540', '672596b59ccbb1a675755bc57e1c281d59a43dd91d5d127983734dac2b1f55ce', 'KFfdCvRnBEvc0aFD71MB8kKTO/yD1FwpNlU44MyafWaVS/MMMDy4zdM548Sw6BWR0MtdXqxt9BQdL/bSl1oUwGEMHNq/93C7oDfs+HW9lScfrGMnX69ZnuULvgCm3Xct4axFjIvlqmOpOAfNZiMWhLPvSotSv54f1LvHwxpQvC47cIuqvwFfVfR9UbtIkwW0ak13P3cMRPkh4O3FYQeIUA+eeMZU8sRWq7n4/SiAGiElXVOncO8ophNaKFsM9tVoAdLTf8qXrZnseEiGKbiH3fFZfEDiFmQ1eR80K6G2+c/I3NrtwWwlJxiLdpjSqnEt6uu64UC7EEGIOI5hxCtIbA==', 0),
(20, 11, NULL, NULL, NULL, NULL, NULL, 'Nasional', NULL, NULL, 'Juara 1', NULL, 'diproses', NULL, 0, NULL, NULL, NULL, NULL, NULL, '2026-07-04 16:01:44', NULL, 'Fakultas Sains dan Teknologi', 'Individu', 'lutfi laeli nur afiah', '22205067', '08123456789', 'S-1 Teknik Informatika', NULL, NULL, 'https://kemenag.go.id', '', '', '1783155693_6a48cbed2e868.pdf', '1783155693_6a48cbed2f752.jpg', '1783155693_6a48cbed301d4.pdf', 'Program Kreativitas Mahasiswa (PKM) ', 'Akademik', '2025-12-04', '', '0.0000', '63f6c4572ae9508dd9b2529f4a6fcd479d0b201039028f05fb2b1333304e021a', 'nuXyLTD8Y96OVSVQTO7hMhWjEcoD+hne/hPywo+vET7FJrYMROmtXX9kTkrE2XOP4bpBFgSQUmRi2iUIY4mhdTA3WZwRUXvInCPeU0a0PnQtBUqLLvAYBr1oIHaULCuSYEyVDigBb7/HRoiwjUMeMKfq9UFXOVmQs45coPFlh/llNeP198baezJfw1uljJcUXwgf2aDx3gMukO9q9Xro8yAh8gqJHLF4ZXy/PwsMWGnZY/yJeeCYysxLVrr+btxehnJVLXUneD05K9N4of4Ms2FrX4EwcMKet1K898NmlbLMoyvBj9C4XzsKouKbnh64QIllhwQ8EcZj8pTWPxObbA==', 0),
(21, 11, NULL, NULL, NULL, NULL, NULL, 'Nasional', NULL, NULL, 'Juara 3', NULL, 'diproses', NULL, 0, NULL, NULL, NULL, NULL, NULL, '2026-07-04 16:11:51', NULL, 'Fakultas Sains dan Teknologi', 'Individu', 'Alvaro Putra', '22205067', '08895909587', 'S-1 Teknik Informatika', NULL, NULL, 'https://kemenag.go.id', '', '', '1783156292_6a48ce44846d0.pdf', '1783156292_6a48ce448553f.jpg', '1783156292_6a48ce4486d77.pdf', 'Melukis ', 'Seni', '2026-01-12', 'REPUBLIKINDONESIA KEMENTERIANPENDIDIKAN,KEUDAYAAN,RISET,DANTEKNOLOGI DIREKTORATJENDERALKEBUDAYAAN SERTIFIKAT PENGHARGAAN DiberikanKepada ALVARO PUTRA MAHARDIKA SEBAGAI JUARAKE-III LOMBAMELUKISTINGKATNASIONAL FESTIVALSENILUKISPELAJARINDONESIA2026 Ataspencapaianluarbiasa dalammengembangkanbakatseni lukis dan semangatkreativitas di tingkatNasional dengan tema \"HARMONIALAMDANBUDAYAINDONESIA\" Jakarta,12Agustus2026 DIREKTORATPENGEMBANGAN KETUAJURINASIONAL DANPEMANFAATANKEBUDAYAAN Nahyaf NAMA:PROF.DR.WAHYUHIDAYAT,M.SN BAPAKSUPARYO,M.HUM', '0.9473', '76443f42d6c529e6f7d785fb93287505787b17af22f853846cfd408635c59b9c', 'M4k8A/IW5dBZnq+gMjm26KZTogK4JMeJ3dGFB/xIJfy2h43aD9CvmnepXGYtUjcfjUYcB4i4v6VHZw94cmosiykTIt1iF4tKNN1sWuqc6Y246zwTh9mq6+jm5IPjFtB3P3dh79eCyjkQUFGDiC2efPIRIB6lIdHscgOer7i3DazjW4ezz1gEh6rqIstHu8u5yuvoMPy3S+Nw6z1AJ7/6seEk8mkPdak/TfZbx1XIOTG2GzjqlSqXdyXMk9QR1BlKnjhN69aVqJ45Yj/05TFHuxyovRhxXXZj4HpRKUqYz3WU++lD58EyvllgzG4h6LcTGrn3smsecqCCIsyik76a9w==', 0),
(22, 19, NULL, NULL, NULL, NULL, NULL, 'Lokal', NULL, NULL, 'Juara 1', NULL, 'diproses', NULL, 0, NULL, NULL, NULL, NULL, NULL, '2026-07-04 16:24:02', NULL, 'Fakultas Sains dan Teknologi', 'Individu', 'alfaro', '22205067', '08895909587', 'S-1 Teknik Informatika', NULL, NULL, 'https://kemenag.go.id', '', '', '1783157025_6a48d121ccecd.pdf', '1783157025_6a48d121cd5d0.png', '1783157025_6a48d121ce5b6.pdf', 'Melukis ', 'Akademik', '2026-01-12', 'REPUBLIKINDONESIA KEMENTERIANPENDIDIKAN,KEUDAYAAN,RISET,DANTEKNOLOGI DIREKTORATJENDERALKEBUDAYAAN SERTIFIKAT PENGHARGAAN DiberikanKepada ALVARO PUTRA MAHARDIKA SEBAGAI JUARAKE-III LOMBAMELUKISTINGKATNASIONAL FESTIVALSENILUKISPELAJARINDONESIA2026 Ataspencapaianluarbiasa dalammengembangkanbakatseni lukis dan semangatkreativitas di tingkatNasional dengan tema \"HARMONIALAMDANBUDAYAINDONESIA\" Jakarta,12Agustus2026 DIREKTORATPENGEMBANGAN KETUAJURINASIONAL DANPEMANFAATANKEBUDAYAAN Nahyaf NAMA:PROF.DR.WAHYUHIDAYAT,M.SN BAPAKSUPARYO,M.HUM', '0.9473', '76443f42d6c529e6f7d785fb93287505787b17af22f853846cfd408635c59b9c', 'M4k8A/IW5dBZnq+gMjm26KZTogK4JMeJ3dGFB/xIJfy2h43aD9CvmnepXGYtUjcfjUYcB4i4v6VHZw94cmosiykTIt1iF4tKNN1sWuqc6Y246zwTh9mq6+jm5IPjFtB3P3dh79eCyjkQUFGDiC2efPIRIB6lIdHscgOer7i3DazjW4ezz1gEh6rqIstHu8u5yuvoMPy3S+Nw6z1AJ7/6seEk8mkPdak/TfZbx1XIOTG2GzjqlSqXdyXMk9QR1BlKnjhN69aVqJ45Yj/05TFHuxyovRhxXXZj4HpRKUqYz3WU++lD58EyvllgzG4h6LcTGrn3smsecqCCIsyik76a9w==', 0),
(23, 19, NULL, NULL, NULL, NULL, NULL, 'Nasional', NULL, NULL, 'Juara 3', NULL, 'disetujui', NULL, 1000000, NULL, '\nVerifikator: HASIL VERIFIKASI: SESUAI\r\n\r\n1. Sertifikat: Valid\r\n2. Foto: Lengkap\r\n3. Surat Tugas: Valid\r\n\r\nREKOMENDASI: DISETUJUI\r\nReward: Rp 1.000.000 (Level: Nasional | Raihan: Juara 3)', NULL, NULL, NULL, '2026-07-04 16:54:05', NULL, 'Fakultas Sains dan Teknologi', 'Individu', 'alfaro', '22205067', '08123456789', 'S-1 Teknik Informatika', NULL, NULL, 'https://kemenag.go.id', '', '', '1783158821_6a48d825bb485.pdf', '1783158821_6a48d825bd2b2.jpg', '1783158821_6a48d825bf2ef.pdf', 'Melukis ', 'Seni', '2026-01-12', 'REPUBLIKINDONESIA KEMENTERIANPENDIDIKAN,KEUDAYAAN,RISET,DANTEKNOLOGI DIREKTORATJENDERALKEBUDAYAAN SERTIFIKAT PENGHARGAAN DiberikanKepada ALVARO PUTRA MAHARDIKA SEBAGAI JUARAKE-III LOMBAMELUKISTINGKATNASIONAL FESTIVALSENILUKISPELAJARINDONESIA2026 Ataspencapaianluarbiasa dalammengembangkanbakatseni lukis dan semangatkreativitas di tingkatNasional dengan tema \"HARMONIALAMDANBUDAYAINDONESIA\" Jakarta,12Agustus2026 DIREKTORATPENGEMBANGAN KETUAJURINASIONAL DANPEMANFAATANKEBUDAYAAN Nahyaf NAMA:PROF.DR.WAHYUHIDAYAT,M.SN BAPAKSUPARYO,M.HUM', '0.9473', '76443f42d6c529e6f7d785fb93287505787b17af22f853846cfd408635c59b9c', 'M4k8A/IW5dBZnq+gMjm26KZTogK4JMeJ3dGFB/xIJfy2h43aD9CvmnepXGYtUjcfjUYcB4i4v6VHZw94cmosiykTIt1iF4tKNN1sWuqc6Y246zwTh9mq6+jm5IPjFtB3P3dh79eCyjkQUFGDiC2efPIRIB6lIdHscgOer7i3DazjW4ezz1gEh6rqIstHu8u5yuvoMPy3S+Nw6z1AJ7/6seEk8mkPdak/TfZbx1XIOTG2GzjqlSqXdyXMk9QR1BlKnjhN69aVqJ45Yj/05TFHuxyovRhxXXZj4HpRKUqYz3WU++lD58EyvllgzG4h6LcTGrn3smsecqCCIsyik76a9w==', 0),
(24, 19, NULL, NULL, NULL, NULL, NULL, 'Nasional', NULL, NULL, 'Juara 3', NULL, 'ditolak', NULL, 0, NULL, NULL, NULL, NULL, NULL, '2026-07-04 18:09:49', NULL, 'Fakultas Sains dan Teknologi', 'Individu', 'alfaro', '22205067', '08896789092', 'S-1 Teknik Informatika', NULL, NULL, 'https://kemenag.go.id', '', '', '1783163345_6a48e9d1cda82.pdf', '1783163345_6a48e9d1cf1ab.png', '1783163345_6a48e9d1cfda4.pdf', 'melukis', 'Akademik', '2026-07-04', 'PIAGAM 1 Piagam persembahan acara MAKAN CEPAT di Universitas Harkat Negeri dipersembahkan untuk : ALVARO Atas berterimakasih atas partisipasinya di ajang acara lomba ini Tegal 20Agustus2025 Fatih Pamitia acara', '0.9724', 'd0be5ac76a9236e63f8ba217c5a7bd71a3087d6bec9e377d03a309e356cbbc82', 'am2d6hgGsmYtgiqnsbwfKjAn6fxThu95DxB8/gRSCmlRd487/QUNueS380fxH7jF2sA8Pwb1fRITlqqX5hu0lxuB5Cjxf7+B+M3fLzB3ty8NIWECS6wpa6RIXCrAIN7RWl46x0V5XodGRdJIqUI6H2ATO8SAZoHCNYTTwAKD50yLOT7jUOstWzKgEuHon88SBj+qoWr/1yoW6DbpmXmxiYJnmpr1itNIU0W6ol8YSUuszvJVwdPqK4Pyv2irOkDQ/xZkV2IGgmN5vCJbI2PnKmcoKFuVso636620rJn3nMoHaHxQ03S9g53uOcZR1uvcBiA/AeKz1wW8HIO6/ISqPw==', 0),
(25, 19, NULL, NULL, NULL, NULL, NULL, 'Nasional', NULL, NULL, 'Juara 3', NULL, 'ditolak', NULL, 0, NULL, '\nVerifikator: HASIL VERIFIKASI: DITOLAK\r\n\r\n1. Sertifikat: Tidak Valid\r\n   - Hanya ditemukan 1 dari 10 kata kunci penting.\r\n   - Keyword ditemukan: lomba\r\n   - Dokumen tidak sesuai karakteristik sertifikat prestasi.\r\n\r\n2. Foto: Tidak Valid\r\n   - Foto hanya 1 buah (minimal 3).\r\n\r\n3. Surat Tugas: Valid\r\n   - Surat tugas sesuai.\r\n\r\nREKOMENDASI: DITOLAK\r\nCatatan:\r\n- Kata kunci penting pada sertifikat sangat sedikit.\r\n- Dokumentasi foto belum memenuhi persyaratan.\r\n\r\nKeputusan ini bersifat final dan tidak dapat diajukan kembali.', NULL, NULL, NULL, '2026-07-04 18:10:08', NULL, 'Fakultas Sains dan Teknologi', 'Individu', 'alfaro', '22205067', '08896789092', 'S-1 Teknik Informatika', NULL, NULL, 'https://kemenag.go.id', '', '', '1783163389_6a48e9fdaf53b.pdf', '1783163389_6a48e9fdb0723.png', '1783163389_6a48e9fdb1395.pdf', 'melukis', 'Akademik', '2026-07-04', 'PIAGAM 1 Piagam persembahan acara MAKAN CEPAT di Universitas Harkat Negeri dipersembahkan untuk : ALVARO Atas berterimakasih atas partisipasinya di ajang acara lomba ini Tegal 20Agustus2025 Fatih Pamitia acara', '0.9724', 'd0be5ac76a9236e63f8ba217c5a7bd71a3087d6bec9e377d03a309e356cbbc82', 'am2d6hgGsmYtgiqnsbwfKjAn6fxThu95DxB8/gRSCmlRd487/QUNueS380fxH7jF2sA8Pwb1fRITlqqX5hu0lxuB5Cjxf7+B+M3fLzB3ty8NIWECS6wpa6RIXCrAIN7RWl46x0V5XodGRdJIqUI6H2ATO8SAZoHCNYTTwAKD50yLOT7jUOstWzKgEuHon88SBj+qoWr/1yoW6DbpmXmxiYJnmpr1itNIU0W6ol8YSUuszvJVwdPqK4Pyv2irOkDQ/xZkV2IGgmN5vCJbI2PnKmcoKFuVso636620rJn3nMoHaHxQ03S9g53uOcZR1uvcBiA/AeKz1wW8HIO6/ISqPw==', 0),
(26, 19, NULL, NULL, NULL, NULL, NULL, 'Nasional', NULL, NULL, 'Juara 3', NULL, 'disetujui', '2026-07-04 18:43:58', 1000000, NULL, '\nVerifikator: HASIL VERIFIKASI: SESUAI\r\n\r\n1. Sertifikat: Valid\r\n2. Foto: Lengkap\r\n3. Surat Tugas: Valid\r\n\r\nREKOMENDASI: DISETUJUI\r\nReward: Rp 1.000.000 (Level: Nasional | Raihan: Juara 3)\nVerifikator: HASIL VERIFIKASI: SESUAI\r\n\r\n1. Sertifikat: Valid\r\n2. Foto: Lengkap\r\n3. Surat Tugas: Valid\r\n\r\nREKOMENDASI: DISETUJUI\r\nReward: Rp 1.000.000 (Level: Nasional | Raihan: Juara 3)\nPimpinan: Pimpinan menetapkan mahasiswa ini sebagai Mahasiswa Berprestasi periode 2026.', NULL, NULL, NULL, '2026-07-04 18:27:08', NULL, 'Fakultas Sains dan Teknologi', 'Individu', 'alfaro', '22205067', '08896789092', 'S-1 Teknik Informatika', NULL, NULL, 'https://kemenag.go.id', '', '', '1783164414_6a48edfe00692.pdf', '1783164414_6a48edfe01d81.jpg', '1783164414_6a48edfe027cd.pdf', 'melukis', 'Seni', '2024-01-12', 'REPUBLIKINDONESIA KEMENTERIANPENDIDIKAN,KEUDAYAAN,RISET,DANTEKNOLOGI DIREKTORATJENDERALKEBUDAYAAN SERTIFIKAT PENGHARGAAN DiberikanKepada ALVARO PUTRA MAHARDIKA SEBAGAI JUARAKE-III LOMBAMELUKISTINGKATNASIONAL FESTIVALSENILUKISPELAJARINDONESIA2026 Ataspencapaianluarbiasa dalammengembangkanbakatseni lukis dan semangatkreativitas di tingkatNasional dengan tema \"HARMONIALAMDANBUDAYAINDONESIA\" Jakarta,12Agustus2026 DIREKTORATPENGEMBANGAN KETUAJURINASIONAL DANPEMANFAATANKEBUDAYAAN Nahyaf NAMA:PROF.DR.WAHYUHIDAYAT,M.SN BAPAKSUPARYO,M.HUM', '0.9473', '76443f42d6c529e6f7d785fb93287505787b17af22f853846cfd408635c59b9c', 'M4k8A/IW5dBZnq+gMjm26KZTogK4JMeJ3dGFB/xIJfy2h43aD9CvmnepXGYtUjcfjUYcB4i4v6VHZw94cmosiykTIt1iF4tKNN1sWuqc6Y246zwTh9mq6+jm5IPjFtB3P3dh79eCyjkQUFGDiC2efPIRIB6lIdHscgOer7i3DazjW4ezz1gEh6rqIstHu8u5yuvoMPy3S+Nw6z1AJ7/6seEk8mkPdak/TfZbx1XIOTG2GzjqlSqXdyXMk9QR1BlKnjhN69aVqJ45Yj/05TFHuxyovRhxXXZj4HpRKUqYz3WU++lD58EyvllgzG4h6LcTGrn3smsecqCCIsyik76a9w==', 1),
(27, 19, NULL, NULL, NULL, NULL, NULL, 'Nasional', NULL, NULL, 'Juara 3', NULL, 'disetujui', '2026-07-04 19:15:03', 1000000, NULL, '\nVerifikator: HASIL VERIFIKASI: SESUAI\r\n\r\n1. Sertifikat: Valid\r\n2. Foto: Lengkap\r\n3. Surat Tugas: Valid\r\n\r\nREKOMENDASI: DISETUJUI\r\nReward: Rp 1.000.000 (Level: Nasional | Raihan: Juara 3)\nPimpinan: Mahasiswa ini telah melalui proses verifikasi oleh Belum diverifikasi dan dinyatakan layak menerima predikat Mahasiswa Berprestasi periode 2026 dengan skor akhir 0 dan reward Rp 1.000.000.', NULL, NULL, NULL, '2026-07-04 19:13:15', NULL, 'Fakultas Sains dan Teknologi', 'Individu', 'alfaro', '22205067', '08123456789', 'S-1 Teknik Informatika', NULL, NULL, 'https://kemenag.go.id', '', '', '1783167185_6a48f8d1dda6b.pdf', '1783167185_6a48f8d1dee03.png', '1783167185_6a48f8d1df289.pdf', 'melukis', 'Seni', '2025-03-12', 'REPUBLIKINDONESIA KEMENTERIANPENDIDIKAN,KEUDAYAAN,RISET,DANTEKNOLOGI DIREKTORATJENDERALKEBUDAYAAN SERTIFIKAT PENGHARGAAN DiberikanKepada ALVARO PUTRA MAHARDIKA SEBAGAI JUARAKE-III LOMBAMELUKISTINGKATNASIONAL FESTIVALSENILUKISPELAJARINDONESIA2026 Ataspencapaianluarbiasa dalammengembangkanbakatseni lukis dan semangatkreativitas di tingkatNasional dengan tema \"HARMONIALAMDANBUDAYAINDONESIA\" Jakarta,12Agustus2026 DIREKTORATPENGEMBANGAN KETUAJURINASIONAL DANPEMANFAATANKEBUDAYAAN Nahyaf NAMA:PROF.DR.WAHYUHIDAYAT,M.SN BAPAKSUPARYO,M.HUM', '0.9473', '76443f42d6c529e6f7d785fb93287505787b17af22f853846cfd408635c59b9c', 'M4k8A/IW5dBZnq+gMjm26KZTogK4JMeJ3dGFB/xIJfy2h43aD9CvmnepXGYtUjcfjUYcB4i4v6VHZw94cmosiykTIt1iF4tKNN1sWuqc6Y246zwTh9mq6+jm5IPjFtB3P3dh79eCyjkQUFGDiC2efPIRIB6lIdHscgOer7i3DazjW4ezz1gEh6rqIstHu8u5yuvoMPy3S+Nw6z1AJ7/6seEk8mkPdak/TfZbx1XIOTG2GzjqlSqXdyXMk9QR1BlKnjhN69aVqJ45Yj/05TFHuxyovRhxXXZj4HpRKUqYz3WU++lD58EyvllgzG4h6LcTGrn3smsecqCCIsyik76a9w==', 1),
(28, 5, NULL, NULL, NULL, NULL, NULL, 'Provinsi', NULL, NULL, 'Juara 2', NULL, 'ditolak', NULL, 0, NULL, '\nAdmin: Dokumen pendukung sudah lengkap. Silakan verifikasi keaslian dokumen.\nVerifikator: HASIL VERIFIKASI: SESUAI\r\n\r\n1. Sertifikat: Valid\r\n2. Foto: Lengkap\r\n3. Surat Tugas: Valid\r\n\r\nREKOMENDASI: DISETUJUI\r\nReward: Rp 1.000.000 (Level: Provinsi | Raihan: Juara 2)', NULL, NULL, NULL, '2026-07-09 14:51:47', NULL, 'Fakultas Sains dan Teknologi', 'Individu', 'lutfanalwafi', '22205024', '08123456789', 'S-1 Teknik Informatika', NULL, NULL, 'https://kemenag.go.id', '', '', '1783583433_6a4f52c9585bf.pdf', '1783583433_6a4f52c95a67a.jpg', '1783583433_6a4f52c95aff8.pdf', 'melukis', 'Seni', '2026-02-23', 'Dokumen Ini telah ditandatanganl secara elektronik Dikeluarkan Tanggal: 6 LEMBAR BELUMKAWIN KAWINTERCATAT ALYAFIRYALDA BELUMKAWIN SALIMAH Perkawinan Status Nama Lengkap (10) NamaKepalaKeluarga amai Kecamatan Desa/Kelurahan Kepala Keluarga 15-08-2019 06-02-2003 Perkawinan 11 3327016201040008 3327016112810004PEREMPUAN 8327011 ANAK ANAK STRI Status Huobuangan EPALAKELUARGA （2） NIK 52354 001/002 KRAJ 12 LAKI-LAKI Jenis No .3327032612110013 Kewarganegaraan KARTU KELUARGA PEMALANO PEMALANG PEMALANC （13） Tempat Lahir yang No.Pasp （4） Dok nen olehBalal Sertifikasi Elektronik(BSrE),BSSN No.KITAP Imigras -03-2011 -12-1981 09-2015 （5） ISLAM ISLAM ISLAM ISLAM Agama BELUMTAMATSD/SEDERAJAT TP/SEDERAJAT Provinsi Kabupaten/Kota Desa/Kelurahan Syar Pendidikan PENEAARKANG NamaOrangTua PEM MOGA MANDIRAJA PELAJAR/MAHASISW ENGURUSF JenisPekerjaan RUMAH （8) TANGGA cS Dipindai denganCamScanner', '0.9406', '38749e9a2c3fa3405acc66e807d8d56c4239b67f286c7221eafbc8e4033397aa', 'G3VvAo15GdlbDvenDbO/FLhVzMO0yl4IfzwhAHpPAuP3jeEr0mQzjyV+m9j+PHWx5BQD2hwPb4FYZ4L1Kkn81CfSHdiXnLbLHzAm03egXeQxBHrOr4rtblAwDVWuwWLqXTH7W9MsowU4uU7aQj64iXCZnwMw10PVHvhNFIPAZlOjgTwWgyJSin3DXV5QLI9VblG/d2E2vIj6m38K2EpSP96uh9wIZ6ozX+qNtcqLzy7w3m7NhdMFUABmCB+BWCMo95prDKAaVPjSWdJJWzYCoeWXoMjYyg9RnG8yKn7/3Tpoj907hcFk/K8MsyTIQ9XPNSt3IkA63nxm4I29vc8uSg==', 0),
(29, 5, NULL, NULL, NULL, NULL, NULL, 'Nasional', NULL, NULL, 'Juara 3', NULL, 'disetujui', NULL, 1000000, NULL, '\nAdmin: Dokumen pendukung sudah lengkap. Silakan verifikasi keaslian dokumen.\nVerifikator: HASIL VERIFIKASI: SESUAI\r\n\r\n1. Sertifikat: Valid\r\n2. Foto: Lengkap\r\n3. Surat Tugas: Valid\r\n\r\nREKOMENDASI: DISETUJUI\r\nReward: Rp 1.000.000 (Level: Nasional | Raihan: Juara 3)', NULL, NULL, NULL, '2026-07-09 16:22:59', NULL, 'Fakultas Sains dan Teknologi', 'Individu', 'lutfanalwafi', '22205024', '08123456789', 'S-1 Teknik Informatika', NULL, NULL, 'https://kemenag.go.id', '', '', '1783588958_6a4f685eb95ac.pdf', '1783588958_6a4f685eba62e.jpg', '1783588958_6a4f685ebb1f4.pdf', 'olimpiade sains', 'Akademik', '2026-01-22', 'SERTIFIKAT PENGHARGAAN Denganbanggadiberikankepada GretaJMae Gvamy Sebagai: JUARA1 DalamLombaKewirausahaanPelajartingkatsekolahmenengahatas. Semogakeberhasilaninisemakinmemacusemangatbelajar,berinovasi,danmenumbuhkan generasi wirausahamuda yangkreatif danberdayasaing tinggi. LARSPEETERS HARPERRUSSO Ketua Organisasi KetuaPenyelenggara', '0.9503', '029fdbaa3cb3a90e2cf198d8204b9f769ebf7ab2cec6bfc05a8bfb0dc13b4b03', 'pd3vwQhfe8Q/jLFltToEt90scqaUxx9xIHMbOkEK2eAMTd6U5R2EYMC6s/uUIl4ae7oN4/yKDFJN5CA3cg6DI2PA46lgfy5zNqVtNLjr2SZsFI67+Lp4PAA5h/FfFXt7vw+uE5jGK7lO+IVyLBlxU/5h3YP+/0gbfyyjy4B6lHCjZXfW8bWf7ejF0iTYE6h1ZilaLYNGPDm52uezIE8sQRuLsj96lGXDzReRjIvSHwDb3NmU7gsKtPNHtpzEQvqRJwa4KVYcENqQzBgtQXRly66t7knYMEEwAOpmiGtdvZGGwVM0OvcJuf9lFNGmnxAmxgLMnzYLpMCMVzKsb+yafw==', 0),
(30, 21, NULL, NULL, NULL, NULL, NULL, 'Internasional', NULL, NULL, 'Juara 3', NULL, 'ditolak', NULL, 0, NULL, '\nAdmin: Dokumen pendukung sudah lengkap. Silakan verifikasi keaslian dokumen.\nVerifikator: HASIL VERIFIKASI: DITOLAK\r\n\r\n1. Sertifikat: Tidak Valid\r\n   - OCR gagal membaca isi sertifikat.\r\n   - Sertifikat tidak dapat diverifikasi.\r\n\r\n2. Foto: Tidak Valid\r\n   - Foto yang diupload bukan dokumentasi kegiatan lomba.\r\n   - Dokumentasi foto harus berupa foto saat mengikuti lomba/kegiatan.\r\n\r\n3. Surat Tugas: Valid\r\n   - Surat tugas sesuai.\r\n\r\nREKOMENDASI: DITOLAK\r\nCatatan:\r\n- OCR gagal membaca isi sertifikat.\r\n- Kata kunci penting pada sertifikat sangat sedikit.\r\n- Dokumentasi foto belum memenuhi persyaratan.\r\n\r\nKeputusan ini bersifat final dan tidak dapat diajukan kembali.', NULL, NULL, NULL, '2026-07-09 17:11:55', NULL, 'Fakultas Sains dan Teknologi', 'Individu', 'fajarnusantara', '22205028', '08896789092', 'S-1 Teknik Mesin', NULL, NULL, 'https://kemenag.go.id', '', '', '1783591896_6a4f73d8526ce.pdf', '1783591896_6a4f73d8540b8.jpg', '1783591896_6a4f73d854a8b.pdf', 'kewirausahaan pelajar', 'Kewirausahaan', '2025-01-22', '', '0.0000', '328c1458514f356a1c18e879f078e6c61050666de09c8b1d8a3254b018db1770', 'hU5EJ+tMIJTTatEH4qrqJTHo3/74+IURY1Z1brj7bDr3OBeTBvhyiovtthDIkWg4sFslzfd3dk9aG38/OKhqrd2txj1WkQVrgha9BNBM96PE7TrLFUrEHhHBQqXVfd50HsoB1fWJ1GDTepiiWp3ejwPGxRM2VGk9SG2ZiATVPys4rOJbqO/f7+sW5hhhvdC7P+ZqWdKBkwmtNpyzNy0j4VTo3G6FPfRAFaLLnIjRwMMkY939cch1RRSvFypNjY50ByHOfV7Wt59tcUqFOHBRAGWrvYFz66KHDeMlLSv2RqhfmLjH3H+kjqahfnysUpu9POaxep+smdZD9GttxNVVXQ==', 0),
(31, 21, NULL, NULL, NULL, NULL, NULL, 'Nasional', NULL, NULL, 'Juara 1', NULL, 'disetujui', NULL, 1750000, NULL, '\nAdmin: Dokumen pendukung sudah lengkap. Silakan verifikasi keaslian dokumen.\nVerifikator: HASIL VERIFIKASI: SESUAI\r\n\r\n1. Sertifikat: Valid\r\n2. Foto: Lengkap\r\n3. Surat Tugas: Valid\r\n\r\nREKOMENDASI: DISETUJUI\r\nReward: Rp 1.750.000 (Level: Nasional | Raihan: Juara 1)\nPimpinan: Pimpinan menetapkan mahasiswa ini sebagai Mahasiswa Berprestasi periode 2026.', NULL, NULL, NULL, '2026-07-09 17:15:57', NULL, 'Fakultas Sains dan Teknologi', 'Individu', 'fajarnusantara', '22205028', '08896789092', 'S-1 Teknik Mesin', NULL, NULL, 'https://kemenag.go.id', '', '', '1783592150_6a4f74d690e77.pdf', '1783592150_6a4f74d6914ee.jpg', '1783592150_6a4f74d691a66.pdf', 'kewirausahaan pelajar', 'Kewirausahaan', '2025-01-20', 'SERTIFIKAT PENGHARGAAN Denganbanggadiberikankepada GretaJMae Gvamy Sebagai: JUARA1 DalamLombaKewirausahaanPelajartingkatsekolahmenengahatas. Semogakeberhasilaninisemakinmemacusemangatbelajar,berinovasi,danmenumbuhkan generasi wirausahamuda yangkreatif danberdayasaing tinggi. LARSPEETERS HARPERRUSSO Ketua Organisasi KetuaPenyelenggara', '0.9503', '029fdbaa3cb3a90e2cf198d8204b9f769ebf7ab2cec6bfc05a8bfb0dc13b4b03', 'pd3vwQhfe8Q/jLFltToEt90scqaUxx9xIHMbOkEK2eAMTd6U5R2EYMC6s/uUIl4ae7oN4/yKDFJN5CA3cg6DI2PA46lgfy5zNqVtNLjr2SZsFI67+Lp4PAA5h/FfFXt7vw+uE5jGK7lO+IVyLBlxU/5h3YP+/0gbfyyjy4B6lHCjZXfW8bWf7ejF0iTYE6h1ZilaLYNGPDm52uezIE8sQRuLsj96lGXDzReRjIvSHwDb3NmU7gsKtPNHtpzEQvqRJwa4KVYcENqQzBgtQXRly66t7knYMEEwAOpmiGtdvZGGwVM0OvcJuf9lFNGmnxAmxgLMnzYLpMCMVzKsb+yafw==', 1),
(32, 22, NULL, NULL, NULL, NULL, NULL, 'Lokal', NULL, NULL, 'Juara 1', NULL, 'disetujui', '2026-08-04 13:54:42', 750000, NULL, '\nAdmin: Dokumen pendukung sudah lengkap. Silakan verifikasi keaslian dokumen.\nVerifikator: HASIL VERIFIKASI: SESUAI\r\n\r\n1. Sertifikat: Valid\r\n2. Foto: Lengkap\r\n3. Surat Tugas: Valid\r\n\r\nREKOMENDASI: DISETUJUI\r\nReward: Rp 750.000 (Level: Lokal | Raihan: Juara 1)\nPimpinan: Pimpinan menetapkan mahasiswa ini sebagai Mahasiswa Berprestasi periode 2026.', NULL, NULL, NULL, '2026-08-04 13:30:08', NULL, 'Fakultas Sains dan Teknologi', 'Individu', 'cahaya dewi', '22205089', '083156789876', 'S-1 Teknik Informatika', NULL, NULL, 'https://www.instagram.com/p/DV0S0Mpkasd/?img_index=1', '', '', '1785824956_6a7186bce50a5.pdf', '1785824956_6a7186bce6132.jpg', '1785824956_6a7186bce70d5.pdf', 'balap karung', 'Non-Akademik', '2025-08-17', 'SERTIFIKAT 79 TH PENGHARGAAN DiberikanKepada: CahayaDewi Sebagai: JUARA1LOMBABALAPKARUNG PadaPeringatanHUTRIKe-79diKelurahanBorcelle 17Agustus2024 KETUTSUSILO SAMIRAHADID KetuaPanitia Juri Lomba', '0.9792', '3c41c3031cc1d1534a15e2e2a030e99cf04cf1245e3ac6726e069eb468d2fb54', 'YVjFP2qMjGr0wK4dppbRO4XRv5aljCj0Ju/q+A+CA/VLc2bR1SNJ7cr6C+r5nqJto/cPoJQ3PtxTwnSePM0rjoLrBY35AExR9pbvDn3tfS5b7w6qy9C/lIsUbovY9+Tx53dRA6YhsoD8WNSE9oVWYDzeR0ppclSwJgpaM7U6hpKkrDBLngshMDTvc9iDZZtgfvY53gmhavVzgW49M4DQTv6Gd0UJRMYrqFB3eqdsSWGdSJtj0ayTBRRnvXMOWXjcPolRRUEHWguwyZr4EDEv82RRA3U5xFe77Q1UYLH0L17FcjbrSgN60zHHfpp+xPOISnwfWOlNYAYIU1nLG9527w==', 1);

-- --------------------------------------------------------

--
-- Table structure for table `pengaturan_periode`
--

CREATE TABLE `pengaturan_periode` (
  `id` int NOT NULL,
  `periode_mulai` date NOT NULL,
  `periode_selesai` date NOT NULL,
  `tahun_akademik` varchar(20) NOT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `pengaturan_periode`
--

INSERT INTO `pengaturan_periode` (`id`, `periode_mulai`, `periode_selesai`, `tahun_akademik`, `is_active`, `created_at`, `updated_at`) VALUES
(1, '2026-07-30', '2026-09-30', '2026/2027', 1, '2026-06-28 12:17:30', '2026-08-02 03:08:49');

-- --------------------------------------------------------

--
-- Table structure for table `sertifikat_asli`
--

CREATE TABLE `sertifikat_asli` (
  `id` int NOT NULL,
  `nomor_sertifikat` varchar(50) NOT NULL,
  `qr_text` text NOT NULL,
  `nama_mahasiswa` varchar(100) NOT NULL,
  `nim` varchar(20) NOT NULL,
  `nama_kegiatan` varchar(200) NOT NULL,
  `tingkat` enum('Lokal','Provinsi','Nasional','Internasional') NOT NULL,
  `juara` varchar(50) NOT NULL,
  `tanggal_terbit` date NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `sertifikat_asli`
--

INSERT INTO `sertifikat_asli` (`id`, `nomor_sertifikat`, `qr_text`, `nama_mahasiswa`, `nim`, `nama_kegiatan`, `tingkat`, `juara`, `tanggal_terbit`, `created_at`) VALUES
(1, 'CERT-2024-001', 'http://localhost/prestasi-mahasiswa/verifikasi_sertifikat.php?nomor=CERT-2024-001', 'Budi Santoso', '20210001', 'Lomba Debat Mahasiswa Nasional 2025', 'Nasional', 'Juara 1', '2025-01-15', '2026-04-16 16:42:41'),
(2, 'CERT-2024-002', 'http://localhost/prestasi-mahasiswa/verifikasi_sertifikat.php?nomor=CERT-2024-002', 'Siti Aminah', '20210002', 'Inovasi Teknologi 2025', 'Provinsi', 'Juara 2', '2025-02-10', '2026-04-16 16:42:41');

-- --------------------------------------------------------

--
-- Table structure for table `sertifikat_valid`
--

CREATE TABLE `sertifikat_valid` (
  `id` int NOT NULL,
  `nomor_sertifikat` varchar(100) NOT NULL,
  `nama_peserta` varchar(100) NOT NULL,
  `nim` varchar(20) NOT NULL,
  `nama_kejuaraan` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `value` text,
  `description` text,
  `category` varchar(50) DEFAULT 'general',
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `name`, `value`, `description`, `category`, `created_at`, `updated_at`) VALUES
(1, 'site_name', 'Sistem Prestasi Mahasiswa', 'Nama sistem', 'general', '0000-00-00 00:00:00', NULL),
(2, 'site_description', 'Platform pengajuan dan penilaian prestasi mahasiswa', 'Deskripsi sistem', 'general', '0000-00-00 00:00:00', NULL),
(3, 'maintenance_mode', '0', 'Mode maintenance (0=off, 1=on)', 'general', '0000-00-00 00:00:00', NULL),
(4, 'max_file_size', '5', 'Ukuran maksimal file upload (MB)', 'upload', '0000-00-00 00:00:00', NULL),
(5, 'allowed_file_types', 'pdf,jpg,jpeg,png,doc,docx', 'Tipe file yang diizinkan', 'upload', '0000-00-00 00:00:00', NULL),
(6, 'achievement_submission_open', '1', 'Apakah pengajuan prestasi dibuka', 'achievement', '0000-00-00 00:00:00', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `nim` varchar(20) DEFAULT NULL,
  `fakultas` varchar(100) DEFAULT NULL,
  `prodi` varchar(50) DEFAULT NULL,
  `role` enum('mahasiswa','reviewer','admin','superadmin') NOT NULL DEFAULT 'mahasiswa',
  `no_hp` varchar(20) DEFAULT NULL,
  `alamat` text,
  `foto_profil` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive','locked','suspended') NOT NULL DEFAULT 'active',
  `email_verified` tinyint(1) DEFAULT '1',
  `verification_token` varchar(100) DEFAULT NULL,
  `reset_token` varchar(100) DEFAULT NULL,
  `reset_expiry` datetime DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `lock_until` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `nama_lengkap`, `nim`, `fakultas`, `prodi`, `role`, `no_hp`, `alamat`, `foto_profil`, `status`, `email_verified`, `verification_token`, `reset_token`, `reset_expiry`, `last_login`, `lock_until`, `created_at`, `updated_at`) VALUES
(1, 'admin', '0192023a7bbd73250516f069df18b500', 'admin@example.com', 'Administrator', NULL, NULL, NULL, 'admin', '', NULL, '1782656771_6a412f031d63d.jpg', 'active', 0, NULL, NULL, NULL, NULL, NULL, '2026-02-16 01:40:37', NULL),
(3, 'alya', 'a42d640365f9ba18208ab059cd068e09', 'firyalda@gmail.com', 'alya firyalda', '22205023', 'Fakultas Sosial dan Humaniora', 'S-1 Ilmu Komunikasi', 'mahasiswa', '08123456789', NULL, NULL, 'active', 1, NULL, NULL, NULL, NULL, NULL, '2026-04-18 14:17:31', '2026-04-18 14:17:31'),
(4, 'riska', 'c786ba863b0c7e943a79643a7b7f8d6f', 'adha@gmail.com', 'adhaazkia', '22205043', 'Fakultas Sosial dan Humaniora', 'S-1 Ilmu Komunikasi', 'mahasiswa', '08896789092', NULL, NULL, 'active', 1, NULL, NULL, NULL, NULL, NULL, '2026-04-18 14:19:57', '2026-04-18 14:19:57'),
(5, 'lutfan', '8485030acbb7ff246b7bff2884f02c1d', 'lutfanalwafi@gmail.com', 'lutfanalwafi', '22205024', 'Fakultas Sains dan Teknologi', 'Sistem Informasi', 'mahasiswa', '08895909590', NULL, NULL, 'active', 1, NULL, NULL, NULL, NULL, NULL, '2026-04-18 14:51:29', '2026-04-18 14:51:29'),
(7, 'yusuf', 'add3deb05fc6625aae939041e4131624', 'yusufshafwan@gmail.com', 'yusuf shafwan', '22295913', 'Fakultas Sains dan Teknologi', 'Teknik Komputer', 'mahasiswa', '083156789712', NULL, NULL, 'active', 1, NULL, NULL, NULL, NULL, NULL, '2026-04-21 06:58:35', '2026-04-21 06:58:35'),
(8, 'syakirfirdaus', '25d55ad283aa400af464c76d713c07ad', 'syakir@gmail.com', 'syakir firdaus', '22205020', 'Fakultas Sains dan Teknologi', 'S-1 Teknik Informatika', 'mahasiswa', '08896789092', NULL, NULL, 'active', 1, NULL, NULL, NULL, NULL, NULL, '2026-05-02 14:37:00', '2026-05-02 14:37:00'),
(9, 'intan', '46a7357b0b816cb9dd56d70d2a385cfd', 'intan@gmail.com', 'intankomalasari', '22205031', 'Fakultas Sains dan Teknologi', 'S-1 Teknik Informatika', 'mahasiswa', '08896789092', NULL, NULL, 'active', 1, NULL, NULL, NULL, NULL, NULL, '2026-06-01 10:56:27', NULL),
(10, 'agustina', '4e817b7b5292b8e002d7d90290210b0a', 'farhan20@gmail.com', 'Tina', '22205034', 'Fakultas Sains dan Teknologi', 'S-1 Teknik Mesin', 'mahasiswa', '08896789092', NULL, NULL, 'active', 1, NULL, NULL, NULL, NULL, NULL, '2026-06-03 09:53:56', NULL),
(12, 'rizkaa', '4e3f21ae72da3df6486396428caa0508', 'rizkayuniartiii@gmail.com', 'rizka yuniarti', '22205051', 'Fakultas Sains dan Teknologi', 'S-1 Teknik Informatika', 'mahasiswa', '08123456789', NULL, NULL, 'active', 1, NULL, NULL, NULL, NULL, NULL, '2026-06-10 20:26:53', NULL),
(13, 'elsa', 'eebe7a9510effa608ae9bfc8e70e93b9', 'elsa@gmail.com', 'elsafebiana', '22205010', 'Fakultas Sains dan Teknologi', 'S-1 Teknik Informatika', 'mahasiswa', '08123456789', NULL, NULL, 'active', 1, NULL, NULL, NULL, NULL, NULL, '2026-06-11 00:02:02', NULL),
(14, 'Fatih', 'e821a8bfc2c786f275e5d5ea94d519a7', 'fatih@gmail.com', 'fatihmuzaki', '22205026', 'Fakultas Sains dan Teknologi', 'S-1 Teknik Informatika', 'mahasiswa', '08896789092', NULL, NULL, 'active', 1, NULL, NULL, NULL, NULL, NULL, '2026-06-24 14:45:30', NULL),
(15, 'salma', '34b0d4d0b3c4173ec489e4ab677cbbdf', 'salma@gmail.com', 'salmanafisa', '22205036', 'Fakultas Psikologi dan Pendidikan', 'S-1 Psikologi', 'mahasiswa', '08896789092', NULL, NULL, 'active', 1, NULL, NULL, NULL, NULL, NULL, '2026-06-24 15:25:44', NULL),
(16, 'nopal', 'f740ede9091a8ca13cb3ec5a783b9ad2', 'nopal@gmail.com', 'nopalzen', '22205087', 'Fakultas Sains dan Teknologi', 'S-1 Sains Data', 'mahasiswa', '08123456789', NULL, NULL, 'active', 1, NULL, NULL, NULL, NULL, NULL, '2026-06-26 01:12:47', NULL),
(17, 'verifikator1', '488e1c470f549f029d0b34f32abd3475', 'andi@univ.ac.id', 'Dr. Andi Wijaya, M.Kom', NULL, NULL, NULL, 'reviewer', '08123456789', NULL, NULL, 'active', 1, NULL, NULL, NULL, NULL, NULL, '2026-07-02 17:32:11', NULL),
(18, 'pimpinan1', '7d3207a13dc221ac13c2f3dac3011f50', 'budi@univ.ac.id', 'Prof. Dr. Budi Santoso, M.Si', NULL, NULL, NULL, 'superadmin', '08123456780', NULL, NULL, 'active', 1, NULL, NULL, NULL, NULL, NULL, '2026-07-02 17:32:11', NULL),
(19, 'alvaro', '25d55ad283aa400af464c76d713c07ad', 'alvaro@gmail.com', 'alfaro', '22205067', 'Fakultas Sains dan Teknologi', 'S-1 Teknik Informatika', 'mahasiswa', '0832465789', NULL, NULL, 'active', 1, NULL, NULL, NULL, NULL, NULL, '2026-07-04 16:22:09', NULL),
(20, 'muzaqi', '25d55ad283aa400af464c76d713c07ad', 'muzaqi@gmail.com', 'muzaqi', '22205070', 'Fakultas Sains dan Teknologi', 'S-1 Teknik Informatika', 'mahasiswa', '08896789092', NULL, NULL, 'active', 1, NULL, NULL, NULL, NULL, NULL, '2026-07-09 16:56:22', NULL),
(21, 'fajar', '7bedc9fd30769590c992b8f7f23738f7', 'fajar@gmail.com', 'fajarnusantara', '22205028', 'Fakultas Sains dan Teknologi', 'S-1 Teknik Mesin', 'mahasiswa', '08896789092', NULL, NULL, 'active', 1, NULL, NULL, NULL, NULL, NULL, '2026-07-09 17:08:16', NULL),
(22, 'cahaya', 'c464ec5a50c46219854c3079a06b5e04', 'cahayadewi@gmail.com', 'cahaya dewi', '22205089', 'Fakultas Sains dan Teknologi', 'S-1 Teknik Informatika', 'mahasiswa', '087898657854', NULL, NULL, 'active', 1, NULL, NULL, NULL, NULL, NULL, '2026-08-04 13:25:16', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `achievement_periods`
--
ALTER TABLE `achievement_periods`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kategori_prestasi`
--
ALTER TABLE `kategori_prestasi`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kategori_reward`
--
ALTER TABLE `kategori_reward`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifikasi`
--
ALTER TABLE `notifikasi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `role` (`role`),
  ADD KEY `is_read` (`is_read`);

--
-- Indexes for table `pengajuan`
--
ALTER TABLE `pengajuan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mahasiswa_id` (`mahasiswa_id`),
  ADD KEY `kategori_id` (`kategori_id`),
  ADD KEY `admin_id` (`admin_id`),
  ADD KEY `periode_id` (`periode_id`);

--
-- Indexes for table `pengaturan_periode`
--
ALTER TABLE `pengaturan_periode`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sertifikat_asli`
--
ALTER TABLE `sertifikat_asli`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nomor_sertifikat` (`nomor_sertifikat`);

--
-- Indexes for table `sertifikat_valid`
--
ALTER TABLE `sertifikat_valid`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `nim` (`nim`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `achievement_periods`
--
ALTER TABLE `achievement_periods`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `kategori_prestasi`
--
ALTER TABLE `kategori_prestasi`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kategori_reward`
--
ALTER TABLE `kategori_reward`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `notifikasi`
--
ALTER TABLE `notifikasi`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `pengajuan`
--
ALTER TABLE `pengajuan`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `pengaturan_periode`
--
ALTER TABLE `pengaturan_periode`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `sertifikat_asli`
--
ALTER TABLE `sertifikat_asli`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `sertifikat_valid`
--
ALTER TABLE `sertifikat_valid`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `pengajuan`
--
ALTER TABLE `pengajuan`
  ADD CONSTRAINT `pengajuan_ibfk_1` FOREIGN KEY (`mahasiswa_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pengajuan_ibfk_2` FOREIGN KEY (`kategori_id`) REFERENCES `kategori_prestasi` (`id`),
  ADD CONSTRAINT `pengajuan_ibfk_3` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `pengajuan_ibfk_4` FOREIGN KEY (`periode_id`) REFERENCES `achievement_periods` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
