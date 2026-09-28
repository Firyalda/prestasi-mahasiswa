<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

require_once '../config/database.php';

$nama = $_SESSION['nama'] ?? 'Admin';

// Tandai semua sebagai dibaca
if (isset($_GET['mark_all_read'])) {
    mysqli_query($conn, "UPDATE notifikasi SET is_read = 1 WHERE role = 'admin'");
    header("Location: notifikasi.php");
    exit();
}

// Tandai satu notifikasi sebagai dibaca
if (isset($_GET['read']) && is_numeric($_GET['read'])) {
    $id = (int)$_GET['read'];
    mysqli_query($conn, "UPDATE notifikasi SET is_read = 1 WHERE id = $id");
    header("Location: notifikasi.php");
    exit();
}

// Hapus notifikasi
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM notifikasi WHERE id = $id");
    header("Location: notifikasi.php");
    exit();
}

// Ambil semua notifikasi
$query = "SELECT * FROM notifikasi WHERE role = 'admin' ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);
$total_notif = mysqli_num_rows($result);

include 'includes/sidebar.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifikasi - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #f1f4f8; display: flex; min-height: 100vh; }


        /* ===== MAIN ===== */
        .main-content {
            margin-left: 260px;
            padding: 28px 32px;
            width: 100%;
            min-height: 100vh;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: white;
            padding: 14px 24px;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            margin-bottom: 24px;
        }
        .header h1 { font-size: 20px; font-weight: 600; color: #0f172a; }
        .header .user { display: flex; align-items: center; gap: 14px; font-size: 14px; color: #475569; }
        .header .user .avatar {
            width: 38px; height: 38px; background: #fbbf24; border-radius: 50%;
            display: flex; align-items: center; justify-content: center; font-weight: 600; color: #0f172a; font-size: 16px;
        }

        .btn {
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 13px;
            border: none;
            cursor: pointer;
            transition: 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-primary { background: #3b82f6; color: white; }
        .btn-primary:hover { background: #2563eb; }
        .btn-secondary { background: #e2e8f0; color: #475569; }
        .btn-secondary:hover { background: #cbd5e1; }
        .btn-danger { background: #ef4444; color: white; }
        .btn-danger:hover { background: #dc2626; }
        .btn-sm { padding: 4px 12px; font-size: 12px; }

        .notif-list { display: flex; flex-direction: column; gap: 12px; }
        .notif-item {
            display: flex;
            align-items: flex-start;
            gap: 16px;
            background: white;
            padding: 16px 20px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            transition: 0.2s;
        }
        .notif-item.unread { border-left: 4px solid #3b82f6; background: #f8faff; }
        .notif-item.read { border-left: 4px solid #94a3b8; opacity: 0.7; }
        .notif-item:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.05); }

        .notif-icon {
            width: 40px; height: 40px; background: #dbeafe; border-radius: 50%;
            display: flex; align-items: center; justify-content: center; color: #3b82f6; flex-shrink: 0;
        }
        .notif-content { flex: 1; }
        .notif-title { font-weight: 600; color: #0f172a; font-size: 15px; }
        .notif-pesan { font-size: 14px; color: #475569; margin-top: 4px; line-height: 1.6; white-space: pre-wrap; }
        .notif-time { font-size: 12px; color: #94a3b8; margin-top: 6px; }
        .notif-actions { display: flex; gap: 8px; margin-top: 8px; flex-wrap: wrap; }

        .badge { display: inline-block; padding: 2px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; }
        .badge-primary { background: #dbeafe; color: #1d4ed8; }
        .badge-secondary { background: #e2e8f0; color: #64748b; }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
        }
        .empty-state .icon { font-size: 48px; color: #94a3b8; margin-bottom: 16px; }
        .empty-state h3 { color: #0f172a; margin-bottom: 8px; }
        .empty-state p { color: #94a3b8; }

        .btn-group-top {
            display: flex;
            gap: 12px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

    </style>
</head>
<body>


<!-- ===== MAIN CONTENT ===== -->
<div class="main-content">

    <div class="header">
        <h1>🔔 Notifikasi</h1>
        <div class="user">
            <span><?php echo date('l, d F Y'); ?></span>
            <div class="avatar"><?php echo strtoupper(substr($nama, 0, 1)); ?></div>
            <span><?php echo htmlspecialchars($nama); ?></span>
        </div>
    </div>

    <!-- Tombol Aksi -->
    <div class="btn-group-top">
        <?php if ($total_notif > 0): ?>
            <a href="?mark_all_read=1" class="btn btn-primary">
                <i class="fas fa-check-double"></i> Tandai Semua Dibaca
            </a>
        <?php endif; ?>
        <a href="verifikasi.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    <?php if ($total_notif == 0): ?>
        <div class="empty-state">
            <div class="icon"><i class="fas fa-bell-slash"></i></div>
            <h3>Belum Ada Notifikasi</h3>
            <p>Notifikasi dari Tim Verifikator akan muncul di sini.</p>
        </div>
    <?php else: ?>
        <div class="notif-list">
            <?php while ($notif = mysqli_fetch_assoc($result)): ?>
                <div class="notif-item <?php echo $notif['is_read'] ? 'read' : 'unread'; ?>">
                    <div class="notif-icon">
                        <i class="fas fa-bell"></i>
                    </div>
                    <div class="notif-content">
                        <div class="notif-title">
                            <?php echo htmlspecialchars($notif['judul']); ?>
                            <?php if (!$notif['is_read']): ?>
                                <span class="badge badge-primary">Baru</span>
                            <?php endif; ?>
                        </div>
                        <div class="notif-pesan"><?php echo nl2br(htmlspecialchars($notif['pesan'])); ?></div>
                        <div class="notif-time">
                            <i class="far fa-clock"></i> <?php echo date('d F Y, H:i WIB', strtotime($notif['created_at'])); ?>
                        </div>
                        <div class="notif-actions">
                            <?php if ($notif['link']): ?>
                                <a href="<?php echo $notif['link']; ?>" class="btn btn-primary btn-sm">
                                    <i class="fas fa-eye"></i> Lihat Detail
                                </a>
                            <?php endif; ?>
                            <?php if (!$notif['is_read']): ?>
                                <a href="?read=<?php echo $notif['id']; ?>" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-check"></i> Tandai Dibaca
                                </a>
                            <?php endif; ?>
                            <a href="?delete=<?php echo $notif['id']; ?>" class="btn btn-danger btn-sm" onclick="hapusNotif(event, this.href)">
                                <i class="fas fa-trash"></i> Hapus
                            </a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>

</div>

</body>
<script>
    // SweetAlert2 delete confirmation for notifications
    function hapusNotif(event, href) {
        event.preventDefault();
        Swal.fire({
            title: 'Konfirmasi Hapus',
            text: 'Apakah Anda yakin ingin menghapus notifikasi ini?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6b7a8f',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = href;
            }
        });
    }
</script>
</html>