<?php
// login.php - SATU LOGIN UNTUK SEMUA ROLE (OOP MVC)
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/flash.php';

use App\Models\User;
use App\Core\Session;

$session = new Session();

// Jika sudah login, redirect berdasarkan role
if ($session->has('user_id') && !empty($session->get('user_id'))) {
    $role = $session->get('role', 'mahasiswa');
    
    $redirect_map = [
        'admin'      => 'admin/dashboard.php',
        'reviewer'   => 'verifikator/dashboard.php',
        'superadmin' => 'pimpinan/dashboard.php',
        'mahasiswa'  => 'mahasiswa/dashboard.php'
    ];
    
    $redirect = $redirect_map[$role] ?? 'mahasiswa/dashboard.php';
    
    if (file_exists($redirect)) {
        header("Location: $redirect");
        exit();
    } else {
        $session->destroy();
        header("Location: login.php");
        exit();
    }
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    $userModel = new User();
    $user = $userModel->authenticate($username, $password);
    
    if ($user) {
        $session->set('user_id', $user['id']);
        $session->set('username', $user['username']);
        $session->set('nama', $user['nama_lengkap']);
        $session->set('role', $user['role']);
        $session->set('nim', $user['nim'] ?? null);
        $session->set('login_success', true);
        $session->set('login_nama', $user['nama_lengkap']);

        set_flash_message('success', 'Login Berhasil', 'Selamat datang kembali!');

        $role_map = [
            'admin'      => 'admin/dashboard.php',
            'reviewer'   => 'verifikator/dashboard.php',
            'superadmin' => 'pimpinan/dashboard.php',
            'mahasiswa'  => 'mahasiswa/dashboard.php'
        ];
        
        $redirect = $role_map[$user['role']] ?? 'mahasiswa/dashboard.php';

        if (file_exists($redirect)) {
            header("Location: $redirect");
            exit();
        } else {
            $error = "Dashboard tidak ditemukan! Hubungi admin.";
        }
    } else {
        $error = "Username atau password salah!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SMB</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php render_flash_script(); ?>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f7fb;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 24px;
            position: relative;
        }

        body::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: radial-gradient(#cbd5e1 1px, transparent 1px);
            background-size: 30px 30px;
            opacity: 0.4;
            pointer-events: none;
        }

        .login-card {
            max-width: 1050px;
            width: 100%;
            background: white;
            border-radius: 32px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.12);
            display: flex;
            overflow: hidden;
            position: relative;
            z-index: 1;
        }

        /* ===== SISI KIRI - GAMBAR FULL ===== */
        .kampus-side {
            flex: 1.2;
            position: relative;
            min-height: 500px;
            background: #1a2634;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            align-items: flex-start;
            padding: 40px 32px 50px 32px;
        }

        .kampus-side .bg-image {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            opacity: 1;
            z-index: 0;
        }

        .kampus-side .overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.3);
            z-index: 1;
        }

        .kampus-side .content {
            position: relative;
            z-index: 2;
            text-align: left;
            color: white;
            max-width: 420px;
            width: 100%;
            margin-top: auto;
            margin-bottom: 0;
            padding-bottom: 5px;
        }

        .kampus-side .content h1 {
            font-size: 1.6rem;
            font-weight: 700;
            letter-spacing: 1px;
            line-height: 1.3;
            color: #ffffff;
            text-shadow: 0 4px 30px rgba(0,0,0,0.5);
            margin-bottom: 6px;
        }

        .kampus-side .content p {
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.85);
            margin-bottom: 2px;
            text-shadow: 0 2px 15px rgba(0,0,0,0.5);
            font-weight: 400;
            letter-spacing: 0.5px;
        }

        .kampus-side .content .univ {
            font-size: 0.95rem;
            font-weight: 700;
            color: rgba(255, 255, 255, 0.9);
            margin-top: 2px;
            text-shadow: 0 2px 15px rgba(0,0,0,0.5);
            letter-spacing: 0.5px;
        }

        /* ===== SISI KANAN - FORM ===== */
        .form-side {
            flex: 1;
            padding: 40px 36px;
            background: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        /* LOGO DI KIRI ATAS */
        .logo-area {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 24px;
        }

        .logo-univ {
            width: 48px;
            height: 48px;
            background: #eef2f7;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid #e2e8f0;
            flex-shrink: 0;
            overflow: hidden;
        }

        .logo-univ img {
            width: 34px;
            height: 34px;
            object-fit: contain;
        }

        .logo-text h2 {
            font-size: 1.2rem;
            font-weight: 700;
            color: #1a2634;
            letter-spacing: -0.3px;
            line-height: 1.2;
        }

        .logo-text p {
            font-size: 0.6rem;
            color: #6b7a8f;
            margin-top: 1px;
            letter-spacing: 0.5px;
        }

        .form-header {
            margin-bottom: 28px;
        }

        .form-header h3 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 4px;
        }

        .form-header p {
            font-size: 0.85rem;
            color: #64748b;
        }

        .form-header .role-badge {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 12px;
        }

        .form-header .role-badge span {
            padding: 4px 14px;
            border-radius: 100px;
            font-size: 11px;
            font-weight: 500;
            color: #555;
        }

        .form-header .role-badge .admin-badge {
            background: #e8ecf1;
            color: #1a4a6f;
        }
        .form-header .role-badge .admin-badge i {
            margin-right: 4px;
        }

        .form-header .role-badge .student-badge {
            background: #eafaf1;
            color: #1a7a4a;
        }
        .form-header .role-badge .student-badge i {
            margin-right: 4px;
        }

        .form-header .role-badge .verifikator-badge {
            background: #fef3c7;
            color: #92400e;
        }
        .form-header .role-badge .verifikator-badge i {
            margin-right: 4px;
        }

        .form-header .role-badge .pimpinan-badge {
            background: #e0e7ff;
            color: #3730a3;
        }
        .form-header .role-badge .pimpinan-badge i {
            margin-right: 4px;
        }

        .alert-error {
            background: #fef2f2;
            border-radius: 14px;
            padding: 12px 16px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.75rem;
            color: #dc2626;
            border: 1px solid #fecaca;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 0.7rem;
            font-weight: 600;
            color: #475569;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .input-field {
            position: relative;
        }

        .input-field i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 0.9rem;
        }

        .input-field input {
            width: 100%;
            padding: 12px 14px 12px 42px;
            border: 1.5px solid #e2e8f0;
            border-radius: 16px;
            font-size: 0.85rem;
            font-family: inherit;
            transition: all 0.2s;
            background: #ffffff;
        }

        .input-field input:focus {
            outline: none;
            border-color: #1a4a6f;
            box-shadow: 0 0 0 3px rgba(26, 74, 111, 0.08);
        }

        /* PASSWORD FIELD DENGAN ICON MATA */
        .password-field {
            position: relative;
        }

        .password-field input {
            padding-right: 45px;
        }

        .toggle-pwd {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #94a3b8;
            font-size: 1.1rem;
            transition: color 0.3s;
            z-index: 3;
            background: transparent;
            border: none;
            padding: 8px;
        }

        .toggle-pwd:hover {
            color: #1a4a6f;
        }

        .btn-login {
            width: 100%;
            padding: 14px;
            background: #1a4a6f;
            border: none;
            border-radius: 16px;
            color: white;
            font-size: 0.9rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
            margin-bottom: 20px;
        }

        .btn-login:hover {
            background: #0f3a5a;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(26, 74, 111, 0.25);
        }

        .btn-login i {
            margin-right: 8px;
        }

        .register-link {
            text-align: center;
            font-size: 0.8rem;
            color: #64748b;
        }

        .register-link a {
            color: #1a4a6f;
            text-decoration: none;
            font-weight: 600;
        }

        .register-link a:hover {
            text-decoration: underline;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 820px) {
            .login-card {
                flex-direction: column;
                max-width: 460px;
            }

            .kampus-side {
                min-height: 280px;
                padding: 30px 24px 40px 24px;
            }

            .kampus-side .content h1 {
                font-size: 1.4rem;
            }

            .kampus-side .content .univ {
                font-size: 0.85rem;
            }

            .form-side {
                padding: 28px 24px;
            }

            .logo-univ {
                width: 42px;
                height: 42px;
            }

            .logo-univ img {
                width: 28px;
                height: 28px;
            }

            .logo-text h2 {
                font-size: 1rem;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 16px;
            }

            .form-side {
                padding: 20px 16px;
            }

            .kampus-side {
                min-height: 200px;
                padding: 20px 16px 30px 16px;
            }

            .kampus-side .content h1 {
                font-size: 1.2rem;
            }

            .kampus-side .content p {
                font-size: 0.7rem;
            }

            .kampus-side .content .univ {
                font-size: 0.7rem;
            }
        }
    </style>
</head>
<body>

<div class="login-card">
    <!-- ===== SISI KIRI - GAMBAR FULL ===== -->
    <div class="kampus-side">
        <img src="assets/img/kampus-uhn.jpeg" alt="Kampus UHN" class="bg-image" onerror="this.style.display='none'">
        <div class="overlay"></div>

        <div class="content">
            <h1>SELAMAT DATANG</h1>
            <p>Sistem Mahasiswa Berprestasi</p>
            <div class="univ">Universitas Harkat Negeri</div>
        </div>
    </div>

    <!-- ===== SISI KANAN - LOGO + FORM ===== -->
    <div class="form-side">
        <!-- LOGO DI KIRI ATAS -->
        <div class="logo-area">
            <div class="logo-univ">
                <img src="assets/img/logo-uhn.png" alt="Logo UHN" onerror="this.style.display='none';this.parentElement.innerHTML='🏛️'">
            </div>
            <div class="logo-text">
                <h2>SMB</h2>
                <p>Sistem Mahasiswa Berprestasi</p>
            </div>
        </div>

        <div class="form-header">
            <h3>Login</h3>
            <p>Silakan masuk ke akun Anda</p>
            <div class="role-badge">
                <span class="admin-badge"><i class="fas fa-user-shield"></i> Admin</span>
                <span class="student-badge"><i class="fas fa-user-graduate"></i> Mahasiswa</span>
                <span class="verifikator-badge"><i class="fas fa-user-check"></i> Verifikator</span>
                <span class="pimpinan-badge"><i class="fas fa-user-tie"></i> Pimpinan</span>
            </div>
        </div>

        <?php if ($error): ?>
            <script>
                Swal.fire({
                    icon: 'error',
                    title: 'Login Gagal',
                    text: '<?php echo addslashes($error); ?>',
                    confirmButtonColor: '#dc2626',
                    confirmButtonText: 'OK'
                });
            </script>
        <?php endif; ?>

        <?php if (isset($_SESSION['logout_message'])): ?>
            <script>
                Swal.fire({
                    icon: 'success',
                    title: 'Logout Berhasil',
                    text: '<?php echo $_SESSION['logout_message']; ?>',
                    confirmButtonColor: '#2c7da0',
                    confirmButtonText: 'OK'
                });
            </script>
            <?php unset($_SESSION['logout_message']); ?>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>USERNAME</label>
                <div class="input-field">
                    <i class="fas fa-user"></i>
                    <input type="text" name="username" placeholder="Masukkan username" required>
                </div>
            </div>

            <div class="form-group">
                <label>PASSWORD</label>
                <div class="password-field input-field">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" id="password" placeholder="Masukkan password" required>
                    <button type="button" class="toggle-pwd" id="togglePassword" aria-label="Tampilkan password">
                        <i class="fas fa-eye-slash"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-login">
                <i class="fas fa-sign-in-alt"></i> LOGIN
            </button>

            <div class="register-link">
                Belum punya akun? <a href="register.php">Daftar disini</a>
            </div>
        </form>
    </div>
</div>

<script>
    // Toggle Password Visibility
    const togglePassword = document.getElementById('togglePassword');
    const password = document.getElementById('password');
    
    if (togglePassword && password) {
        togglePassword.addEventListener('click', function() {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            const icon = this.querySelector('i');
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        });
    }
</script>
</body>
</html>