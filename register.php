<?php
// register.php - REGISTRASI MAHASISWA (OOP MVC)
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/flash.php';

use App\Models\User;
use App\Core\Session;

$session = new Session();

// Jika sudah login, redirect ke dashboard
if ($session->has('user_id') && $session->get('role') === 'mahasiswa') {
    header("Location: mahasiswa/dashboard.php");
    exit();
}

$error = '';
$success = '';

// Process flash messages for error/success
$flash = $session->getFlash();
if ($flash) {
    if ($flash['type'] === 'error') {
        $error = $flash['title'];
    } elseif ($flash['type'] === 'success') {
        $success = $flash['title'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username     = trim($_POST['username'] ?? '');
    $email        = trim($_POST['email'] ?? '');
    $password     = trim($_POST['password'] ?? '');
    $nama_lengkap = trim($_POST['nama_lengkap'] ?? '');
    $nim          = trim($_POST['nim'] ?? '');
    $no_hp        = trim($_POST['no_hp'] ?? '');
    $fakultas     = trim($_POST['fakultas'] ?? '');
    $prodi        = trim($_POST['prodi'] ?? '');
    
    $userModel = new User();
    
    // Cek apakah username atau NIM sudah ada
    $check = $userModel->query("SELECT * FROM users WHERE username = :u OR nim = :n LIMIT 1", ['u' => $username, 'n' => $nim]);
    
    if (!empty($check)) {
        // UPDATE DATA
        $updated = $userModel->execute(
            "UPDATE users SET email = :email, password = :pass, nama_lengkap = :nama, no_hp = :no_hp, fakultas = :fakultas, prodi = :prodi WHERE username = :u OR nim = :n",
            [
                'email' => $email,
                'pass' => md5($password),
                'nama' => $nama_lengkap,
                'no_hp' => $no_hp,
                'fakultas' => $fakultas,
                'prodi' => $prodi,
                'u' => $username,
                'n' => $nim
            ]
        );
        
        if ($updated) {
            $success = "✅ Data berhasil diperbarui! Silakan login.";
        } else {
            $error = "❌ Gagal memperbarui data.";
        }
    } else {
        // INSERT DATA BARU
        $created = $userModel->execute(
            "INSERT INTO users (username, email, password, nama_lengkap, nim, no_hp, fakultas, prodi, role, created_at) VALUES (:u, :email, :pass, :nama, :nim, :no_hp, :fakultas, :prodi, 'mahasiswa', NOW())",
            [
                'u' => $username,
                'email' => $email,
                'pass' => md5($password),
                'nama' => $nama_lengkap,
                'nim' => $nim,
                'no_hp' => $no_hp,
                'fakultas' => $fakultas,
                'prodi' => $prodi
            ]
        );
        
        if ($created) {
            $success = "✅ Registrasi berhasil! Silakan login.";
        } else {
            $error = "❌ Registrasi gagal.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi - SMB</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

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

        .register-card {
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
            font-size: 2.6rem;
            font-weight: 700;
            letter-spacing: 1px;
            line-height: 1.2;
            color: #ffffff;
            text-shadow: 0 4px 30px rgba(0,0,0,0.5);
            margin-bottom: 8px;
        }

        .kampus-side .content p {
            font-size: 1.1rem;
            color: rgba(255, 255, 255, 0.85);
            margin-bottom: 2px;
            text-shadow: 0 2px 15px rgba(0,0,0,0.5);
            font-weight: 400;
            letter-spacing: 0.5px;
        }

        .kampus-side .content .univ {
            font-size: 1.2rem;
            font-weight: 700;
            color: rgba(255, 255, 255, 0.9);
            margin-top: 4px;
            text-shadow: 0 2px 15px rgba(0,0,0,0.5);
            letter-spacing: 0.5px;
        }

        /* ===== SISI KANAN - FORM ===== (TIDAK DIUBAH) ===== */
        .form-side {
            flex: 1;
            padding: 40px 36px;
            background: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .logo-area {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 20px;
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
            margin-bottom: 20px;
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

        .sso-info {
            background: #f0f9ff;
            border: 1px solid #bae6fd;
            border-radius: 50px;
            padding: 0.5rem 1rem;
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 18px;
            font-size: 0.7rem;
            color: #0369a1;
            flex-wrap: wrap;
        }

        .sso-info .badge-new {
            background: #0284c7;
            color: white;
            padding: 2px 8px;
            border-radius: 30px;
            font-size: 0.6rem;
            font-weight: 600;
        }

        .alert {
            padding: 0.85rem 1rem;
            border-radius: 14px;
            margin-bottom: 16px;
            font-size: 0.8rem;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-error {
            background: #fef2f2;
            border-left: 4px solid #dc2626;
            color: #991b1b;
        }

        .alert-success {
            background: #ecfdf5;
            border-left: 4px solid #059669;
            color: #065f46;
        }

        .form-row {
            display: flex;
            gap: 1rem;
            margin-bottom: 0.8rem;
        }

        .form-row .form-group {
            flex: 1;
            margin-bottom: 0;
        }

        .form-group {
            margin-bottom: 0.8rem;
        }

        .form-group label {
            display: block;
            font-size: 0.65rem;
            font-weight: 600;
            color: #475569;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .input-icon {
            position: relative;
        }

        .input-icon i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 0.9rem;
        }

        .input-icon input,
        .input-icon select {
            width: 100%;
            padding: 0.75rem 1rem 0.75rem 2.6rem;
            border: 1.5px solid #e2e8f0;
            border-radius: 14px;
            font-size: 0.85rem;
            font-family: 'Plus Jakarta Sans', sans-serif;
            transition: all 0.2s;
            background: #ffffff;
            appearance: none;
        }

        .input-icon select {
            cursor: pointer;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2394a3b8'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            background-size: 1.2rem;
        }

        .input-icon input:focus,
        .input-icon select:focus {
            outline: none;
            border-color: #1a4a6f;
            box-shadow: 0 0 0 3px rgba(26, 74, 111, 0.08);
        }

        .btn-register {
            width: 100%;
            padding: 0.85rem;
            background: #1a4a6f;
            border: none;
            border-radius: 14px;
            color: white;
            font-size: 0.9rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
            margin-top: 0.5rem;
            margin-bottom: 1.2rem;
        }

        .btn-register:hover {
            background: #0f3a5a;
            transform: translateY(-2px);
            box-shadow: 0 6px 14px rgba(26, 74, 111, 0.25);
        }

        .login-link {
            text-align: center;
            font-size: 0.8rem;
            color: #64748b;
        }

        .login-link a {
            color: #1a4a6f;
            text-decoration: none;
            font-weight: 600;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        .note {
            font-size: 0.65rem;
            color: #94a3b8;
            text-align: center;
            margin-top: 0.8rem;
            padding-top: 0.8rem;
            border-top: 1px solid #e2e8f0;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 820px) {
            .register-card {
                flex-direction: column;
                max-width: 460px;
            }

            .kampus-side {
                min-height: 220px;
                padding: 30px 24px 30px 24px;
            }

            .kampus-side .content h1 {
                font-size: 1.8rem;
            }

            .kampus-side .content p {
                font-size: 0.9rem;
            }

            .kampus-side .content .univ {
                font-size: 1rem;
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

            .form-row {
                flex-direction: column;
                gap: 0;
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
                min-height: 150px;
                padding: 20px 16px 20px 16px;
            }

            .kampus-side .content h1 {
                font-size: 1.3rem;
            }

            .kampus-side .content p {
                font-size: 0.7rem;
            }

            .kampus-side .content .univ {
                font-size: 0.75rem;
            }
        }
    </style>
</head>
<body>

<div class="register-card">
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
            <h3>Registrasi Mahasiswa</h3>
            <p>Sistem Pengajuan Prestasi Mahasiswa</p>
        </div>

        <div class="sso-info">
            <i class="fas fa-sync-alt"></i>
            <span class="badge-new">Update!</span>
            <span>Jika sudah punya akun, data akan diperbarui</span>
        </div>

        <?php if ($error): ?>
            <script>
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: '<?php echo addslashes($error); ?>',
                    confirmButtonColor: '#dc2626',
                    confirmButtonText: 'OK'
                });
            </script>
        <?php endif; ?>

        <?php if ($success): ?>
            <script>
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: '<?php echo addslashes($success); ?>',
                    confirmButtonColor: '#059669',
                    confirmButtonText: 'OK'
                });
            </script>
        <?php endif; ?>

        <form method="POST" id="formRegister">
            <div class="form-row">
                <div class="form-group">
                    <label>USERNAME</label>
                    <div class="input-icon">
                        <i class="fas fa-user-circle"></i>
                        <input type="text" name="username" placeholder="Masukkan username" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>EMAIL</label>
                    <div class="input-icon">
                        <i class="fas fa-envelope"></i>
                        <input type="email" name="email" placeholder="email@example.com" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>PASSWORD</label>
                    <div class="input-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password" placeholder="Masukkan password" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>NAMA LENGKAP</label>
                    <div class="input-icon">
                        <i class="fas fa-user"></i>
                        <input type="text" name="nama_lengkap" placeholder="Nama lengkap" value="<?php echo isset($_POST['nama_lengkap']) ? htmlspecialchars($_POST['nama_lengkap']) : ''; ?>" required>
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>NIM</label>
                    <div class="input-icon">
                        <i class="fas fa-id-card"></i>
                        <input type="text" name="nim" placeholder="Nomor Induk Mahasiswa" value="<?php echo isset($_POST['nim']) ? htmlspecialchars($_POST['nim']) : ''; ?>" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>NO. HANDPHONE</label>
                    <div class="input-icon">
                        <i class="fas fa-phone"></i>
                        <input type="text" name="no_hp" placeholder="081234567890" value="<?php echo isset($_POST['no_hp']) ? htmlspecialchars($_POST['no_hp']) : ''; ?>" required>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>FAKULTAS</label>
                <div class="input-icon">
                    <i class="fas fa-building"></i>
                    <select name="fakultas" id="fakultas" required>
                        <option value="">Pilih Fakultas</option>
                        <option value="Fakultas Sains dan Teknologi" <?php echo (isset($_POST['fakultas']) && $_POST['fakultas'] == 'Fakultas Sains dan Teknologi') ? 'selected' : ''; ?>>Fakultas Sains dan Teknologi</option>
                        <option value="Fakultas Sosial dan Humaniora" <?php echo (isset($_POST['fakultas']) && $_POST['fakultas'] == 'Fakultas Sosial dan Humaniora') ? 'selected' : ''; ?>>Fakultas Sosial dan Humaniora</option>
                        <option value="Fakultas Psikologi dan Pendidikan" <?php echo (isset($_POST['fakultas']) && $_POST['fakultas'] == 'Fakultas Psikologi dan Pendidikan') ? 'selected' : ''; ?>>Fakultas Psikologi dan Pendidikan</option>
                        <option value="Sekolah Vokasi" <?php echo (isset($_POST['fakultas']) && $_POST['fakultas'] == 'Sekolah Vokasi') ? 'selected' : ''; ?>>Sekolah Vokasi</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>PROGRAM STUDI</label>
                <div class="input-icon">
                    <i class="fas fa-graduation-cap"></i>
                    <select name="prodi" id="prodi" required>
                        <option value="">Pilih Program Studi</option>
                    </select>
                </div>
            </div>

            <button type="submit" class="btn-register">
                <i class="fas fa-save"></i> SIMPAN / UPDATE
            </button>
        </form>

        <div class="login-link">
            Sudah punya akun? <a href="login.php">Login disini</a>
        </div>
        <div class="note">
            <i class="fas fa-info-circle"></i> Jika username/NIM sudah ada, data akan diperbarui (UPDATE)
        </div>
    </div>
</div>

<script>
    const dataProdi = {
        "Fakultas Sains dan Teknologi": ["S-1 Sains Data", "S-1 Teknik Mesin", "S-1 Teknik Informatika", "S-1 Sistem Informasi"],
        "Fakultas Sosial dan Humaniora": ["S-1 Hukum", "S-1 Ilmu Komunikasi", "S-1 Akuntansi", "S-1 Manajemen"],
        "Fakultas Psikologi dan Pendidikan": ["S-1 Psikologi", "S-1 Pendidikan Guru SD"],
        "Sekolah Vokasi": ["D-4 Kebidanan", "D-4 Teknik Informatika", "D-4 Akuntansi Sektor Publik", "D-3 Farmasi", "D-3 Keperawatan", "D-3 Akuntansi", "D-3 Desain Komunikasi Visual", "D-3 Teknik Komputer", "D-3 Teknik Mesin", "D-3 Teknik Elektronika", "D-3 Perhotelan", "Profesi Bidan"]
    };

    const fakultasSelect = document.getElementById('fakultas');
    const prodiSelect = document.getElementById('prodi');

    function updateProdi() {
        const selectedFakultas = fakultasSelect.value;
        const oldValue = "<?php echo isset($_POST['prodi']) ? $_POST['prodi'] : ''; ?>";
        prodiSelect.innerHTML = '<option value="">Pilih Program Studi</option>';
        
        if (selectedFakultas && dataProdi[selectedFakultas]) {
            const prodiList = dataProdi[selectedFakultas];
            prodiList.forEach(prodi => {
                const option = document.createElement('option');
                option.value = prodi;
                option.textContent = prodi;
                if (oldValue === prodi) {
                    option.selected = true;
                }
                prodiSelect.appendChild(option);
            });
        }
    }

    fakultasSelect.addEventListener('change', updateProdi);
    
    if (fakultasSelect.value) {
        updateProdi();
    }
</script>
</body>
</html>