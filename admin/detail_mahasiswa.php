<?php
// detail_mahasiswa.php - Halaman detail mahasiswa
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once '../config/database.php';

// Ambil ID dari URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id == 0) {
    header("Location: data_mahasiswa.php");
    exit();
}

// Query dari database
$query = "SELECT * FROM users WHERE id = $id AND role = 'mahasiswa'";
$result = mysqli_query($conn, $query);
$mahasiswa = mysqli_fetch_assoc($result);

if (!$mahasiswa) {
    echo "<div style='text-align:center;padding:50px;font-family:Arial;'>";
    echo "<h2 style='color:#c62828;'>❌ Data Mahasiswa Tidak Ditemukan</h2>";
    echo "<br><a href='data_mahasiswa.php' style='color:#2c7da0;text-decoration:none;font-weight:600;'>← Kembali ke Daftar</a>";
    echo "</div>";
    exit();
}

// Hitung jumlah pengajuan
$cek_pengajuan = mysqli_query($conn, "SELECT COUNT(*) as jml, SUM(CASE WHEN status='disetujui' THEN 1 ELSE 0 END) as disetujui FROM pengajuan WHERE user_id = " . $mahasiswa['id']);
$data_pengajuan = mysqli_fetch_assoc($cek_pengajuan);
$jml_pengajuan = $data_pengajuan['jml'];
$jml_disetujui = $data_pengajuan['disetujui'];

$nama = ucwords($mahasiswa['nama_lengkap']);
$inisial = strtoupper(substr($mahasiswa['nama_lengkap'], 0, 1));
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Mahasiswa - SMB</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #eef1f5;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px 20px;
        }

        /* ===== CARD ===== */
        .card {
            max-width: 580px;
            width: 100%;
            background: #ffffff;
            border-radius: 24px;
            box-shadow: 0 8px 40px rgba(0, 0, 0, 0.06), 0 2px 12px rgba(0, 0, 0, 0.04);
            overflow: hidden;
            transition: all 0.2s ease;
        }

        /* ===== HEADER ===== */
        .card-header {
            padding: 22px 32px 18px 32px;
            border-bottom: 1px solid #f0f2f5;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .card-header .left {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .card-header .left .btn-back {
            color: #6b7a8f;
            font-size: 17px;
            text-decoration: none;
            padding: 6px 10px;
            border-radius: 10px;
            transition: 0.2s;
        }

        .card-header .left .btn-back:hover {
            background: #f0f2f5;
            color: #1a2634;
        }

        .card-header h1 {
            font-size: 19px;
            font-weight: 600;
            color: #1a2634;
            letter-spacing: -0.2px;
        }

        .card-header .badge-smb {
            font-size: 11px;
            font-weight: 600;
            color: #6b7a8f;
            background: #f0f2f5;
            padding: 4px 16px;
            border-radius: 100px;
            letter-spacing: 0.8px;
        }

        /* ===== BODY ===== */
        .card-body {
            padding: 28px 32px 12px 32px;
        }

        /* -- PROFIL -- */
        .profile-wrap {
            display: flex;
            align-items: center;
            gap: 22px;
            padding-bottom: 24px;
            margin-bottom: 24px;
            border-bottom: 1px solid #f0f2f5;
        }

        .avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: #eef2f7;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 34px;
            font-weight: 600;
            color: #2c3e50;
            flex-shrink: 0;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        }

        .avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-meta {
            flex: 1;
            min-width: 0;
        }

        .profile-meta .name {
            font-size: 21px;
            font-weight: 600;
            color: #1a2634;
            letter-spacing: -0.2px;
            margin-bottom: 2px;
        }

        .profile-meta .nim {
            font-size: 14px;
            color: #6b7a8f;
            font-weight: 500;
            letter-spacing: 0.2px;
        }

        .profile-meta .status-label {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-top: 8px;
            font-size: 13px;
            font-weight: 500;
            padding: 4px 18px 4px 12px;
            border-radius: 100px;
            background: #eafaf1;
            color: #1a7a4a;
            letter-spacing: 0.2px;
        }

        .profile-meta .status-label.belum {
            background: #fef6e7;
            color: #b7791f;
        }

        .profile-meta .status-label i {
            font-size: 12px;
        }

        /* -- INFO -- */
        .info-grid {
            display: flex;
            flex-direction: column;
            gap: 0;
        }

        .info-row {
            display: flex;
            align-items: center;
            padding: 11px 0;
            border-bottom: 1px solid #f5f6f8;
        }

        .info-row:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .info-label {
            width: 120px;
            flex-shrink: 0;
            font-size: 13px;
            font-weight: 500;
            color: #8a9aa8;
            letter-spacing: 0.4px;
        }

        .info-value {
            flex: 1;
            font-size: 14.5px;
            font-weight: 500;
            color: #1a2634;
            word-break: break-word;
            letter-spacing: 0.15px;
        }

        .info-value .highlight {
            color: #1a2634;
            font-weight: 600;
        }

        /* -- PENGAJUAN -- */
        .pengajuan-wrap {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: #f0f4fe;
            padding: 4px 18px 4px 12px;
            border-radius: 100px;
            font-size: 13px;
            font-weight: 600;
            color: #1a4f8a;
            letter-spacing: 0.2px;
        }

        .pengajuan-wrap .num {
            background: #1a4f8a;
            color: #fff;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: 700;
        }

        .pengajuan-detail {
            font-size: 13px;
            color: #6b7a8f;
            margin-left: 8px;
            letter-spacing: 0.2px;
        }

        .pengajuan-detail .green {
            color: #1a7a4a;
            font-weight: 500;
        }

        /* ===== FOOTER ===== */
        .card-footer {
            padding: 18px 32px 28px 32px;
            border-top: 1px solid #f0f2f5;
            display: flex;
            justify-content: flex-start;
        }

        .btn-back-footer {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: #f0f2f5;
            color: #3d4e5f;
            padding: 10px 26px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s ease;
            border: none;
            cursor: pointer;
            letter-spacing: 0.2px;
        }

        .btn-back-footer:hover {
            background: #e5e8ed;
            color: #1a2634;
            transform: translateY(-1px);
        }

        .btn-back-footer i {
            font-size: 14px;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 480px) {
            .card-body {
                padding: 20px 20px 6px 20px;
            }

            .card-header {
                padding: 18px 20px 14px 20px;
                flex-wrap: wrap;
                gap: 6px;
            }

            .card-header h1 {
                font-size: 16px;
            }

            .profile-wrap {
                flex-direction: column;
                text-align: center;
                gap: 12px;
            }

            .profile-meta .name {
                font-size: 19px;
            }

            .info-row {
                flex-direction: column;
                align-items: flex-start;
                padding: 8px 0;
                gap: 2px;
            }

            .info-label {
                width: 100%;
                font-size: 12px;
                color: #8a9aa8;
                letter-spacing: 0.3px;
            }

            .info-value {
                font-size: 14px;
                width: 100%;
            }

            .card-footer {
                padding: 14px 20px 22px 20px;
                justify-content: center;
            }

            .btn-back-footer {
                width: 100%;
                justify-content: center;
            }
        }

        @media print {
            body {
                background: #fff;
                padding: 20px;
            }
            .card {
                box-shadow: none;
                border: 1px solid #e5e8ed;
                border-radius: 12px;
                max-width: 100%;
            }
            .btn-back-footer {
                display: none;
            }
            .card-header .btn-back {
                display: none;
            }
            .card-header {
                border-bottom: 1px solid #e5e8ed;
            }
            .avatar {
                background: #eef2f7 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .status-label {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .pengajuan-wrap {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .pengajuan-wrap .num {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>

<div class="card">

    <!-- ===== HEADER ===== -->
    <div class="card-header">
        <div class="left">
            <a href="data_mahasiswa.php" class="btn-back" title="Kembali">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1>Detail Mahasiswa</h1>
        </div>
        <span class="badge-smb">SMB</span>
    </div>

    <!-- ===== BODY ===== -->
    <div class="card-body">

        <!-- PROFIL -->
        <div class="profile-wrap">
            <div class="avatar">
                <?php if (!empty($mahasiswa['foto_profil']) && file_exists("../uploads/profil/" . $mahasiswa['foto_profil'])): ?>
                    <img src="../uploads/profil/<?php echo $mahasiswa['foto_profil']; ?>" alt="Foto">
                <?php else: ?>
                    <?= $inisial ?>
                <?php endif; ?>
            </div>
            <div class="profile-meta">
                <div class="name"><?= $nama ?></div>
                <div class="nim">NIM. <?= htmlspecialchars($mahasiswa['nim'] ?? '-') ?></div>
                <div class="status-label <?= $jml_pengajuan > 0 ? '' : 'belum' ?>">
                    <i class="fas <?= $jml_pengajuan > 0 ? 'fa-check-circle' : 'fa-clock' ?>"></i>
                    <?= $jml_pengajuan > 0 ? 'Sudah Mengajukan' : 'Belum Mengajukan' ?>
                </div>
            </div>
        </div>

        <!-- INFO -->
        <div class="info-grid">

            <div class="info-row">
                <span class="info-label">Program Studi</span>
                <span class="info-value"><?= htmlspecialchars($mahasiswa['prodi'] ?? '-') ?></span>
            </div>

            <div class="info-row">
                <span class="info-label">Email</span>
                <span class="info-value"><?= htmlspecialchars($mahasiswa['email'] ?? '-') ?></span>
            </div>

            <div class="info-row">
                <span class="info-label">Nomor HP</span>
                <span class="info-value"><?= htmlspecialchars($mahasiswa['no_hp'] ?? '-') ?></span>
            </div>

            <div class="info-row">
                <span class="info-label">Username</span>
                <span class="info-value"><?= htmlspecialchars($mahasiswa['username']) ?></span>
            </div>

            <div class="info-row">
                <span class="info-label">Tanggal Daftar</span>
                <span class="info-value"><?= date('d F Y', strtotime($mahasiswa['created_at'])) ?></span>
            </div>

            <div class="info-row" style="border-bottom: none; padding-bottom: 0;">
                <span class="info-label">Pengajuan</span>
                <span class="info-value">
                    <span class="pengajuan-wrap">
                        <span class="num"><?= $jml_pengajuan ?></span>
                        Total
                    </span>
                    <?php if ($jml_disetujui > 0): ?>
                        <span class="pengajuan-detail">
                            <span class="green">● <?= $jml_disetujui ?> disetujui</span>
                        </span>
                    <?php endif; ?>
                </span>
            </div>

        </div>

    </div>

    <!-- ===== FOOTER ===== -->
    <div class="card-footer">
        <a href="data_mahasiswa.php" class="btn-back-footer">
            <i class="fas fa-arrow-left"></i> Kembali ke Daftar
        </a>
    </div>

</div>

</body>
</html>