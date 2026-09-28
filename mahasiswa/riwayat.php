<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: ../login.php");
    exit();
}

require_once '../config/database.php';

$user_id = $_SESSION['user_id'];

// Ambil data pengguna
$user_data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT nama_lengkap, nim, foto_profil FROM users WHERE id = $user_id"));
$foto_profil = $user_data['foto_profil'] ?? '';

// Format tanggal (Friday, 12 June 2026)
$tanggal_hari_ini = date('l, d F Y');

// =========================================================
// AMBIL DATA RIWAYAT PENGAJUAN
// =========================================================
$query = "SELECT p.*, 
          DATE_FORMAT(p.created_at, '%d/%m/%Y %H:%i') as tanggal_pengajuan 
          FROM pengajuan p 
          WHERE p.user_id = $user_id 
          ORDER BY p.created_at DESC";

$result = mysqli_query($conn, $query);
$total_pengajuan = mysqli_num_rows($result);

include 'includes/sidebar.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pengajuan - Mahasiswa</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/burger-menu.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', system-ui, sans-serif; background: #f4f6f9; color: #1a1a1a; display: flex; min-height: 100vh; }

        .main-content { margin-left: 260px; padding: 24px 32px; width: 100%; min-height: 100vh; }

        /* TOP BAR */
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
        .page-title h1 { font-size: 20px; font-weight: 600; color: #1a1a1a; }
        .page-title p { font-size: 13px; color: #666; margin-top: 4px; }
        .user-info {
            display: flex;
            align-items: center;
            gap: 16px;
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
        .user-avatar i {
            color: white;
            font-size: 18px;
        }
        .user-name { font-weight: 500; }
        .tanggal {
            font-size: 13px;
            color: #666;
            background: #f0f2f5;
            padding: 6px 14px;
            border-radius: 20px;
        }

        /* CARD */
        .card {
            background: white;
            border-radius: 12px;
            border: 1px solid #e8e8e8;
            overflow: hidden;
            margin-bottom: 20px;
        }
        .card-header {
            padding: 16px 20px;
            border-bottom: 1px solid #eee;
            background: #f8f9fa;
            font-weight: 600;
            font-size: 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .card-body { padding: 20px; }

        /* TABLE */
        .table-wrapper { overflow-x: auto; }
        .table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }
        .table th {
            text-align: left;
            padding: 12px 12px;
            background: #f8f9fa;
            font-weight: 600;
            color: #475569;
            border-bottom: 2px solid #e2e8f0;
            white-space: nowrap;
        }
        .table td {
            padding: 12px 12px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }
        .table tr:hover td { background: #f8fafc; }

        /* BADGE */
        .badge {
            display: inline-block;
            padding: 4px 14px;
            border-radius: 30px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge-warning {
            background: #fff3e0;
            color: #e67e22;
        }
        .badge-success {
            background: #e8f5e9;
            color: #2e7d32;
        }
        .badge-danger {
            background: #ffebee;
            color: #c62828;
        }
        .badge-info {
            background: #d1ecf1;
            color: #0c5460;
        }
        .badge-secondary {
            background: #e2e8f0;
            color: #475569;
        }

        .btn-detail {
            display: inline-block;
            padding: 4px 14px;
            background: #2c7da0;
            color: white;
            border-radius: 30px;
            text-decoration: none;
            font-size: 12px;
            font-weight: 500;
            transition: 0.2s;
        }
        .btn-detail:hover {
            background: #1f5e7a;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #94a3b8;
        }
        .empty-state i { font-size: 48px; margin-bottom: 12px; }
        .empty-state p { font-size: 16px; }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 16px;
            }
            .top-bar {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }
            .table { font-size: 12px; }
            .table th, .table td { padding: 8px 6px; }
        }
    </style>
</head>
<body>

<div class="main-content">

    <!-- TOP BAR -->
    <div class="top-bar">
        <div class="page-title">
            <h1>Riwayat Pengajuan</h1>
            <p>Lihat riwayat pengajuan prestasi Anda</p>
        </div>
        <div class="user-info">
            <div class="user-avatar">
                <?php if (!empty($foto_profil) && file_exists("../uploads/profil/" . $foto_profil)): ?>
                    <img src="../uploads/profil/<?php echo $foto_profil; ?>" alt="Foto Profil">
                <?php else: ?>
                    <i class="fas fa-user"></i>
                <?php endif; ?>
            </div>
            <span class="user-name"><?php echo htmlspecialchars($user_data['nama_lengkap'] ?? 'Mahasiswa'); ?></span>
            <div class="tanggal"><?php echo $tanggal_hari_ini; ?></div>
        </div>
    </div>

    <!-- DAFTAR RIWAYAT -->
    <div class="card">
        <div class="card-header">
            <span><i class="fas fa-history"></i> Daftar Riwayat Pengajuan Prestasi</span>
            <span style="font-size:13px; color:#888;">Total: <?php echo $total_pengajuan; ?> pengajuan</span>
        </div>
        <div class="card-body">
            <?php if ($total_pengajuan > 0): ?>
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width:50px;">No</th>
                            <th>Tanggal Pengajuan</th>
                            <th>Nama Kejuaraan</th>
                            <th>Level Kompetisi</th>
                            <th>Raihan</th>
                            <th>Status</th>
                            <th style="width:100px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
                        while ($row = mysqli_fetch_assoc($result)): 
                            $status = $row['status'];
                            $badge_class = '';
                            $label = '';
                            
                            switch ($status) {
                                case 'diproses':
                                    $badge_class = 'badge-warning';
                                    $label = 'Diproses';
                                    break;
                                case 'disetujui':
                                    $badge_class = 'badge-success';
                                    $label = 'Disetujui';
                                    break;
                                case 'ditolak':
                                    $badge_class = 'badge-danger';
                                    $label = 'Ditolak';
                                    break;
                                case 'revisi':
                                    $badge_class = 'badge-info';
                                    $label = 'Revisi';
                                    break;
                                default:
                                    $badge_class = 'badge-secondary';
                                    $label = ucfirst($status);
                            }
                        ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td><?php echo $row['tanggal_pengajuan']; ?></td>
                            <td><?php echo htmlspecialchars($row['nama_kejuaraan']); ?></td>
                            <td><?php echo htmlspecialchars($row['level_kompetisi']); ?></td>
                            <td><?php echo htmlspecialchars($row['raihan_prestasi']); ?></td>
                            <td><span class="badge <?php echo $badge_class; ?>"><?php echo $label; ?></span></td>
                            <td>
                                <a href="detail_pengajuan.php?id=<?php echo $row['id']; ?>" class="btn-detail">
                                    <i class="fas fa-eye"></i> Detail
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <p>Belum ada pengajuan prestasi.</p>
                <p style="font-size:13px; color:#aaa;">Silakan ajukan prestasi Anda melalui halaman Pengajuan Prestasi.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<script src="../assets/js/burger-menu.js"></script>
</body>
</html>