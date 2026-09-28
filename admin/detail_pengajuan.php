<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once '../config/database.php';

function hitungRewardOtomatis($conn, $level, $raihan, $jenis_peserta) {
    $query = "SELECT nominal FROM kategori_reward 
              WHERE tingkat = '$level' AND raihan = '$raihan' AND kategori = '$jenis_peserta' 
              LIMIT 1";
    $result = mysqli_query($conn, $query);
    
    if ($row = mysqli_fetch_assoc($result)) {
        return $row['nominal'];
    }
    return 0;
}

// =========================================================
// FUNGSI HAPUS EMOJI UNTUK NOTIFIKASI
// =========================================================
function hapusEmoji($text) {
    $text = preg_replace('/[\x{1F600}-\x{1F64F}]/u', '', $text);
    $text = preg_replace('/[\x{1F300}-\x{1F5FF}]/u', '', $text);
    $text = preg_replace('/[\x{1F680}-\x{1F6FF}]/u', '', $text);
    $text = preg_replace('/[\x{2600}-\x{26FF}]/u', '', $text);
    $text = preg_replace('/[\x{2700}-\x{27BF}]/u', '', $text);
    $text = preg_replace('/\x{FE0F}/u', '', $text);
    return trim($text);
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id == 0) { header("Location: verifikasi.php"); exit(); }

$query = "SELECT p.*, u.nama_lengkap, u.nim, u.email, u.no_hp, u.prodi, u.id as user_id
          FROM pengajuan p 
          JOIN users u ON p.user_id = u.id 
          WHERE p.id = $id";
$result = mysqli_query($conn, $query);
$row = mysqli_fetch_assoc($result);
if (!$row) { echo "<div style='padding:20px; text-align:center;'><h2>Data tidak ditemukan</h2><a href='verifikasi.php'>Kembali</a></div>"; exit(); }

$reward_otomatis = hitungRewardOtomatis($conn, $row['level_kompetisi'], $row['raihan_prestasi'], $row['jenis_peserta']);

// ===== CEK STATUS DARI VERIFIKATOR =====
$status_verifikator = $row['status'];
$catatan_verifikator = $row['admin_catatan'] ?? '';

// ===== VALIDASI DOKUMEN OTOMATIS =====
function cekValidasiDokumen($row) {
    $hasil = [
        'sertifikat' => ['status' => false, 'pesan' => ''],
        'foto' => ['status' => false, 'pesan' => ''],
        'surat_tugas' => ['status' => false, 'pesan' => '']
    ];
    
    // 1. CEK SERTIFIKAT
    if (!empty($row['sertifikat'])) {
        $ext = strtolower(pathinfo($row['sertifikat'], PATHINFO_EXTENSION));
        $ext_valid = ['pdf', 'jpg', 'jpeg', 'png'];
        if (in_array($ext, $ext_valid)) {
            $hasil['sertifikat']['status'] = true;
            $hasil['sertifikat']['pesan'] = '✅ Valid ('.strtoupper($ext).')';
        } else {
            $hasil['sertifikat']['status'] = false;
            $hasil['sertifikat']['pesan'] = '❌ Format harus PDF/JPG/PNG (sekarang: '.strtoupper($ext).')';
        }
    } else {
        $hasil['sertifikat']['status'] = false;
        $hasil['sertifikat']['pesan'] = '❌ Belum diupload';
    }
    
    // 2. CEK FOTO
    if (!empty($row['foto_dokumentasi'])) {
        $foto_list = explode(',', $row['foto_dokumentasi']);
        $semua_valid = true;
        $ext_foto = [];
        foreach ($foto_list as $foto) {
            $foto = trim($foto);
            if (!empty($foto)) {
                $ext = strtolower(pathinfo($foto, PATHINFO_EXTENSION));
                $ext_foto[] = strtoupper($ext);
                if (!in_array($ext, ['jpg', 'jpeg', 'png'])) {
                    $semua_valid = false;
                }
            }
        }
        if ($semua_valid) {
            $hasil['foto']['status'] = true;
            $hasil['foto']['pesan'] = '✅ Valid ('.count($foto_list).' foto JPG/PNG)';
        } else {
            $hasil['foto']['status'] = false;
            $hasil['foto']['pesan'] = '❌ Format harus JPG/PNG (sekarang: '.implode(', ', array_unique($ext_foto)).')';
        }
    } else {
        $hasil['foto']['status'] = false;
        $hasil['foto']['pesan'] = '❌ Belum diupload';
    }
    
    // 3. CEK SURAT TUGAS
    if (!empty($row['surat_tugas'])) {
        $ext = strtolower(pathinfo($row['surat_tugas'], PATHINFO_EXTENSION));
        $ext_valid = ['pdf', 'jpg', 'jpeg', 'png'];
        if (in_array($ext, $ext_valid)) {
            $hasil['surat_tugas']['status'] = true;
            $hasil['surat_tugas']['pesan'] = '✅ Valid ('.strtoupper($ext).')';
        } else {
            $hasil['surat_tugas']['status'] = false;
            $hasil['surat_tugas']['pesan'] = '❌ Format harus PDF/JPG/PNG (sekarang: '.strtoupper($ext).')';
        }
    } else {
        $hasil['surat_tugas']['status'] = false;
        $hasil['surat_tugas']['pesan'] = '❌ Belum diupload';
    }
    
    return $hasil;
}

$validasi = cekValidasiDokumen($row);

// ===== CEK APAKAH SEMUA LENGKAP =====
$semua_lengkap = $validasi['sertifikat']['status'] && $validasi['foto']['status'] && $validasi['surat_tugas']['status'];

// =========================================================
// ===== PISAHKAN CATATAN ADMIN DAN VERIFIKATOR =====
// =========================================================
$full_catatan = $row['admin_catatan'] ?? '';
$catatan_admin_only = '';
$catatan_verifikator_only = '';

$pos_verifikator = strpos($full_catatan, 'Verifikator:');
$pos_admin = strpos($full_catatan, 'Admin:');

if ($pos_admin !== false) {
    $catatan_admin_only = trim(substr($full_catatan, 0, $pos_verifikator !== false ? $pos_verifikator : strlen($full_catatan)));
    $catatan_admin_only = str_replace('Admin: ', '', $catatan_admin_only);
}

if ($pos_verifikator !== false) {
    $catatan_verifikator_only = trim(substr($full_catatan, $pos_verifikator));
}

$catatan_verifikator_only = preg_replace('/^Verifikator:\s*/', '', $catatan_verifikator_only);
$catatan_admin_only = trim($catatan_admin_only);

// =========================================================
// PROSES KIRIM KE VERIFIKATOR / TOLAK
// =========================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    
    if ($action == 'kirim_verifikator') {
        $catatan = mysqli_real_escape_string($conn, $_POST['catatan']);
        $query_get = "SELECT admin_catatan FROM pengajuan WHERE id = $id";
        $result_get = mysqli_query($conn, $query_get);
        $row_get = mysqli_fetch_assoc($result_get);
        $catatan_lama = $row_get['admin_catatan'] ?? '';
        
        if (strpos($catatan_lama, $catatan) === false) {
            $catatan_baru = $catatan_lama . "\nAdmin: " . $catatan;
            mysqli_query($conn, "UPDATE pengajuan SET status = 'diproses', admin_catatan = '$catatan_baru' WHERE id = $id");
        } else {
            mysqli_query($conn, "UPDATE pengajuan SET status = 'diproses' WHERE id = $id");
        }
        echo "<script>alert('✅ Pengajuan berhasil dikirim ke Tim Verifikator!'); window.location.href='verifikasi.php';</script>";
        exit();
    }
    
    if ($action == 'setuju') {
        $nominal_reward = isset($_POST['nominal_reward']) ? (int)$_POST['nominal_reward'] : $reward_otomatis;
        mysqli_query($conn, "UPDATE pengajuan SET 
            status = 'disetujui', 
            reward_nominal = $nominal_reward, 
            tanggal_disetujui = NOW() 
            WHERE id = $id");
        echo "<script>alert('✅ Pengajuan disetujui! Reward: Rp " . number_format($nominal_reward, 0, ',', '.') . "'); window.location.href='verifikasi.php';</script>";
        exit();
    } 
    
    if ($action == 'tolak') {
        // Ambil catatan dari textarea
        $catatan = mysqli_real_escape_string($conn, $_POST['catatan']);
        
        // Update status jadi ditolak
        mysqli_query($conn, "UPDATE pengajuan SET status = 'ditolak' WHERE id = $id");
        
        // ===== KIRIM NOTIFIKASI KE MAHASISWA =====
        $user_id = $row['user_id'];
        $judul = "Pengajuan Ditolak";
        $pesan = "Pengajuan prestasi Anda telah ditolak.\n\n" .
                 "Alasan penolakan:\n" . $catatan . 
                 "\n\nKeputusan ini bersifat final.";
        $link = "../mahasiswa/detail_pengajuan.php?id=" . $id;
        
        $judul_clean = hapusEmoji($judul);
        $pesan_clean = hapusEmoji($pesan);
        
        $query_notif = "INSERT INTO notifikasi (user_id, role, judul, pesan, link) 
                        VALUES ('$user_id', 'mahasiswa', '$judul_clean', '$pesan_clean', '$link')";
        mysqli_query($conn, $query_notif);
        
        echo "<script>alert('❌ Pengajuan ditolak! Notifikasi telah dikirim ke mahasiswa.'); window.location.href='verifikasi.php';</script>";
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pengajuan - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Inter', 'Segoe UI', sans-serif; 
            background: #f4f6f9; 
            color: #1a1a1a;
            display: flex;
            min-height: 100vh;
        }

        /* ========================================================= */
        /* SIDEBAR - WARNA BIRU                                      */
        /* ========================================================= */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 260px;
            height: 100vh;
            background: linear-gradient(180deg, #1a365d 0%, #2a5298 100%);
            padding: 20px 0;
            border-right: 1px solid rgba(255, 255, 255, 0.1);
            overflow-y: auto;
            z-index: 100;
            flex-shrink: 0;
            box-shadow: 2px 0 15px rgba(26, 54, 93, 0.2);
        }

        .sidebar::-webkit-scrollbar {
            width: 4px;
        }
        .sidebar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
        }
        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 10px;
        }

        .sidebar-header {
            text-align: center;
            margin-bottom: 25px;
            padding: 0 20px 15px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-header h2 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 5px;
            color: #ffffff;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .sidebar-header h2 span {
            color: #60a5fa;
        }

        .sidebar-header p {
            font-size: 11px;
            color: rgba(255, 255, 255, 0.7);
        }

        .sidebar a {
            display: flex;
            align-items: center;
            padding: 10px 16px;
            margin: 2px 12px;
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s;
            border-radius: 8px;
        }

        .sidebar a i {
            width: 24px;
            margin-right: 12px;
            color: rgba(255, 255, 255, 0.5);
            font-size: 16px;
            text-align: center;
            transition: all 0.2s;
        }

        .sidebar a:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #ffffff;
        }
        .sidebar a:hover i {
            color: #60a5fa;
        }

        .sidebar a.active {
            background: rgba(255, 255, 255, 0.15);
            color: #ffffff;
            border-left: 3px solid #60a5fa;
            box-shadow: inset 0 0 20px rgba(255, 255, 255, 0.05);
        }

        .sidebar a.active i {
            color: #60a5fa;
        }

        .sidebar a:last-child {
            margin-top: 40px;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            padding-top: 16px;
        }

        /* ========================================================= */
        /* MAIN CONTENT - RESPONSIF                                  */
        /* ========================================================= */
        .main-content {
            margin-left: 260px;
            flex: 1;
            padding: 24px 32px;
            min-height: 100vh;
            max-width: calc(100% - 260px);
        }

        .top-bar {
            background: white;
            border-radius: 12px;
            padding: 16px 24px;
            margin-bottom: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: 1px solid #e8e8e8;
        }
        .page-title h1 {
            font-size: 20px;
            font-weight: 600;
            color: #1a1a1a;
        }
        .page-title p {
            font-size: 13px;
            color: #666;
            margin-top: 4px;
        }
        .user-avatar {
            width: 40px;
            height: 40px;
            background: #2c7da0;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }
        .user-name {
            margin: 0 10px;
            font-weight: 500;
        }
        .btn-logout {
            background: none;
            border: 1px solid #ddd;
            padding: 6px 16px;
            border-radius: 8px;
            color: #555;
            text-decoration: none;
            font-size: 13px;
        }
        .btn-logout:hover { background: #f0f2f5; }

        .detail-card {
            background: white;
            border-radius: 12px;
            border: 1px solid #e8e8e8;
            margin-bottom: 24px;
            overflow: hidden;
        }
        .card-header {
            padding: 12px 20px;
            border-bottom: 1px solid #eee;
            background: #f8f9fa;
            font-weight: 600;
            color: #1a1a1a;
            font-size: 15px;
        }
        .card-header i {
            margin-right: 8px;
            color: #2c7da0;
        }
        .card-body {
            padding: 20px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px 24px;
        }
        .info-item {
            display: flex;
            align-items: baseline;
            gap: 8px;
            padding: 6px 0;
            border-bottom: 1px dashed #f0f0f0;
        }
        .info-label {
            width: 130px;
            font-size: 13px;
            font-weight: 500;
            color: #888;
        }
        .info-value {
            flex: 1;
            font-size: 13px;
            font-weight: 500;
            color: #333;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 14px;
            border-radius: 30px;
            font-size: 12px;
            font-weight: 600;
        }
        .status-diproses { background: #fff3e0; color: #e67e22; }
        .status-disetujui { background: #e8f5e9; color: #2e7d32; }
        .status-ditolak { background: #ffebee; color: #c62828; }
        .status-revisi { background: #fff3cd; color: #856404; }
        
        .reward-input {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #f8f9fa;
            padding: 6px 12px;
            border-radius: 10px;
            border: 1px solid #e0e6ed;
        }
        .reward-input span {
            font-size: 13px;
            font-weight: 600;
            color: #2c7da0;
        }
        .reward-input input {
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 6px 10px;
            font-size: 13px;
            font-weight: 600;
            width: 140px;
            text-align: right;
            font-family: 'Inter', monospace;
        }
        .reward-input input:focus {
            outline: none;
            border-color: #2c7da0;
        }
        .reward-note {
            font-size: 11px;
            color: #888;
            margin-top: 5px;
        }

        .file-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #2c7da0;
            color: white;
            padding: 4px 12px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 12px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .file-link:hover {
            background: #1f5e7a;
        }
        .no-file {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #f0f2f5;
            padding: 4px 12px;
            border-radius: 8px;
            color: #999;
            font-size: 12px;
        }

        .btn-approve {
            background: #2e7d32;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
        }
        .btn-reject {
            background: #c62828;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
        }
        .btn-kirim {
            background: #2c7da0;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
        }
        .btn-kirim:disabled {
            background: #bdc3c7;
            cursor: not-allowed;
        }
        .btn-back {
            background: #6c757d;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            font-weight: 600;
        }
        .button-group {
            display: flex;
            gap: 12px;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        .rekomendasi-box {
            background: #f0f7ff;
            padding: 16px 20px;
            border-radius: 12px;
            border-left: 4px solid #2c7da0;
            margin-top: 12px;
            margin-bottom: 16px;
        }
        .rekomendasi-box .label {
            font-weight: 600;
            color: #1a4a6f;
            font-size: 13px;
            display: block;
            margin-bottom: 4px;
        }
        .rekomendasi-box .text {
            font-size: 14px;
            color: #1a1a1a;
            line-height: 1.6;
        }

        .catatan-box {
            background: #f8f9fa;
            padding: 12px 16px;
            border-radius: 8px;
            border: 1px solid #e0e6ed;
            margin-top: 12px;
        }
        .catatan-box textarea {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            resize: vertical;
            min-height: 60px;
        }
        .catatan-box textarea:focus {
            outline: none;
            border-color: #2c7da0;
        }

        .status-kelengkapan {
            padding: 12px 16px;
            border-radius: 8px;
            margin-top: 12px;
            font-weight: 600;
            font-size: 14px;
        }
        .status-lengkap {
            background: #e8f5e9;
            border-left: 4px solid #2e7d32;
            color: #2e7d32;
        }
        .status-tidak-lengkap {
            background: #ffebee;
            border-left: 4px solid #c62828;
            color: #c62828;
        }

        .dokumen-status {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 12px;
            margin-top: 8px;
        }
        .dokumen-status-item {
            padding: 10px 14px;
            border-radius: 8px;
            background: #f8f9fa;
            border: 1px solid #e0e6ed;
            font-size: 13px;
        }
        .dokumen-status-item .label {
            font-weight: 600;
            color: #555;
        }
        .dokumen-status-item .status {
            font-weight: 600;
            margin-top: 4px;
        }
        .status-ok { color: #2e7d32; }
        .status-error { color: #c62828; }

        .catatan-admin-box {
            background: #f8f9fa;
            padding: 12px 16px;
            border-radius: 8px;
            border-left: 4px solid #2c7da0;
            margin-bottom: 12px;
        }

        @media (max-width: 900px) {
            .main-content {
                padding: 16px;
                max-width: 100%;
            }
            .info-grid {
                grid-template-columns: 1fr;
            }
            .dokumen-status {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 200px;
            }
            .main-content {
                margin-left: 200px;
                padding: 16px;
                max-width: calc(100% - 200px);
            }
            .info-grid {
                grid-template-columns: 1fr;
            }
            .info-label {
                width: 110px;
            }
            .dokumen-status {
                grid-template-columns: 1fr;
            }
            .top-bar {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }
        }

        @media (max-width: 600px) {
            body {
                flex-direction: column;
            }
            .sidebar {
                position: relative;
                width: 100%;
                height: auto;
                border-right: none;
                border-bottom: 1px solid rgba(255, 255, 255, 0.1);
                padding: 10px 0;
            }
            .sidebar-header {
                margin-bottom: 10px;
                padding-bottom: 10px;
            }
            .sidebar a {
                padding: 8px 16px;
                font-size: 13px;
                margin: 1px 8px;
            }
            .main-content {
                margin-left: 0;
                padding: 12px;
                max-width: 100%;
            }
            .dokumen-status {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<!-- ========================================================= -->
<!-- SIDEBAR - WARNA BIRU                                      -->
<!-- ========================================================= -->
<div class="sidebar">
    <div class="sidebar-header">
        <h2>SMB</h2>
        <p>Sistem Mahasiswa Berprestasi</p>
    </div>
    <a href="dashboard.php">
        <i class="fas fa-tachometer-alt"></i> Dashboard
    </a>
    <a href="verifikasi.php" class="active">
        <i class="fas fa-check-circle"></i> Verifikasi Pengajuan
    </a>
    <a href="semua_pengajuan.php">
        <i class="fas fa-list"></i> Semua Pengajuan
    </a>
    <a href="kategori_reward.php">
        <i class="fas fa-tag"></i> Kategori Reward
    </a>
    <a href="data_mahasiswa.php">
        <i class="fas fa-users"></i> Data Mahasiswa
    </a>
    <a href="laporan_prestasi.php">
        <i class="fas fa-chart-bar"></i> Laporan Prestasi
    </a>
    <a href="aturan_periode.php">
        <i class="fas fa-calendar-alt"></i> Aturan Periode
    </a>
    <a href="profil_saya.php">
        <i class="fas fa-user"></i> Profil Saya
    </a>
    <a href="../logout.php" style="margin-top: 40px;">
        <i class="fas fa-sign-out-alt"></i> Logout
    </a>
</div>

<div class="main-content">

    <div class="top-bar">
        <div class="page-title">
            <h1>Detail Pengajuan Prestasi</h1>
            <p>Informasi lengkap pengajuan prestasi mahasiswa</p>
        </div>
        <div style="display: flex; align-items: center; gap: 16px;">
            <div class="user-avatar">A</div>
            <span class="user-name"><?php echo htmlspecialchars($_SESSION['nama'] ?? 'Admin'); ?></span>
            <a href="../logout.php" class="btn-logout">Logout</a>
        </div>
    </div>

    <!-- ========================================================= -->
    <!-- INFORMASI MAHASISWA                                       -->
    <!-- ========================================================= -->
    <div class="detail-card">
        <div class="card-header"><i class="fas fa-user-graduate"></i> Informasi Mahasiswa</div>
        <div class="card-body">
            <div class="info-grid">
                <div class="info-item"><span class="info-label">Nama Lengkap</span><span class="info-value">: <?php echo htmlspecialchars($row['nama_lengkap']); ?></span></div>
                <div class="info-item"><span class="info-label">NIM</span><span class="info-value">: <?php echo htmlspecialchars($row['nim']); ?></span></div>
                <div class="info-item"><span class="info-label">Program Studi</span><span class="info-value">: <?php echo htmlspecialchars($row['prodi']); ?></span></div>
                <div class="info-item"><span class="info-label">No. Handphone</span><span class="info-value">: <?php echo htmlspecialchars($row['no_hp']); ?></span></div>
                <div class="info-item"><span class="info-label">Email</span><span class="info-value">: <?php echo htmlspecialchars($row['email'] ?? '-'); ?></span></div>
            </div>
        </div>
    </div>

    <!-- ========================================================= -->
    <!-- DATA PRESTASI                                             -->
    <!-- ========================================================= -->
    <div class="detail-card">
        <div class="card-header"><i class="fas fa-trophy"></i> Data Prestasi</div>
        <div class="card-body">
            <div class="info-grid">
                <div class="info-item"><span class="info-label">Nama Kejuaraan</span><span class="info-value">: <?php echo htmlspecialchars($row['nama_kejuaraan']); ?></span></div>
                <div class="info-item"><span class="info-label">Level Kompetisi</span><span class="info-value">: <?php echo htmlspecialchars($row['level_kompetisi']); ?></span></div>
                <div class="info-item"><span class="info-label">Kategori Prestasi</span><span class="info-value">: <?php echo htmlspecialchars($row['kategori_prestasi']); ?></span></div>
                <div class="info-item"><span class="info-label">Raihan Prestasi</span><span class="info-value">: <?php echo htmlspecialchars($row['raihan_prestasi']); ?></span></div>
                <div class="info-item"><span class="info-label">Tanggal Perolehan</span><span class="info-value">: <?php echo htmlspecialchars($row['tanggal_raihan']); ?></span></div>
            </div>
        </div>
    </div>

    <!-- ========================================================= -->
    <!-- DOKUMEN PENDUKUNG                                         -->
    <!-- ========================================================= -->
    <div class="detail-card">
        <div class="card-header"><i class="fas fa-paperclip"></i> Dokumen Pendukung</div>
        <div class="card-body">
            <div class="info-item">
                <span class="info-label">Sertifikat</span>
                <span class="info-value">: 
                    <?php if (!empty($row['sertifikat'])): ?>
                        <a href="../uploads/sertifikat/<?php echo $row['sertifikat']; ?>" target="_blank" class="file-link">📄 Lihat Sertifikat</a>
                    <?php else: ?>
                        <span class="no-file">Tidak ada</span>
                    <?php endif; ?>
                </span>
            </div>
            <div class="info-item">
                <span class="info-label">Foto Dokumentasi</span>
                <span class="info-value">: 
                    <?php if (!empty($row['foto_dokumentasi'])): ?>
                        <?php 
                        $foto_list = explode(',', $row['foto_dokumentasi']);
                        foreach ($foto_list as $index => $foto):
                            $foto = trim($foto);
                            if (!empty($foto)):
                        ?>
                            <a href="../uploads/foto/<?php echo $foto; ?>" target="_blank" class="file-link" style="margin-right: 8px; margin-bottom: 5px; display: inline-block;">
                                📸 Foto <?php echo $index + 1; ?>
                            </a>
                        <?php 
                            endif;
                        endforeach; 
                        ?>
                    <?php else: ?>
                        <span class="no-file">Tidak ada</span>
                    <?php endif; ?>
                </span>
            </div>
            <?php if (!empty($row['surat_tugas'])): ?>
            <div class="info-item">
                <span class="info-label">Surat Tugas</span>
                <span class="info-value">: 
                    <a href="../uploads/surat_tugas/<?php echo $row['surat_tugas']; ?>" target="_blank" class="file-link">📑 Lihat Surat</a>
                </span>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ========================================================= -->
    <!-- ✅ CEK KELENGKAPAN BERKAS                                 -->
    <!-- ========================================================= -->
    <div class="detail-card">
        <div class="card-header"><i class="fas fa-check-circle"></i> Cek Kelengkapan Berkas</div>
        <div class="card-body">
            
            <div class="dokumen-status">
                <div class="dokumen-status-item">
                    <div class="label">📄 Sertifikat</div>
                    <div class="status <?php echo $validasi['sertifikat']['status'] ? 'status-ok' : 'status-error'; ?>">
                        <?php echo $validasi['sertifikat']['pesan']; ?>
                    </div>
                </div>
                <div class="dokumen-status-item">
                    <div class="label">🖼️ Foto</div>
                    <div class="status <?php echo $validasi['foto']['status'] ? 'status-ok' : 'status-error'; ?>">
                        <?php echo $validasi['foto']['pesan']; ?>
                    </div>
                </div>
                <div class="dokumen-status-item">
                    <div class="label">📑 Surat Tugas</div>
                    <div class="status <?php echo $validasi['surat_tugas']['status'] ? 'status-ok' : 'status-error'; ?>">
                        <?php echo $validasi['surat_tugas']['pesan']; ?>
                    </div>
                </div>
            </div>

            <?php if ($semua_lengkap): ?>
                <div class="status-kelengkapan status-lengkap">
                    <i class="fas fa-check-circle"></i> <strong>STATUS: LENGKAP</strong> — Semua dokumen lengkap dan sesuai!
                </div>
            <?php else: ?>
                <div class="status-kelengkapan status-tidak-lengkap">
                    <i class="fas fa-exclamation-circle"></i> <strong>STATUS: TIDAK LENGKAP</strong> — Ada dokumen yang tidak sesuai.
                </div>
            <?php endif; ?>

        </div>
    </div>

    <!-- ========================================================= -->
    <!-- 📝 CATATAN ADMIN                                           -->
    <!-- ========================================================= -->
    <?php if ($row['status'] == 'diproses'): ?>
    <div class="detail-card">
        <div class="card-header"><i class="fas fa-pen"></i> Catatan Admin</div>
        <div class="card-body">
            <?php if (!empty($catatan_admin_only)): ?>
                <div class="catatan-admin-box">
                    <div style="font-size: 13px; color: #666;">Catatan dari Admin:</div>
                    <div style="font-size: 14px; color: #1a1a1a;"><?php echo nl2br(htmlspecialchars($catatan_admin_only)); ?></div>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="catatan-box">
                    <label style="font-weight: 600; font-size: 13px; display: block; margin-bottom: 6px;">Catatan Admin</label>
                    <textarea name="catatan" rows="3" placeholder="Tulis catatan..."><?php echo $semua_lengkap ? 'Dokumen pendukung sudah lengkap. Silakan verifikasi keaslian dokumen.' : 'Dokumen tidak lengkap. Pengajuan ditolak.'; ?></textarea>
                </div>
                
                <div class="button-group">
                    <?php if ($semua_lengkap): ?>
                        <button type="submit" name="action" value="kirim_verifikator" class="btn-kirim">
                            <i class="fas fa-paper-plane"></i> Kirim ke Tim Verifikator
                        </button>
                    <?php else: ?>
                        <button type="submit" name="action" value="tolak" class="btn-reject">
                            <i class="fas fa-times"></i> Tolak Pengajuan
                        </button>
                    <?php endif; ?>
                    <a href="verifikasi.php" class="btn-back"><i class="fas fa-arrow-left"></i> Kembali</a>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <!-- ========================================================= -->
    <!-- 📝 REKOMENDASI DARI VERIFIKATOR                           -->
    <!-- ========================================================= -->
    <?php if ($row['status'] == 'disetujui' || $row['status'] == 'ditolak'): ?>
    <div class="detail-card">
        <div class="card-header"><i class="fas fa-check-double"></i> Hasil Verifikasi Verifikator</div>
        <div class="card-body">
            <div class="rekomendasi-box">
                <span class="label">
                    <?php if ($row['status'] == 'disetujui'): ?>
                        ✅ Rekomendasi: DISETUJUI
                    <?php elseif ($row['status'] == 'ditolak'): ?>
                        ❌ Rekomendasi: DITOLAK
                    <?php else: ?>
                        ⏳ Rekomendasi: DIPROSES
                    <?php endif; ?>
                </span>
                <div class="text">
                    <?php 
                    $catatan_verifikator_tampil = !empty($catatan_verifikator_only) ? $catatan_verifikator_only : 'Tidak ada catatan dari verifikator.';
                    echo nl2br(htmlspecialchars($catatan_verifikator_tampil));
                    ?>
                </div>
            </div>
            
            <?php if ($row['status'] == 'disetujui'): ?>
            <form method="POST">
                <div class="info-item">
                    <span class="info-label">Nominal Reward</span>
                    <span class="info-value">
                        <div class="reward-input">
                            <span>Rp</span>
                            <input type="text" name="nominal_reward" id="nominal_reward" value="<?php echo number_format($reward_otomatis, 0, ',', '.'); ?>" onkeyup="formatRupiah(this)">
                            <span style="font-size:11px;">,00</span>
                        </div>
                        <div class="reward-note">
                            <i class="fas fa-info-circle"></i> 
                            Nilai otomatis: Rp <?php echo number_format($reward_otomatis, 0, ',', '.'); ?> (bisa diubah)
                        </div>
                    </span>
                </div>
                <div class="button-group">
                    <button type="submit" name="action" value="setuju" class="btn-approve"><i class="fas fa-check"></i> Setujui</button>
                    <a href="verifikasi.php" class="btn-back"><i class="fas fa-arrow-left"></i> Kembali</a>
                </div>
            </form>
            <?php else: ?>
            <div class="button-group">
                <a href="verifikasi.php" class="btn-back"><i class="fas fa-arrow-left"></i> Kembali ke Daftar</a>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

</div>

<script>
    function formatRupiah(input) {
        let value = input.value.replace(/[^0-9]/g, '');
        if (value !== '') {
            let formatted = new Intl.NumberFormat('id-ID').format(parseInt(value));
            input.value = formatted;
        }
    }
    
    document.querySelector('form')?.addEventListener('submit', function(e) {
        let rewardInput = document.getElementById('nominal_reward');
        if (rewardInput) {
            let rawValue = rewardInput.value.replace(/[^0-9]/g, '');
            rewardInput.value = rawValue;
        }
    });
</script>

</body>
</html>