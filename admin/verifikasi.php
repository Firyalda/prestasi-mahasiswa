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

// Ambil pengajuan yang statusnya 'diproses'
$query = "SELECT p.*, u.nama_lengkap, u.nim 
          FROM pengajuan p 
          JOIN users u ON p.user_id = u.id 
          WHERE p.status = 'diproses' 
          ORDER BY p.created_at DESC";
$result = mysqli_query($conn, $query);

include 'includes/sidebar.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Pengajuan - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', system-ui, sans-serif; background: #f0f4f8; color: #1a1a1a; }

        /* SIDEBAR - PUTIH RAPI */

        /* MAIN CONTENT */
        .main-content { margin-left: 260px; padding: 24px 32px; }

        /* TOP BAR - TANPA LOGOUT & TANPA CUACA */
        .top-bar {
            background: linear-gradient(135deg, #ffffff 0%, #f0f6ff 100%);
            border-radius: 16px;
            padding: 16px 24px;
            margin-bottom: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: 1px solid #dce5f0;
            box-shadow: 0 2px 8px rgba(42, 82, 152, 0.06);
        }
        .page-title h1 {
            font-size: 20px;
            font-weight: 700;
            color: #1a365d;
            background: linear-gradient(135deg, #1a365d, #2a5298);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .page-title p {
            font-size: 13px;
            color: #4a6a8a;
            margin-top: 4px;
            font-weight: 500;
        }

        /* PROFIL + TANGGAL (TANPA LOGOUT) */
        .user-info {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .user-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #2a5298, #1a365d);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(26, 54, 93, 0.2);
        }
        .user-name {
            font-weight: 600;
            color: #1a365d;
        }
        .date-divider {
            width: 1px;
            height: 30px;
            background: #dce2ea;
        }
        .date-text {
            font-size: 13px;
            color: #6a7e9e;
            font-weight: 500;
        }

        /* WEATHER WIDGET DIHAPUS */

        /* TABLE CARD */
        .table-card {
            background: white;
            border-radius: 14px;
            border: 1px solid #dce5f0;
            overflow: hidden;
            box-shadow: 0 2px 6px rgba(0,0,0,0.02);
        }
        .table-header {
            padding: 12px 20px;
            border-bottom: 1px solid #eef2f7;
            background: linear-gradient(135deg, #f8faff, #f0f6ff);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .table-header h3 {
            font-size: 15px;
            font-weight: 600;
            color: #1a365d;
        }
        .badge-count {
            background: linear-gradient(135deg, #1a365d, #2a5298);
            padding: 4px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            color: #ffffff;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }
        .data-table th {
            text-align: left;
            padding: 10px 16px;
            background: linear-gradient(135deg, #1a365d, #2a5298);
            color: #ffffff;
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .data-table td {
            padding: 8px 16px;
            border-bottom: 1px solid #eef2f7;
            font-size: 13px;
            color: #1a365d;
        }
        .data-table tr:hover {
            background: #f8faff;
        }
        .data-table tr:last-child td {
            border-bottom: none;
        }
        .btn-detail {
            background: #f0f4f8;
            border: 1px solid #dce5f0;
            padding: 4px 14px;
            border-radius: 8px;
            color: #4a6a8a;
            text-decoration: none;
            font-size: 11px;
            font-weight: 500;
            transition: all 0.2s;
            display: inline-block;
        }
        .btn-detail:hover {
            background: linear-gradient(135deg, #2a5298, #1a365d);
            color: white;
            border-color: #2a5298;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(42, 82, 152, 0.2);
        }

        @media (max-width: 768px) {
            .sidebar { width: 80px; }
            .sidebar a span { display: none; }
            .sidebar-header h2, .sidebar-header p { display: none; }
            .main-content { margin-left: 80px; padding: 16px; }
            .table-header { flex-direction: column; gap: 10px; }
            .user-info .user-name { display: none; }
        }
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
            <h1>Verifikasi Pengajuan Prestasi</h1>
            <p>Daftar pengajuan yang menunggu diverifikasi</p>
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
    <!-- TABEL PENGAJUAN                                           -->
    <!-- ========================================================= -->
    <div class="table-card">
        <div class="table-header">
            <h3>Daftar Pengajuan Menunggu Verifikasi</h3>
            <div class="badge-count">Total: <?php echo mysqli_num_rows($result); ?> pengajuan</div>
        </div>
        <div style="overflow-x: auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Mahasiswa</th>
                        <th>NIM</th>
                        <th>Kejuaraan</th>
                        <th>Level</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    if (mysqli_num_rows($result) > 0):
                        while ($row = mysqli_fetch_assoc($result)):
                    ?>
                    <tr>
                        <td style="font-weight: 600;"><?php echo $no++; ?></td>
                        <td><?php echo date('d/m/Y', strtotime($row['created_at'])); ?></td>
                        <td><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                        <td><?php echo htmlspecialchars($row['nim']); ?></td>
                        <td><?php echo htmlspecialchars(substr($row['nama_kejuaraan'] ?? '-', 0, 35)); ?>...</td>
                        <td><?php echo htmlspecialchars($row['level_kompetisi']); ?></td>
                        <td><a href="detail_pengajuan.php?id=<?php echo $row['id']; ?>" class="btn-detail">Detail</a></td>
                    </tr>
                    <?php 
                        endwhile;
                    else:
                    ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 40px; color: #6a7e9e;">Belum ada pengajuan yang menunggu verifikasi</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>