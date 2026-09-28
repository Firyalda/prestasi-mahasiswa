<!-- sidebar.php -->
<!-- Burger Menu -->
<button class="burger-menu" id="burgerMenu">
    <i class="fas fa-bars"></i>
</button>

<!-- Overlay (hanya untuk mobile) -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- SIDEBAR -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <h2>SMB</h2>
        <p>Sistem Mahasiswa Berprestasi</p>
    </div>
    <a href="dashboard.php" <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'class="active"' : ''; ?>>
        <i class="fas fa-tachometer-alt"></i> <span>Dashboard</span>
    </a>
    <a href="verifikasi.php" <?php echo basename($_SERVER['PHP_SELF']) == 'verifikasi.php' ? 'class="active"' : ''; ?>>
        <i class="fas fa-check-circle"></i> <span>Verifikasi Pengajuan</span>
    </a>
    <a href="semua_pengajuan.php" <?php echo basename($_SERVER['PHP_SELF']) == 'semua_pengajuan.php' ? 'class="active"' : ''; ?>>
        <i class="fas fa-list"></i> <span>Semua Pengajuan</span>
    </a>
    <a href="kategori_reward.php" <?php echo basename($_SERVER['PHP_SELF']) == 'kategori_reward.php' ? 'class="active"' : ''; ?>>
        <i class="fas fa-tag"></i> <span>Kategori Reward</span>
    </a>
    <a href="data_mahasiswa.php" <?php echo basename($_SERVER['PHP_SELF']) == 'data_mahasiswa.php' ? 'class="active"' : ''; ?>>
        <i class="fas fa-users"></i> <span>Data Mahasiswa</span>
    </a>
    <a href="laporan_prestasi.php" <?php echo basename($_SERVER['PHP_SELF']) == 'laporan_prestasi.php' ? 'class="active"' : ''; ?>>
        <i class="fas fa-chart-bar"></i> <span>Laporan Prestasi</span>
    </a>
    <a href="aturan_periode.php" <?php echo basename($_SERVER['PHP_SELF']) == 'aturan_periode.php' ? 'class="active"' : ''; ?>>
        <i class="fas fa-calendar-alt"></i> <span>Aturan Periode</span>
    </a>
    <a href="profil_saya.php" <?php echo basename($_SERVER['PHP_SELF']) == 'profil_saya.php' ? 'class="active"' : ''; ?>>
        <i class="fas fa-user"></i> <span>Profil Saya</span>
    </a>
    <a href="../logout.php" style="margin-top: 40px;">
        <i class="fas fa-sign-out-alt"></i> <span>Logout</span>
    </a>
</div>

<style>
/* =========================
   RESET & GLOBAL
========================= */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}
body {
    font-family: 'Segoe UI', system-ui, sans-serif;
    background: #f4f6f9;
    color: #1a1a1a;
}

/* =========================
   BURGER MENU
========================= */
.burger-menu {
    position: fixed;
    top: 15px;
    left: 15px;
    width: 45px;
    height: 45px;
    background: linear-gradient(135deg, #1a365d, #2a5298);
    color: #fff;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    display: flex;
    justify-content: center;
    align-items: center;
    font-size: 22px;
    z-index: 9999;
    transition: left 0.3s ease;
    box-shadow: 0 2px 10px rgba(26, 54, 93, 0.3);
}
.burger-menu:hover {
    background: linear-gradient(135deg, #2a5298, #1a365d);
    transform: scale(1.05);
}

/* =========================
   SIDEBAR
========================= */
.sidebar {
    position: fixed;
    top: 0;
    left: 0;
    width: 260px;
    height: 100vh;
    background: linear-gradient(180deg, #1a365d 0%, #2a5298 100%);
    border-right: 1px solid rgba(255, 255, 255, 0.1);
    overflow-y: auto;
    overflow-x: hidden;
    padding: 20px 0;
    transition: width 0.3s ease, transform 0.3s ease;
    z-index: 1000;
    box-shadow: 2px 0 15px rgba(26, 54, 93, 0.2);
}

.sidebar::-webkit-scrollbar {
    width: 4px;
}
.sidebar::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.1);
}
.sidebar::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.3);
    border-radius: 10px;
}

/* Mode collapsed (hanya icon) - untuk desktop */
.sidebar.collapsed {
    width: 70px;
}
.sidebar.collapsed .sidebar-header h2,
.sidebar.collapsed .sidebar-header p,
.sidebar.collapsed a span {
    display: none;
}
.sidebar.collapsed a {
    padding: 12px 0;
    justify-content: center;
}
.sidebar.collapsed a i {
    margin-right: 0;
    font-size: 20px;
}
.sidebar.collapsed .sidebar-header {
    padding: 0 0 15px 0;
}
/* Ikon pengganti judul saat collapsed */
.sidebar.collapsed .sidebar-header::after {
    content: "\f0c9";
    font-family: "Font Awesome 6 Free";
    font-weight: 900;
    font-size: 28px;
    display: block;
    text-align: center;
    color: #60a5fa;
    padding-bottom: 15px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

/* =========================
   SIDEBAR HEADER
========================= */
.sidebar-header {
    text-align: center;
    margin-bottom: 25px;
    padding: 0 20px 15px 20px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    transition: padding 0.3s;
}
.sidebar-header h2 {
    font-size: 28px;
    font-weight: 700;
    margin-bottom: 5px;
    color: #ffffff;
    text-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
.sidebar-header p {
    font-size: 11px;
    color: rgba(255, 255, 255, 0.7);
    font-weight: 300;
}

/* =========================
   SIDEBAR LINKS
========================= */
.sidebar a {
    display: flex;
    align-items: center;
    padding: 12px 20px;
    color: rgba(255, 255, 255, 0.7);
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.2s;
    white-space: nowrap;
    margin: 2px 12px;
    border-radius: 8px;
}
.sidebar a i {
    width: 24px;
    margin-right: 12px;
    color: rgba(255, 255, 255, 0.5);
    font-size: 18px;
    text-align: center;
    flex-shrink: 0;
    transition: all 0.2s;
}
.sidebar a span {
    display: inline-block;
}
.sidebar a:hover {
    background: rgba(255, 255, 255, 0.1);
    color: #ffffff;
}
.sidebar a:hover i {
    color: #60a5fa;
}
.sidebar a.active {
    background: rgba(255, 255, 255, 0.15);
    color: #ffffff;
    border-left: 3px solid #60a5fa;
    box-shadow: inset 0 0 20px rgba(255, 255, 255, 0.05);
}
.sidebar a.active i {
    color: #60a5fa;
}

/* =========================
   OVERLAY (hanya untuk mobile)
========================= */
.sidebar-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.5);
    display: none;
    z-index: 999;
    backdrop-filter: blur(4px);
}
.sidebar-overlay.active {
    display: block;
}

/* =========================
   MAIN CONTENT
========================= */
.main-content {
    margin-left: 260px;
    padding: 30px 35px;
    min-height: 100vh;
    transition: margin-left 0.3s ease;
}

/* Saat sidebar collapsed, geser margin */
.sidebar.collapsed ~ .main-content,
.sidebar.collapsed + .main-content {
    margin-left: 70px;
}

/* =========================
   RESPONSIVE (Mobile)
========================= */
@media (max-width: 768px) {
    .sidebar {
        width: 260px !important;
        transform: translateX(-100%);
        transition: transform 0.3s ease;
    }
    .sidebar.active {
        transform: translateX(0);
    }
    /* Nonaktifkan efek collapsed di mobile */
    .sidebar.collapsed {
        width: 260px !important;
        transform: translateX(-100%);
    }
    .sidebar.collapsed.active {
        transform: translateX(0);
    }
    .sidebar.collapsed .sidebar-header h2,
    .sidebar.collapsed .sidebar-header p,
    .sidebar.collapsed a span {
        display: block;
    }
    .sidebar.collapsed a {
        padding: 12px 20px;
        justify-content: flex-start;
    }
    .sidebar.collapsed a i {
        margin-right: 12px;
        font-size: 18px;
    }
    .sidebar.collapsed .sidebar-header {
        padding: 0 20px 15px 20px;
    }
    .sidebar.collapsed .sidebar-header::after {
        display: none;
    }
    .main-content {
        margin-left: 0 !important;
    }
    .burger-menu {
        left: 15px;
    }
}
</style>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const burger = document.getElementById("burgerMenu");
    const sidebar = document.getElementById("sidebar");
    const overlay = document.getElementById("sidebarOverlay");
    const mainContent = document.getElementById("mainContent");

    function toggleSidebar() {
        if (window.innerWidth > 768) {
            // Desktop: toggle collapsed
            sidebar.classList.toggle("collapsed");
            // Overlay tidak dipakai di desktop
            overlay.classList.remove("active");
        } else {
            // Mobile: toggle active (muncul/hilang)
            sidebar.classList.toggle("active");
            overlay.classList.toggle("active");
            // Pastikan collapsed tidak mengganggu
            sidebar.classList.remove("collapsed");
        }
    }

    burger.addEventListener("click", toggleSidebar);

    overlay.addEventListener("click", function () {
        if (window.innerWidth <= 768) {
            sidebar.classList.remove("active");
            overlay.classList.remove("active");
        }
    });

    // Saat resize, reset state jika perlu
    window.addEventListener("resize", function () {
        if (window.innerWidth > 768) {
            // Jika sidebar dalam mode active (mobile), hapus
            if (sidebar.classList.contains("active")) {
                sidebar.classList.remove("active");
                overlay.classList.remove("active");
            }
            // Jika sidebar collapsed di desktop, biarkan
        } else {
            // Di mobile, jika sidebar sedang collapsed, ubah ke mode mobile
            if (sidebar.classList.contains("collapsed")) {
                sidebar.classList.remove("collapsed");
            }
        }
    });
});
</script>