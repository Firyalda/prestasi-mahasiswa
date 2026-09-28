<!-- Burger Menu (selalu tampil) -->
<button class="burger-menu" id="burgerMenu" aria-label="Toggle sidebar">
    <i class="fas fa-bars"></i>
</button>

<!-- Overlay (hanya untuk mobile) -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<div class="sidebar" id="sidebar">
    <div class="logo">📊 <span>SMB</span></div>
    <ul class="menu">
        <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
            <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a>
        </li>
        <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'ranking_kandidat.php' ? 'active' : ''; ?>">
            <a href="ranking_kandidat.php"><i class="fas fa-trophy"></i> <span>Ranking</span></a>
        </li>
        <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'laporan.php' ? 'active' : ''; ?>">
            <a href="laporan.php"><i class="fas fa-file-pdf"></i> <span>Laporan</span></a>
        </li>
        <!-- <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'profil.php' ? 'active' : ''; ?>">
            <a href="profil.php"><i class="fas fa-user"></i> <span>Profil</span></a>
        </li> -->
        <li class="logout">
            <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a>
        </li>
    </ul>
</div>

<style>
/* =========================
   BURGER MENU
========================= */
.burger-menu {
    position: fixed;
    top: 15px;
    left: 15px;
    width: 45px;
    height: 45px;
    background: #0f172a;
    color: #fff;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    display: flex;
    justify-content: center;
    align-items: center;
    font-size: 22px;
    z-index: 9999;
    transition: left 0.3s ease, background 0.2s;
}
.burger-menu:hover {
    background: #1e293b;
}

/* =========================
   SIDEBAR (GABUNGAN, TIDAK DUPLIKASI)
========================= */
.sidebar {
    position: fixed;
    top: 0;
    left: 0;
    width: 250px;
    height: 100vh;
    background: #0f172a;
    color: #e2e8f0;
    padding: 24px 20px;
    overflow-y: auto;
    transition: width 0.3s ease, transform 0.3s ease;
    z-index: 1000;
}

/* =========================
   LOGO
========================= */
.logo {
    font-size: 22px;
    font-weight: 700;
    color: #f8fafc;
    margin-bottom: 30px;
    padding-bottom: 16px;
    border-bottom: 1px solid #1e293b;
    display: flex;
    align-items: center;
    gap: 8px;
    white-space: nowrap;
}
.logo span {
    color: #fbbf24;
}

/* =========================
   MENU
========================= */
.sidebar .menu {
    list-style: none;
    padding: 0;
    margin: 0;
}
.sidebar .menu li {
    padding: 10px 14px;
    margin: 2px 0;
    border-radius: 10px;
    cursor: pointer;
    transition: 0.2s;
}
.sidebar .menu li:hover {
    background: #1e293b;
}
.sidebar .menu li.active {
    background: #1e293b;
    color: #fbbf24;
}
.sidebar .menu li a {
    color: #e2e8f0;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 12px;
}
.sidebar .menu li.active a {
    color: #fbbf24;
}
.sidebar .menu li.logout {
    margin-top: 40px;
    border-top: 1px solid #1e293b;
    padding-top: 16px;
}
.sidebar .menu li.logout a {
    color: #f87171;
}

/* =========================
   MODE COLLAPSED (DESKTOP) - SIDEBAR MENJADI ICON SAJA
========================= */
.sidebar.collapsed {
    width: 70px;
    padding: 24px 10px;
}
.sidebar.collapsed .logo span {
    display: none;
}
.sidebar.collapsed .menu li a span {
    display: none;
}
.sidebar.collapsed .menu li {
    padding: 10px 0;
    text-align: center;
}
.sidebar.collapsed .menu li a {
    justify-content: center;
    gap: 0;
}
.sidebar.collapsed .menu li a i {
    font-size: 20px;
    margin: 0;
}
.sidebar.collapsed .logo {
    justify-content: center;
}

/* =========================
   OVERLAY (hanya untuk mobile)
========================= */
.sidebar-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.4);
    display: none;
    z-index: 999;
}
.sidebar-overlay.active {
    display: block;
}

/* =========================
   MAIN CONTENT (opsional, sesuaikan margin)
========================= */
.main-content {
    margin-left: 250px;
    transition: margin-left 0.3s ease;
}
.sidebar.collapsed ~ .main-content,
.sidebar.collapsed + .main-content {
    margin-left: 70px;
}

/* =========================
   RESPONSIVE (MOBILE)
========================= */
@media (max-width: 768px) {
    .sidebar {
        width: 250px !important;
        transform: translateX(-100%);
        transition: transform 0.3s ease;
        padding: 24px 20px;
    }
    .sidebar.active {
        transform: translateX(0);
    }
    /* Nonaktifkan efek collapsed di mobile */
    .sidebar.collapsed {
        width: 250px !important;
        transform: translateX(-100%);
    }
    .sidebar.collapsed.active {
        transform: translateX(0);
    }
    .sidebar.collapsed .logo span {
        display: inline;
    }
    .sidebar.collapsed .menu li a span {
        display: inline;
    }
    .sidebar.collapsed .menu li {
        padding: 10px 14px;
        text-align: left;
    }
    .sidebar.collapsed .menu li a {
        justify-content: flex-start;
        gap: 12px;
    }
    .sidebar.collapsed .menu li a i {
        font-size: 18px;
        margin-right: 12px;
    }
    .sidebar.collapsed .logo {
        justify-content: flex-start;
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

    if (!burger || !sidebar) return;

    function toggleSidebar() {
        if (window.innerWidth > 768) {
            // Desktop: toggle collapsed (mengecil menjadi icon)
            sidebar.classList.toggle("collapsed");
            if (overlay) overlay.classList.remove("active");
        } else {
            // Mobile: toggle active (muncul/hilang)
            sidebar.classList.toggle("active");
            if (overlay) overlay.classList.toggle("active");
            // Hapus class collapsed agar tidak mengganggu
            sidebar.classList.remove("collapsed");
        }
    }

    burger.addEventListener("click", toggleSidebar);

    if (overlay) {
        overlay.addEventListener("click", function () {
            if (window.innerWidth <= 768) {
                sidebar.classList.remove("active");
                overlay.classList.remove("active");
            }
        });
    }

    // Saat resize window, reset state jika perlu
    window.addEventListener("resize", function () {
        if (window.innerWidth > 768) {
            // Jika sidebar dalam mode active (mobile), hapus
            if (sidebar.classList.contains("active")) {
                sidebar.classList.remove("active");
                if (overlay) overlay.classList.remove("active");
            }
        } else {
            // Di mobile, jika sidebar sedang collapsed, hapus collapsed
            if (sidebar.classList.contains("collapsed")) {
                sidebar.classList.remove("collapsed");
            }
        }
    });
});
</script>