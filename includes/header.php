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
    <title><?php echo $page_title ?? 'Sistem Pengajuan Prestasi Mahasiswa'; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', system-ui, sans-serif; background: #f4f6f9; color: #1a1a1a; }

        .navbar {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            padding: 12px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1001;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .navbar-brand {
            color: white;
            font-size: 18px;
            font-weight: 700;
            text-decoration: none;
        }
        .navbar-brand:hover { color: #ffc107; }
        .navbar-right {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .navbar-right span {
            color: #c5d5ea;
            font-size: 14px;
        }
        .navbar-right .btn-logout {
            background: rgba(255,255,255,0.15);
            color: white;
            padding: 6px 16px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.3s ease;
            border: 1px solid rgba(255,255,255,0.1);
        }
        .navbar-right .btn-logout:hover {
            background: rgba(255,255,255,0.25);
        }

        .burger-btn {
            display: none;
            background: none;
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
            padding: 4px 10px;
            border-radius: 6px;
        }
        .burger-btn:hover {
            background: rgba(255,255,255,0.1);
        }

        .sidebar {
            position: fixed;
            left: 0;
            top: 60px;
            width: 260px;
            height: calc(100vh - 60px);
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            padding: 20px 0;
            overflow-y: auto;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            z-index: 1000;
            transition: transform 0.3s ease;
        }
        .sidebar a {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: #c5d5ea;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .sidebar a i { width: 24px; margin-right: 12px; color: #c5d5ea; }
        .sidebar a:hover { background: rgba(255,255,255,0.1); color: #ffffff; padding-left: 25px; }
        .sidebar a.active { background: rgba(255,255,255,0.15); color: #ffffff; border-left: 3px solid #ffc107; }
        .sidebar a.active i { color: #ffc107; }
        .sidebar .menu-logout { margin-top: 40px; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 20px; }

        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 60px;
            left: 0;
            width: 100%;
            height: calc(100vh - 60px);
            background: rgba(0,0,0,0.4);
            z-index: 999;
        }
        .sidebar-overlay.active { display: block; }

        .main-content {
            margin-left: 260px;
            padding: 84px 32px 32px 32px;
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.open {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0;
                padding: 76px 16px 16px 16px;
            }
            .burger-btn {
                display: block;
            }
            .navbar-right span {
                display: none;
            }
        }

        @media (max-width: 480px) {
            .navbar { padding: 10px 16px; }
            .navbar-brand { font-size: 15px; }
            .main-content { padding: 70px 12px 12px 12px; }
            .burger-btn { font-size: 20px; }
            .navbar-right .btn-logout { padding: 4px 12px; font-size: 12px; }
        }
    </style>
</head>
<body>

<nav class="navbar">
    <button class="burger-btn" onclick="toggleSidebar()" aria-label="Toggle Menu">
        <i class="fas fa-bars"></i>
    </button>
    <a href="dashboard.php" class="navbar-brand">🎓 PRESTASI MAHASISWA</a>
    <div class="navbar-right">
        <span>👋 Halo, <?php echo htmlspecialchars($_SESSION['nama'] ?? 'User'); ?></span>
        <a href="../logout.php" class="btn-logout">🚪 Logout</a>
    </div>
</nav>

<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

<div class="sidebar" id="sidebar">
    <a href="dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
        <i class="fas fa-tachometer-alt"></i> Dashboard
    </a>
    <a href="pengajuan.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'pengajuan.php' ? 'active' : ''; ?>">
        <i class="fas fa-file-alt"></i> Pengajuan Prestasi
    </a>
    <a href="riwayat.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'riwayat.php' ? 'active' : ''; ?>">
        <i class="fas fa-history"></i> Riwayat Pengajuan
    </a>
    <a href="profil.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'profil.php' ? 'active' : ''; ?>">
        <i class="fas fa-user"></i> Profil Saya
    </a>
    <a href="../logout.php" class="menu-logout">
        <i class="fas fa-sign-out-alt"></i> Logout
    </a>
</div>

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
        if (window.innerWidth > 768) closeSidebar();
    });
</script>