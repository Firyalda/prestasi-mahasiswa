<?php
session_start();
require_once '../config/database.php';

// Jika sudah login sebagai admin, langsung ke dashboard
if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin') {
    header("Location: dashboard.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = md5($_POST['password']);
    
    // CEK HANYA ADMIN
    $query = "SELECT * FROM users WHERE username = '$username' AND password = '$password' AND role = 'admin'";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) === 1) {
        $user = mysqli_fetch_assoc($result);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['nama'] = $user['nama_lengkap'];
        $_SESSION['role'] = $user['role'];
        
        header("Location: dashboard.php");
        exit();
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
    <title>Login Admin - SMB</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', 'Inter', sans-serif;
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
            max-width: 1000px;
            width: 100%;
            background: white;
            border-radius: 32px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.12);
            display: flex;
            overflow: hidden;
            position: relative;
            z-index: 1;
        }

        /* SISI KIRI - BRANDING */
        .brand-side {
            flex: 1.2;
            background: linear-gradient(165deg, #1a4a6f 0%, #0f2b3d 100%);
            padding: 40px 32px;
            position: relative;
            display: flex;
            flex-direction: column;
        }

        .brand-side::after {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" fill="none"><circle cx="20" cy="20" r="3" fill="white" opacity="0.1"/><circle cx="80" cy="40" r="4" fill="white" opacity="0.08"/><circle cx="50" cy="85" r="5" fill="white" opacity="0.06"/></svg>');
            background-repeat: repeat;
            pointer-events: none;
        }

        .logo-area {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 48px;
            position: relative;
            z-index: 1;
        }

        .logo-badge {
            width: 48px;
            height: 48px;
            background: rgba(255, 255, 255, 0.12);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
        }

        .logo-text h2 {
            font-size: 1.3rem;
            font-weight: 700;
            color: white;
            letter-spacing: -0.3px;
        }

        .logo-text p {
            font-size: 0.65rem;
            color: rgba(255, 255, 255, 0.65);
            margin-top: 2px;
        }

        .hero-text {
            margin-top: auto;
            margin-bottom: 32px;
            position: relative;
            z-index: 1;
        }

        .hero-text h1 {
            font-size: 1.8rem;
            font-weight: 700;
            color: white;
            margin-bottom: 16px;
            line-height: 1.3;
        }

        .hero-text p {
            font-size: 0.85rem;
            color: rgba(255, 255, 255, 0.75);
            line-height: 1.5;
            margin-bottom: 24px;
        }

        .feature-grid {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.8rem;
            color: rgba(255, 255, 255, 0.85);
        }

        .feature-item i {
            width: 24px;
            height: 24px;
            background: rgba(255, 255, 255, 0.12);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
        }

        /* SISI KANAN - FORM */
        .form-side {
            flex: 1;
            padding: 40px 36px;
            background: white;
        }

        .form-header {
            margin-bottom: 28px;
        }

        .form-header h3 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 6px;
        }

        .form-header p {
            font-size: 0.8rem;
            color: #64748b;
        }

        /* Alert error */
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

        /* Form group */
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

        /* Password field */
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
            font-size: 0.9rem;
        }

        .toggle-pwd:hover {
            color: #1a4a6f;
        }

        .btn-login {
            width: 100%;
            padding: 12px;
            background: #1a4a6f;
            border: none;
            border-radius: 16px;
            color: white;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            margin-bottom: 20px;
        }

        .btn-login:hover {
            background: #0f3a5a;
            transform: translateY(-1px);
        }

        .back-link {
            text-align: center;
            font-size: 0.8rem;
            color: #64748b;
        }

        .back-link a {
            color: #1a4a6f;
            text-decoration: none;
            font-weight: 600;
        }

        .back-link a:hover {
            text-decoration: underline;
        }

        /* Responsive */
        @media (max-width: 800px) {
            .login-card {
                flex-direction: column;
                max-width: 450px;
            }
            .brand-side {
                padding: 32px 24px;
            }
            .hero-text h1 {
                font-size: 1.4rem;
            }
            .form-side {
                padding: 32px 24px;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 16px;
            }
            .form-side {
                padding: 24px 20px;
            }
        }
    </style>
</head>
<body>

<div class="login-card">
    <!-- SISI KIRI - BRANDING -->
    <div class="brand-side">
        <div class="logo-area">
            <div class="logo-badge">
                <i class="fas fa-shield-alt"></i>
            </div>
            <div class="logo-text">
                <h2>Admin Panel</h2>
                <p>Sistem Mahasiswa Berprestasi</p>
            </div>
        </div>
        <div class="hero-text">
            <h1>Login Admin<br>SMB</h1>
            <p>Kelola pengajuan prestasi, verifikasi data, dan atur reward mahasiswa dengan mudah.</p>
            <div class="feature-grid">
                <div class="feature-item"><i class="fas fa-check"></i> <span>Verifikasi pengajuan</span></div>
                <div class="feature-item"><i class="fas fa-chart-line"></i> <span>Monitoring prestasi</span></div>
                <div class="feature-item"><i class="fas fa-trophy"></i> <span>Kelola reward</span></div>
            </div>
        </div>
    </div>

    <!-- SISI KANAN - FORM LOGIN ADMIN -->
    <div class="form-side">
        <div class="form-header">
            <h3>Login Admin</h3>
            <p>Panel Administrator SMB</p>
        </div>

        <?php if ($error): ?>
            <div class="alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php echo $error; ?></span>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>USERNAME</label>
                <div class="input-field">
                    <i class="fas fa-user"></i>
                    <input type="text" name="username" placeholder="admin" required>
                </div>
            </div>

            <div class="form-group">
                <label>PASSWORD</label>
                <div class="password-field input-field">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" id="password" placeholder="********" required>
                    <i class="fas fa-eye-slash toggle-pwd" id="togglePassword"></i>
                </div>
            </div>

            <button type="submit" class="btn-login">
                <i class="fas fa-sign-in-alt"></i> Login
            </button>

            <div class="back-link">
                <a href="../index.php"><i class="fas fa-arrow-left"></i> Kembali ke Login Mahasiswa</a>
            </div>
        </form>
    </div>
</div>

<script>
    // Show/Hide Password Toggle
    const togglePassword = document.getElementById('togglePassword');
    const password = document.getElementById('password');
    
    if (togglePassword && password) {
        togglePassword.addEventListener('click', function() {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
    }
</script>
</body>
</html>