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

// ===== PENCARIAN =====
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Ambil filter dari URL (default: semua)
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'semua';

// ===== QUERY DASAR =====
$query_base = "SELECT * FROM users WHERE role='mahasiswa'";

// Filter berdasarkan status pengajuan
if ($filter == 'sudah') {
    $query_base .= " AND id IN (SELECT DISTINCT user_id FROM pengajuan)";
    $filter_label = 'Sudah Mengajukan Prestasi';
} elseif ($filter == 'belum') {
    $query_base .= " AND id NOT IN (SELECT DISTINCT user_id FROM pengajuan)";
    $filter_label = 'Belum Mengajukan Prestasi';
} else {
    $filter_label = 'Semua Mahasiswa';
}

// ===== PENCARIAN =====
if (!empty($search)) {
    $search_escaped = mysqli_real_escape_string($conn, $search);
    $query_base .= " AND (nim LIKE '%$search_escaped%' 
                          OR nama_lengkap LIKE '%$search_escaped%' 
                          OR prodi LIKE '%$search_escaped%'
                          OR username LIKE '%$search_escaped%'
                          OR email LIKE '%$search_escaped%')";
}

$query = $query_base . " ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);
$total = mysqli_num_rows($result);

// Statistik untuk badge
$total_semua = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM users WHERE role='mahasiswa'"))['t'];
$total_sudah = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(DISTINCT user_id) as t FROM pengajuan"))['t'];
$total_belum = $total_semua - $total_sudah;

include 'includes/sidebar.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Mahasiswa - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', system-ui, sans-serif; background: #f4f6f9; color: #1a1a1a; }


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
        .date-divider { width: 1px; height: 30px; background: #ddd; }
        .date-text { font-size: 13px; color: #888; }

        /* WEATHER WIDGET DIHAPUS */

        /* ===== FILTER & SEARCH ===== */
        .filter-search-section {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 24px;
            align-items: center;
        }

        .filter-buttons {
            display: flex;
            gap: 15px;
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

        /* ===== SEARCH BOX ===== */
        .search-box {
            display: flex;
            align-items: center;
            gap: 0;
            background: white;
            border: 1px solid #e8e8e8;
            border-radius: 30px;
            padding: 0 4px 0 18px;
            transition: all 0.3s;
            flex: 1;
            max-width: 380px;
            min-width: 180px;
        }

        .search-box:focus-within {
            border-color: #2c7da0;
            box-shadow: 0 0 0 3px rgba(44, 125, 160, 0.1);
        }

        .search-box input {
            border: none;
            padding: 10px 12px;
            font-size: 14px;
            font-weight: 400;
            background: transparent;
            flex: 1;
            outline: none;
            color: #1a2634;
            font-family: 'Segoe UI', system-ui, sans-serif;
        }

        .search-box input::placeholder {
            color: #aab3c0;
        }

        .search-box .btn-search {
            background: #2c7da0;
            color: white;
            border: none;
            padding: 8px 18px;
            border-radius: 30px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s;
            font-family: 'Segoe UI', system-ui, sans-serif;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .search-box .btn-search:hover {
            background: #1a5a7a;
        }

        .search-box .btn-search i {
            font-size: 13px;
        }

        .btn-clear-search {
            background: none;
            border: none;
            color: #999;
            padding: 6px 12px;
            cursor: pointer;
            font-size: 16px;
            display: <?php echo !empty($search) ? 'inline' : 'none'; ?>;
        }

        .btn-clear-search:hover {
            color: #555;
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
            flex-wrap: wrap;
            gap: 10px;
        }
        .table-header h3 { font-size: 15px; font-weight: 600; }
        .table-header h3 .search-keyword {
            font-weight: 400;
            color: #888;
            font-size: 13px;
            margin-left: 8px;
        }
        .badge-count {
            background: #eef2f5; padding: 4px 12px; border-radius: 20px; font-size: 12px;
        }
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table th {
            text-align: left; padding: 10px 16px; background: #fafbfc;
            color: #666; font-weight: 600; font-size: 12px;
            border-bottom: 1px solid #e8e8e8;
        }
        .data-table td {
            padding: 8px 16px; border-bottom: 1px solid #f0f0f0; font-size: 13px;
            vertical-align: middle;
        }
        .status-badge {
            display: inline-block; padding: 3px 10px; border-radius: 20px;
            font-size: 11px; font-weight: 500;
        }
        .status-sudah { background: #e8f5e9; color: #2e7d32; }
        .status-belum { background: #ffebee; color: #c62828; }
        .badge-new {
            background: #e3f2fd; color: #1565c0;
            font-size: 9px; padding: 2px 8px; border-radius: 20px;
            margin-left: 8px; font-weight: 500;
        }
        .avatar-small {
            width: 32px; height: 32px; border-radius: 50%;
            background: #2c7da0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 12px;
        }
        .avatar-small img {
            width: 32px; height: 32px; border-radius: 50%;
            object-fit: cover;
        }

        .btn-detail {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: #2c7da0;
            color: white;
            padding: 5px 14px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 12px;
            font-weight: 500;
            transition: all 0.25s ease;
            border: none;
            cursor: pointer;
            white-space: nowrap;
        }

        .btn-detail i {
            font-size: 12px;
            color: white;
        }

        .btn-detail:hover {
            background: #1a5a7a;
            transform: translateY(-1px);
            box-shadow: 0 3px 10px rgba(44, 125, 160, 0.25);
        }

        .btn-detail:active {
            transform: translateY(0);
        }

        .empty-state { text-align: center; padding: 40px; color: #999; }
        .empty-state i { font-size: 40px; color: #ccc; display: block; margin-bottom: 10px; }


        @media (max-width: 600px) {
            .btn-detail {
                padding: 4px 10px;
                font-size: 11px;
            }
            .btn-detail i {
                font-size: 11px;
            }
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
            <h2>Data Mahasiswa</h2>
            <p>Daftar mahasiswa yang telah registrasi</p>
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
    <!-- FILTER & SEARCH                                           -->
    <!-- ========================================================= -->
    <div class="filter-search-section">
        <div class="filter-buttons">
            <a href="?filter=semua<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" class="filter-btn <?php echo $filter == 'semua' ? 'active' : ''; ?>">
                <i class="fas fa-users"></i> Semua Mahasiswa
                <span class="filter-badge"><?php echo $total_semua; ?></span>
            </a>
            <a href="?filter=sudah<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" class="filter-btn <?php echo $filter == 'sudah' ? 'active' : ''; ?>">
                <i class="fas fa-check-circle"></i> Sudah Mengajukan
                <span class="filter-badge"><?php echo $total_sudah; ?></span>
            </a>
            <a href="?filter=belum<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" class="filter-btn <?php echo $filter == 'belum' ? 'active' : ''; ?>">
                <i class="fas fa-clock"></i> Belum Mengajukan
                <span class="filter-badge"><?php echo $total_belum; ?></span>
            </a>
        </div>

        <!-- SEARCH BOX -->
        <form method="GET" action="" class="search-box">
            <input type="hidden" name="filter" value="<?php echo $filter; ?>">
            <input type="text" name="search" placeholder="Cari NIM, Nama, Prodi..." value="<?php echo htmlspecialchars($search); ?>">
            <?php if (!empty($search)): ?>
                <a href="?filter=<?php echo $filter; ?>" class="btn-clear-search" title="Hapus pencarian">
                    <i class="fas fa-times-circle"></i>
                </a>
            <?php endif; ?>
            <button type="submit" class="btn-search">
                <i class="fas fa-search"></i> Cari
            </button>
        </form>
    </div>

    <!-- ========================================================= -->
    <!-- TABLE                                                    -->
    <!-- ========================================================= -->
    <div class="table-card">
        <div class="table-header">
            <h3>
                Daftar Mahasiswa - <?php echo $filter_label; ?>
                <?php if (!empty($search)): ?>
                    <span class="search-keyword">(Pencarian: "<?php echo htmlspecialchars($search); ?>")</span>
                <?php endif; ?>
            </h3>
            <div class="badge-count">Total: <?php echo $total; ?> mahasiswa</div>
        </div>
        <div style="overflow-x: auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Foto</th>
                        <th>NIM</th>
                        <th>Nama Lengkap</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Prodi</th>
                        <th>No HP</th>
                        <th>Pengajuan</th>
                        <th>Status</th>
                        <th>Tgl Daftar</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    if (mysqli_num_rows($result) > 0):
                        while ($row = mysqli_fetch_assoc($result)):
                            // Cek jumlah pengajuan mahasiswa
                            $cek_pengajuan = mysqli_query($conn, "SELECT COUNT(*) as jml, SUM(CASE WHEN status='disetujui' THEN 1 ELSE 0 END) as disetujui FROM pengajuan WHERE user_id = " . $row['id']);
                            $data_pengajuan = mysqli_fetch_assoc($cek_pengajuan);
                            $jml_pengajuan = $data_pengajuan['jml'];
                            $jml_disetujui = $data_pengajuan['disetujui'];
                            $status_pengajuan = $jml_pengajuan > 0 ? 'Sudah' : 'Belum';
                            $status_class = $jml_pengajuan > 0 ? 'status-sudah' : 'status-belum';
                            $isNew = (strtotime($row['created_at']) > strtotime('-7 days'));
                    ?>
                    <tr>
                        <td style="font-weight: 600;"><?php echo $no++; ?></td>
                        <td>
                            <?php if (!empty($row['foto_profil']) && file_exists("../uploads/profil/" . $row['foto_profil'])): ?>
                                <img src="../uploads/profil/<?php echo $row['foto_profil']; ?>" style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover;">
                            <?php else: ?>
                                <div class="avatar-small">
                                    <?php echo strtoupper(substr($row['nama_lengkap'], 0, 1)); ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($row['nim'] ?? '-'); ?></td>
                        <td>
                            <?php echo htmlspecialchars($row['nama_lengkap']); ?>
                            <?php if ($isNew): ?>
                                <span class="badge-new">Baru</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                        <td class="email-text"><?php echo htmlspecialchars($row['email'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($row['prodi'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($row['no_hp'] ?? '-'); ?></td>
                        <td>
                            <strong><?php echo $jml_pengajuan; ?></strong> pengajuan
                            <?php if ($jml_disetujui > 0): ?>
                                <br><small style="color: #2e7d32;">(<?php echo $jml_disetujui; ?> disetujui)</small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="status-badge <?php echo $status_class; ?>">
                                <?php echo $status_pengajuan; ?>
                            </span>
                        </td>
                        <td><?php echo date('d/m/Y', strtotime($row['created_at'])); ?></td>
                        <td>
                            <a href="detail_mahasiswa.php?id=<?php echo $row['id']; ?>" class="btn-detail">
                                <i class="fas fa-eye"></i> Detail
                            </a>
                        </td>
                    </tr>
                    <?php 
                        endwhile;
                    else:
                    ?>
                    <tr>
                        <td colspan="12" class="empty-state">
                            <?php if (!empty($search)): ?>
                                <i class="fas fa-search"></i>
                                Tidak ada mahasiswa yang cocok dengan pencarian "<strong><?php echo htmlspecialchars($search); ?></strong>"
                                <br><br>
                                <a href="?filter=<?php echo $filter; ?>" style="color:#2c7da0;text-decoration:none;">← Hapus pencarian</a>
                            <?php else: ?>
                                <i class="fas fa-users"></i>
                                Belum ada mahasiswa yang registrasi
                            <?php endif; ?>
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