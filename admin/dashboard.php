<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/flash.php';

use App\Core\Session;
use App\Models\User;
use App\Models\Pengajuan;

$session = new Session();
if (!$session->has('user_id') || $session->get('role') !== 'admin') {
    header("Location: login.php");
    exit();
}

$pengajuanModel = new Pengajuan();
$allPengajuan = $pengajuanModel->all();

$total_mahasiswa = count(array_unique(array_column($allPengajuan, 'user_id')));
$total_pengajuan = count($allPengajuan);
$total_diproses  = count(array_filter($allPengajuan, fn($i) => $i['status'] === 'diproses'));
$total_disetujui = count(array_filter($allPengajuan, fn($i) => $i['status'] === 'disetujui'));
$total_ditolak   = count(array_filter($allPengajuan, fn($i) => $i['status'] === 'ditolak'));

$total_reward = array_reduce($allPengajuan, function($sum, $item) {
    return $sum + ($item['status'] === 'disetujui' ? (int)($item['reward_nominal'] ?? 0) : 0);
}, 0);

// Pengajuan terbaru via OOP Model
$recent = mysqli_query($conn, "SELECT p.*, u.nama_lengkap FROM pengajuan p JOIN users u ON p.user_id = u.id ORDER BY p.created_at DESC LIMIT 5");

// =========================================================
// 🔔 AMBIL NOTIFIKASI UNTUK ADMIN
// =========================================================
$query_notif_count = "SELECT COUNT(*) as total FROM notifikasi 
                      WHERE role = 'admin' AND is_read = 0";
$result_notif_count = mysqli_query($conn, $query_notif_count);
$notif_count = mysqli_fetch_assoc($result_notif_count)['total'] ?? 0;

// Ambil 5 notifikasi terbaru
$query_notif_list = "SELECT * FROM notifikasi 
                      WHERE role = 'admin' 
                      ORDER BY created_at DESC 
                      LIMIT 5";
$result_notif_list = mysqli_query($conn, $query_notif_list);

include 'includes/sidebar.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - SMB</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <?php render_flash_script(); ?>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', system-ui, sans-serif; background: #f0f4f8; color: #1a1a1a; }

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

        /* PROFIL + TANGGAL + NOTIFIKASI (TANPA LOGOUT) */
        .user-info {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .user-avatar {
            width: 40px; height: 40px; 
            background: linear-gradient(135deg, #2a5298, #1a365d);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center; color: white;
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(26, 54, 93, 0.2);
        }
        .user-name { 
            font-weight: 600; 
            color: #1a365d; 
        }
        .date-divider {
            width: 1px; height: 30px; background: #dce2ea;
        }
        .date-text {
            font-size: 13px; color: #6a7e9e;
            font-weight: 500;
        }

        /* ===== NOTIFIKASI BELL DI HEADER ===== */
        .notif-bell {
            position: relative;
            font-size: 20px;
            color: #2a5298;
            text-decoration: none;
            padding: 6px 10px;
            border-radius: 10px;
            transition: 0.2s;
            background: rgba(42, 82, 152, 0.08);
        }
        .notif-bell:hover {
            background: rgba(42, 82, 152, 0.15);
            color: #1a365d;
            transform: scale(1.05);
        }
        .notif-bell .badge-notif {
            position: absolute;
            top: -5px;
            right: -5px;
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
            border-radius: 50%;
            padding: 2px 7px;
            font-size: 10px;
            font-weight: 700;
            min-width: 18px;
            text-align: center;
            box-shadow: 0 2px 6px rgba(239, 68, 68, 0.3);
        }

        /* STATS CARDS */
        .stats-row {
            display: flex;
            gap: 20px;
            margin-bottom: 24px;
        }
        .stat-card {
            background: white;
            border-radius: 14px;
            border: 1px solid #dce5f0;
            padding: 10px 15px;
            flex: 1;
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
            transition: all 0.3s;
            box-shadow: 0 2px 6px rgba(0,0,0,0.02);
        }
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(42, 82, 152, 0.12);
            border-color: #2a5298;
        }
        .stat-title { 
            font-size: 10px; 
            color: #6a7e9e; 
            margin-bottom: 2px; 
            text-transform: uppercase; 
            letter-spacing: 0.5px;
            font-weight: 600;
        }
        .stat-number { 
            font-size: 20px; 
            font-weight: 700; 
            color: #1a365d; 
        }
        .stat-icon { 
            font-size: 24px; 
            color: #2a5298; 
            opacity: 0.5;
            transition: 0.3s;
        }
        .stat-card:hover .stat-icon {
            opacity: 1;
            transform: scale(1.1);
        }

        /* TOTAL PENGAJUAN CARD */
        .stat-card-large {
            background: linear-gradient(135deg, #ffffff 0%, #f8faff 100%);
            border-radius: 14px;
            border: 1px solid #dce5f0;
            flex: 2;
            overflow: hidden;
            box-shadow: 0 2px 6px rgba(0,0,0,0.02);
            transition: all 0.3s;
        }
        .stat-card-large:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(42, 82, 152, 0.12);
            border-color: #2a5298;
        }
        .stat-large-top {
            padding: 10px 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #eef2f7;
        }
        .stat-large-bottom {
            display: flex;
            padding: 8px 15px;
            gap: 15px;
            background: #fafcff;
        }
        .status-item {
            flex: 1;
            text-align: center;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
            display: block;
            transition: all 0.3s;
            padding: 6px 4px;
            border-radius: 10px;
        }
        .status-item:hover {
            background: linear-gradient(135deg, #eef2ff, #e2e8f0);
            transform: translateY(-2px);
        }
        .status-icon { font-size: 16px; }
        .status-label { 
            font-size: 10px; 
            color: #6a7e9e; 
            margin-top: 2px; 
            font-weight: 500;
        }
        .status-number { 
            font-size: 18px; 
            font-weight: 700; 
        }
        .status-number.menunggu { 
            color: #d97706; 
            background: rgba(217, 119, 6, 0.1);
            padding: 0 10px;
            border-radius: 20px;
        }
        .status-number.disetujui { 
            color: #059669; 
            background: rgba(5, 150, 105, 0.1);
            padding: 0 10px;
            border-radius: 20px;
        }
        .status-number.ditolak { 
            color: #dc2626; 
            background: rgba(220, 38, 38, 0.1);
            padding: 0 10px;
            border-radius: 20px;
        }

        /* ===== NOTIFIKASI WIDGET DI DASHBOARD ===== */
        .notif-widget {
            background: white;
            border-radius: 14px;
            border: 1px solid #dce5f0;
            margin-bottom: 24px;
            overflow: hidden;
            box-shadow: 0 2px 6px rgba(0,0,0,0.02);
        }
        .notif-widget-header {
            padding: 12px 20px;
            border-bottom: 1px solid #eef2f7;
            background: linear-gradient(135deg, #f8faff, #f0f6ff);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .notif-widget-header h3 {
            font-size: 15px;
            font-weight: 600;
            color: #1a365d;
        }
        .notif-widget-header h3 i {
            color: #2a5298;
        }
        .notif-widget-header a {
            font-size: 13px;
            color: #2a5298;
            text-decoration: none;
            font-weight: 500;
        }
        .notif-widget-header a:hover {
            text-decoration: underline;
            color: #1a365d;
        }
        .notif-item-widget {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 10px 20px;
            border-bottom: 1px solid #f0f4f8;
            transition: 0.2s;
        }
        .notif-item-widget:hover {
            background: #f8faff;
        }
        .notif-item-widget:last-child {
            border-bottom: none;
        }
        .notif-item-widget .notif-icon {
            width: 32px;
            height: 32px;
            background: linear-gradient(135deg, #dbeafe, #bfdbfe);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #2a5298;
            flex-shrink: 0;
            font-size: 14px;
        }
        .notif-item-widget .notif-content {
            flex: 1;
        }
        .notif-item-widget .notif-content .judul {
            font-size: 13px;
            font-weight: 600;
            color: #1a365d;
        }
        .notif-item-widget .notif-content .pesan {
            font-size: 12px;
            color: #4a6a8a;
            margin-top: 2px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .notif-item-widget .notif-content .waktu {
            font-size: 11px;
            color: #6a7e9e;
            margin-top: 4px;
        }
        .notif-item-widget .notif-link {
            font-size: 12px;
            color: #2a5298;
            text-decoration: none;
            margin-left: 8px;
            font-weight: 500;
        }
        .notif-item-widget .notif-link:hover {
            text-decoration: underline;
            color: #1a365d;
        }
        .notif-empty {
            padding: 30px 20px;
            text-align: center;
            color: #6a7e9e;
            font-size: 14px;
        }
        .notif-empty i {
            color: #dce2ea;
        }

        /* TABLE - HEADER BIRU DENGAN TULISAN PUTIH */
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
        }
        .table-header h3 { 
            font-size: 15px; 
            font-weight: 600; 
            color: #1a365d; 
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
        .status-badge {
            display: inline-block; 
            padding: 4px 14px; 
            border-radius: 20px;
            font-size: 11px; 
            font-weight: 600;
        }
        .status-diproses { 
            background: #fef3c7; 
            color: #d97706; 
            border: 1px solid #fcd34d;
        }
        .status-disetujui { 
            background: #d1fae5; 
            color: #059669; 
            border: 1px solid #6ee7b7;
        }
        .status-ditolak { 
            background: #fee2e2; 
            color: #dc2626; 
            border: 1px solid #fca5a5;
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
        }
        .btn-detail:hover { 
            background: linear-gradient(135deg, #2a5298, #1a365d); 
            color: white; 
            border-color: #2a5298;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(42, 82, 152, 0.2);
        }
    </style>
</head>
<body>

<!-- ========================================================= -->
<!-- MAIN CONTENT                                              -->
<!-- ========================================================= -->
<div class="main-content">
    <div class="top-bar">
        <div class="page-title">
            <h1>Dashboard Admin</h1>
            <p>Selamat datang, <?php echo htmlspecialchars($_SESSION['nama'] ?? 'Administrator'); ?>!</p>
        </div>
        <div class="user-info">
            <!-- 🔔 Notifikasi Bell -->
            <a href="notifikasi.php" class="notif-bell">
                <i class="fas fa-bell"></i>
                <?php if ($notif_count > 0): ?>
                    <span class="badge-notif"><?php echo $notif_count; ?></span>
                <?php endif; ?>
            </a>
            <!-- Avatar -->
            <div class="user-avatar">A</div>
            <!-- Nama Admin -->
            <span class="user-name"><?php echo htmlspecialchars($_SESSION['nama'] ?? 'Admin'); ?></span>
            <!-- Pemisah -->
            <span class="date-divider"></span>
            <!-- Tanggal (format: Tuesday, 30 June 2026) -->
            <span class="date-text"><?php echo date('l, d F Y'); ?></span>
        </div>
    </div>

    <!-- ========================================================= -->
    <!-- STATS CARDS                                               -->
    <!-- ========================================================= -->
    <div class="stats-row">
        <!-- Card Mahasiswa Yang Sudah Mengajukan -->
        <a href="data_mahasiswa.php?filter=sudah" class="stat-card" style="border-left: 4px solid #2a5298;">
            <div>
                <div class="stat-title">MAHASISWA YANG SUDAH MENGAJUKAN</div>
                <div class="stat-number"><?php echo $total_mahasiswa; ?></div>
            </div>
            <div class="stat-icon"><i class="fas fa-users"></i></div>
        </a>

        <!-- Card Total Reward -->
        <a href="laporan_prestasi.php" class="stat-card" style="border-left: 4px solid #059669;">
            <div>
                <div class="stat-title">TOTAL REWARD</div>
                <div class="stat-number" style="color: #059669;">Rp <?php echo number_format($total_reward, 0, ',', '.'); ?></div>
            </div>
            <div class="stat-icon" style="color: #059669;"><i class="fas fa-chart-bar"></i></div>
        </a>

        <!-- Card Total Pengajuan -->
        <div class="stat-card-large" style="border-left: 4px solid #d97706;">
            <div class="stat-large-top">
                <div>
                    <div class="stat-title">TOTAL PENGAJUAN</div>
                    <div class="stat-number" style="font-size: 18px; color: #1a365d;"><?php echo $total_pengajuan; ?></div>
                </div>
                <div class="stat-icon" style="color: #d97706;"><i class="fas fa-file-alt"></i></div>
            </div>
            <div class="stat-large-bottom">
                <a href="verifikasi.php" class="status-item">
                    <div class="status-icon">⏳</div>
                    <div class="status-label">Menunggu</div>
                    <div class="status-number menunggu"><?php echo $total_diproses; ?></div>
                </a>
                <a href="semua_pengajuan.php?status=disetujui" class="status-item">
                    <div class="status-icon">✅</div>
                    <div class="status-label">Disetujui</div>
                    <div class="status-number disetujui"><?php echo $total_disetujui; ?></div>
                </a>
                <a href="semua_pengajuan.php?status=ditolak" class="status-item">
                    <div class="status-icon">❌</div>
                    <div class="status-label">Ditolak</div>
                    <div class="status-number ditolak"><?php echo $total_ditolak; ?></div>
                </a>
            </div>
        </div>
    </div>

    <!-- ========================================================= -->
    <!-- 🔔 NOTIFIKASI TERBARU (WIDGET DI DASHBOARD)               -->
    <!-- ========================================================= -->
    <div class="notif-widget">
        <div class="notif-widget-header">
            <h3><i class="fas fa-bell"></i> Notifikasi Terbaru</h3>
            <a href="notifikasi.php">Lihat Semua →</a>
        </div>
        <?php if (mysqli_num_rows($result_notif_list) > 0): ?>
            <?php while ($notif = mysqli_fetch_assoc($result_notif_list)): ?>
                <div class="notif-item-widget" style="<?php echo $notif['is_read'] ? '' : 'background: #f8faff; border-left: 3px solid #2a5298;'; ?>">
                    <div class="notif-icon">
                        <i class="fas fa-bell"></i>
                    </div>
                    <div class="notif-content">
                        <div class="judul">
                            <?php echo htmlspecialchars($notif['judul']); ?>
                            <?php if (!$notif['is_read']): ?>
                                <span style="background: linear-gradient(135deg, #2a5298, #1a365d); color: white; padding: 1px 10px; border-radius: 20px; font-size: 10px; font-weight: 600; margin-left: 6px;">Baru</span>
                            <?php endif; ?>
                        </div>
                        <div class="pesan"><?php echo htmlspecialchars(substr($notif['pesan'], 0, 80)) . (strlen($notif['pesan']) > 80 ? '...' : ''); ?></div>
                        <div class="waktu">
                            <i class="far fa-clock"></i> <?php echo date('d/m/Y H:i', strtotime($notif['created_at'])); ?>
                            <?php if ($notif['link']): ?>
                                <a href="<?php echo $notif['link']; ?>" class="notif-link">Lihat Detail</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="notif-empty">
                <i class="fas fa-bell-slash" style="font-size: 24px; display: block; margin-bottom: 8px;"></i>
                Belum ada notifikasi
            </div>
        <?php endif; ?>
    </div>

    <!-- ========================================================= -->
    <!-- PENGAJUAN TERBARU                                         -->
    <!-- ========================================================= -->
    <div class="table-card">
        <div class="table-header">
            <h3>Pengajuan Terbaru</h3>
        </div>
        <div style="overflow-x: auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Mahasiswa</th>
                        <th>Kejuaraan</th>
                        <th>Level</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($recent) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($recent)): ?>
                            <tr>
                                <td><?php echo date('d/m/Y', strtotime($row['created_at'])); ?></td>
                                <td><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                                <td><?php echo htmlspecialchars(substr($row['nama_kejuaraan'] ?? '-', 0, 30)) . (strlen($row['nama_kejuaraan'] ?? '') > 30 ? '...' : ''); ?></td>
                                <td><?php echo $row['level_kompetisi']; ?></td>
                                <td><span class="status-badge status-<?php echo $row['status']; ?>"><?php echo $row['status'] == 'diproses' ? 'Diproses' : ($row['status'] == 'disetujui' ? 'Disetujui' : 'Ditolak'); ?></span></td>
                                <td><a href="detail_pengajuan.php?id=<?php echo $row['id']; ?>" class="btn-detail">Detail</a></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" style="text-align:center; padding:40px; color: #6a7e9e;">Belum ada pengajuan</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
</html>