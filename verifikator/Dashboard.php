<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/flash.php';

use App\Core\Session;

$session = new Session();

// Cek login dan role
if (!$session->has('user_id') || $session->get('role') !== 'reviewer') {
    header("Location: ../login.php");
    exit();
}

$nama = $session->get('nama', 'Verifikator');
$user_id = $session->get('user_id');

// =========================================================
// AMBIL DATA STATISTIK
// =========================================================

// 1. TOTAL SEMUA BERKAS
$query_total = "SELECT COUNT(*) as total FROM pengajuan";
$result_total = mysqli_query($conn, $query_total);
$total_berkas = mysqli_fetch_assoc($result_total)['total'] ?? 0;

// 2. MENUNGGU REVIEW (status = 'diproses')
$query_menunggu = "SELECT COUNT(*) as total FROM pengajuan WHERE status = 'diproses'";
$result_menunggu = mysqli_query($conn, $query_menunggu);
$total_menunggu = mysqli_fetch_assoc($result_menunggu)['total'] ?? 0;

// 3. SELESAI REVIEW (status = 'disetujui' atau 'ditolak')
$query_selesai = "SELECT COUNT(*) as total FROM pengajuan WHERE status IN ('disetujui', 'ditolak')";
$result_selesai = mysqli_query($conn, $query_selesai);
$total_selesai = mysqli_fetch_assoc($result_selesai)['total'] ?? 0;

// Daftar pengajuan yang perlu review (status = 'diproses')
$query_list = "SELECT p.*, u.nama_lengkap, u.nim, u.prodi
               FROM pengajuan p 
               JOIN users u ON p.user_id = u.id 
               WHERE p.status = 'diproses'
               ORDER BY p.created_at DESC
               LIMIT 10";
$result_list = mysqli_query($conn, $query_list);

include 'includes/sidebar.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Verifikator - SMB</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <?php render_flash_script(); ?>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; background: #f0f4f8; display: flex; min-height: 100vh; }

        .main-content { margin-left: 260px; padding: 28px 32px; width: 100%; min-height: 100vh; }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: linear-gradient(135deg, #ffffff 0%, #f0f6ff 100%);
            padding: 14px 24px;
            border-radius: 14px;
            border: 1px solid #dce5f0;
            box-shadow: 0 2px 8px rgba(42, 82, 152, 0.06);
            margin-bottom: 24px;
        }
        .header-left h1 { 
            font-size: 20px; 
            font-weight: 700; 
            color: #1a365d;
            background: linear-gradient(135deg, #1a365d, #2a5298);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .header-left p {
            font-size: 14px;
            color: #4a6a8a;
            margin-top: 4px;
        }
        .header-left p strong {
            color: #1a365d;
        }
        .header .user { display: flex; align-items: center; gap: 14px; font-size: 14px; color: #4a6a8a; font-weight: 500; }
        .header .user .avatar {
            width: 38px; height: 38px; 
            background: linear-gradient(135deg, #2a5298, #1a365d);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center; font-weight: 600; color: white; font-size: 16px;
            box-shadow: 0 2px 8px rgba(26, 54, 93, 0.2);
        }

        .card { 
            background: white; 
            border-radius: 14px; 
            border: 1px solid #dce5f0;
            box-shadow: 0 2px 6px rgba(0,0,0,0.02);
            padding: 20px 24px; 
            margin-bottom: 20px; 
        }
        .card-title { 
            font-size: 15px; 
            font-weight: 600; 
            color: #1a365d; 
            margin-bottom: 12px; 
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-top: 4px;
        }
        .stat-item {
            background: #f8faff;
            padding: 16px;
            border-radius: 12px;
            text-align: center;
            border-left: 4px solid #2a5298;
            transition: all 0.3s;
        }
        .stat-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(42, 82, 152, 0.08);
        }
        .stat-item .number { 
            font-size: 28px; 
            font-weight: 700; 
            color: #1a365d; 
        }
        .stat-item .label { 
            font-size: 13px; 
            color: #4a6a8a; 
            margin-top: 4px; 
            font-weight: 500; 
        }
        .stat-item .sub { 
            font-size: 11px; 
            color: #6a7e9e; 
            margin-top: 2px; 
        }
        .stat-item.gold { border-left-color: #d97706; }
        .stat-item.blue { border-left-color: #2a5298; }
        .stat-item.green { border-left-color: #059669; }

        .table { 
            width: 100%; 
            border-collapse: collapse; 
            font-size: 14px; 
            margin-top: 4px; 
        }
        .table th { 
            text-align: left; 
            padding: 10px 14px; 
            background: linear-gradient(135deg, #1a365d, #2a5298);
            color: #ffffff;
            font-weight: 600; 
            border-bottom: 2px solid #1a365d;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            font-size: 12px;
        }
        .table td { 
            padding: 10px 14px; 
            border-bottom: 1px solid #eef2f7; 
            color: #1a365d; 
        }
        .table tr:hover td { 
            background: #f8faff; 
        }
        .table tr:last-child td { 
            border-bottom: none; 
        }

        .badge { 
            display: inline-block; 
            padding: 4px 14px; 
            border-radius: 20px; 
            font-size: 12px; 
            font-weight: 600; 
        }
        .badge-warning { 
            background: #fef3c7; 
            color: #d97706; 
            border: 1px solid #fcd34d; 
        }

        .btn-review {
            background: linear-gradient(135deg, #2a5298, #1a365d);
            color: white;
            padding: 6px 16px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 12px;
            font-weight: 600;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border: none;
            cursor: pointer;
            white-space: nowrap;
        }
        .btn-review:hover { 
            background: linear-gradient(135deg, #1a365d, #2a5298);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(42, 82, 152, 0.3);
        }
        .btn-review i {
            font-size: 12px;
        }

        .empty-state {
            text-align: center;
            color: #6a7e9e;
            padding: 30px 20px;
        }
        .empty-state i {
            font-size: 32px;
            color: #dce2ea;
            display: block;
            margin-bottom: 10px;
        }

        .pagination-info {
            margin-top: 12px; 
            text-align: center; 
            color: #6a7e9e; 
            font-size: 13px;
        }

        @media (max-width: 768px) {
            .main-content { margin-left: 0; padding: 16px; }
            .stats-grid { grid-template-columns: 1fr; }
            .header { flex-direction: column; align-items: flex-start; gap: 12px; }
            .table { font-size: 12px; }
            .table th, .table td { padding: 6px 8px; }
            .btn-review { padding: 4px 10px; font-size: 11px; }
        }
    </style>
</head>
<body>


<div class="main-content">

    <!-- ========================================================= -->
    <!-- HEADER DENGAN UCAPAN SELAMAT DATANG                        -->
    <!-- ========================================================= -->
    <div class="header">
        <div class="header-left">
            <h1>📊 Dashboard Verifikator</h1>
            <p>
                Selamat datang, <strong><?php echo htmlspecialchars($nama); ?></strong>! 
                Silakan verifikasi pengajuan prestasi mahasiswa.
            </p>
        </div>
        <div class="user">
            <span><?php echo date('l, d F Y'); ?></span>
            <div class="avatar"><?php echo strtoupper(substr($nama, 0, 1)); ?></div>
            <span><?php echo htmlspecialchars($nama); ?></span>
        </div>
    </div>

    <!-- ========================================================= -->
    <!-- STATISTIK                                                  -->
    <!-- ========================================================= -->
    <div class="card">
        <div class="card-title">📊 Statistik Verifikasi</div>
        <div class="stats-grid">
            <div class="stat-item gold">
                <div class="number"><?php echo $total_berkas; ?></div>
                <div class="label">Total Berkas</div>
                <div class="sub">Semua pengajuan</div>
            </div>
            <div class="stat-item blue">
                <div class="number"><?php echo $total_menunggu; ?></div>
                <div class="label">⏳ Menunggu Review</div>
                <div class="sub">Belum diperiksa</div>
            </div>
            <div class="stat-item green">
                <div class="number"><?php echo $total_selesai; ?></div>
                <div class="label">✅ Selesai Review</div>
                <div class="sub">Sudah diverifikasi</div>
            </div>
        </div>
    </div>

    <!-- ========================================================= -->
    <!-- DAFTAR PENGAJUAN                                           -->
    <!-- ========================================================= -->
    <div class="card">
        <div class="card-title">📋 Daftar Pengajuan Perlu Verifikasi</div>
        <div style="overflow-x: auto;">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width:50px;">No</th>
                        <th>Tanggal</th>
                        <th>NIM</th>
                        <th>Nama</th>
                        <th>Prestasi</th>
                        <th>Status</th>
                        <th style="width:120px; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result_list) > 0): ?>
                        <?php $no = 1; while ($row = mysqli_fetch_assoc($result_list)): ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td><?php echo date('d/m/Y', strtotime($row['created_at'])); ?></td>
                            <td><?php echo htmlspecialchars($row['nim']); ?></td>
                            <td><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                            <td><?php echo htmlspecialchars(substr($row['nama_kejuaraan'], 0, 20)); ?>...</td>
                            <td><span class="badge badge-warning">⏳ Diproses</span></td>
                            <td style="text-align: center;">
                                <a href="review_berkas.php?id=<?php echo $row['id']; ?>" class="btn-review">
                                    <i class="fas fa-eye"></i> Review
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="empty-state">
                                <i class="fas fa-inbox"></i>
                                Tidak ada pengajuan yang perlu diverifikasi.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if (mysqli_num_rows($result_list) > 0): ?>
        <div class="pagination-info">
            Menampilkan 1-<?php echo min(mysqli_num_rows($result_list), 10); ?> dari <?php echo $total_menunggu; ?> data
        </div>
        <?php endif; ?>
    </div>

</div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
</html>