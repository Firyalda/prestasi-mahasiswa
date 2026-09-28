<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once '../config/database.php';

$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM users WHERE id = $user_id";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

// Format tanggal
$tanggal_hari_ini = date('l, d F Y');

$success = '';
$error = '';

// Proses update data diri
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_FILES['foto_profil']) && !isset($_POST['hapus_foto'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $no_hp = mysqli_real_escape_string($conn, $_POST['no_hp']);
    $password = $_POST['password'];
    
    if (!empty($password)) {
        $password_hash = md5($password);
        $update = "UPDATE users SET nama_lengkap = '$nama', email = '$email', no_hp = '$no_hp', password = '$password_hash' WHERE id = $user_id";
    } else {
        $update = "UPDATE users SET nama_lengkap = '$nama', email = '$email', no_hp = '$no_hp' WHERE id = $user_id";
    }
    
    if (mysqli_query($conn, $update)) {
        $success = "✅ Profil berhasil diperbarui!";
        $result = mysqli_query($conn, $query);
        $user = mysqli_fetch_assoc($result);
        $_SESSION['nama'] = $nama;
    } else {
        $error = "❌ Gagal memperbarui profil: " . mysqli_error($conn);
    }
}

// Proses upload foto profil
if (isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] === 0) {
    $file = $_FILES['foto_profil'];
    $allowed = ['jpg', 'jpeg', 'png'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($ext, $allowed)) {
        $error = "Format file harus JPG, JPEG, atau PNG!";
    } elseif ($file['size'] > 2 * 1024 * 1024) {
        $error = "Ukuran file maksimal 2MB!";
    } else {
        if (!file_exists('../uploads/profil')) {
            mkdir('../uploads/profil', 0777, true);
        }
        
        if (!empty($user['foto_profil']) && file_exists('../uploads/profil/' . $user['foto_profil'])) {
            unlink('../uploads/profil/' . $user['foto_profil']);
        }
        
        $filename = time() . '_' . uniqid() . '.' . $ext;
        $target = '../uploads/profil/' . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $target)) {
            mysqli_query($conn, "UPDATE users SET foto_profil = '$filename' WHERE id = $user_id");
            $_SESSION['foto_profil'] = $filename;
            $success = "✅ Foto profil berhasil diupload!";
            $result = mysqli_query($conn, $query);
            $user = mysqli_fetch_assoc($result);
        } else {
            $error = "❌ Gagal upload foto!";
        }
    }
}

// Proses hapus foto profil
if (isset($_POST['hapus_foto'])) {
    if (!empty($user['foto_profil']) && file_exists('../uploads/profil/' . $user['foto_profil'])) {
        unlink('../uploads/profil/' . $user['foto_profil']);
    }
    
    mysqli_query($conn, "UPDATE users SET foto_profil = NULL WHERE id = $user_id");
    $_SESSION['foto_profil'] = null;
    
    $success = "✅ Foto profil berhasil dihapus!";
    
    $result = mysqli_query($conn, $query);
    $user = mysqli_fetch_assoc($result);
}

include 'includes/sidebar.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', system-ui, sans-serif; background: #f4f6f9; color: #1a1a1a; }

        .main-content {
            margin-left: 260px;
            padding: 24px 32px;
            min-height: 100vh;
        }

        /* TOP BAR - TANPA LOGOUT */
        .top-bar {
            background: white;
            border-radius: 12px;
            padding: 16px 24px;
            margin-bottom: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: 1px solid #e8e8e8;
        }
        .page-title h1 { font-size: 20px; font-weight: 600; color: #1a1a1a; }
        .page-title p { font-size: 13px; color: #666; margin-top: 4px; }

        /* USER INFO - TANPA LOGOUT */
        .user-info {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .user-avatar-small {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #2c7da0;
            overflow: hidden;
        }
        .user-avatar-small img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .user-name { font-weight: 500; color: #1a1a1a; }
        .date-divider { width: 1px; height: 30px; background: #ddd; }
        .date-text { font-size: 13px; color: #888; }

        .form-card {
            background: white;
            border-radius: 12px;
            border: 1px solid #e8e8e8;
            overflow: hidden;
        }
        .form-header {
            padding: 16px 20px;
            border-bottom: 1px solid #eee;
            background: #f8f9fa;
            font-weight: 600;
            font-size: 16px;
        }
        .form-body { padding: 24px; }
        
        .profile-layout {
            display: flex;
            gap: 30px;
            flex-wrap: wrap;
        }
        .profile-photo {
            text-align: center;
            min-width: 180px;
        }
        .photo-preview {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #2c7da0;
            margin-bottom: 15px;
            background: #f0f2f5;
        }
        .photo-placeholder {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
            border: 3px solid #2c7da0;
        }
        .photo-placeholder i {
            font-size: 60px;
            color: white;
        }
        .profile-form {
            flex: 1;
        }

        .btn-photo {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 8px 18px;
            border: none;
            border-radius: 30px;
            font-size: 12px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: 'Segoe UI', system-ui, sans-serif;
        }
        .btn-photo i { font-size: 13px; }
        .btn-photo-primary {
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            color: white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .btn-photo-primary:hover {
            background: linear-gradient(135deg, #2a5298, #1e3c72);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(30,60,114,0.3);
        }
        .btn-photo-success {
            background: linear-gradient(135deg, #1e7e34, #28a745);
            color: white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .btn-photo-success:hover {
            background: linear-gradient(135deg, #28a745, #1e7e34);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(40,167,69,0.3);
        }
        .btn-photo-danger {
            background: linear-gradient(135deg, #a71d2a, #dc3545);
            color: white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .btn-photo-danger:hover {
            background: linear-gradient(135deg, #dc3545, #a71d2a);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(220,53,69,0.3);
        }
        .btn-photo:active {
            transform: translateY(0);
        }

        .btn-save {
            background: #2c7da0;
            color: white;
            border: none;
            padding: 10px 24px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.3s;
        }
        .btn-save:hover { background: #1f5e7a; transform: translateY(-2px); }

        .form-group {
            margin-bottom: 18px;
        }
        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 500;
            color: #555;
            margin-bottom: 6px;
        }
        .form-group input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 13px;
        }
        .form-group input:focus { outline: none; border-color: #2c7da0; }
        .form-group input[readonly] { background: #f8f9fa; cursor: not-allowed; }

        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .alert-success { background: #e8f5e9; color: #2e7d32; border: 1px solid #c8e6c9; }
        .alert-error { background: #ffebee; color: #c62828; border: 1px solid #ffcdd2; }

        .info-note {
            background: #e8f0fe;
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 12px;
            color: #2c7da0;
            margin-top: 20px;
        }
        .file-info {
            font-size: 10px;
            color: #888;
            margin-top: 8px;
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 80px;
                padding: 16px;
            }
            .profile-layout {
                flex-direction: column;
                align-items: center;
            }
            .top-bar {
                flex-direction: column;
                gap: 12px;
                text-align: center;
            }
            .user-info .user-name { display: none; }
        }

        @media (max-width: 480px) {
            .main-content {
                margin-left: 0;
                padding: 12px;
            }
            .profile-photo {
                min-width: unset;
                width: 100%;
            }
            .photo-preview, .photo-placeholder {
                width: 120px;
                height: 120px;
            }
            .btn-photo {
                font-size: 11px;
                padding: 6px 12px;
            }
        }
    </style>
</head>
<body>

<div class="main-content">

    <!-- ========================================================= -->
    <!-- TOP BAR - TANPA LOGOUT                                     -->
    <!-- ========================================================= -->
    <div class="top-bar">
        <div class="page-title">
            <h1>Profil Saya</h1>
            <p>Informasi akun administrator</p>
        </div>
        <div class="user-info">
            <div class="user-avatar-small">
                <?php if (!empty($user['foto_profil']) && file_exists("../uploads/profil/" . $user['foto_profil'])): ?>
                    <img src="../uploads/profil/<?php echo $user['foto_profil']; ?>" alt="Foto Profil">
                <?php else: ?>
                    <i class="fas fa-user" style="color: white;"></i>
                <?php endif; ?>
            </div>
            <span class="user-name"><?php echo htmlspecialchars($user['nama_lengkap']); ?></span>
            <span class="date-divider"></span>
            <span class="date-text"><?php echo date('l, d F Y'); ?></span>
        </div>
    </div>

    <!-- ========================================================= -->
    <!-- FORM CARD                                                 -->
    <!-- ========================================================= -->
    <div class="form-card">
        <div class="form-header">Edit Profil Administrator</div>
        <div class="form-body">
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <div class="profile-layout">
                <!-- Bagian Foto Profil -->
                <div class="profile-photo">
                    <?php if (!empty($user['foto_profil']) && file_exists("../uploads/profil/" . $user['foto_profil'])): ?>
                        <img src="../uploads/profil/<?php echo $user['foto_profil']; ?>" class="photo-preview" alt="Foto Profil">
                    <?php else: ?>
                        <div class="photo-placeholder">
                            <i class="fas fa-user-circle"></i>
                        </div>
                    <?php endif; ?>
                    
                    <div class="button-group" style="display: flex; gap: 12px; justify-content: center; margin-top: 15px; flex-wrap: wrap;">
                        <button type="button" class="btn-photo btn-photo-primary" onclick="document.getElementById('foto_profil').click();">
                            <i class="fas fa-folder-open"></i> Pilih Foto
                        </button>
                        <button type="submit" name="upload_foto" class="btn-photo btn-photo-success" form="formFoto">
                            <i class="fas fa-cloud-upload-alt"></i> Upload
                        </button>
                        <button type="submit" name="hapus_foto" class="btn-photo btn-photo-danger" onclick="return confirm('Yakin ingin menghapus foto profil?')">
                            <i class="fas fa-trash-alt"></i> Hapus Foto
                        </button>
                    </div>
                    
                    <div class="file-info">
                        <i class="fas fa-info-circle"></i> Format JPG/PNG, maksimal 2MB
                    </div>
                </div>

                <!-- Form Upload & Hapus -->
                <form method="POST" enctype="multipart/form-data" id="formFoto" style="display: none;">
                    <input type="file" name="foto_profil" id="foto_profil" accept="image/jpeg,image/png,image/jpg">
                </form>

                <!-- Form Hapus Foto -->
                <form method="POST" id="formHapus" style="display: none;">
                    <input type="hidden" name="hapus_foto" value="1">
                </form>

                <!-- Bagian Form Data Diri -->
                <div class="profile-form">
                    <form method="POST">
                        <div class="form-group">
                            <label>Username</label>
                            <input type="text" value="<?php echo htmlspecialchars($user['username']); ?>" readonly>
                        </div>
                        <div class="form-group">
                            <label>Nama Lengkap</label>
                            <input type="text" name="nama" value="<?php echo htmlspecialchars($user['nama_lengkap']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>No. Handphone</label>
                            <input type="text" name="no_hp" value="<?php echo htmlspecialchars($user['no_hp'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label>Password Baru (kosongkan jika tidak diubah)</label>
                            <input type="password" name="password" placeholder="Masukkan password baru">
                        </div>
                        <button type="submit" class="btn-save">Simpan Perubahan</button>
                    </form>
                </div>
            </div>

            <div class="info-note">
                <i class="fas fa-info-circle"></i> Username tidak dapat diubah. Hubungi admin jika perlu mengubah username.
            </div>
        </div>
    </div>
</div>

<script>
    // Preview foto sebelum upload
    document.getElementById('foto_profil').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(event) {
                const preview = document.querySelector('.photo-preview');
                const placeholder = document.querySelector('.photo-placeholder');
                if (preview) {
                    preview.src = event.target.result;
                } else if (placeholder) {
                    const img = document.createElement('img');
                    img.src = event.target.result;
                    img.className = 'photo-preview';
                    img.style.width = '150px';
                    img.style.height = '150px';
                    img.style.borderRadius = '50%';
                    img.style.objectFit = 'cover';
                    img.style.border = '3px solid #2c7da0';
                    placeholder.parentNode.replaceChild(img, placeholder);
                }
            };
            reader.readAsDataURL(file);
        }
    });
    
    // Menangani tombol hapus
    document.querySelector('.btn-photo-danger').addEventListener('click', function(e) {
        if (confirm('Yakin ingin menghapus foto profil?')) {
            document.getElementById('formHapus').submit();
        }
    });
</script>

</body>
</html>