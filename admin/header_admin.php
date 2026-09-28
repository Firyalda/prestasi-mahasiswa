<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Dashboard Admin - SMB'; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Segoe+UI:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        /* ===== TAMBAHAN UNTUK BURGER & NAVBAR ===== */
        
        /* OVERRIDE NAVBAR DARI STYLE.CSS */
        .navbar {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%) !important;
            padding: 12px 24px !important;
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
            z-index: 1001 !important;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1) !important;
            height: 60px !important;
            margin-bottom: 0 !important;
        }
        .navbar-brand {
            color: white !important;
            font-size: 18px !important;
            font-weight: 700 !important;
            text-decoration: none !important;
            -webkit-text-fill-color: white !important;
            background: none !important;
        }
        .navbar-brand:hover { color: #ffc107 !important; }
        .navbar-right {
            display: flex !important;
            align-items: center !important;
            gap: 16px !important;
        }
        .navbar-right span {
            color: #c5d5ea !important;
            font-size: 14px !important;
        }
        .navbar .btn-logout {
            background: rgba(255,255,255,0.15) !important;
            color: white !important;
            padding: 6px 16px !important;
            border-radius: 8px !important;
            text-decoration: none !important;
            font-size: 13px !important;
            font-weight: 500 !important;
            transition: all 0.3s ease !important;
            border: 1px solid rgba(255,255,255,0.1) !important;
        }
        .navbar .btn-logout:hover {
            background: rgba(255,255,255,0.25) !important;
            color: white !important;
        }

        /* ===== TOMBOL BURGER ===== */
        .burger-btn {
            display: none !important;
            background: none !important;
            border: none !important;
            color: white !important;
            font-size: 24px !important;
            cursor: pointer !important;
            padding: 4px 10px !important;
            border-radius: 6px !important;
        }
        .burger-btn:hover {
            background: rgba(255,255,255,0.15) !important;
        }

        /* ===== OVERLAY ===== */
        .sidebar-overlay {
            display: none !important;
            position: fixed !important;
            top: 60px !important;
            left: 0 !important;
            width: 100% !important;
            height: calc(100vh - 60px) !important;
            background: rgba(0,0,0,0.4) !important;
            z-index: 999 !important;
        }
        .sidebar-overlay.active {
            display: block !important;
        }

        /* ===== SIDEBAR - PAKAI DARI STYLE.CSS ===== */
        /* Kita pakai .sidebar dari style.css, tapi tambahkan top:60px */
        .sidebar {
            top: 60px !important;
            height: calc(100vh - 60px) !important;
            transition: transform 0.3s ease !important;
            z-index: 1000 !important;
        }

        /* ===== MAIN CONTENT ===== */
        .main-content {
            margin-left: 260px !important;
            padding: 84px 32px 32px 32px !important;
            min-height: 100vh !important;
            transition: margin-left 0.3s ease !important;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%) !important;
            }
            .sidebar.open {
                transform: translateX(0) !important;
            }
            .main-content {
                margin-left: 0 !important;
                padding: 76px 16px 16px 16px !important;
            }
            .burger-btn {
                display: block !important;
            }
            .navbar-right span {
                display: none !important;
            }
        }

        @media (max-width: 480px) {
            .navbar { padding: 10px 16px !important; }
            .navbar-brand { font-size: 15px !important; }
            .main-content { padding: 70px 12px 12px 12px !important; }
            .burger-btn { font-size: 20px !important; }
            .navbar .btn-logout { padding: 4px 12px !important; font-size: 12px !important; }
        }
    </style>
</head>
<body>

<!-- ===== NAVBAR ===== -->
<nav class="navbar">
    <button class="burger-btn" onclick="toggleSidebar()" aria-label="Toggle Menu">
        <i class="fas fa-bars"></i>
    </button>
    <a href="dashboard.php" class="navbar-brand">🎓 SMB - ADMIN</a>
    <div class="navbar-right">
        <span>👋 Halo, <?php echo htmlspecialchars($_SESSION['nama'] ?? 'Admin'); ?></span>
        <a href="../logout.php" class="btn-logout">🚪 Logout</a>
    </div>
</nav>

<!-- ===== OVERLAY ===== -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

<!-- ===== SIDEBAR - DARI STYLE.CSS ===== -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <h2>SMB</h2>
        <p>Sistem Mahasiswa Berprestasi</p>
    </div>
    <a href="dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
        <i class="fas fa-tachometer-alt"></i> <span>Dashboard</span>
    </a>
    <a href="verifikasi.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'verifikasi.php' ? 'active' : ''; ?>">
        <i class="fas fa-check-circle"></i> <span>Verifikasi Pengajuan</span>
    </a>
    <a href="semua_pengajuan.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'semua_pengajuan.php' ? 'active' : ''; ?>">
        <i class="fas fa-list"></i> <span>Semua Pengajuan</span>
    </a>
    <a href="kategori_reward.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'kategori_reward.php' ? 'active' : ''; ?>">
        <i class="fas fa-tag"></i> <span>Kategori Reward</span>
    </a>
    <a href="data_mahasiswa.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'data_mahasiswa.php' ? 'active' : ''; ?>">
        <i class="fas fa-users"></i> <span>Data Mahasiswa</span>
    </a>
    <a href="laporan_prestasi.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'laporan_prestasi.php' ? 'active' : ''; ?>">
        <i class="fas fa-chart-bar"></i> <span>Laporan Prestasi</span>
    </a>
    <!-- ===== MENU BARU: ATURAN PERIODE ===== -->
    <a href="aturan_periode.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'aturan_periode.php' ? 'active' : ''; ?>">
        <i class="fas fa-calendar-alt"></i> <span>Aturan Periode</span>
    </a>
    <!-- ==================================== -->
    <a href="profil_saya.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'profil_saya.php' ? 'active' : ''; ?>">
        <i class="fas fa-user"></i> <span>Profil Saya</span>
    </a>
    <a href="../logout.php" style="margin-top: 40px;">
        <i class="fas fa-sign-out-alt"></i> <span>Logout</span>
    </a>
</div>

<!-- ===== MAIN CONTENT ===== -->
<div class="main-content">

<script>
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('open');
        document.getElementById('sidebarOverlay').classList.toggle('active');
    }
    function closeSidebar() {
        document.getElementById('sidebar').classList.remove('open');
        document.getElementById('sidebarOverlay').classList.remove('active');
    }
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            closeSidebar();
        }
    });
</script>