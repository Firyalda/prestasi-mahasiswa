<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/flash.php';

use App\Core\Session;

$session = new Session();

// Cek login dan role
if (!$session->has('user_id') || $session->get('role') !== 'superadmin') {
    header("Location: ../login.php");
    exit();
}

$nama = $session->get('nama', 'Pimpinan');

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

// =========================================================
// AMBIL DATA STATISTIK
// =========================================================

// Total Kandidat (mahasiswa yang lolos verifikasi / disetujui admin)
$query_kandidat = "SELECT COUNT(DISTINCT user_id) as total FROM pengajuan WHERE status = 'disetujui'";
$result_kandidat = mysqli_query($conn, $query_kandidat);
$total_kandidat = mysqli_fetch_assoc($result_kandidat)['total'] ?? 0;

// Total Pengajuan yang disetujui
$query_pengajuan = "SELECT COUNT(*) as total FROM pengajuan WHERE status = 'disetujui'";
$result_pengajuan = mysqli_query($conn, $query_pengajuan);
$total_pengajuan = mysqli_fetch_assoc($result_pengajuan)['total'] ?? 0;

// Total Menunggu Penetapan (sudah lolos tapi belum ditetapkan)
$query_penetapan = "SELECT COUNT(*) as total FROM pengajuan WHERE status = 'disetujui' AND penetapan = 0";
$result_penetapan = mysqli_query($conn, $query_penetapan);
$total_penetapan = mysqli_fetch_assoc($result_penetapan)['total'] ?? 0;

// Total Sudah Ditetapkan
$query_ditetapkan = "SELECT COUNT(*) as total FROM pengajuan WHERE status = 'disetujui' AND penetapan = 1";
$result_ditetapkan = mysqli_query($conn, $query_ditetapkan);
$total_ditetapkan = mysqli_fetch_assoc($result_ditetapkan)['total'] ?? 0;

// Ranking Top 5 (dengan skor)
$query_ranking = "SELECT p.*, u.nama_lengkap, u.nim, u.prodi 
                   FROM pengajuan p 
                   JOIN users u ON p.user_id = u.id 
                   WHERE p.status = 'disetujui' 
                   ORDER BY p.nilai_akhir DESC, p.reward_nominal DESC 
                   LIMIT 5";
$result_ranking = mysqli_query($conn, $query_ranking);

// =========================================================
// DAFTAR KANDIDAT (untuk ditetapkan)
// =========================================================
$query_kandidat_list = "SELECT p.*, u.nama_lengkap, u.nim, u.prodi 
                        FROM pengajuan p 
                        JOIN users u ON p.user_id = u.id 
                        WHERE p.status = 'disetujui' AND p.penetapan = 0
                        ORDER BY p.nilai_akhir DESC, p.reward_nominal DESC";
$result_kandidat_list = mysqli_query($conn, $query_kandidat_list);

include 'includes/sidebar.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Pimpinan - SMB</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <?php render_flash_script(); ?>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #f1f5f9; display: flex; min-height: 100vh; }

        .main-content { margin-left: 250px; padding: 30px 35px; width: 100%; min-height: 100vh; }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: white;
            padding: 16px 24px;
            border-radius: 14px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
            margin-bottom: 28px;
        }
        .header h1 { font-size: 20px; font-weight: 700; color: #0f172a; }
        .header .user { display: flex; align-items: center; gap: 14px; }
        .header .user .avatar {
            width: 40px; height: 40px; background: #8b5cf6; border-radius: 50%;
            display: flex; align-items: center; justify-content: center; font-weight: 700; color: white;
        }
        .header .user .date { font-size: 13px; color: #94a3b8; }

        .card { background: white; border-radius: 14px; box-shadow: 0 1px 3px rgba(0,0,0,0.06); padding: 20px 24px; margin-bottom: 24px; }
        .card h3 { font-size: 16px; font-weight: 600; color: #0f172a; margin-bottom: 14px; }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-top: 10px;
        }
        .stat-item {
            background: #f8fafc;
            padding: 18px 16px;
            border-radius: 12px;
            text-align: center;
            border-left: 4px solid #8b5cf6;
            transition: all 0.3s ease;
            text-decoration: none;
            color: #0f172a;
            display: block;
            cursor: pointer;
        }
        .stat-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            border-color: #8b5cf6;
        }
        .stat-item .number { font-size: 28px; font-weight: 700; color: #0f172a; }
        .stat-item .label { font-size: 13px; color: #64748b; margin-top: 4px; }
        .stat-item .sub { font-size: 11px; color: #94a3b8; margin-top: 2px; }
        .stat-item.gold { border-left-color: #fbbf24; }
        .stat-item.green { border-left-color: #10b981; }
        .stat-item.blue { border-left-color: #3b82f6; }
        .stat-item.purple { border-left-color: #8b5cf6; }

        .action-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-top: 10px;
        }
        .action-card {
            background: #f8fafc;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            transition: 0.3s;
            border: 1px solid #e2e8f0;
            text-decoration: none;
            color: #0f172a;
        }
        .action-card:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(0,0,0,0.08); border-color: #8b5cf6; }
        .action-card .icon { font-size: 32px; margin-bottom: 10px; display: block; }
        .action-card .title { font-weight: 600; font-size: 14px; }
        .action-card .desc { font-size: 13px; color: #64748b; margin-top: 4px; }

        .table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
            margin-top: 10px;
        }
        .table th {
            text-align: left;
            padding: 10px 12px;
            background: #f8fafc;
            font-weight: 600;
            color: #475569;
            border-bottom: 2px solid #e2e8f0;
        }
        .table td {
            padding: 10px 12px;
            border-bottom: 1px solid #f1f5f9;
        }
        .table tr:hover td { background: #f8fafc; }

        .badge {
            display: inline-block;
            padding: 2px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }
        .badge-success { background: #d1fae5; color: #065f46; }
        .badge-warning { background: #fef3c7; color: #92400e; }
        .badge-info { background: #dbeafe; color: #1e40af; }

        .btn {
            padding: 6px 16px;
            border-radius: 8px;
            border: none;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: 0.3s;
        }
        .btn-primary { background: #8b5cf6; color: white; }
        .btn-primary:hover { background: #7c3aed; }
        .btn-success { background: #10b981; color: white; }
        .btn-success:hover { background: #059669; }

        .rank-medal { font-weight: 700; }
        .rank-gold { color: #fbbf24; }
        .rank-silver { color: #94a3b8; }
        .rank-bronze { color: #d97706; }

        @media (max-width: 768px) {
            .main-content { margin-left: 70px; padding: 16px; }
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .action-grid { grid-template-columns: 1fr; }
            .table { font-size: 12px; }
            .table th, .table td { padding: 6px 8px; }
        }
    </style>
</head>
<body>

<div class="main-content">

    <!-- HEADER -->
    <div class="header">
        <h1>👋 Dashboard Pimpinan</h1>
        <div class="user">
            <span class="date"><?php echo date('l, d F Y'); ?></span>
            <div class="avatar"><?php echo strtoupper(substr($nama, 0, 1)); ?></div>
            <span style="font-weight:600;"><?php echo htmlspecialchars($nama); ?></span>
        </div>
    </div>

    <!-- RINGKASAN SELEKSI -->
    <div class="card">
        <h3>📊 Ringkasan Seleksi</h3>
        <div class="stats-grid">
            <a href="ranking_kandidat.php" class="stat-item gold">
                <div class="number"><?php echo $total_kandidat; ?></div>
                <div class="label">Total Kandidat</div>
                <div class="sub">Lolos verifikasi</div>
            </a>
            <a href="" class="stat-item green">
                <div class="number"><?php echo $total_pengajuan; ?></div>
                <div class="label">Total Pengajuan</div>
                <div class="sub">Sudah disetujui</div>
            </a>
            <a href="ranking_kandidat.php?status=menunggu" class="stat-item blue">
                <div class="number"><?php echo $total_penetapan; ?></div>
                <div class="label">⏳ Menunggu Penetapan</div>
                <div class="sub">Perlu ditetapkan</div>
            </a>
            <a href="ranking_kandidat.php?status=ditetapkan" class="stat-item purple">
                <div class="number"><?php echo $total_ditetapkan; ?></div>
                <div class="label">✅ Sudah Ditetapkan</div>
                <div class="sub">Mahasiswa Berprestasi</div>
            </a>
        </div>
    </div>

    <!-- TINDAKAN -->
    <div class="card">
        <h3>⚡ Tindakan yang Perlu Dilakukan</h3>
        <div class="action-grid">
            <a href="ranking_kandidat.php" class="action-card">
                <span class="icon">👥</span>
                <div class="title"><?php echo $total_penetapan; ?> kandidat menunggu penetapan Anda</div>
                <div class="desc">Segera tetapkan mahasiswa berprestasi</div>
            </a>
            <a href="laporan.php" class="action-card">
                <span class="icon">📄</span>
                <div class="title">Lihat laporan hasil seleksi</div>
                <div class="desc">Unduh laporan dalam format PDF</div>
            </a>
            <a href="ranking_kandidat.php" class="action-card">
                <span class="icon">🏆</span>
                <div class="title">Lihat ranking kandidat</div>
                <div class="desc">Urutan peringkat berdasarkan skor</div>
            </a>
        </div>
    </div>

    <!-- TOP 5 RANKING -->
    <?php if (mysqli_num_rows($result_ranking) > 0): ?>
    <div class="card">
        <h3>🏆 Top 5 Ranking Kandidat</h3>
        <table class="table">
            <thead>
                <tr>
                    <th style="width:60px;">Rank</th>
                    <th>NIM</th>
                    <th>Nama</th>
                    <th>Prestasi</th>
                    <th style="text-align:center;">Skor</th>
                    <th style="text-align:center;">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $rank = 1;
                while ($row = mysqli_fetch_assoc($result_ranking)): 
                    $medal = '';
                    $medal_class = '';
                    if ($rank == 1) {
                        $medal = '🏅';
                        $medal_class = 'rank-gold';
                    } elseif ($rank == 2) {
                        $medal = '🥈';
                        $medal_class = 'rank-silver';
                    } elseif ($rank == 3) {
                        $medal = '🥉';
                        $medal_class = 'rank-bronze';
                    } else {
                        $medal = '#'.$rank;
                    }
                    $skor = hitungNilaiAkhir($row);
                    $status_label = ($row['penetapan'] == 1) ? '✅ Ditetapkan' : '⏳ Menunggu';
                    $status_badge = ($row['penetapan'] == 1) ? 'badge-success' : 'badge-warning';
                ?>
                <tr>
                    <td><span class="rank-medal <?php echo $medal_class; ?>"><?php echo $medal; ?></span></td>
                    <td><?php echo htmlspecialchars($row['nim']); ?></td>
                    <td><strong><?php echo htmlspecialchars($row['nama_lengkap']); ?></strong></td>
                    <td><?php echo htmlspecialchars($row['nama_kejuaraan'] ?? '-'); ?></td>
                    <td style="text-align:center; font-weight:700;"><?php echo $skor; ?>/100</td>
                    <td style="text-align:center;">
                        <span class="badge <?php echo $status_badge; ?>"><?php echo $status_label; ?></span>
                    </td>
                </tr>
                <?php 
                $rank++;
                endwhile; 
                ?>
            </tbody>
        </table>
        <div style="margin-top: 12px; text-align: right;">
            <a href="ranking_kandidat.php" style="color: #8b5cf6; text-decoration: none; font-weight: 600; font-size: 14px;">
                Lihat Semua Ranking →
            </a>
        </div>
    </div>
    <?php else: ?>
    <div class="card">
        <h3>🏆 Top 5 Ranking Kandidat</h3>
        <p style="color: #94a3b8; text-align: center; padding: 20px 0;">
            Belum ada kandidat yang lolos seleksi.
        </p>
    </div>
    <?php endif; ?>

    <!-- DAFTAR KANDIDAT (UNTUK DITETAPKAN) -->
    <div class="card">
        <h3>📋 Daftar Kandidat Menunggu Penetapan</h3>
        
        <?php if (mysqli_num_rows($result_kandidat_list) > 0): ?>
        <table class="table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>NIM</th>
                    <th>Nama</th>
                    <th>Prestasi</th>
                    <th>Level</th>
                    <th>Skor</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                while ($row = mysqli_fetch_assoc($result_kandidat_list)): 
                    $skor = hitungNilaiAkhir($row);
                ?>
                <tr>
                    <td><?php echo $no++; ?></td>
                    <td><?php echo htmlspecialchars($row['nim']); ?></td>
                    <td><strong><?php echo htmlspecialchars($row['nama_lengkap']); ?></strong></td>
                    <td><?php echo htmlspecialchars($row['nama_kejuaraan']); ?></td>
                    <td><?php echo htmlspecialchars($row['level_kompetisi']); ?></td>
                    <td style="text-align:center; font-weight:700;"><?php echo $skor; ?>/100</td>
                    <td>
                        <a href="tetapkan_prestasi.php?id=<?php echo $row['id']; ?>" class="btn btn-success">
                            <i class="fas fa-check"></i> Tetapkan
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p style="color: #94a3b8; text-align: center; padding: 20px 0;">
            Semua kandidat sudah ditetapkan. Tidak ada yang menunggu penetapan.
        </p>
        <?php endif; ?>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
</html>