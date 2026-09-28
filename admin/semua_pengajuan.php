<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}
require_once '../config/database.php';
// =========================================================
// WEATHER_WIDGET DIHAPUS
// =========================================================
// require_once __DIR__ . '/../includes/weather_widget.php';

// Ambil filter status dari URL
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'semua';

// Query berdasarkan filter
if ($status_filter == 'disetujui') {
    $query = "SELECT p.*, u.nama_lengkap FROM pengajuan p JOIN users u ON p.user_id = u.id WHERE p.status = 'disetujui' ORDER BY p.created_at DESC";
    $judul = "Pengajuan Disetujui";
    $subtitle = "Daftar pengajuan yang telah disetujui";
} elseif ($status_filter == 'ditolak') {
    $query = "SELECT p.*, u.nama_lengkap FROM pengajuan p JOIN users u ON p.user_id = u.id WHERE p.status = 'ditolak' ORDER BY p.created_at DESC";
    $judul = "Pengajuan Ditolak";
    $subtitle = "Daftar pengajuan yang ditolak";
} elseif ($status_filter == 'diproses') {
    $query = "SELECT p.*, u.nama_lengkap FROM pengajuan p JOIN users u ON p.user_id = u.id WHERE p.status = 'diproses' ORDER BY p.created_at DESC";
    $judul = "Pengajuan Diproses";
    $subtitle = "Daftar pengajuan yang sedang diproses";
} else {
    $query = "SELECT p.*, u.nama_lengkap FROM pengajuan p JOIN users u ON p.user_id = u.id ORDER BY p.created_at DESC";
    $judul = "Semua Pengajuan";
    $subtitle = "Seluruh data pengajuan prestasi mahasiswa";
}

$result = mysqli_query($conn, $query);
$total = mysqli_num_rows($result);

// Statistik untuk badge
$total_semua = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM pengajuan"))['t'];
$total_diproses = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM pengajuan WHERE status='diproses'"))['t'];
$total_disetujui = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM pengajuan WHERE status='disetujui'"))['t'];
$total_ditolak = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM pengajuan WHERE status='ditolak'"))['t'];
include 'includes/sidebar.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Semua Pengajuan - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', system-ui, -apple-system, sans-serif; background: #f4f6f9; color: #1a1a1a; }
        /* MAIN CONTENT */
        .main-content { margin-left: 260px; padding: 24px 32px; }

        /* TOP BAR - TANPA LOGOUT & TANPA CUACA */
        .top-bar {
            display: flex; justify-content: space-between; align-items: center;
            background: white; border-radius: 12px; padding: 16px 24px;
            margin-bottom: 24px; border: 1px solid #e8e8e8;
        }
        .page-title h2 { font-size: 20px; font-weight: 600; color: #1a1a1a; }
        .page-title p { font-size: 13px; color: #777; margin-top: 4px; }

        /* PROFIL + TANGGAL (TANPA LOGOUT) */
        .user-info {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .user-avatar {
            width: 40px; height: 40px; background: #2c7da0; border-radius: 50%;
            display: flex; align-items: center; justify-content: center; color: white;
            font-weight: 600;
        }
        .user-name { font-weight: 500; color: #1a1a1a; }
        .date-divider {
            width: 1px; height: 30px; background: #ddd;
        }
        .date-text {
            font-size: 13px; color: #888;
        }

        /* WEATHER WIDGET DIHAPUS */

        /* FILTER BUTTONS */
        .filter-buttons {
            display: flex;
            gap: 15px;
            margin-bottom: 24px;
            flex-wrap: wrap;
        }
        .filter-btn {
            background: white;
            border: 1px solid #e8e8e8;
            padding: 8px 18px;
            border-radius: 30px;
            text-decoration: none;
            color: #555;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .filter-btn i { font-size: 13px; }
        .filter-btn:hover {
            background: #f0f2f5;
            border-color: #2c7da0;
        }
        .filter-btn.active {
            background: #2c7da0;
            color: white;
            border-color: #2c7da0;
        }
        .filter-badge {
            background: #eef2f5;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: 11px;
            color: #666;
            margin-left: 8px;
        }
        .filter-btn.active .filter-badge {
            background: rgba(255,255,255,0.2);
            color: white;
        }

        /* TABLE CARD */
        .table-card {
            background: white; border-radius: 12px; border: 1px solid #e8e8e8;
            overflow: hidden;
        }
        .table-header {
            padding: 12px 20px; border-bottom: 1px solid #eee;
            background: #f8f9fa;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .table-header h3 { font-size: 15px; font-weight: 600; color: #1a1a1a; }
        .badge-count {
            background: #eef2f5; color: #555; padding: 4px 12px;
            border-radius: 20px; font-size: 12px;
        }
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table th {
            text-align: left; padding: 10px 16px; background: #fafbfc;
            color: #666; font-weight: 600; font-size: 12px;
            border-bottom: 1px solid #e8e8e8;
        }
        .data-table td {
            padding: 8px 16px; border-bottom: 1px solid #f0f0f0;
            font-size: 13px;
            vertical-align: middle;
        }
        .status-badge {
            display: inline-block; padding: 3px 10px; border-radius: 20px;
            font-size: 11px; font-weight: 500;
        }
        .status-diproses { background: #e3f2fd; color: #1565c0; }
        .status-disetujui { background: #e8f5e9; color: #2e7d32; }
        .status-ditolak { background: #ffebee; color: #c62828; }
        .reward-text { font-weight: 600; color: #2c7da0; }
        .btn-detail {
            background: #eef2f5; border: 1px solid #ddd; padding: 4px 10px;
            border-radius: 6px; color: #555; text-decoration: none; font-size: 11px;
        }
        .btn-detail:hover { background: #2c7da0; color: white; border-color: #2c7da0; }

    </style>
</head>
<body>

<!-- ========================================================= -->
<!-- MAIN CONTENT                                              -->
<!-- ========================================================= -->
<div class="main-content">

    <!-- ========================================================= -->
    <!-- TOP BAR - TANPA LOGOUT & TANPA CUACA                       -->
    <!-- ========================================================= -->
    <div class="top-bar">
        <div class="page-title">
            <h2><?php echo $judul; ?></h2>
            <p><?php echo $subtitle; ?></p>
        </div>
        <div class="user-info">
            <div class="user-avatar">A</div>
            <span class="user-name"><?php echo htmlspecialchars($_SESSION['nama'] ?? 'Admin'); ?></span>
            <span class="date-divider"></span>
            <span class="date-text"><?php echo date('l, d F Y'); ?></span>
        </div>
    </div>

    <!-- ========================================================= -->
    <!-- WEATHER WIDGET DIHAPUS                                     -->
    <!-- ========================================================= -->

    <!-- ========================================================= -->
    <!-- FILTER BUTTONS                                            -->
    <!-- ========================================================= -->
    <div class="filter-buttons">
        <a href="?status=semua" class="filter-btn <?php echo $status_filter == 'semua' ? 'active' : ''; ?>">
            <i class="fas fa-list"></i> Semua
            <span class="filter-badge"><?php echo $total_semua; ?></span>
        </a>
        <a href="?status=diproses" class="filter-btn <?php echo $status_filter == 'diproses' ? 'active' : ''; ?>">
            <i class="fas fa-clock"></i> Diproses
            <span class="filter-badge"><?php echo $total_diproses; ?></span>
        </a>
        <a href="?status=disetujui" class="filter-btn <?php echo $status_filter == 'disetujui' ? 'active' : ''; ?>">
            <i class="fas fa-check-circle"></i> Disetujui
            <span class="filter-badge"><?php echo $total_disetujui; ?></span>
        </a>
        <a href="?status=ditolak" class="filter-btn <?php echo $status_filter == 'ditolak' ? 'active' : ''; ?>">
            <i class="fas fa-times-circle"></i> Ditolak
            <span class="filter-badge"><?php echo $total_ditolak; ?></span>
        </a>
    </div>

    <!-- ========================================================= -->
    <!-- TABLE                                                    -->
    <!-- ========================================================= -->
    <div class="table-card">
        <div class="table-header">
            <h3>Daftar <?php echo $judul; ?></h3>
            <div class="badge-count">Total: <?php echo $total; ?> pengajuan</div>
        </div>
        <div style="overflow-x: auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>No</th><th>Tgl</th><th>Mahasiswa</th><th>Kejuaraan</th>
                        <th>Level</th><th>Status</th><th>Reward</th><th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    if (mysqli_num_rows($result) > 0):
                        while ($row = mysqli_fetch_assoc($result)):
                            $status_class = '';
                            $status_text = '';
                            if ($row['status'] == 'diproses') {
                                $status_class = 'status-diproses';
                                $status_text = '⏳ Diproses';
                            } elseif ($row['status'] == 'disetujui') {
                                $status_class = 'status-disetujui';
                                $status_text = '✅ Disetujui';
                            } else {
                                $status_class = 'status-ditolak';
                                $status_text = '❌ Ditolak';
                            }
                    ?>
                    <tr>
                        <td style="font-weight: 600;"><?php echo $no++; ?></td>
                        <td><?php echo date('d/m/Y', strtotime($row['created_at'])); ?></td>
                        <td><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                        <td><?php echo htmlspecialchars(substr($row['nama_kejuaraan'] ?? '-', 0, 35)); ?>...</td>
                        <td><?php echo htmlspecialchars($row['level_kompetisi']); ?></td>
                        <td><span class="status-badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span></td>
                        <td class="reward-text">Rp <?php echo number_format($row['reward_nominal'] ?? 0, 0, ',', '.'); ?></td>
                        <td><a href="detail_pengajuan.php?id=<?php echo $row['id']; ?>" class="btn-detail">Detail</a></td>
                    </tr>
                    <?php 
                        endwhile;
                    else:
                    ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 40px; color: #999;">
                            <i class="fas fa-inbox" style="font-size: 40px; color: #ccc; margin-bottom: 10px; display: block;"></i>
                            Tidak ada data pengajuan
                         </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>