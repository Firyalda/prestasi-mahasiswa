<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: ../login.php");
    exit();
}

require_once '../config/database.php';

$user_id = $_SESSION['user_id'];

$query = "SELECT id, nama_kejuaraan, level_kompetisi, raihan_prestasi, status, created_at 
          FROM pengajuan 
          WHERE user_id = $user_id 
          ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Pengajuan - Mahasiswa</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: #f0f2f5;
            min-height: 100vh;
        }
        
        /* SIDEBAR */
        .admin-sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 260px;
            height: 100vh;
            background: linear-gradient(180deg, #8B5A2B 0%, #6B1A1A 100%);
            color: white;
            z-index: 100;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }
        
        .sidebar-header {
            padding: 25px 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.2);
        }
        
        .sidebar-header h3 {
            font-size: 22px;
            margin-bottom: 5px;
        }
        
        .sidebar-header p {
            font-size: 11px;
            opacity: 0.8;
        }
        
        .sidebar-menu {
            padding: 20px 0;
        }
        
        .menu-item {
            padding: 12px 25px;
            display: flex;
            align-items: center;
            gap: 12px;
            color: white;
            text-decoration: none;
            transition: all 0.3s;
            border-radius: 10px;
            margin: 5px 10px;
        }
        
        .menu-item:hover {
            background: rgba(255,255,255,0.15);
            padding-left: 30px;
        }
        
        .menu-item.active {
            background: rgba(255,255,255,0.2);
            border-left: 4px solid #ffc107;
        }
        
        .menu-logout {
            margin-top: 50px;
            border-top: 1px solid rgba(255,255,255,0.2);
            padding-top: 15px;
        }
        
        /* MAIN CONTENT */
        .admin-main {
            margin-left: 260px;
            padding: 25px 35px;
            min-height: 100vh;
        }
        
        /* TOP BAR */
        .top-bar {
            background: white;
            border-radius: 16px;
            padding: 15px 25px;
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .page-title h2 {
            color: #6B1A1A;
            font-size: 22px;
            font-weight: 700;
        }
        
        .page-title p {
            font-size: 13px;
            color: #8B7355;
            margin-top: 5px;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .user-avatar {
            width: 42px;
            height: 42px;
            background: linear-gradient(135deg, #6B1A1A 0%, #8B5A2B 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: white;
            font-size: 18px;
        }
        
        /* TABLE CARD */
        .table-card {
            background: white;
            border-radius: 20px;
            padding: 0;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            overflow: hidden;
        }
        
        .table-header {
            padding: 20px 25px;
            border-bottom: 2px solid #f0e6d8;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .table-header h3 {
            color: #6B1A1A;
            font-size: 18px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .badge-count {
            background: #6B1A1A;
            color: white;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th {
            text-align: left;
            padding: 15px 20px;
            background: #fef9f0;
            color: #6B1A1A;
            font-weight: 600;
            font-size: 13px;
            border-bottom: 1px solid #f0e6d8;
        }
        
        td {
            padding: 15px 20px;
            border-bottom: 1px solid #f0e6d8;
            font-size: 13px;
            color: #4a4a4a;
        }
        
        tr:hover td {
            background: #fef9f0;
        }
        
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 6px 14px;
            border-radius: 30px;
            font-size: 11px;
            font-weight: 600;
        }
        
        .status-diproses {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-disetujui {
            background: #d4edda;
            color: #155724;
        }
        
        .status-ditolak {
            background: #f8d7da;
            color: #721c24;
        }
        
        .empty-message {
            text-align: center;
            padding: 50px;
            color: #999;
        }
        
        .empty-message a {
            color: #6B1A1A;
            text-decoration: none;
            font-weight: 600;
        }
        
        .empty-icon {
            font-size: 50px;
            margin-bottom: 15px;
            opacity: 0.5;
        }
        
        @media (max-width: 768px) {
            .admin-sidebar {
                width: 80px;
            }
            .admin-sidebar .menu-text,
            .admin-sidebar .sidebar-header p {
                display: none;
            }
            .admin-main {
                margin-left: 80px;
                padding: 15px;
            }
            th, td {
                padding: 12px 10px;
                font-size: 11px;
            }
            .status-badge {
                padding: 4px 8px;
                font-size: 9px;
            }
        }
    </style>
</head>
<body>
    <!-- SIDEBAR -->
    <div class="admin-sidebar">
        <div class="sidebar-header">
            <h3>🎓 SMB</h3>
            <p>Sistem Prestasi</p>
        </div>
        <div class="sidebar-menu">
            <a href="dashboard.php" class="menu-item">
                <span>📊</span> <span class="menu-text">Dashboard</span>
            </a>
            <a href="pengajuan.php" class="menu-item">
                <span>📝</span> <span class="menu-text">Ajukan Prestasi</span>
            </a>
            <a href="status.php" class="menu-item active">
                <span>📋</span> <span class="menu-text">Status Pengajuan</span>
            </a>
            <a href="riwayat.php" class="menu-item">
                <span>📜</span> <span class="menu-text">Riwayat</span>
            </a>
            <a href="../logout.php" class="menu-item menu-logout">
                <span>🚪</span> <span class="menu-text">Logout</span>
            </a>
        </div>
    </div>
    
    <!-- MAIN CONTENT -->
    <div class="admin-main">
        <div class="top-bar">
            <div class="page-title">
                <h2>📋 Status Pengajuan Prestasi</h2>
                <p>Lihat status pengajuan prestasi yang telah Anda kirimkan</p>
            </div>
            <div class="user-info">
                <div class="user-avatar">👤</div>
                <span><?php echo htmlspecialchars($_SESSION['nama'] ?? 'Mahasiswa'); ?></span>
            </div>
        </div>
        
        <div class="table-card">
            <div class="table-header">
                <h3>
                    <span>📋</span> Daftar Pengajuan Prestasi
                </h3>
                <div class="badge-count">
                    Total: <?php echo mysqli_num_rows($result); ?> pengajuan
                </div>
            </div>
            
            <?php if(mysqli_num_rows($result) > 0): ?>
                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>Nama Kejuaraan</th>
                                <th>Level</th>
                                <th>Raihan</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; while($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><span style="font-weight: 600; color: #6B1A1A;"><?php echo $no++; ?></span></td>
                                <td><?php echo date('d/m/Y', strtotime($row['created_at'])); ?></td>
                                <td style="font-weight: 500;"><?php echo htmlspecialchars($row['nama_kejuaraan'] ?? '-'); ?></td>
                                <td>
                                    <span style="display: inline-block; padding: 4px 10px; background: #f0e6d8; border-radius: 20px; font-size: 11px;">
                                        <?php echo $row['level_kompetisi'] ?? '-'; ?>
                                    </span>
                                </td>
                                <td><?php echo $row['raihan_prestasi'] ?? '-'; ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $row['status']; ?>">
                                        <?php 
                                        if($row['status'] == 'diproses') echo '⏳ Diproses';
                                        elseif($row['status'] == 'disetujui') echo '✅ Disetujui';
                                        else echo '❌ Ditolak';
                                        ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-message">
                    <div class="empty-icon">📭</div>
                    <p>Belum ada pengajuan prestasi</p>
                    <p style="margin-top: 10px;"><a href="pengajuan.php">➕ Ajukan prestasi sekarang</a></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>