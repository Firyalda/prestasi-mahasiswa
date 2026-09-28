<?php
session_start();

include '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'mahasiswa') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$query_user = "SELECT id, nama_lengkap, nim FROM users WHERE id = $user_id";
$result_user = mysqli_query($conn, $query_user);
$user = mysqli_fetch_assoc($result_user);

if (!$user) {
    die("Data user tidak ditemukan!");
}

// =========================================================
// AMBIL PERIODE
// =========================================================
$query_periode = "SELECT * FROM pengaturan_periode LIMIT 1";
$result_periode = mysqli_query($conn, $query_periode);
$periode = mysqli_fetch_assoc($result_periode);

$sisa_hari = 0;
$status_warning = '';
$periode_aktif = false;
$periode_ada = false;
$tanggal_mulai = null;
$tanggal_selesai = null;
$sekarang = new DateTime();

if ($periode) {
    $periode_ada = true;
    $tanggal_mulai = new DateTime($periode['periode_mulai']);
    $tanggal_selesai = new DateTime($periode['periode_selesai']);
    
    $tanggal_mulai->setTime(0, 0, 0);
    $tanggal_selesai->setTime(0, 0, 0);
    $sekarang->setTime(0, 0, 0);
    
    if ($periode['is_active'] == 1 && $sekarang >= $tanggal_mulai && $sekarang <= $tanggal_selesai) {
        $periode_aktif = true;
        $sisa_hari = $sekarang->diff($tanggal_selesai)->days;
        
        if ($sisa_hari <= 7) {
            $status_warning = 'SEGERA BERAKHIR';
        } else {
            $status_warning = 'Masih Tersedia';
        }
    } elseif ($periode['is_active'] == 0) {
        $status_warning = 'Periode Dinonaktifkan';
    } elseif ($sekarang > $tanggal_selesai) {
        $status_warning = 'Periode Telah Berakhir';
    } elseif ($sekarang < $tanggal_mulai) {
        $status_warning = 'Periode Belum Dimulai';
    }
}

include 'includes/sidebar.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifikasi Deadline</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; 
            background: #f1f4f8; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            min-height: 100vh; 
            padding: 24px;
        }
        .card { 
            max-width: 520px; 
            width: 100%;
            background: #ffffff; 
            border-radius: 18px; 
            box-shadow: 0 4px 24px rgba(0,0,0,0.06); 
            padding: 32px 36px;
            border: 1px solid #eef2f6;
        }

        /* === HEADER === */
        .card-header {
            text-align: center;
            margin-bottom: 24px;
            padding-bottom: 18px;
            border-bottom: 1px solid #edf2f7;
        }
        .card-header .icon {
            font-size: 32px;
            display: block;
            margin-bottom: 4px;
        }
        .card-header h1 {
            font-size: 20px;
            font-weight: 600;
            color: #1a202c;
            letter-spacing: -0.3px;
        }
        .card-header p {
            font-size: 13px;
            color: #a0aec0;
            margin-top: 2px;
        }

        /* === GREETING === */
        .greeting {
            font-size: 15px;
            color: #2d3748;
            line-height: 1.6;
            margin-bottom: 18px;
        }
        .greeting strong {
            color: #1a202c;
            font-weight: 600;
        }
        .greeting .nim {
            display: block;
            font-size: 13px;
            color: #a0aec0;
            font-weight: 400;
            margin-top: 1px;
        }

        /* === PERIODE BOX === */
        .periode-box {
            background: #f7fafc;
            padding: 14px 18px;
            border-radius: 12px;
            border: 1px solid #edf2f7;
            margin-bottom: 18px;
        }
        .periode-box .label {
            font-size: 12px;
            font-weight: 600;
            color: #718096;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            display: block;
            margin-bottom: 3px;
        }
        .periode-box .date {
            font-size: 15px;
            font-weight: 600;
            color: #1a202c;
        }
        .periode-box .detail {
            display: flex;
            flex-wrap: wrap;
            gap: 4px 20px;
            margin-top: 6px;
            font-size: 13px;
            color: #4a5568;
        }
        .periode-box .detail strong {
            color: #1a202c;
            font-weight: 600;
        }

        /* === STATUS === */
        .status-box {
            padding: 14px 18px;
            border-radius: 12px;
            margin-bottom: 18px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
            border: 1px solid;
        }
        .status-box.deadline {
            background: #fffbeb;
            border-color: #f6c23e;
        }
        .status-box.deadline .status-text {
            color: #92400e;
        }
        .status-box.active {
            background: #f0fff4;
            border-color: #9ae6b4;
        }
        .status-box.active .status-text {
            color: #276749;
        }
        .status-box.inactive {
            background: #fffaf0;
            border-color: #fbd38d;
        }
        .status-box.inactive .status-text {
            color: #9c4221;
        }
        .status-box.ended {
            background: #f7fafc;
            border-color: #e2e8f0;
        }
        .status-box.ended .status-text {
            color: #4a5568;
        }

        .status-box .status-text {
            font-weight: 600;
            font-size: 15px;
        }
        .status-box .status-days {
            font-size: 20px;
            font-weight: 700;
            background: #ffffff;
            padding: 2px 16px;
            border-radius: 40px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.04);
            color: inherit;
        }
        .status-box .status-days small {
            font-size: 12px;
            font-weight: 400;
            color: #a0aec0;
        }

        /* === MESSAGE === */
        .message {
            background: #f7fafc;
            padding: 14px 18px;
            border-radius: 12px;
            border-left: 4px solid #f6c23e;
            font-size: 14px;
            line-height: 1.7;
            color: #2d3748;
            margin-bottom: 20px;
        }
        .message strong {
            color: #92400e;
        }

        /* === BUTTON === */
        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: center;
            margin-bottom: 16px;
        }
        .btn {
            display: inline-block;
            padding: 10px 28px;
            border-radius: 40px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-primary {
            background: #f6c23e;
            color: #1a202c;
            box-shadow: 0 4px 12px rgba(246, 194, 62, 0.25);
        }
        .btn-primary:hover {
            background: #e2b03a;
            transform: translateY(-1px);
        }
        .btn-secondary {
            background: transparent;
            color: #4a5568;
            border: 1.5px solid #e2e8f0;
        }
        .btn-secondary:hover {
            background: #f7fafc;
        }
        .btn-disabled {
            display: inline-block;
            padding: 10px 28px;
            border-radius: 40px;
            font-size: 14px;
            font-weight: 600;
            background: #edf2f7;
            color: #a0aec0;
            cursor: not-allowed;
            border: none;
        }

        /* === FOOTER NOTE === */
        .note {
            background: #f7fafc;
            padding: 10px 16px;
            border-radius: 10px;
            text-align: center;
            font-size: 12px;
            color: #718096;
            border-left: 3px solid #cbd5e0;
            margin-top: 4px;
        }
        .note.warning {
            background: #fffbeb;
            color: #92400e;
            border-left-color: #f6c23e;
        }

        /* === FOOTER === */
        .card-footer {
            margin-top: 24px;
            padding-top: 16px;
            border-top: 1px solid #edf2f7;
            text-align: center;
            font-size: 12px;
            color: #a0aec0;
            line-height: 1.8;
        }
        .card-footer strong {
            color: #1a202c;
            font-weight: 600;
        }

        @media (max-width: 480px) {
            .card { padding: 24px 20px; }
            .status-box { flex-direction: column; text-align: center; }
            .status-box .status-days { font-size: 18px; }
            .btn { padding: 8px 20px; font-size: 13px; }
        }
    </style>
    <link rel="stylesheet" href="../assets/css/burger-menu.css">
</head>
<body>

<div class="card">

    <!-- HEADER -->
    <div class="card-header">
        <span class="icon">📬</span>
        <h1>Notifikasi</h1>
        <p>Pengingat Deadline Pengajuan</p>
    </div>

    <!-- GREETING -->
    <div class="greeting">
        Kepada Yth. <strong><?php echo htmlspecialchars($user['nama_lengkap'] ?? 'Mahasiswa'); ?></strong>
        <span class="nim">NIM: <?php echo htmlspecialchars($user['nim'] ?? '-'); ?></span>
    </div>

    <?php if ($periode_ada): ?>

        <!-- PERIODE -->
        <div class="periode-box">
            <span class="label">Periode</span>
            <div class="date">
                <?php echo date('d F Y', strtotime($periode['periode_mulai'])); ?> — 
                <?php echo date('d F Y', strtotime($periode['periode_selesai'])); ?>
            </div>
            <div class="detail">
                <span>Tahun: <strong><?php echo $periode['tahun_akademik']; ?></strong></span>
                <?php if ($periode_aktif): ?>
                    <span>Sisa: <strong><?php echo $sisa_hari; ?> hari</strong></span>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($periode_aktif): ?>

            <!-- STATUS -->
            <?php 
            $status_class = ($sisa_hari <= 7) ? 'deadline' : 'active';
            $status_label = ($sisa_hari <= 7) ? 'SEGERA BERAKHIR' : 'Masih Tersedia';
            ?>
            <div class="status-box <?php echo $status_class; ?>">
                <span class="status-text"><?php echo $status_label; ?></span>
                <span class="status-days"><?php echo $sisa_hari; ?> <small>hari</small></span>
            </div>

            <!-- MESSAGE -->
            <div class="message">
                Periode pengajuan akan berakhir dalam <?php echo $sisa_hari; ?> hari. Segera ajukan prestasi Anda!
                <br><strong>Dokumen yang diajukan setelah periode berakhir tidak akan diproses.</strong>
            </div>

            <!-- BUTTON -->
            <div class="actions">
                <a href="pengajuan.php" class="btn btn-primary">Ajukan Sekarang</a>
                <a href="dashboard.php" class="btn btn-secondary">Kembali</a>
            </div>

        <?php elseif ($periode['is_active'] == 0): ?>

            <div class="status-box inactive">
                <span class="status-text">⛔ Periode Dinonaktifkan</span>
            </div>
            <div class="note warning">
                ⚠️ Periode pengajuan sedang dinonaktifkan oleh admin. Anda tidak dapat mengajukan prestasi saat ini.
            </div>
            <div class="actions">
                <span class="btn-disabled">Tidak Dapat Mengajukan</span>
                <a href="dashboard.php" class="btn btn-secondary">Kembali</a>
            </div>

        <?php elseif ($sekarang > $tanggal_selesai): ?>

            <div class="status-box ended">
                <span class="status-text">⛔ Periode Telah Berakhir</span>
            </div>
            <div class="note warning">
                ⛔ Periode pengajuan telah berakhir pada <?php echo date('d F Y', strtotime($periode['periode_selesai'])); ?>.
            </div>
            <div class="actions">
                <span class="btn-disabled">Tidak Dapat Mengajukan</span>
                <a href="dashboard.php" class="btn btn-secondary">Kembali</a>
            </div>

        <?php elseif ($sekarang < $tanggal_mulai): ?>

            <div class="status-box inactive">
                <span class="status-text">⏳ Periode Belum Dimulai</span>
            </div>
            <div class="note warning">
                ⏳ Periode pengajuan akan dibuka pada <?php echo date('d F Y', strtotime($periode['periode_mulai'])); ?>.
            </div>
            <div class="actions">
                <span class="btn-disabled">Belum Dibuka</span>
                <a href="dashboard.php" class="btn btn-secondary">Kembali</a>
            </div>

        <?php endif; ?>

    <?php else: ?>

        <div class="status-box inactive">
            <span class="status-text">⚠️ Belum Ada Periode</span>
        </div>
        <div class="note warning">
            ⚠️ Belum ada pengaturan periode. Silakan hubungi admin.
        </div>
        <div class="actions">
            <a href="dashboard.php" class="btn btn-secondary">Kembali</a>
        </div>

    <?php endif; ?>

    <!-- FOOTER NOTE -->
    <div class="note">
        ⚠️ Pastikan semua dokumen pendukung sudah lengkap sebelum mengajukan.
    </div>

    <!-- FOOTER -->
    <div class="card-footer">
        Hormat kami,<br>
        <strong>Tim Kemahasiswaan</strong><br>
        Universitas Harkat Negeri<br>
        <?php echo date('d F Y'); ?>
    </div>

</div>
<script src="../assets/js/burger-menu.js"></script>
</body>
</html>