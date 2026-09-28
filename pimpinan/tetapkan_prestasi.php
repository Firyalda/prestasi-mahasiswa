<?php
session_start();

// Cek login dan role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'superadmin') {
    header("Location: ../login.php");
    exit();
}

require_once '../config/database.php';

$nama = $_SESSION['nama'] ?? 'Pimpinan';

// Ambil ID dari URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id == 0) {
    header("Location: ranking_kandidat.php");
    exit();
}

// =========================================================
// FUNGSI HITUNG NILAI AKHIR
// =========================================================
function hitungNilaiAkhir($row) {
    if (!empty($row['nilai_akhir'])) {
        return $row['nilai_akhir'];
    }
    
    $bobot_level = [
        'Lokal' => 1, 
        'Provinsi' => 2, 
        'Nasional' => 3, 
        'Internasional' => 4
    ];
    
    $bobot_raihan = [
        'Juara 1' => 4,
        'Juara 2' => 3,
        'Juara 3' => 2,
        'Finalis' => 1,
        'Silver Medal' => 3,
        'Gold Medal' => 4,
        'Bronze Medal' => 2,
        'Peserta Aktif' => 1,
        'Juara Harapan' => 2,
    ];
    
    $level = $row['level_kompetisi'] ?? 'Lokal';
    $raihan = $row['raihan_prestasi'] ?? 'Juara 3';
    $bobot_raihan_value = $bobot_raihan[$raihan] ?? 2;
    
    $skor = ($bobot_level[$level] * 15) + ($bobot_raihan_value * 10);
    return min($skor, 100);
}

// Ambil data mahasiswa
$query = "SELECT p.*, u.nama_lengkap, u.nim, u.prodi, u.fakultas,
          kr.nominal as reward_nominal
          FROM pengajuan p 
          JOIN users u ON p.user_id = u.id 
          LEFT JOIN kategori_reward kr ON (
              kr.tingkat = p.level_kompetisi AND 
              kr.kategori = p.jenis_peserta AND
              kr.raihan = p.raihan_prestasi
          )
          WHERE p.id = $id AND p.status = 'disetujui'";
$result = mysqli_query($conn, $query);
$data = mysqli_fetch_assoc($result);

if (!$data) {
    header("Location: ranking_kandidat.php");
    exit();
}

$reward = $data['reward_nominal'] ?? 0;
$reward_formatted = number_format($reward, 0, ',', '.');
$nilai_akhir = hitungNilaiAkhir($data);

// Proses tetapkan
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tetapkan'])) {
    // Cek apakah sudah ditetapkan sebelumnya
    if ($data['penetapan'] == 1) {
        $message = '⚠️ Mahasiswa ini sudah ditetapkan sebelumnya.';
        $message_type = 'error';
    } else {
        $catatan = "Pimpinan menetapkan mahasiswa ini sebagai Mahasiswa Berprestasi periode " . date('Y') . ".";
        
        $query_update = "UPDATE pengajuan SET 
                            penetapan = 1,
                            admin_catatan = CONCAT(admin_catatan, '\nPimpinan: ', '$catatan')
                         WHERE id = $id";
        
        if (mysqli_query($conn, $query_update)) {
            // Kirim notifikasi ke mahasiswa
            $user_id = $data['user_id'];
            $judul = "Selamat! Anda Ditetapkan sebagai Mahasiswa Berprestasi";
            $pesan = "Selamat! Anda telah ditetapkan sebagai Mahasiswa Berprestasi periode " . date('Y') . ".\n\n" .
                     "Nama: " . $data['nama_lengkap'] . "\n" .
                     "NIM: " . $data['nim'] . "\n" .
                     "Prestasi: " . $data['nama_kejuaraan'] . "\n" .
                     "Reward: Rp " . $reward_formatted;
            $link = "../mahasiswa/detail_pengajuan.php?id=" . $id;
            
            $query_notif = "INSERT INTO notifikasi (user_id, role, judul, pesan, link) 
                            VALUES ('$user_id', 'mahasiswa', '$judul', '$pesan', '$link')";
            mysqli_query($conn, $query_notif);
            
            $message = '✅ Mahasiswa berhasil ditetapkan! Notifikasi telah dikirim.';
            $message_type = 'success';
            echo "<script>
                setTimeout(function() {
                    window.location.href = 'ranking_kandidat.php';
                }, 1500);
            </script>";
        } else {
            $message = '❌ Gagal menetapkan mahasiswa: ' . mysqli_error($conn);
            $message_type = 'error';
        }
    }
}

include 'includes/sidebar.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tetapkan Mahasiswa Berprestasi - SMB</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #f1f4f8; display: flex; min-height: 100vh; }

        .main-content { margin-left: 250px; padding: 28px 32px; width: 100%; min-height: 100vh; }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: white;
            padding: 14px 24px;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            margin-bottom: 24px;
        }
        .header h1 { font-size: 20px; font-weight: 600; color: #0f172a; }
        .header .user { display: flex; align-items: center; gap: 14px; font-size: 14px; color: #475569; }
        .header .user .avatar {
            width: 38px; height: 38px; background: #8b5cf6; border-radius: 50%;
            display: flex; align-items: center; justify-content: center; font-weight: 600; color: white; font-size: 16px;
        }

        .card { background: white; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); padding: 20px 24px; margin-bottom: 20px; }

        .alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; font-size: 14px; display: flex; align-items: center; gap: 10px; }
        .alert-success { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
        .alert-error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .alert-warning { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }

        .detail-grid {
            display: grid;
            grid-template-columns: 140px 1fr;
            gap: 6px 16px;
            margin-bottom: 14px;
        }
        .detail-grid .label {
            font-weight: 500;
            color: #475569;
            padding: 4px 0;
        }
        .detail-grid .value {
            color: #0f172a;
            padding: 4px 0;
            border-bottom: 1px dashed #eef2f6;
        }
        .detail-grid .value strong { color: #0f172a; }

        .reward-box {
            background: #f0fdf4;
            border: 1px solid #86efac;
            border-radius: 8px;
            padding: 10px 16px;
            margin: 10px 0 14px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }
        .reward-box .label { font-weight: 500; color: #065f46; font-size: 14px; }
        .reward-box .amount { font-size: 18px; font-weight: 700; color: #065f46; }

        .catatan-otomatis {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 14px;
            font-size: 13px;
            color: #475569;
            line-height: 1.7;
        }
        .catatan-otomatis .label {
            font-weight: 600;
            color: #0f172a;
            display: block;
            margin-bottom: 3px;
            font-size: 13px;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .status-badge.success { background: #d1fae5; color: #065f46; }
        .status-badge.warning { background: #fef3c7; color: #92400e; }

        .action-buttons {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 6px;
        }
        .btn {
            padding: 10px 28px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            border: none;
            cursor: pointer;
            transition: 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .btn-primary { background: #10b981; color: white; }
        .btn-primary:hover { background: #059669; }
        .btn-secondary { background: #e2e8f0; color: #475569; }
        .btn-secondary:hover { background: #cbd5e1; }
        .btn-disabled { background: #d1d5db; color: #6b7280; cursor: not-allowed; }
        .btn-disabled:hover { transform: none; box-shadow: none; }

        @media (max-width: 768px) {
            .main-content { margin-left: 70px; padding: 16px; }
            .detail-grid { grid-template-columns: 1fr; gap: 2px; }
            .detail-grid .value { border-bottom: none; }
            .action-buttons { flex-direction: column; }
            .btn { justify-content: center; }
        }
    </style>
</head>
<body>

<div class="main-content">

    <div class="header">
        <h1>📝 Tetapkan Mahasiswa Berprestasi</h1>
        <div class="user">
            <span><?php echo date('l, d F Y'); ?></span>
            <div class="avatar"><?php echo strtoupper(substr($nama, 0, 1)); ?></div>
            <span><?php echo htmlspecialchars($nama); ?></span>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?php echo $message_type; ?>">
            <i class="fas <?php echo $message_type == 'success' ? 'fa-check-circle' : ($message_type == 'warning' ? 'fa-exclamation-triangle' : 'fa-exclamation-circle'); ?>"></i>
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <form method="POST">

            <!-- INFORMASI MAHASISWA -->
            <div class="detail-grid">
                <div class="label">Nama Lengkap</div>
                <div class="value"><strong><?php echo htmlspecialchars($data['nama_lengkap']); ?></strong></div>

                <div class="label">NIM</div>
                <div class="value"><?php echo htmlspecialchars($data['nim']); ?></div>

                <div class="label">Program Studi</div>
                <div class="value"><?php echo htmlspecialchars($data['prodi'] ?? 'Teknik Informatika'); ?></div>

                <div class="label">Fakultas</div>
                <div class="value"><?php echo htmlspecialchars($data['fakultas'] ?? '-'); ?></div>

                <div class="label">Nama Kejuaraan</div>
                <div class="value"><strong><?php echo htmlspecialchars($data['nama_kejuaraan']); ?></strong></div>

                <div class="label">Level Kompetisi</div>
                <div class="value"><?php echo htmlspecialchars($data['level_kompetisi']); ?></div>

                <div class="label">Kategori Prestasi</div>
                <div class="value"><?php echo htmlspecialchars($data['kategori_prestasi'] ?? '-'); ?></div>

                <div class="label">Raihan Prestasi</div>
                <div class="value"><strong><?php echo htmlspecialchars($data['raihan_prestasi']); ?></strong></div>

                <div class="label">Nilai Akhir</div>
                <div class="value"><strong><?php echo $nilai_akhir; ?>/100</strong></div>

                <div class="label">Reward</div>
                <div class="value"><strong style="color: #10b981;">Rp <?php echo $reward_formatted; ?></strong></div>

                <div class="label">Status Penetapan</div>
                <div class="value">
                    <?php if ($data['penetapan'] == 1): ?>
                        <span class="status-badge success">✅ Sudah Ditetapkan</span>
                    <?php else: ?>
                        <span class="status-badge warning">⏳ Belum Ditetapkan</span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="reward-box">
                <span class="label">💰 Mahasiswa Ini Berhak Ditetapkan Sebagai</span>
                <span class="amount">🏆 Mahasiswa Berprestasi</span>
            </div>

            <div class="catatan-otomatis">
                <span class="label">📝 Catatan Sistem (Otomatis)</span>
                <p>
                    Mahasiswa ini telah melalui proses verifikasi dan dinyatakan layak menerima 
                    predikat <strong>Mahasiswa Berprestasi</strong> periode <?php echo date('Y'); ?> 
                    dengan reward <strong>Rp <?php echo $reward_formatted; ?></strong>.
                </p>
                <p style="margin-top: 6px; font-size: 12px; color: #94a3b8;">
                    <i class="fas fa-info-circle"></i> Catatan ini akan tersimpan di sistem dan dikirim ke mahasiswa.
                </p>
            </div>

            <div class="action-buttons">
                <?php if ($data['penetapan'] == 1): ?>
                    <button type="button" class="btn btn-disabled" disabled>
                        <i class="fas fa-check-circle"></i> Sudah Ditetapkan
                    </button>
                <?php else: ?>
                    <button type="submit" name="tetapkan" class="btn btn-primary" onclick="return confirm('Yakin ingin menetapkan <?php echo htmlspecialchars($data['nama_lengkap']); ?> sebagai Mahasiswa Berprestasi?')">
                        <i class="fas fa-check"></i> Tetapkan
                    </button>
                <?php endif; ?>
                <a href="ranking_kandidat.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali ke Ranking
                </a>
            </div>

        </form>
    </div>

</div>

</body>
</html>