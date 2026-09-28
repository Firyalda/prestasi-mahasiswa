<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: ../login.php");
    exit();
}

require_once '../config/database.php';

$user_id = $_SESSION['user_id'];

// Ambil foto_profil
$user_data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT nama_lengkap, nim, foto_profil FROM users WHERE id = $user_id"));
$foto_profil = $user_data['foto_profil'] ?? '';

// Format tanggal (Friday, 12 June 2026)
$tanggal_hari_ini = date('l, d F Y');

// Data dropdown
$level_list = ['Lokal', 'Provinsi', 'Nasional', 'Internasional'];
$raihan_individu = ['Juara 1', 'Juara 2', 'Juara 3'];
$raihan_ekstra = ['Gold Medal', 'Silver Medal', 'Bronze Medal', 'Finalis', 'Juara Harapan/Favorit'];
$kategori_prestasi_list = ['Akademik', 'Non-Akademik', 'Olahraga', 'Seni', 'Kewirausahaan'];
$semua_raihan = array_merge($raihan_individu, $raihan_ekstra);

$fakultas_list = [
    'Fakultas Sains dan Teknologi' => ['S-1 Sains Data', 'S-1 Teknik Mesin', 'S-1 Teknik Informatika', 'S-1 Sistem Informasi'],
    'Fakultas Sosial dan Humaniora' => ['S-1 Hukum', 'S-1 Ilmu Komunikasi', 'S-1 Akuntansi', 'S-1 Manajemen'],
    'Fakultas Psikologi dan Pendidikan' => ['S-1 Psikologi', 'S-1 Pendidikan Guru SD'],
    'Sekolah Vokasi' => ['D-4 Kebidanan', 'D-4 Teknik Informatika', 'D-4 Akuntansi Sektor Publik', 'D-3 Farmasi', 'D-3 Keperawatan', 'D-3 Akuntansi', 'D-3 Desain Komunikasi Visual', 'D-3 Teknik Komputer', 'D-3 Teknik Mesin', 'D-3 Teknik Elektronika', 'D-3 Perhotelan', 'Profesi Bidan']
];

function uploadFile($file, $target_dir, $allowed_types = ['pdf'], $max_size_mb = 10) {
    if ($file['error'] !== 0) return null;
    if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($file_extension, $allowed_types)) return null;
    if ($file['size'] > $max_size_mb * 1024 * 1024) return null;
    $new_filename = time() . '_' . uniqid() . '.' . $file_extension;
    if (move_uploaded_file($file['tmp_name'], $target_dir . $new_filename)) {
        return $new_filename;
    }
    return null;
}

// ========== FUNGSI UNTUK UPLOAD MULTIPLE FILE ==========
function uploadMultipleFiles($files, $target_dir, $allowed_types = ['png', 'jpg', 'jpeg'], $max_size_mb = 10) {
    $uploaded_files = [];
    
    if (!isset($files['name']) || empty($files['name'][0])) {
        return [];
    }
    
    $file_count = count($files['name']);
    
    for ($i = 0; $i < $file_count; $i++) {
        if ($files['error'][$i] !== 0) continue;
        
        $file = [
            'name' => $files['name'][$i],
            'type' => $files['type'][$i],
            'tmp_name' => $files['tmp_name'][$i],
            'error' => $files['error'][$i],
            'size' => $files['size'][$i]
        ];
        
        $uploaded = uploadFile($file, $target_dir, $allowed_types, $max_size_mb);
        if ($uploaded) {
            $uploaded_files[] = $uploaded;
        }
    }
    
    return $uploaded_files;
}
// =====================================================

function prosesOCR($filePath) {
    $pythonPath = 'C:\Users\ACER\AppData\Local\Programs\Python\Python310\python.exe';
    $scriptPath = dirname(__DIR__) . '/ocr/extract_sertifikat.py';
    $command = $pythonPath . ' "' . $scriptPath . '" "' . $filePath . '" 2>&1';
    $output = shell_exec($command);
    
    $ocr_text = '';
    $ocr_accuracy = 0;
    $lines = explode("\n", $output);
    foreach ($lines as $line) {
        if (strpos($line, '{') === 0) {
            $result = json_decode($line, true);
            if ($result && isset($result['success']) && $result['success']) {
                $ocr_text = $result['text'];
                $ocr_accuracy = $result['avg_confidence'] ?? 0;
            }
            break;
        }
    }
    return ['text' => $ocr_text, 'accuracy' => $ocr_accuracy];
}

// ========== DIGITAL SIGNATURE ==========
function generateFileHash($file_path) {
    if (!file_exists($file_path)) return null;
    return hash_file('sha256', $file_path);
}

function signCertificate($file_path) {
    $private_key_path = __DIR__ . '/../keys/private.key';
    if (!file_exists($private_key_path)) return null;
    $private_key = file_get_contents($private_key_path);
    $certificate_content = file_get_contents($file_path);
    openssl_sign($certificate_content, $signature, $private_key, OPENSSL_ALGO_SHA256);
    return base64_encode($signature);
}
// =========================================

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jenis_peserta = mysqli_real_escape_string($conn, $_POST['jenis_peserta']);
    $level_kompetisi = mysqli_real_escape_string($conn, $_POST['level_kompetisi']);
    $raihan_prestasi = mysqli_real_escape_string($conn, $_POST['raihan_prestasi']);
    $nama_ketua = mysqli_real_escape_string($conn, $_POST['nama_ketua']);
    $nim_ketua = mysqli_real_escape_string($conn, $_POST['nim_ketua']);
    $no_hp = mysqli_real_escape_string($conn, $_POST['no_hp']);
    $fakultas = mysqli_real_escape_string($conn, $_POST['fakultas']);
    $prodi = mysqli_real_escape_string($conn, $_POST['prodi']);
    $nama_kejuaraan = mysqli_real_escape_string($conn, $_POST['nama_kejuaraan']);
    $kategori_prestasi = mysqli_real_escape_string($conn, $_POST['kategori_prestasi']);
    $tanggal_raihan = mysqli_real_escape_string($conn, $_POST['tanggal_raihan']);
    $url_penyelenggara = mysqli_real_escape_string($conn, $_POST['url_penyelenggara']);
    
    $nama_tim = ($jenis_peserta == 'Tim') ? mysqli_real_escape_string($conn, $_POST['nama_tim']) : NULL;
    $anggota_tim = ($jenis_peserta == 'Tim') ? mysqli_real_escape_string($conn, $_POST['anggota_tim']) : NULL;
    
    $sertifikat = uploadFile($_FILES['sertifikat'], "../uploads/sertifikat/", ['pdf'], 10);
    
    // ========== UPLOAD MULTIPLE FOTO ==========
    $foto_dokumentasi_list = uploadMultipleFiles($_FILES['foto_dokumentasi'], "../uploads/foto/", ['png', 'jpg', 'jpeg'], 10);
    $foto_dokumentasi_simpan = implode(',', $foto_dokumentasi_list);
    // ==========================================
    
    $surat_tugas = uploadFile($_FILES['surat_tugas'], "../uploads/surat_tugas/", ['pdf'], 10);
    
    if ($sertifikat && !empty($foto_dokumentasi_list)) {
        $fullPath = "../uploads/sertifikat/" . $sertifikat;
        $ocrResult = prosesOCR($fullPath);
        $ocr_text = mysqli_real_escape_string($conn, $ocrResult['text']);
        $ocr_accuracy = $ocrResult['accuracy'];
        
        $file_hash = generateFileHash($fullPath);
        $file_hash_escaped = mysqli_real_escape_string($conn, $file_hash ?? '');
        $digital_signature = signCertificate($fullPath);
        $digital_signature_escaped = mysqli_real_escape_string($conn, $digital_signature ?? '');
        
        $query = "INSERT INTO pengajuan (
            user_id, jenis_peserta, level_kompetisi, raihan_prestasi,
            nama_ketua, nim_ketua, no_hp, fakultas, prodi, nama_kejuaraan,
            kategori_prestasi, tanggal_raihan, url_penyelenggara,
            nama_tim, anggota_tim, sertifikat, foto_dokumentasi, surat_tugas, 
            ocr_text, ocr_accuracy, file_hash, digital_signature, status
        ) VALUES (
            '$user_id', '$jenis_peserta', '$level_kompetisi', '$raihan_prestasi',
            '$nama_ketua', '$nim_ketua', '$no_hp', '$fakultas', '$prodi', '$nama_kejuaraan',
            '$kategori_prestasi', '$tanggal_raihan', '$url_penyelenggara',
            '$nama_tim', '$anggota_tim', '$sertifikat', '$foto_dokumentasi_simpan', '$surat_tugas',
            '$ocr_text', '$ocr_accuracy', '$file_hash_escaped', '$digital_signature_escaped', 'diproses'
        )";
        
        if (mysqli_query($conn, $query)) {
            $success = "Pengajuan berhasil dikirim! Silakan tunggu verifikasi dari admin.";
        } else {
            $error = "Gagal menyimpan: " . mysqli_error($conn);
        }
    } else {
        $error = "Gagal upload file! Pastikan sertifikat (PDF) dan minimal 1 foto (JPG/PNG) diupload, maksimal 10MB per file.";
    }
}

include 'includes/sidebar.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengajuan Prestasi - Mahasiswa</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/burger-menu.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', system-ui, sans-serif; background: #f4f6f9; color: #1a1a1a; }
        .menu-logout { margin-top: 40px; }

        .main-content { margin-left: 260px; padding: 24px 32px; min-height: 100vh; }

        /* TOP BAR - TANPA WEATHER, DENGAN TANGGAL DAN FOTO PROFIL */
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
        .user-info {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #2c7da0;
            overflow: hidden;
        }
        .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .user-name { margin: 0 10px; font-weight: 500; }
        .tanggal {
            font-size: 13px;
            color: #666;
            background: #f0f2f5;
            padding: 6px 14px;
            border-radius: 20px;
        }

        /* FORM CARD */
        .form-card {
            background: white; border-radius: 12px;
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
        .form-section { margin-bottom: 25px; }
        .section-title {
            font-size: 16px;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid #f0f0f0;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .participant-row {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }
        .participant-card {
            flex: 1;
            border: 2px solid #e8e8e8;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            background: white;
        }
        .participant-card.selected {
            border-color: #2c7da0;
            background: #f0f8ff;
        }
        .participant-card input { display: none; }
        .participant-icon { font-size: 40px; margin-bottom: 10px; }
        .participant-card h3 { font-size: 16px; color: #1a1a1a; }
        .participant-card p { font-size: 11px; color: #888; }
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }
        .form-group { margin-bottom: 18px; }
        .form-group label {
            display: block;
            font-size: 12px;
            font-weight: 500;
            color: #555;
            margin-bottom: 6px;
        }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 13px;
            font-family: inherit;
        }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            outline: none;
            border-color: #2c7da0;
        }
        .tim-fields {
            display: none;
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            border: 1px solid #e8e8e8;
        }
        .tim-fields.show { display: block; }
        .file-info {
            font-size: 10px;
            color: #888;
            margin-top: 5px;
        }
        .button-group {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 25px;
        }
        .btn-submit {
            background: #2c7da0;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 30px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        .btn-submit:hover {
            background: #1f5e7a;
            transform: translateY(-2px);
        }
        .btn-cancel {
            background: #6c757d;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 30px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s;
        }
        .btn-cancel:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .note {
            background: #fff3e0;
            padding: 12px;
            border-radius: 8px;
            margin-top: 20px;
            font-size: 11px;
            color: #e67e22;
            text-align: center;
        }

    </style>
</head>
<body>

<div class="main-content">
    <div class="top-bar">
        <div class="page-title">
            <h1>Pengajuan Prestasi</h1>
            <p>Isi form berikut untuk mengajukan prestasi</p>
        </div>
        <div class="user-info">
            <div class="user-avatar">
                <?php if (!empty($foto_profil) && file_exists("../uploads/profil/" . $foto_profil)): ?>
                    <img src="../uploads/profil/<?php echo $foto_profil; ?>" alt="Foto Profil">
                <?php else: ?>
                    <i class="fas fa-user"></i>
                <?php endif; ?>
            </div>
            <span class="user-name"><?php echo htmlspecialchars($user_data['nama_lengkap']); ?></span>
            <div class="tanggal">
                <?php echo $tanggal_hari_ini; ?>
            </div>
        </div>
    </div>

    <!-- WEATHER WIDGET DIHAPUS -->

    <div class="form-card">
        <div class="form-header">Form Pengajuan Prestasi</div>
        <div class="form-body">
            <?php if ($success): ?>
                <div class="alert alert-success">✅ <?php echo $success; ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-error">❌ <?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" id="prestasiForm">
                <!-- Jenis Peserta -->
                <div class="form-section">
                    <div class="section-title">👥 Jenis Peserta</div>
                    <div class="participant-row">
                        <label class="participant-card" id="cardIndividu">
                            <input type="radio" name="jenis_peserta" value="Individu" checked>
                            <div class="participant-icon">👤</div>
                            <h3>INDIVIDU</h3>
                            <p>Pendaftaran perorangan</p>
                        </label>
                        <label class="participant-card" id="cardTim">
                            <input type="radio" name="jenis_peserta" value="Tim">
                            <div class="participant-icon">👥</div>
                            <h3>TIM / BEREGU</h3>
                            <p>Pendaftaran beregu</p>
                        </label>
                    </div>
                </div>

                <!-- Data Tim -->
                <div id="timFields" class="tim-fields">
                    <div class="section-title" style="margin-bottom: 15px;">👥 Data Tim</div>
                    <div class="form-group">
                        <label>Nama Tim</label>
                        <input type="text" name="nama_tim" placeholder="Contoh: Tim Inovasi UNNES">
                    </div>
                    <div class="form-group">
                        <label>Anggota Tim</label>
                        <textarea name="anggota_tim" rows="3" placeholder="Contoh:&#10;1. Budi Santoso (20210001)&#10;2. Siti Aminah (20210002)"></textarea>
                        <div class="file-info">📌 Cantumkan semua anggota tim (Nama dan NIM)</div>
                    </div>
                </div>

                <!-- Data Peserta/Ketua -->
                <div class="form-section">
                    <div class="section-title" id="dataTitle">📋 Data Peserta</div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Nama Lengkap</label>
                            <input type="text" name="nama_ketua" value="<?php echo htmlspecialchars($user_data['nama_lengkap']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>NIM</label>
                            <input type="text" name="nim_ketua" value="<?php echo htmlspecialchars($user_data['nim']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>No. Handphone</label>
                            <input type="tel" name="no_hp" placeholder="081234567890" required>
                        </div>
                        <div class="form-group">
                            <label>Fakultas</label>
                            <select name="fakultas" id="fakultas" required>
                                <option value="">Pilih Fakultas</option>
                                <?php foreach ($fakultas_list as $fak => $prodis): ?>
                                    <option value="<?php echo $fak; ?>"><?php echo $fak; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Program Studi</label>
                            <select name="prodi" id="prodi" required>
                                <option value="">Pilih Fakultas terlebih dahulu</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Data Prestasi -->
                <div class="form-section">
                    <div class="section-title">🏆 Data Prestasi</div>
                    <div class="form-group">
                        <label>Nama Kejuaraan</label>
                        <input type="text" name="nama_kejuaraan" placeholder="Sesuai nama kompetisi yang diikuti" required>
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Level Kompetisi</label>
                            <select name="level_kompetisi" id="level_kompetisi" required>
                                <option value="">Pilih Level</option>
                                <?php foreach ($level_list as $l): ?>
                                    <option value="<?php echo $l; ?>"><?php echo $l; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Kategori Prestasi</label>
                            <select name="kategori_prestasi" required>
                                <option value="">Pilih Kategori</option>
                                <?php foreach ($kategori_prestasi_list as $k): ?>
                                    <option value="<?php echo $k; ?>"><?php echo $k; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Prestasi yang diraih</label>
                            <select name="raihan_prestasi" id="raihan_prestasi" required>
                                <option value="">Pilih prestasi</option>
                                <?php foreach ($semua_raihan as $r): ?>
                                    <option value="<?php echo $r; ?>"><?php echo $r; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Tanggal Sertifikat</label>
                            <input type="date" name="tanggal_raihan" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>URL Penyelenggara (Opsional)</label>
                        <input type="url" name="url_penyelenggara" placeholder="https://...">
                    </div>
                </div>

                <!-- Upload Dokumen -->
                <div class="form-section">
                    <div class="section-title">📎 Upload Dokumen</div>
                    <div class="form-group">
                        <label>Sertifikat/Piagam/Medali (PDF)</label>
                        <input type="file" name="sertifikat" accept=".pdf" required>
                        <div class="file-info">Format PDF, maksimal 10 MB</div>
                    </div>
                    <div class="form-group">
                        <label>Foto Dokumentasi (JPG/PNG) - Bisa pilih beberapa</label>
                        <input type="file" name="foto_dokumentasi[]" accept="image/png,image/jpg,image/jpeg" multiple required>
                        <div class="file-info">📌 Bisa pilih lebih dari satu foto (tekan CTRL + klik untuk memilih beberapa file)</div>
                        <div class="file-info">Format PNG/JPG, maksimal 10 MB per file</div>
                    </div>
                    <div class="form-group">
                        <label>Surat Tugas (PDF) (Opsional)</label>
                        <input type="file" name="surat_tugas" accept=".pdf">
                        <div class="file-info">Format PDF, maksimal 10 MB</div>
                    </div>
                </div>

                <div class="note">
                    📌 Pastikan semua data diisi dengan benar. Setelah pengajuan, cek status di halaman Riwayat.
                </div>

                <div class="button-group">
                    <button type="submit" class="btn-submit">✅ AJUKAN PRESTASI</button>
                    <a href="dashboard.php" class="btn-cancel">❌ BATAL</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const individuRadio = document.querySelector('input[value="Individu"]');
    const timRadio = document.querySelector('input[value="Tim"]');
    const cardIndividu = document.getElementById('cardIndividu');
    const cardTim = document.getElementById('cardTim');
    const timFields = document.getElementById('timFields');
    const dataTitle = document.getElementById('dataTitle');
    
    function updateParticipantType() {
        if (individuRadio.checked) {
            cardIndividu.classList.add('selected');
            cardTim.classList.remove('selected');
            timFields.classList.remove('show');
            dataTitle.textContent = '📋 Data Peserta';
        } else {
            cardIndividu.classList.remove('selected');
            cardTim.classList.add('selected');
            timFields.classList.add('show');
            dataTitle.textContent = '📋 Data Ketua Tim';
        }
    }
    
    cardIndividu.onclick = () => { individuRadio.checked = true; updateParticipantType(); };
    cardTim.onclick = () => { timRadio.checked = true; updateParticipantType(); };
    updateParticipantType();
    
    const prodiData = <?php echo json_encode($fakultas_list); ?>;
    document.getElementById('fakultas').addEventListener('change', function() {
        const fakultas = this.value;
        const prodiSelect = document.getElementById('prodi');
        prodiSelect.innerHTML = '<option value="">Pilih Program Studi</option>';
        if (fakultas && prodiData[fakultas]) {
            prodiData[fakultas].forEach(prodi => {
                const option = document.createElement('option');
                option.value = prodi;
                option.textContent = prodi;
                prodiSelect.appendChild(option);
            });
        }
    });
</script>
<script src="../assets/js/burger-menu.js"></script>
</body>

</html>
