<?php
session_start();

// Cek login dan role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'superadmin') {
    header("Location: ../login.php");
    exit();
}

require_once '../config/database.php';

$user_id = $_SESSION['user_id'];
$nama = $_SESSION['nama'] ?? 'Pimpinan';
$username = $_SESSION['username'] ?? '';
$email = '';

// Ambil data user dari database
$query_user = "SELECT * FROM users WHERE id = $user_id";
$result_user = mysqli_query($conn, $query_user);
$user = mysqli_fetch_assoc($result_user);

if ($user) {
    $email = $user['email'] ?? '';
    $no_hp = $user['no_hp'] ?? '';
    $alamat = $user['alamat'] ?? '';
    $foto_profil = $user['foto_profil'] ?? '';
    $role_display = 'Pimpinan';
} else {
    $email = 'pimpinan@univ.ac.id';
    $no_hp = '-';
    $alamat = '-';
    $foto_profil = '';
    $role_display = 'Pimpinan';
}

// Proses update profil
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_baru = mysqli_real_escape_string($conn, $_POST['nama']);
    $email_baru = mysqli_real_escape_string($conn, $_POST['email']);
    $no_hp_baru = mysqli_real_escape_string($conn, $_POST['no_hp']);
    $alamat_baru = mysqli_real_escape_string($conn, $_POST['alamat']);
    
    // Upload foto profil
    $foto_baru = $foto_profil;
    if (!empty($_FILES['foto_profil']['name'])) {
        $target_dir = "../uploads/profil/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $file_name = time() . '_' . basename($_FILES['foto_profil']['name']);
        $target_file = $target_dir . $file_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array($imageFileType, $allowed_types)) {
            if (move_uploaded_file($_FILES['foto_profil']['tmp_name'], $target_file)) {
                $foto_baru = $file_name;
            }
        }
    }
    
    $query_update = "UPDATE users SET 
                        nama_lengkap = '$nama_baru',
                        email = '$email_baru',
                        no_hp = '$no_hp_baru',
                        alamat = '$alamat_baru',
                        foto_profil = '$foto_baru'
                     WHERE id = $user_id";
    
    if (mysqli_query($conn, $query_update)) {
        $_SESSION['nama'] = $nama_baru;
        $message = '✅ Profil berhasil diperbarui!';
        $message_type = 'success';
        // Refresh data
        $nama = $nama_baru;
        $email = $email_baru;
        $no_hp = $no_hp_baru;
        $alamat = $alamat_baru;
        $foto_profil = $foto_baru;
    } else {
        $message = '❌ Gagal memperbarui profil: ' . mysqli_error($conn);
        $message_type = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Pimpinan - SMB</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; background: #f1f4f8; display: flex; min-height: 100vh; }

        /* ===== SIDEBAR ===== */
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
        }
        .sidebar .logo { font-size: 22px; font-weight: 700; color: #f8fafc; margin-bottom: 30px; padding-bottom: 16px; border-bottom: 1px solid #1e293b; }
        .sidebar .logo span { color: #fbbf24; }
        .sidebar .menu { list-style: none; }
        .sidebar .menu li { padding: 10px 14px; margin: 2px 0; border-radius: 10px; cursor: pointer; transition: 0.2s; }
        .sidebar .menu li:hover { background: #1e293b; }
        .sidebar .menu li.active { background: #1e293b; color: #fbbf24; }
        .sidebar .menu li a { color: #e2e8f0; text-decoration: none; display: flex; align-items: center; gap: 12px; }
        .sidebar .menu li.active a { color: #fbbf24; }
        .sidebar .menu li.logout { margin-top: 40px; border-top: 1px solid #1e293b; padding-top: 16px; }
        .sidebar .menu li.logout a { color: #f87171; }

        /* ===== MAIN CONTENT ===== */
        .main-content { margin-left: 250px; padding: 28px 32px; width: 100%; min-height: 100vh; }

        /* ===== HEADER ===== */
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
            width: 38px; height: 38px; background: #8b5cf6; border-radius: 50%;
            display: flex; align-items: center; justify-content: center; font-weight: 600; color: white; font-size: 16px;
        }

        /* ===== CARD ===== */
        .card { background: white; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); padding: 20px 24px; margin-bottom: 20px; }

        /* ===== NOTIFIKASI ===== */
        .alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; font-size: 14px; display: flex; align-items: center; gap: 10px; }
        .alert-success { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
        .alert-error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }

        /* ===== PROFIL ===== */
        .profile-header {
            display: flex;
            align-items: center;
            gap: 24px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .profile-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: #eef2f6;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            font-weight: 700;
            color: #475569;
            overflow: hidden;
            border: 3px solid #eef2f6;
            flex-shrink: 0;
            position: relative;
        }
        .profile-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .profile-avatar .placeholder {
            font-size: 40px;
            font-weight: 600;
            color: #94a3b8;
        }
        .profile-name { font-size: 20px; font-weight: 600; color: #0f172a; }
        .profile-role { font-size: 14px; color: #64748b; }

        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; font-size: 13px; font-weight: 500; color: #475569; margin-bottom: 4px; }
        .form-group input, .form-group textarea {
            width: 100%;
            padding: 10px 14px;
            border: 1.5px solid #e2e8f0;
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            transition: 0.2s;
            background: #fafcff;
        }
        .form-group input:focus, .form-group textarea:focus {
            outline: none;
            border-color: #8b5cf6;
            box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.08);
        }
        .form-group textarea { resize: vertical; min-height: 60px; }
        .form-group .readonly {
            background: #f1f4f8;
            color: #64748b;
            cursor: not-allowed;
        }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }

        .btn-save {
            background: #8b5cf6;
            color: white;
            border: none;
            padding: 10px 30px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: 0.2s;
        }
        .btn-save:hover { background: #7c3aed; transform: translateY(-2px); }

        .file-upload-wrapper {
            position: relative;
            overflow: hidden;
            display: inline-block;
        }
        .file-upload-wrapper input[type="file"] {
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }
        .file-upload-btn {
            display: inline-block;
            padding: 8px 16px;
            background: #eef2f6;
            color: #475569;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: 0.2s;
            border: 1px solid #e2e8f0;
        }
        .file-upload-btn:hover { background: #e2e8f0; }

        @media (max-width: 768px) {
            .sidebar { width: 70px; padding: 16px 12px; }
            .sidebar .logo { font-size: 16px; }
            .sidebar .logo span { display: none; }
            .sidebar .menu li span { display: none; }
            .main-content { margin-left: 70px; padding: 16px; }
            .form-row { grid-template-columns: 1fr; }
            .profile-header { flex-direction: column; text-align: center; }
        }
    </style>
</head>
<body>

<!-- ===== SIDEBAR ===== -->
<?php include 'includes/sidebar.php'; ?>

<!-- ===== MAIN CONTENT ===== -->
<div class="main-content">

    <!-- ===== HEADER ===== -->
    <div class="header">
        <h1>👤 Profil Pimpinan</h1>
        <div class="user">
            <span><?php echo date('l, d F Y'); ?></span>
            <div class="avatar"><?php echo strtoupper(substr($nama, 0, 1)); ?></div>
            <span><?php echo htmlspecialchars($nama); ?></span>
        </div>
    </div>

    <!-- ===== NOTIFIKASI ===== -->
    <?php if ($message): ?>
        <div class="alert alert-<?php echo $message_type; ?>">
            <i class="fas <?php echo $message_type == 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <!-- ===== FORM PROFIL ===== -->
    <div class="card">
        <form method="POST" enctype="multipart/form-data">
            <div class="profile-header">
                <div class="profile-avatar">
                    <?php if (!empty($foto_profil) && file_exists("../uploads/profil/" . $foto_profil)): ?>
                        <img src="../uploads/profil/<?php echo $foto_profil; ?>" alt="Foto Profil">
                    <?php else: ?>
                        <span class="placeholder"><?php echo strtoupper(substr($nama, 0, 1)); ?></span>
                    <?php endif; ?>
                </div>
                <div>
                    <div class="profile-name"><?php echo htmlspecialchars($nama); ?></div>
                    <div class="profile-role"><?php echo $role_display; ?></div>
                    <div style="margin-top: 8px;">
                        <span class="file-upload-wrapper">
                            <span class="file-upload-btn"><i class="fas fa-camera"></i> Ganti Foto</span>
                            <input type="file" name="foto_profil" accept="image/*">
                        </span>
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="nama" value="<?php echo htmlspecialchars($nama); ?>" required>
                </div>
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" class="readonly" value="<?php echo htmlspecialchars($username); ?>" readonly>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                </div>
                <div class="form-group">
                    <label>Nomor HP</label>
                    <input type="text" name="no_hp" value="<?php echo htmlspecialchars($no_hp); ?>">
                </div>
            </div>

            <div class="form-group">
                <label>Alamat</label>
                <textarea name="alamat" rows="2"><?php echo htmlspecialchars($alamat); ?></textarea>
            </div>

            <div class="form-group">
                <label>Role</label>
                <input type="text" class="readonly" value="<?php echo $role_display; ?>" readonly>
            </div>

            <div style="margin-top: 16px;">
                <button type="submit" class="btn-save"><i class="fas fa-save"></i> Simpan Perubahan</button>
            </div>
        </form>
    </div>

</div>

</body>
</html>