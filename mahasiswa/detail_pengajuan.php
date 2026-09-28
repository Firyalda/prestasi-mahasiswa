<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: ../login.php");
    exit();
}

require_once '../config/database.php';

// ========== Ambil data user termasuk foto_profil ==========
$user_id = $_SESSION['user_id'];
$query_user = "SELECT nama_lengkap, foto_profil FROM users WHERE id = $user_id";
$result_user = mysqli_query($conn, $query_user);
$user_data2 = mysqli_fetch_assoc($result_user);
$foto_profil = $user_data2['foto_profil'] ?? '';
// ===========================================================

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$query = "SELECT p.*, u.nama_lengkap, u.nim, u.prodi, u.no_hp, u.fakultas
          FROM pengajuan p 
          JOIN users u ON p.user_id = u.id 
          WHERE p.id = $id AND p.user_id = $user_id";
$result = mysqli_query($conn, $query);
$data = mysqli_fetch_assoc($result);

if (!$data) {
    header("Location: riwayat.php");
    exit();
}

// Tentukan status badge (REVISI DIHAPUS)
$status_class = '';
$status_text = '';
$status_icon = '';
switch ($data['status']) {
    case 'diproses':
        $status_class = 'status-diproses';
        $status_text = 'Diproses';
        $status_icon = '⏳';
        break;
    case 'disetujui':
        $status_class = 'status-disetujui';
        $status_text = 'Disetujui';
        $status_icon = '✅';
        break;
    case 'ditolak':
        $status_class = 'status-ditolak';
        $status_text = 'Ditolak';
        $status_icon = '❌';
        break;
    default:
        $status_class = 'status-diproses';
        $status_text = 'Diproses';
        $status_icon = '⏳';
}

// =========================================================
// AMBIL CATATAN DARI DATABASE & FILTER
// =========================================================
function hapusDuplikatCatatan($text) {
    $lines = explode("\n", $text);
    $unique_lines = [];
    $seen = [];
    
    foreach ($lines as $line) {
        $line_trim = trim($line);
        if (empty($line_trim)) {
            $unique_lines[] = $line;
            continue;
        }
        if (!in_array($line_trim, $seen)) {
            $seen[] = $line_trim;
            $unique_lines[] = $line;
        }
    }
    return implode("\n", $unique_lines);
}

$catatan = $data['admin_catatan'] ?? '';
$catatan = preg_replace('/\n?Reward: Rp [0-9.,]+[^\n]*/', '', $catatan);
$catatan = preg_replace('/\s*\(Level:[^)]*\)/', '', $catatan);
$catatan = hapusDuplikatCatatan($catatan);
$catatan = trim($catatan);

// Fungsi untuk format ukuran file
function formatFileSize($bytes) {
    if ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 1) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 1) . ' KB';
    } else {
        return $bytes . ' B';
    }
}

include 'includes/sidebar.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pengajuan - Mahasiswa | SMB</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/burger-menu.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', system-ui, sans-serif; background: #f4f6f9; color: #1a1a1a; }
        .menu-logout {
            margin-top: 40px;
        }

        .main-content {
            margin-left: 260px;
            padding: 24px 32px;
            min-height: 100vh;
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
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #2c7da0;
            overflow: hidden;
        }
        .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .user-name {
            margin: 0 10px;
            font-weight: 500;
        }

        .btn-group {
            display: flex;
            gap: 12px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #2c7da0;
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
        }
        .btn-back:hover {
            text-decoration: underline;
        }
        .btn-print {
            background: #28a745;
            color: white;
            border: none;
            padding: 6px 16px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }
        .btn-print:hover {
            background: #1e7e34;
        }

        .detail-card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            border: 1px solid #e8e8e8;
        }

        .detail-section {
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 1px solid #eee;
        }
        .detail-section:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .section-title {
            font-size: 16px;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 16px;
        }

        .detail-row {
            display: flex;
            margin-bottom: 10px;
            flex-wrap: wrap;
        }
        .detail-label {
            width: 140px;
            font-weight: 600;
            color: #666;
            font-size: 13px;
        }
        .detail-value {
            flex: 1;
            color: #333;
            font-size: 13px;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            border-radius: 30px;
            font-size: 12px;
            font-weight: 500;
        }
        .status-diproses {
            background: #fff3e0;
            color: #e67e22;
        }
        .status-disetujui {
            background: #e0f5e8;
            color: #28a745;
        }
        .status-ditolak {
            background: #ffe0e0;
            color: #dc3545;
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

        .dokumen-thumbnail {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 5px;
        }
        .dokumen-thumbnail .thumb-item {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 5px;
            background: #f9f9f9;
            text-align: center;
            width: 120px;
        }
        .dokumen-thumbnail .thumb-item img {
            width: 100%;
            height: 80px;
            object-fit: cover;
            border-radius: 4px;
        }
        .dokumen-thumbnail .thumb-item .thumb-name {
            font-size: 10px;
            color: #666;
            margin-top: 4px;
            word-wrap: break-word;
        }
        .dokumen-thumbnail .thumb-item .thumb-size {
            font-size: 9px;
            color: #999;
        }
        .dokumen-thumbnail .thumb-item .thumb-icon {
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f0f0f0;
            border-radius: 4px;
            color: #999;
            font-size: 36px;
        }

        .btn-back-bottom {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #2c7da0;
            color: white;
            padding: 8px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            margin-top: 20px;
            transition: all 0.3s ease;
        }
        .btn-back-bottom:hover {
            background: #1f5e7a;
        }

        /* ========================================================= */
        /* PRINT CSS                                                */
        /* ========================================================= */
        @media print {
            .main-content {
                margin-left: 0 !important;
                padding: 0 !important;
            }
            .detail-card {
                border: none !important;
                border-radius: 0 !important;
                box-shadow: none !important;
                padding: 20px !important;
            }
            .detail-section {
                border-bottom: 1px solid #ddd !important;
            }
            body {
                background: white !important;
            }
            .dokumen-thumbnail .thumb-item {
                border: 1px solid #ccc !important;
                width: 100px !important;
            }
            .dokumen-thumbnail .thumb-item img {
                height: 70px !important;
            }
            .dokumen-thumbnail .thumb-item .thumb-name {
                font-size: 9px !important;
            }
            .file-link, .no-file {
                border: 1px solid #ddd !important;
                background: none !important;
                color: #1a1a1a !important;
            }
            .status-badge {
                border: 1px solid #ddd !important;
            }
            .print-only {
                display: block !important;
            }
            .page-title {
                display: none !important;
            }
            .page-title h1, .page-title p {
                display: none !important;
            }
            .top-bar .page-title {
                display: none !important;
            }
        }

        @media (max-width: 768px) {
            .sidebar { width: 80px; }
            .sidebar a span { display: none; }
            .sidebar .sidebar-header h2, .sidebar-header p { display: none; }
            .main-content { margin-left: 80px; padding: 16px; }
            .detail-label { width: 110px; }
            .dokumen-thumbnail .thumb-item {
                width: 100px;
            }
        }
    </style>
</head>
<body>

<div class="main-content">
    <!-- TOP BAR -->
    <div class="top-bar">
        <div class="page-title">
            <h1>Detail Pengajuan</h1>
            <p>Informasi lengkap pengajuan prestasi Anda</p>
        </div>
        <div style="display: flex; align-items: center; gap: 16px;">
            <div class="user-avatar">
                <?php if (!empty($foto_profil) && file_exists("../uploads/profil/" . $foto_profil)): ?>
                    <img src="../uploads/profil/<?php echo $foto_profil; ?>" alt="Foto Profil">
                <?php else: ?>
                    <i class="fas fa-user"></i>
                <?php endif; ?>
            </div>
            <span class="user-name"><?php echo htmlspecialchars($user_data2['nama_lengkap'] ?? $_SESSION['nama'] ?? 'Mahasiswa'); ?></span>
        </div>
    </div>

    <!-- TOMBOL AKSI -->
    <div class="btn-group no-print">
        <a href="riwayat.php" class="btn-back">
            <i class="fas fa-arrow-left"></i> Kembali ke Riwayat
        </a>
        <button class="btn-print" onclick="window.print()">
            <i class="fas fa-print"></i> Cetak Bukti
        </button>
    </div>

    <!-- DETAIL CARD -->
    <div class="detail-card" id="buktiPengajuan">

        <!-- HEADER BUKTI (Muncul Saat Print) -->
        <div style="text-align: center; margin-bottom: 20px; display: none;" class="print-only">
            <h1 style="font-size: 22px; font-weight: 700;">📄 BUKTI PENGAJUAN PRESTASI</h1>
            <p style="font-size: 13px; color: #888;">Sistem Mahasiswa Berprestasi (SMB)</p>
            <p style="font-size: 12px; color: #999;">Nomor Pengajuan: #SMB-<?php echo str_pad($data['id'], 5, '0', STR_PAD_LEFT); ?></p>
            <hr style="margin: 10px 0; border: 1px solid #2c7da0;">
        </div>

        <!-- ========================================================= -->
        <!-- INFORMASI PRESTASI                                        -->
        <!-- ========================================================= -->
        <div class="detail-section">
            <div class="section-title">📋 Informasi Prestasi</div>
            <div class="detail-row">
                <div class="detail-label">Nama Kejuaraan</div>
                <div class="detail-value">: <?php echo htmlspecialchars($data['nama_kejuaraan']); ?></div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Level Kompetisi</div>
                <div class="detail-value">: <?php echo $data['level_kompetisi']; ?></div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Kategori Prestasi</div>
                <div class="detail-value">: <?php echo $data['kategori_prestasi']; ?></div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Prestasi yang diraih</div>
                <div class="detail-value">: <?php echo $data['raihan_prestasi']; ?></div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Tanggal Sertifikat</div>
                <div class="detail-value">: <?php echo !empty($data['tanggal_raihan']) ? date('d/m/Y', strtotime($data['tanggal_raihan'])) : '-'; ?></div>
            </div>
            <!-- ========================================================= -->
            <!-- PENYELENGGARA DAN LOKASI DIHAPUS                          -->
            <!-- ========================================================= -->
        </div>

        <!-- Informasi Peserta -->
        <div class="detail-section">
            <div class="section-title">👤 Informasi Peserta</div>
            <div class="detail-row">
                <div class="detail-label">Jenis Peserta</div>
                <div class="detail-value">: <?php echo $data['jenis_peserta']; ?></div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Nama <?php echo $data['jenis_peserta'] == 'Tim' ? 'Ketua' : 'Peserta'; ?></div>
                <div class="detail-value">: <?php echo htmlspecialchars($data['nama_ketua']); ?></div>
            </div>
            <div class="detail-row">
                <div class="detail-label">NIM</div>
                <div class="detail-value">: <?php echo $data['nim_ketua']; ?></div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Program Studi</div>
                <div class="detail-value">: <?php echo $data['prodi']; ?></div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Fakultas</div>
                <div class="detail-value">: <?php echo htmlspecialchars($data['fakultas'] ?? '-'); ?></div>
            </div>
            <div class="detail-row">
                <div class="detail-label">No. Handphone</div>
                <div class="detail-value">: <?php echo $data['no_hp']; ?></div>
            </div>
            <?php if ($data['jenis_peserta'] == 'Tim' && !empty($data['nama_tim'])): ?>
            <div class="detail-row">
                <div class="detail-label">Nama Tim</div>
                <div class="detail-value">: <?php echo htmlspecialchars($data['nama_tim']); ?></div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Anggota Tim</div>
                <div class="detail-value">: <?php echo nl2br(htmlspecialchars($data['anggota_tim'])); ?></div>
            </div>
            <?php endif; ?>
            <?php if (!empty($data['dosen_pembimbing'])): ?>
            <div class="detail-row">
                <div class="detail-label">Dosen Pembimbing</div>
                <div class="detail-value">: <?php echo htmlspecialchars($data['dosen_pembimbing']); ?></div>
            </div>
            <?php endif; ?>
        </div>

        <!-- DOKUMEN PENDUKUNG -->
        <div class="detail-section">
            <div class="section-title">📎 Dokumen Pendukung</div>
            
            <!-- Sertifikat -->
            <div class="detail-row">
                <div class="detail-label">Sertifikat</div>
                <div class="detail-value">
                    <?php if (!empty($data['sertifikat'])): 
                        $sertifikat_path = "../uploads/sertifikat/" . $data['sertifikat'];
                        $file_ext = strtolower(pathinfo($data['sertifikat'], PATHINFO_EXTENSION));
                        $is_image = in_array($file_ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                        $file_size = file_exists($sertifikat_path) ? filesize($sertifikat_path) : 0;
                    ?>
                        <div class="dokumen-thumbnail">
                            <div class="thumb-item">
                                <?php if ($is_image && file_exists($sertifikat_path)): ?>
                                    <img src="<?php echo $sertifikat_path; ?>" alt="Sertifikat">
                                <?php else: ?>
                                    <div class="thumb-icon">📄</div>
                                <?php endif; ?>
                                <div class="thumb-name"><?php echo htmlspecialchars($data['sertifikat']); ?></div>
                                <div class="thumb-size"><?php echo formatFileSize($file_size); ?></div>
                            </div>
                        </div>
                        <div style="margin-top:5px;">
                            <a href="<?php echo $sertifikat_path; ?>" target="_blank" class="file-link">
                                📄 Lihat Full
                            </a>
                        </div>
                    <?php else: ?>
                        <span class="no-file">📄 Tidak ada</span>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Foto Dokumentasi -->
            <div class="detail-row">
                <div class="detail-label">Foto Dokumentasi</div>
                <div class="detail-value">
                    <?php if (!empty($data['foto_dokumentasi'])): 
                        $foto_list = explode(',', $data['foto_dokumentasi']);
                    ?>
                        <div class="dokumen-thumbnail">
                            <?php foreach ($foto_list as $index => $foto): 
                                $foto = trim($foto);
                                if (!empty($foto)):
                                    $foto_path = "../uploads/foto/" . $foto;
                                    $file_size = file_exists($foto_path) ? filesize($foto_path) : 0;
                            ?>
                                <div class="thumb-item">
                                    <?php if (file_exists($foto_path)): ?>
                                        <img src="<?php echo $foto_path; ?>" alt="Foto <?php echo $index+1; ?>">
                                    <?php else: ?>
                                        <div class="thumb-icon">📸</div>
                                    <?php endif; ?>
                                    <div class="thumb-name"><?php echo htmlspecialchars($foto); ?></div>
                                    <div class="thumb-size"><?php echo formatFileSize($file_size); ?></div>
                                </div>
                            <?php 
                                endif;
                            endforeach; 
                            ?>
                        </div>
                        <div style="margin-top:5px;">
                            <a href="../uploads/foto/<?php echo $data['foto_dokumentasi']; ?>" target="_blank" class="file-link">
                                📸 Lihat Semua Foto
                            </a>
                        </div>
                    <?php else: ?>
                        <span class="no-file">📸 Tidak ada</span>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Surat Tugas -->
            <?php if (!empty($data['surat_tugas'])): 
                $surat_path = "../uploads/surat_tugas/" . $data['surat_tugas'];
                $surat_ext = strtolower(pathinfo($data['surat_tugas'], PATHINFO_EXTENSION));
                $is_surat_image = in_array($surat_ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                $file_size = file_exists($surat_path) ? filesize($surat_path) : 0;
            ?>
            <div class="detail-row">
                <div class="detail-label">Surat Tugas</div>
                <div class="detail-value">
                    <div class="dokumen-thumbnail">
                        <div class="thumb-item">
                            <?php if ($is_surat_image && file_exists($surat_path)): ?>
                                <img src="<?php echo $surat_path; ?>" alt="Surat Tugas">
                            <?php else: ?>
                                <div class="thumb-icon">📑</div>
                            <?php endif; ?>
                            <div class="thumb-name"><?php echo htmlspecialchars($data['surat_tugas']); ?></div>
                            <div class="thumb-size"><?php echo formatFileSize($file_size); ?></div>
                        </div>
                    </div>
                    <div style="margin-top:5px;">
                        <a href="<?php echo $surat_path; ?>" target="_blank" class="file-link">
                            📑 Lihat Full
                        </a>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Status Pengajuan -->
        <div class="detail-section">
            <div class="section-title">📊 Status Pengajuan</div>
            <div class="detail-row">
                <div class="detail-label">Status</div>
                <div class="detail-value">
                    <span class="status-badge <?php echo $status_class; ?>">
                        <?php echo $status_icon; ?> <?php echo $status_text; ?>
                    </span>
                </div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Tanggal Pengajuan</div>
                <div class="detail-value">: <?php echo date('d/m/Y H:i', strtotime($data['created_at'])); ?></div>
            </div>
            <?php if ($data['status'] == 'disetujui' && !empty($data['tanggal_disetujui'])): ?>
            <div class="detail-row">
                <div class="detail-label">Tanggal Disetujui</div>
                <div class="detail-value">: <?php echo date('d/m/Y H:i', strtotime($data['tanggal_disetujui'])); ?></div>
            </div>
            <?php endif; ?>

            <!-- CATATAN ADMIN (TANPA REVISI) -->
            <?php if (!empty($catatan)): ?>
            <div class="detail-row">
                <div class="detail-label">Catatan Admin</div>
                <div class="detail-value">: <?php echo nl2br(htmlspecialchars($catatan)); ?></div>
            </div>
            <?php endif; ?>
        </div>

        <!-- TANDA TANGAN -->
        <div style="margin-top: 30px; display: flex; justify-content: space-between; padding: 0 10px; flex-wrap: wrap;">
            <div style="text-align: center;">
                <p style="font-size: 11px; color: #888;">Dicetak pada: <?php echo date('d/m/Y H:i'); ?></p>
            </div>
            <div style="text-align: center;">
                <p style="font-size: 11px; color: #888;">Mengetahui,</p>
                <div style="height: 40px;"></div>
                <p style="font-size: 11px; font-weight: 600;">Admin SMB</p>
            </div>
            <div style="text-align: center;">
                <p style="font-size: 11px; color: #888;">Mahasiswa,</p>
                <div style="height: 40px;"></div>
                <p style="font-size: 11px; font-weight: 600;"><?php echo htmlspecialchars($data['nama_ketua'] ?? $user_data2['nama_lengkap'] ?? '-'); ?></p>
            </div>
        </div>

        <!-- Tombol Kembali -->
        <div style="text-align: center;" class="no-print">
            <a href="riwayat.php" class="btn-back-bottom">
                <i class="fas fa-arrow-left"></i> Kembali ke Riwayat
            </a>
        </div>
    </div>
</div>

<!-- STYLE KHUSUS PRINT -->
<style>
    @media print {
        .print-only {
            display: block !important;
        }
        .no-print {
            display: none !important;
        }
        .detail-row {
            margin-bottom: 6px !important;
        }
        .detail-label {
            width: 160px !important;
        }
        .file-link, .no-file {
            border: 1px solid #ddd !important;
            background: none !important;
            color: #1a1a1a !important;
        }
        .status-badge {
            border: 1px solid #ddd !important;
        }
        .dokumen-thumbnail .thumb-item {
            border: 1px solid #ccc !important;
            width: 100px !important;
        }
        .dokumen-thumbnail .thumb-item img {
            height: 70px !important;
        }
        .dokumen-thumbnail .thumb-item .thumb-name {
            font-size: 9px !important;
        }
        .dokumen-thumbnail .thumb-item .thumb-icon {
            height: 70px !important;
            font-size: 28px !important;
        }
        body {
            background: white !important;
        }
        .page-title {
            display: none !important;
        }
        .page-title h1, .page-title p {
            display: none !important;
        }
        .top-bar .page-title {
            display: none !important;
        }
    }
</style>
<script src="../assets/js/burger-menu.js"></script>
</body>
</html>