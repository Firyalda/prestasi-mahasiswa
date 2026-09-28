<!-- ===== SIDEBAR VERIFIKATOR ===== -->

<!-- Burger Menu (selalu tampil) -->
<button class="burger-menu" id="burgerMenu" aria-label="Toggle sidebar">
    <i class="fas fa-bars"></i>
</button>

<!-- Overlay (hanya untuk mobile) -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
     <div class="logo">
        <span class="logo-icon">📊</span>
        <span class="logo-text">SMB</span>
    </div>
    <ul class="menu">
        <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
            <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a>
        </li>
        <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'review_berkas.php' ? 'active' : ''; ?>">
            <a href="review_berkas.php"><i class="fas fa-file-alt"></i> <span>Review Berkas</span></a>
        </li>
        <!-- <li>
            <a href="profil.php"><i class="fas fa-user"></i> <span>Profil</span></a>
        </li> -->
        <li class="logout">
            <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a>
        </li>
    </ul>
</div>

<style>
/* ============================================================
   SIDEBAR VERIFIKATOR - STYLE LENGKAP DENGAN BURGER TOGGLE
   ============================================================ */

/* -------------------- BURGER MENU -------------------- */
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
    display: flex; /* selalu tampil di semua ukuran */
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
   LOGO - pastikan tidak tertutup toggle
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
.logo .logo-text {
    color: #fbbf24;
    transition: opacity 0.2s;
}
.logo .logo-icon {
    font-size: 28px;
}

/* -------------------- SIDEBAR -------------------- */
.sidebar {
    width: 250px;
    background: #0f172a;
    color: #e2e8f0;
    padding: 24px 20px;
    min-height: 100vh;
    position: fixed;
    top: 0;
    left: 0;
    bottom: 0;
    overflow-y: auto;
    transition: width 0.3s ease, transform 0.3s ease;
    z-index: 1000;
    /* default terbuka */
}

/* Saat sidebar di-collapse (hanya icon) - untuk desktop */
.sidebar.collapsed {
    width: 70px;
    padding: 20px 8px;
}

/* Sembunyikan teks dan span di dalam logo dan menu saat collapsed */
.sidebar.collapsed .logo span,
.sidebar.collapsed .menu li a span {
    display: none;
}

/* Atur logo agar tetap terlihat (tapi disingkat) */
.sidebar.collapsed .logo {
    font-size: 28px; /* perbesar icon saja */
    text-align: center;
    padding-bottom: 16px;
    border-bottom: 1px solid #1e293b;
}
/* Hapus teks "SMB" dari logo saat collapsed (karena kita tampilkan hanya emoji) */
.sidebar.collapsed .logo span {
    display: none; /* sudah disembunyikan di atas */
}
/* Opsional: tambahkan teks singkat "SMB" sebagai pseudo-element */
.sidebar.collapsed .logo::after {
    content: "SMB";
    display: block;
    font-size: 14px;
    font-weight: 700;
    color: #fbbf24;
    margin-top: 4px;
}

/* Atur ulang item menu saat collapsed */
.sidebar.collapsed .menu li {
    padding: 12px 0;
    text-align: center;
}
.sidebar.collapsed .menu li a {
    justify-content: center;
    gap: 0;
}
.sidebar.collapsed .menu li a i {
    font-size: 20px;
    margin-right: 0;
}
/* Sembunyikan teks logout */
.sidebar.collapsed .menu li.logout {
    margin-top: 30px;
    padding-top: 12px;
}
.sidebar.collapsed .menu li.logout a span {
    display: none;
}

/* -------------------- OVERLAY (mobile) -------------------- */
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

/* -------------------- RESPONSIVE (mobile) -------------------- */
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
        padding: 24px 20px;
    }
    .sidebar.collapsed.active {
        transform: translateX(0);
    }
    .sidebar.collapsed .logo span,
    .sidebar.collapsed .menu li a span {
        display: inline; /* tampilkan kembali semua teks */
    }
    .sidebar.collapsed .logo {
        font-size: 22px;
        text-align: left;
    }
    .sidebar.collapsed .logo::after {
        display: none;
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
    .sidebar.collapsed .menu li.logout a span {
        display: inline;
    }
    .burger-menu {
        left: 15px;
    }
}

/* -------------------- STYLE ASLI SIDEBAR (tetap) -------------------- */
.sidebar .logo {
    font-size: 22px;
    font-weight: 700;
    color: #f8fafc;
    margin-bottom: 30px;
    padding-bottom: 16px;
    border-bottom: 1px solid #1e293b;
    transition: all 0.3s;
}
.sidebar .logo span {
    color: #fbbf24;
}
.sidebar .menu {
    list-style: none;
    padding: 0;
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
</style>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const burger = document.getElementById("burgerMenu");
    const sidebar = document.getElementById("sidebar");
    const overlay = document.getElementById("sidebarOverlay");

    function toggleSidebar() {
        if (window.innerWidth > 768) {
            // Desktop: toggle collapsed (mode icon)
            sidebar.classList.toggle("collapsed");
            overlay.classList.remove("active");
        } else {
            // Mobile: toggle active (muncul/hilang)
            sidebar.classList.toggle("active");
            overlay.classList.toggle("active");
            // Pastikan class collapsed tidak mengganggu
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

    // Saat resize window, reset state jika perlu
    window.addEventListener("resize", function () {
        if (window.innerWidth > 768) {
            if (sidebar.classList.contains("active")) {
                sidebar.classList.remove("active");
                overlay.classList.remove("active");
            }
            // Jika sidebar dalam keadaan collapsed di desktop, biarkan
        } else {
            if (sidebar.classList.contains("collapsed")) {
                sidebar.classList.remove("collapsed");
            }
        }
    });
});
</script>