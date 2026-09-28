<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="burger-menu"><div class="burger-lines"></div></div>
<div class="admin-sidebar sidebar">
    <div class="sidebar-header">
        <h3>🎓 SMB</h3>
        <p>Sistem Mahasiswa Berprestasi</p>
    </div>
    <div class="sidebar-menu">
        <a href="dashboard.php" class="menu-item <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
            <span>📊</span> <span class="menu-text">Dashboard</span>
        </a>
        <a href="pengajuan.php" class="menu-item <?php echo $current_page == 'pengajuan.php' ? 'active' : ''; ?>">
            <span>📝</span> <span class="menu-text">Pengajuan Prestasi</span>
        </a>
        <a href="status.php" class="menu-item <?php echo $current_page == 'status.php' ? 'active' : ''; ?>">
            <span>📋</span> <span class="menu-text">Status Pengajuan</span>
        </a>
        <a href="../logout.php" class="menu-item menu-logout">
            <span>🚪</span> <span class="menu-text">Logout</span>
        </a>
    </div>
</div>