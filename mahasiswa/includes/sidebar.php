<!-- Burger Menu -->
<button class="burger-menu" id="burgerMenu" type="button" aria-label="Buka menu">
    <i class="fas fa-bars"></i>
</button>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">

    <div class="sidebar-header">
        <h2>SMB</h2>
        <p>Sistem Mahasiswa Berprestasi</p>
    </div>

    <!-- Dashboard -->
    <a href="dashboard.php"
       class="<?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>">
        <i class="fas fa-tachometer-alt"></i>
        <span>Dashboard</span>
    </a>

    <!-- Pengajuan Prestasi -->
    <a href="pengajuan.php"
       class="<?= basename($_SERVER['PHP_SELF']) == 'pengajuan.php' ? 'active' : '' ?>">
        <i class="fas fa-file-alt"></i>
        <span>Pengajuan Prestasi</span>
    </a>

    <!-- Riwayat Pengajuan -->
    <a href="riwayat.php"
       class="<?= basename($_SERVER['PHP_SELF']) == 'riwayat.php' ? 'active' : '' ?>">
        <i class="fas fa-history"></i>
        <span>Riwayat Pengajuan</span>
    </a>

    <!-- Profil -->
    <a href="profil.php"
       class="<?= basename($_SERVER['PHP_SELF']) == 'profil.php' ? 'active' : '' ?>">
        <i class="fas fa-user"></i>
        <span>Profil Saya</span>
    </a>

    <!-- Logout -->
    <a href="../logout.php" class="menu-logout">
        <i class="fas fa-sign-out-alt"></i>
        <span>Logout</span>
    </a>

</div>

<!-- Overlay untuk HP -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<style>
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
       SIDEBAR DESKTOP
       ========================= */
    .sidebar {
        position: fixed;
        left: 0;
        top: 0;
        width: 260px;
        height: 100vh;

        background: linear-gradient(
            135deg,
            #1e3c72 0%,
            #2a5298 100%
        );

        padding: 20px 0;
        overflow-y: auto;

        box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);

        z-index: 1000;

        transition: transform 0.3s ease;
    }

    /* Header Sidebar */
    .sidebar-header {
        text-align: center;
        margin-bottom: 25px;
        padding: 0 20px 15px 20px;

        border-bottom: 1px solid rgba(255, 255, 255, 0.2);
    }

    .sidebar-header h2 {
        font-size: 28px;
        font-weight: 700;
        color: #ffffff;
    }

    .sidebar-header p {
        font-size: 11px;
        color: #c5d5ea;
    }

    /* Menu Sidebar */
    .sidebar a {
        display: flex;
        align-items: center;

        padding: 12px 20px;

        color: #c5d5ea;
        text-decoration: none;

        font-size: 14px;
        font-weight: 500;

        transition: all 0.3s ease;

        position: relative;
        z-index: 1001;
    }

    .sidebar a i {
        width: 24px;
        margin-right: 12px;
        color: #c5d5ea;
        flex-shrink: 0;
    }

    .sidebar a:hover {
        background: rgba(255, 255, 255, 0.1);
        color: #ffffff;
        padding-left: 25px;
    }

    .sidebar a.active {
        background: rgba(255, 255, 255, 0.15);
        color: #ffffff;

        border-left: 3px solid #ffc107;
    }

    .sidebar a.active i {
        color: #ffc107;
    }

    /* Logout */
    .sidebar a.menu-logout {
        cursor: pointer;
    }

    /* =========================
       BURGER BUTTON
       ========================= */
    .burger-menu {
        position: fixed;

        top: 15px;
        left: 15px;

        width: 45px;
        height: 45px;

        background: #2c7da0;
        color: #ffffff;

        border: none;
        border-radius: 8px;

        cursor: pointer;

        display: flex;
        justify-content: center;
        align-items: center;

        font-size: 22px;

        z-index: 1100;

        transition: all 0.3s ease;

        pointer-events: auto;
    }

    .burger-menu:hover {
        background: #226580;
    }

    /* =========================
       OVERLAY
       ========================= */
    .sidebar-overlay {
        display: none;

        position: fixed;

        top: 0;
        left: 0;

        width: 100%;
        height: 100vh;

        background: rgba(0, 0, 0, 0.45);

        z-index: 900;

        cursor: pointer;
    }

    /* =========================
       DESKTOP
       ========================= */
    @media (min-width: 769px) {

        .burger-menu {
            display: none;
        }

        .sidebar-overlay {
            display: none !important;
        }
    }

    /* =========================
       MOBILE / HP
       ========================= */
    @media (max-width: 768px) {

        /* Sidebar disembunyikan ke kiri */
        .sidebar {
            width: 260px;

            transform: translateX(-100%);

            z-index: 1000;
        }

        /* Sidebar ketika burger dibuka */
        .sidebar.active {
            transform: translateX(0);
        }

        /* Teks menu tetap muncul */
        .sidebar a span {
            display: inline;
        }

        .sidebar .sidebar-header h2,
        .sidebar .sidebar-header p {
            display: block;
        }

        /* Overlay ketika sidebar terbuka */
        .sidebar-overlay.active {
            display: block;
        }

        /* Konten utama memenuhi layar */
        .main-content {
            width: 100%;
            margin-left: 0 !important;

            padding: 70px 16px 16px;

            overflow-x: hidden;
        }

        /* Statistik */
        .stats-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        /* Menu dashboard */
        .menu-grid {
            grid-template-columns: 1fr;
        }

        /* Header */
        .top-bar {
            flex-direction: column;
            gap: 12px;
            text-align: center;
        }

        .user-info .user-name {
            display: none;
        }

        /* Modal */
        .modal-content {
            padding: 25px 20px;
        }
    }

    /* =========================
       HP KECIL
       ========================= */
    @media (max-width: 420px) {

        .main-content {
            padding-left: 12px;
            padding-right: 12px;
        }

        .stats-grid {
            grid-template-columns: 1fr;
        }

        .sidebar {
            width: 250px;
        }
    }
</style>

<script>
document.addEventListener("DOMContentLoaded", function () {

    const burger = document.getElementById("burgerMenu");
    const sidebar = document.getElementById("sidebar");
    const overlay = document.getElementById("sidebarOverlay");

    if (!burger || !sidebar || !overlay) {
        return;
    }

    /* Buka / tutup sidebar */
    burger.addEventListener("click", function (event) {

        event.stopPropagation();

        sidebar.classList.toggle("active");
        overlay.classList.toggle("active");

    });

    /* Klik overlay = tutup sidebar */
    overlay.addEventListener("click", function () {

        sidebar.classList.remove("active");
        overlay.classList.remove("active");

    });

    /* Klik menu = tutup sidebar */
    const menuLinks = sidebar.querySelectorAll("a");

    menuLinks.forEach(function (link) {

        link.addEventListener("click", function () {

            sidebar.classList.remove("active");
            overlay.classList.remove("active");

        });

    });

});
</script>