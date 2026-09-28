<?php
session_start();

// Cek login dan role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'reviewer') {
    header("Location: ../login.php");
    exit();
}

require_once '../config/database.php';

$nama = $_SESSION['nama'] ?? 'Verifikator';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id == 0) {
    header("Location: dashboard.php");
    exit();
}

// Ambil data pengajuan
$query = "SELECT p.*, u.nama_lengkap, u.nim, u.prodi, u.fakultas, u.no_hp, u.email
          FROM pengajuan p 
          JOIN users u ON p.user_id = u.id 
          WHERE p.id = $id";
$result = mysqli_query($conn, $query);
$data = mysqli_fetch_assoc($result);

if (!$data) {
    header("Location: dashboard.php");
    exit();
}

// =========================================================
// AMBIL NOMINAL REWARD DARI DATABASE
// =========================================================
$level = $data['level_kompetisi'];
$raihan = $data['raihan_prestasi'];

$query_reward = "SELECT nominal FROM kategori_reward 
                 WHERE tingkat = '$level' AND raihan = '$raihan' 
                 LIMIT 1";
$result_reward = mysqli_query($conn, $query_reward);
$reward_data = mysqli_fetch_assoc($result_reward);
$nominal_reward = $reward_data ? number_format($reward_data['nominal'], 0, ',', '.') : '0';
$reward_numeric = $reward_data ? $reward_data['nominal'] : 0;

// =========================================================
// FUNGSI GENERATE CATATAN REVISI OTOMATIS (TIDAK DIGUNAKAN)
// =========================================================
function generateCatatanRevisi($data) {
    $catatan = "HASIL VERIFIKASI: REVISI\n\n";
    
    // 1. CEK SERTIFIKAT
    $ocr_text = $data['ocr_text'] ?? '';
    $ocr_accuracy = $data['ocr_accuracy'] ?? 0;
    $sertifikat = $data['sertifikat'] ?? '';
    $ext_sertifikat = !empty($sertifikat) ? strtolower(pathinfo($sertifikat, PATHINFO_EXTENSION)) : '';
    
    if (empty($ocr_text) || $ocr_accuracy < 0.5){
        $catatan .= "1. Sertifikat: Tidak Terdeteksi / Tidak Terbaca\n";
        $catatan .= "   - File sertifikat tidak terbaca oleh sistem OCR\n";
        $catatan .= "   - Mohon upload ulang file sertifikat dengan kualitas yang lebih baik (resolusi tinggi, jelas)\n";
        if (!empty($ext_sertifikat) && $ext_sertifikat != 'pdf') {
            $catatan .= "   - Format file harus PDF (sekarang: " . strtoupper($ext_sertifikat) . ")\n";
        }
        $catatan .= "\n";
    } else {
        $catatan .= "1. Sertifikat: Valid\n";
        $catatan .= "   - Sertifikat terverifikasi oleh sistem\n\n";
    }
    
    // 2. CEK FOTO
    $foto = $data['foto_dokumentasi'] ?? '';
    if (!empty($foto)) {
        $foto_list = explode(',', $foto);
        $catatan .= "2. Foto: Valid\n";
        $catatan .= "   - " . count($foto_list) . " foto dokumentasi terupload dan sesuai\n\n";
    } else {
        $catatan .= "2. Foto: Tidak Ada\n";
        $catatan .= "   - Belum ada foto dokumentasi yang diupload\n";
        $catatan .= "   - Mohon upload foto dokumentasi kegiatan (format JPG/PNG)\n\n";
    }
    
    // 3. CEK SURAT TUGAS
    $surat_tugas = $data['surat_tugas'] ?? '';
    $ext_surat = !empty($surat_tugas) ? strtolower(pathinfo($surat_tugas, PATHINFO_EXTENSION)) : '';
    
    if (!empty($surat_tugas)) {
        if ($ext_surat == 'pdf') {
            $catatan .= "3. Surat Tugas: Valid\n";
            $catatan .= "   - Surat tugas dari kampus terupload dan valid\n\n";
        } else {
            $catatan .= "3. Surat Tugas: Format Tidak Sesuai\n";
            $catatan .= "   - Format file harus PDF (sekarang: " . strtoupper($ext_surat) . ")\n";
            $catatan .= "   - Mohon upload ulang surat tugas dalam format PDF\n\n";
        }
    } else {
        $catatan .= "3. Surat Tugas: Tidak Ada\n";
        $catatan .= "   - Surat tugas belum diupload\n";
        $catatan .= "   - Mohon upload surat tugas dari kampus (format PDF)\n\n";
    }
    
    // REKOMENDASI & CATATAN
    $catatan .= "REKOMENDASI: REVISI\n";
    $catatan .= "Catatan:\n";
    
    if (empty($ocr_text) || $ocr_accuracy < 0.5) {
        $catatan .= "Mohon upload ulang file sertifikat dengan kualitas yang lebih baik agar dapat diverifikasi.\n";
        $catatan .= "Pastikan file sertifikat:\n";
        $catatan .= "- Berformat PDF\n";
        $catatan .= "- Resolusi gambar jelas (minimal 300 dpi)\n";
        $catatan .= "- Nama dan tanggal terbaca dengan baik\n\n";
    }
    
    if (empty($foto)) {
        $catatan .= "Mohon upload foto dokumentasi kegiatan sebagai bukti pendukung (format JPG/PNG).\n\n";
    }
    
    if (empty($surat_tugas) || (!empty($ext_surat) && $ext_surat != 'pdf')) {
        $catatan .= "Mohon upload surat tugas dari kampus dalam format PDF.\n\n";
    }
    
    $catatan .= "Batas waktu revisi: 7 hari setelah notifikasi ini.";
    
    return $catatan;
}

// =========================================================
// FUNGSI GENERATE CATATAN PENOLAKAN OTOMATIS
// =========================================================
function generateCatatanPenolakan($data) {

    $catatan = "HASIL VERIFIKASI: DITOLAK\n\n";

    // ==========================
    // CEK SERTIFIKAT
    // ==========================
    $ocr_text = strtolower(trim($data['ocr_text'] ?? ''));
    $ocr_accuracy = $data['ocr_accuracy'] ?? 0;
    $sertifikat = $data['sertifikat'] ?? '';
    $ext_sertifikat = !empty($sertifikat)
        ? strtolower(pathinfo($sertifikat, PATHINFO_EXTENSION))
        : '';

    // Daftar keyword penting
    $keywords = [
        'sertifikat',
        'juara',
        'penghargaan',
        'diberikan',
        'kepada',
        'sebagai',
        'lomba',
        'kompetisi',
        'penyelenggara',
        'nasional'
    ];

    $found = 0;
    $keyword_ditemukan = [];

    foreach ($keywords as $kw) {
        if (stripos($ocr_text, $kw) !== false) {
            $found++;
            $keyword_ditemukan[] = $kw;
        }
    }

    if (empty($ocr_text) || $ocr_accuracy < 0.5) {

        $catatan .= "1. Sertifikat: Tidak Valid\n";
        $catatan .= "   - OCR gagal membaca isi sertifikat.\n";
        $catatan .= "   - Sertifikat tidak dapat diverifikasi.\n";

        if (!empty($ext_sertifikat) && $ext_sertifikat != 'pdf') {
            $catatan .= "   - Format file harus PDF (sekarang: "
                      . strtoupper($ext_sertifikat) . ")\n";
        }

        $catatan .= "\n";

    } elseif ($found < 3) {

        $catatan .= "1. Sertifikat: Tidak Valid\n";
        $catatan .= "   - Hanya ditemukan $found dari "
                  . count($keywords) . " kata kunci penting.\n";

        if (!empty($keyword_ditemukan)) {
            $catatan .= "   - Keyword ditemukan: "
                      . implode(', ', $keyword_ditemukan) . "\n";
        }

        $catatan .= "   - Dokumen tidak sesuai karakteristik sertifikat prestasi.\n\n";

    } else {

        $catatan .= "1. Sertifikat: Valid\n";
        $catatan .= "   - OCR berhasil membaca sertifikat.\n";
        $catatan .= "   - Keyword ditemukan ($found/"
                  . count($keywords) . "): "
                  . implode(', ', $keyword_ditemukan) . "\n";
        $catatan .= "   - Sertifikat memenuhi indikator dasar keaslian.\n\n";
    }

    // ==========================
    // CEK FOTO
    // ==========================
    $foto = trim($data['foto_dokumentasi'] ?? '');

    if (!empty($foto)) {

        $foto_list = array_filter(explode(',', $foto));

        if (count($foto_list) >= 3) {

            $catatan .= "2. Foto: Valid\n";
            $catatan .= "   - Terdapat " . count($foto_list) . " foto dokumentasi.\n\n";

        } else {

            $catatan .= "2. Foto: Tidak Valid\n";
            $catatan .= "   - Foto yang diupload bukan dokumentasi kegiatan lomba.\n";
            $catatan .= "   - Dokumentasi foto harus berupa foto saat mengikuti lomba/kegiatan.\n\n";

        }

    } else {

        $catatan .= "2. Foto: Tidak Ada\n";
        $catatan .= "   - Dokumentasi kegiatan belum diunggah.\n\n";

    }

    // ==========================
    // CEK SURAT TUGAS
    // ==========================
    $surat = $data['surat_tugas'] ?? '';
    $ext = !empty($surat)
        ? strtolower(pathinfo($surat, PATHINFO_EXTENSION))
        : '';

    if (empty($surat)) {

        $catatan .= "3. Surat Tugas: Tidak Ada\n";
        $catatan .= "   - Surat tugas belum diunggah.\n\n";

    } elseif ($ext != 'pdf') {

        $catatan .= "3. Surat Tugas: Tidak Valid\n";
        $catatan .= "   - Surat tugas harus berupa PDF.\n\n";

    } else {

        $catatan .= "3. Surat Tugas: Valid\n";
        $catatan .= "   - Surat tugas sesuai.\n\n";

    }

    // ==========================
    // REKOMENDASI
    // ==========================
    $catatan .= "REKOMENDASI: DITOLAK\n";
    $catatan .= "Catatan:\n";

    if (empty($ocr_text) || $ocr_accuracy < 0.5) {
        $catatan .= "- OCR gagal membaca isi sertifikat.\n";
    }

    if ($found < 3) {
        $catatan .= "- Kata kunci penting pada sertifikat sangat sedikit.\n";
    }

    if (empty($foto) || count(array_filter(explode(',', $foto))) < 3) {
        $catatan .= "- Dokumentasi foto belum memenuhi persyaratan.\n";
    }

    if (empty($surat) || $ext != 'pdf') {
        $catatan .= "- Surat tugas belum sesuai persyaratan.\n";
    }

    $catatan .= "\nKeputusan ini bersifat final dan tidak dapat diajukan kembali.";

    return $catatan;
}

// =========================================================
// GENERATE CATATAN REVISI & PENOLAKAN OTOMATIS
// =========================================================
$catatan_revisi_otomatis = generateCatatanRevisi($data);
$catatan_penolakan_otomatis = generateCatatanPenolakan($data);

// =========================================================
// PROSES VERIFIKASI - UPDATE STATUS UNTUK ADMIN
// =========================================================
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan'])) {
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $catatan = mysqli_real_escape_string($conn, $_POST['catatan']);
    
    // ===== HAPUS EMOJI DARI CATATAN =====
    $catatan = preg_replace('/[\x{1F600}-\x{1F64F}]/u', '', $catatan);
    $catatan = preg_replace('/[\x{1F300}-\x{1F5FF}]/u', '', $catatan);
    $catatan = preg_replace('/[\x{1F680}-\x{1F6FF}]/u', '', $catatan);
    $catatan = preg_replace('/[\x{1F700}-\x{1F77F}]/u', '', $catatan);
    $catatan = preg_replace('/[\x{1F780}-\x{1F7FF}]/u', '', $catatan);
    $catatan = preg_replace('/[\x{1F800}-\x{1F8FF}]/u', '', $catatan);
    $catatan = preg_replace('/[\x{1F900}-\x{1F9FF}]/u', '', $catatan);
    $catatan = preg_replace('/[\x{1FA00}-\x{1FA6F}]/u', '', $catatan);
    $catatan = preg_replace('/[\x{2600}-\x{26FF}]/u', '', $catatan);
    $catatan = preg_replace('/[\x{2700}-\x{27BF}]/u', '', $catatan);
    $catatan = preg_replace('/\x{FE0F}/u', '', $catatan);
    
    // ===== AMBIL admin_catatan YANG SUDAH ADA =====
    $query_get = "SELECT admin_catatan FROM pengajuan WHERE id = $id";
    $result_get = mysqli_query($conn, $query_get);
    $row_get = mysqli_fetch_assoc($result_get);
    
    $catatan_lama = $row_get['admin_catatan'] ?? '';
    $catatan_baru = $catatan_lama . "\nVerifikator: " . $catatan;
    
    // ===== UPDATE TANPA CONCAT =====
    if ($status == 'disetujui') {
        $query_update = "UPDATE pengajuan SET 
                            status = '$status',
                            reward_nominal = $reward_numeric,
                            admin_catatan = '$catatan_baru'
                         WHERE id = $id";
    } else {
        $query_update = "UPDATE pengajuan SET 
                            status = '$status',
                            admin_catatan = '$catatan_baru'
                         WHERE id = $id";
    }
    
    if (mysqli_query($conn, $query_update)) {
        
        // =========================================================
        // 🔔 KIRIM NOTIFIKASI KE ADMIN
        // =========================================================
        
        // Ambil user_id Admin
        $query_admin = "SELECT id FROM users WHERE role = 'admin' LIMIT 1";
        $result_admin = mysqli_query($conn, $query_admin);
        $admin = mysqli_fetch_assoc($result_admin);
        
        if ($admin) {
            $status_label = strtoupper($status);
            
            $judul = "Hasil Verifikasi dari Tim Verifikator";
            
            // ===== PESAN BERDASARKAN STATUS =====
            if ($status == 'revisi') {
                $pesan = "Verifikator telah menyelesaikan verifikasi pengajuan atas nama " . $data['nama_lengkap'] . 
                         " (NIM: " . $data['nim'] . ") dengan status: " . $status_label . 
                         ".\n\nPengajuan ini perlu direvisi oleh mahasiswa. Silakan kirim notifikasi revisi ke mahasiswa.";
            } elseif ($status == 'ditolak') {
                $pesan = "Verifikator telah menyelesaikan verifikasi pengajuan atas nama " . $data['nama_lengkap'] . 
                         " (NIM: " . $data['nim'] . ") dengan status: " . $status_label . 
                         ".\n\nPengajuan ini ditolak. Silakan cek detail pengajuan untuk melihat alasan penolakan.";
            } else {
                $pesan = "Verifikator telah menyelesaikan verifikasi pengajuan atas nama " . $data['nama_lengkap'] . 
                         " (NIM: " . $data['nim'] . ") dengan status: " . $status_label . 
                         ".\n\nSilakan cek dan tentukan nominal reward.";
            }
            
            $link = "../admin/detail_pengajuan.php?id=" . $id;
            
            $query_notif = "INSERT INTO notifikasi (user_id, role, judul, pesan, link) 
                            VALUES ('" . $admin['id'] . "', 'admin', '$judul', '$pesan', '$link')";
            mysqli_query($conn, $query_notif);
        }
        
        $message = 'Hasil verifikasi berhasil disimpan! Notifikasi telah dikirim ke Admin.';
        $message_type = 'success';
    } else {
        $message = 'Gagal menyimpan: ' . mysqli_error($conn);
        $message_type = 'error';
    }
}

include 'includes/sidebar.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Berkas - Verifikator</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; background: #f1f4f8; display: flex; min-height: 100vh; }


        .main-content { margin-left: 250px; padding: 28px 32px; width: 100%; min-height: 100vh; }

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

        .card { background: white; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); padding: 20px 24px; margin-bottom: 20px; }
        .card-title { font-size: 15px; font-weight: 600; color: #0f172a; margin-bottom: 12px; }

        .alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; font-size: 14px; display: flex; align-items: center; gap: 10px; }
        .alert-success { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
        .alert-error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }

        .status-info {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 16px;
            font-size: 14px;
        }
        .status-info .label { font-weight: 600; display: block; margin-bottom: 4px; }
        .status-info.success { background: #d4edda; border: 1px solid #28a745; color: #155724; }
        .status-info.danger { background: #f8d7da; border: 1px solid #dc3545; color: #721c24; }
        .status-info.warning { background: #fff3cd; border: 1px solid #ffc107; color: #856404; }
        .status-info.info { background: #d1ecf1; border: 1px solid #17a2b8; color: #0c5460; }

        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; font-size: 13px; font-weight: 500; color: #475569; margin-bottom: 4px; }
        .form-group textarea, .form-group select {
            width: 100%;
            padding: 10px 14px;
            border: 1.5px solid #e2e8f0;
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            transition: 0.2s;
            background: #fafcff;
        }
        .form-group textarea:focus, .form-group select:focus {
            outline: none;
            border-color: #8b5cf6;
            box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.08);
        }
        .form-group textarea { resize: vertical; min-height: 120px; }

        .btn-group { display: flex; gap: 12px; flex-wrap: wrap; margin-top: 8px; }
        .btn {
            padding: 10px 28px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            border: none;
            cursor: pointer;
            transition: 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }
        .btn:hover { transform: translateY(-2px); }
        .btn-success { background: #10b981; color: white; }
        .btn-success:hover { background: #059669; }
        .btn-secondary { background: #e2e8f0; color: #475569; }
        .btn-secondary:hover { background: #cbd5e1; }

        .doc-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin-bottom: 16px;
        }
        .doc-item {
            background: #ffffff;
            border-radius: 10px;
            padding: 14px 16px;
            border: 1px solid #edf2f7;
        }
        .doc-item .doc-label {
            font-size: 13px;
            font-weight: 600;
            color: #475569;
            margin-bottom: 6px;
        }
        .doc-item .doc-status {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }
        .doc-item .doc-status .status-text { font-size: 14px; font-weight: 500; color: #0f172a; }

        .btn-link {
            font-size: 12px;
            color: #4a6cf7;
            text-decoration: none;
            background: #f0f4ff;
            padding: 2px 12px;
            border-radius: 4px;
            transition: 0.2s;
        }
        .btn-link:hover { background: #e0e7ff; }

        .kelengkapan-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px 16px;
            font-size: 14px;
        }
        .kelengkapan-grid .item {
            display: flex;
            align-items: center;
            gap: 6px;
            color: #475569;
        }
        .kelengkapan-grid .item .label { font-weight: 500; color: #0f172a; }
        .kelengkapan-grid .item .value { color: #0f172a; }

        .status-badge {
            display: inline-block;
            padding: 2px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
        }
        .status-badge.lengkap { background: #d4edda; color: #155724; }

        .ocr-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 16px 20px;
            margin-bottom: 14px;
            border-left: 4px solid #8b5cf6;
        }
        .ocr-box .ocr-label {
            font-weight: 600;
            color: #0f172a;
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
        }
        .ocr-box .ocr-text {
            background: #ffffff;
            padding: 14px 18px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            font-size: 14px;
            line-height: 1.9;
            color: #1a1a1a;
            white-space: pre-wrap;
            max-height: 200px;
            overflow-y: auto;
            font-family: 'Courier New', monospace;
        }
        .keyword-box {
            margin-top: 12px;
            background: #f1f5f9;
            padding: 10px 16px;
            border-radius: 8px;
        }
        .keyword-box .keyword-label {
            font-size: 13px;
            font-weight: 500;
            color: #475569;
        }
        .keyword-box .keyword-list {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-top: 4px;
        }
        .keyword-box .keyword-list .tag {
            background: #e2e8f0;
            padding: 2px 12px;
            border-radius: 20px;
            font-size: 12px;
            color: #0f172a;
        }
        .ocr-result {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px 16px;
            margin-top: 12px;
            font-size: 14px;
            padding: 12px 16px;
            border-radius: 8px;
        }
        .ocr-result .item { color: #475569; }
        .ocr-result .item strong { color: #0f172a; }

        .info-box {
            background: #e8f5e9;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 16px;
            border-left: 4px solid #2e7d32;
            font-size: 14px;
            color: #1e293b;
        }
        .info-box i { margin-right: 8px; color: #2e7d32; }

        .info-grid {
            display: grid;
            grid-template-columns: 140px 1fr;
            gap: 6px 16px;
            font-size: 14px;
            margin-bottom: 20px;
        }
        .info-grid .label { color: #64748b; font-weight: 500; }
        .info-grid .value { color: #0f172a; }
        .info-grid .value .bold { font-weight: 500; }

        /* ===== TEMPLATE CATATAN ===== */
        .template-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 16px;
        }
        .template-item {
            border-radius: 8px;
            padding: 12px 14px;
            font-size: 13px;
            cursor: pointer;
            transition: 0.2s;
        }
        .template-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .template-item .template-title {
            font-weight: 700;
            display: block;
            margin-bottom: 4px;
        }
        .template-item .template-body {
            font-size: 12px;
            white-space: pre-wrap;
            line-height: 1.6;
        }
        .template-sesuai {
            background: #d4edda;
            border: 2px solid #28a745;
            color: #155724;
        }
        .template-tidak {
            background: #f8d7da;
            border: 2px solid #dc3545;
            color: #721c24;
        }

        /* ===== PANDUAN ===== */
        .panduan-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 16px;
            font-size: 13px;
            color: #475569;
        }
        .panduan-box .reward-info {
            font-size: 12px;
            color: #64748b;
            margin-top: 4px;
        }
        .panduan-box .reward-info strong {
            color: #0f172a;
        }

        .btn-link {
            font-size: 12px;
            color: #4a6cf7;
            text-decoration: none;
            background: #f0f4ff;
            padding: 2px 12px;
            border-radius: 4px;
            transition: 0.2s;
        }
        .btn-link:hover { background: #e0e7ff; }

        /* ===== TOMBOL STATUS VERIFIKASI ===== */
        .status-options {
            display: flex;
            gap: 12px;
            margin-bottom: 16px;
            flex-wrap: wrap;
        }
        .status-card {
            flex: 1;
            min-width: 150px;
            padding: 16px 20px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            cursor: pointer;
            transition: 0.2s;
            display: flex;
            align-items: center;
            gap: 12px;
            background: #ffffff;
        }
        .status-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
        .status-card.active {
            border-color: #8b5cf6;
            background: #f8f4ff;
            box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.15);
        }
        .status-card i {
            font-size: 24px;
        }
        .status-card-content .status-name {
            display: block;
            font-weight: 600;
            font-size: 14px;
            color: #0f172a;
        }
        .status-card-content small {
            font-size: 12px;
            color: #64748b;
        }

    </style>
</head>
<body>

<div class="main-content">

    <!-- HEADER -->
    <div class="header">
        <h1>📄 Review Berkas</h1>
        <div class="user">
            <span><?php echo date('l, d F Y'); ?></span>
            <div class="avatar"><?php echo strtoupper(substr($nama, 0, 1)); ?></div>
            <span><?php echo htmlspecialchars($nama); ?></span>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?php echo $message_type; ?>">
            <i class="fas <?php echo $message_type == 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <!-- STATUS SAAT INI -->
    <?php if ($data['status'] == 'disetujui'): ?>
        <div class="status-info success">
            <span class="label">Status Saat Ini: DISETUJUI</span>
            Pengajuan ini telah disetujui oleh Verifikator. Admin akan menentukan reward.
        </div>
    <?php elseif ($data['status'] == 'ditolak'): ?>
        <div class="status-info danger">
            <span class="label">Status Saat Ini: DITOLAK</span>
            Pengajuan ini ditolak oleh Verifikator. Admin akan mengirim notifikasi.
        </div>
    <?php elseif ($data['status'] == 'revisi'): ?>
        <div class="status-info warning">
            <span class="label">Status Saat Ini: REVISI</span>
            Pengajuan ini memerlukan revisi. Admin akan mengirim permintaan revisi ke mahasiswa.
        </div>
    <?php else: ?>
        <div class="status-info info">
            <span class="label">Status Saat Ini: DIPROSES</span>
            Silakan lakukan verifikasi keaslian dokumen.
        </div>
    <?php endif; ?>

    <div class="card">
        <form method="POST">

            <!-- ========================================================= -->
            <!-- INFORMASI MAHASISWA                                        -->
            <!-- ========================================================= -->
            <div class="card-title">Informasi Mahasiswa</div>
            <div class="info-grid">
                <div class="label">Nama Lengkap</div>
                <div class="value">: <?php echo htmlspecialchars($data['nama_lengkap']); ?></div>
                
                <div class="label">NIM</div>
                <div class="value">: <?php echo $data['nim']; ?></div>
                
                <div class="label">Program Studi</div>
                <div class="value">: <?php echo htmlspecialchars($data['prodi'] ?? 'Teknik Informatika'); ?></div>
                
                <div class="label">No. Handphone</div>
                <div class="value">: <?php echo htmlspecialchars($data['no_hp'] ?? '-'); ?></div>
                
                <div class="label">Email</div>
                <div class="value">: <?php echo htmlspecialchars($data['email'] ?? '-'); ?></div>
            </div>

            <!-- ========================================================= -->
            <!-- DATA PRESTASI - PENYELENGGARA DAN LOKASI DIHAPUS          -->
            <!-- ========================================================= -->
            <div class="card-title">Data Prestasi</div>
            <div class="info-grid">
                <div class="label">Nama Kejuaraan</div>
                <div class="value">: <?php echo htmlspecialchars($data['nama_kejuaraan']); ?></div>
                
                <div class="label">Level Kompetisi</div>
                <div class="value">: <?php echo $data['level_kompetisi']; ?></div>
                
                <div class="label">Kategori Prestasi</div>
                <div class="value">: <?php echo htmlspecialchars($data['kategori_prestasi'] ?? '-'); ?></div>
                
                <div class="label">Raihan Prestasi</div>
                <div class="value">: <?php echo $data['raihan_prestasi']; ?></div>
                
                <div class="label">Tanggal Perolehan</div>
                <div class="value">: <?php echo date('d F Y', strtotime($data['tanggal_raihan'])); ?></div>
            </div>

            <!-- ========================================================= -->
            <!-- DOKUMEN PENDUKUNG                                         -->
            <!-- ========================================================= -->
            <div class="card-title">Dokumen Pendukung</div>
            <div class="doc-grid">
                <div class="doc-item">
                    <div class="doc-label"><i class="fas fa-file-pdf" style="color: #ef4444;"></i> Sertifikat</div>
                    <div class="doc-status">
                        <span class="status-text">Ada</span>
                        <?php if ($data['sertifikat']): ?>
                            <a href="../uploads/sertifikat/<?php echo $data['sertifikat']; ?>" target="_blank" class="btn-link">Lihat</a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="doc-item">
                    <div class="doc-label"><i class="fas fa-image" style="color: #22c55e;"></i> Foto</div>
                    <div class="doc-status">
                        <span class="status-text">Ada</span>
                        <?php if ($data['foto_dokumentasi']): ?>
                            <a href="../uploads/foto/<?php echo $data['foto_dokumentasi']; ?>" target="_blank" class="btn-link">Lihat</a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="doc-item">
                    <div class="doc-label"><i class="fas fa-file-alt" style="color: #3b82f6;"></i> Surat Tugas</div>
                    <div class="doc-status">
                        <span class="status-text">Ada</span>
                        <?php if ($data['surat_tugas']): ?>
                            <a href="../uploads/surat_tugas/<?php echo $data['surat_tugas']; ?>" target="_blank" class="btn-link">Lihat</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- ========================================================= -->
            <!-- CEK KELENGKAPAN BERKAS                                    -->
            <!-- ========================================================= -->
            <div class="card-title">Cek Kelengkapan Berkas</div>
            <div style="background: #fafbfc; border: 1px solid #edf2f7; border-radius: 10px; padding: 14px 18px; margin-bottom: 14px;">
                <div class="kelengkapan-grid">
                    <div class="item">
                        <span class="label">Sertifikat:</span>
                        <span class="value">Terupload</span>
                    </div>
                    <div class="item">
                        <span class="label">Foto:</span>
                        <span class="value">Terupload</span>
                    </div>
                    <div class="item">
                        <span class="label">Surat Tugas:</span>
                        <span class="value">Terupload</span>
                    </div>
                </div>
                <div style="margin-top: 10px; padding-top: 10px; border-top: 1px solid #edf2f7; display: flex; align-items: center; gap: 8px;">
                    <span style="font-size: 13px; font-weight: 500; color: #475569;">Status:</span>
                    <span class="status-badge lengkap">LENGKAP</span>
                </div>
            </div>

            <!-- ========================================================= -->
            <!-- HASIL OCR                                                 -->
            <!-- ========================================================= -->
           <!-- ========================================================= -->
<!-- HASIL OCR (DIPERBAIKI - MENGGUNAKAN 0.5 BUKAN 50)        -->
<!-- ========================================================= -->
<div class="card-title">Hasil OCR</div>
<div class="ocr-box" style="border-left-color: <?php echo (empty($data['ocr_text']) || ($data['ocr_accuracy'] ?? 0) < 0.5) ? '#c62828' : '#8b5cf6'; ?>;">
    
    <?php if (empty($data['ocr_text']) || ($data['ocr_accuracy'] ?? 0) < 0.5): ?>
        <div style="background: #fff3cd; padding: 12px 16px; border-radius: 8px; margin-bottom: 12px; border-left: 4px solid #ffc107;">
            <span style="font-weight: 600; color: #856404;">
                ⚠️ OCR belum berhasil memproses sertifikat.
            </span>
            <p style="font-size: 13px; color: #856404; margin-top: 4px;">
                Pastikan file sertifikat terbaca dengan jelas. Verifikator harus memeriksa keaslian secara manual.
            </p>
        </div>
    <?php endif; ?>
    
   <span class="ocr-label">Ekstraksi Teks dari Sertifikat:</span>

<?php
// ===========================
// DATA OCR
// ===========================
$ocr_text = strtolower(trim($data['ocr_text'] ?? ''));
$accuracy = $data['ocr_accuracy'] ?? 0;

// Daftar keyword
$keywords = [
    'sertifikat',
    'nasional',
    'juara',
    'penghargaan',
    'diberikan',
    'kepada',
    'sebagai',
    'lomba',
    'kompetisi',
    'penyelenggara'
];

$jumlah_keyword = 0;
$keyword_ditemukan = [];

foreach ($keywords as $kw) {

    if (stripos($ocr_text, $kw) !== false) {

        $jumlah_keyword++;
        $keyword_ditemukan[] = $kw;

    }

}

$total_keyword = count($keywords);

// Skor validasi berdasarkan keyword
$total_keyword = count($keywords);

$skor_validasi = ($total_keyword > 0)
    ? ($jumlah_keyword / $total_keyword) * 100
    : 0;

if ($skor_validasi >= 70) {

    $status = "VALID";
    $warna_status = "#2e7d32";
    $background = "#e8f5e9";

} elseif ($skor_validasi >= 40) {

    $status = "PERLU VERIFIKASI";
    $warna_status = "#f57c00";
    $background = "#fff8e1";

} else {

    $status = "TIDAK VALID";
    $warna_status = "#c62828";
    $background = "#ffebee";

}
?>

<div class="ocr-text"
style="<?php echo empty($ocr_text)
    ? 'background:#fff3cd;border-color:#ffc107;color:#856404;'
    : ''; ?>">

<?php
if (empty($ocr_text)) {

    echo "⚠️ Teks tidak berhasil dibaca oleh OCR.";

} else {

    echo nl2br(htmlspecialchars($data['ocr_text']));

}
?>

</div>

<div class="keyword-box">

    <div class="keyword-label">
        Hasil Pencocokan Kata Kunci
    </div>

    <div class="keyword-list">

        <?php foreach($keywords as $kw): ?>

            <?php if(stripos($ocr_text,$kw)!==false): ?>

                <span class="tag"
                style="
                background:#d4edda;
                color:#155724;
                border:1px solid #28a745;">
                    ✓ <?php echo ucfirst($kw); ?>
                </span>

            <?php else: ?>

                <span class="tag"
                style="
                background:#f8d7da;
                color:#721c24;
                border:1px solid #dc3545;">
                    ✗ <?php echo ucfirst($kw); ?>
                </span>

            <?php endif; ?>

        <?php endforeach; ?>

    </div>

    <div style="margin-top:12px;font-size:14px;">

        <strong>Total Keyword :</strong>

        <?php echo $jumlah_keyword; ?>

        dari

        <?php echo count($keywords); ?>

    </div>

</div>

<div class="ocr-result" style="background: <?= $background; ?>;">

    <span class="item">
        OCR Accuracy :
        <strong>
            <?= number_format($accuracy * 100, 2); ?>%
        </strong>
    </span>

    <span class="item">
        Keyword Ditemukan :
        <strong>
            <?= $jumlah_keyword; ?> / <?= $total_keyword; ?>
        </strong>
    </span>

    <span class="item">
        Skor Validasi :
        <strong>
            <?= number_format($skor_validasi, 0); ?>%
        </strong>
    </span>

    <span class="item" style="color: <?= $warna_status; ?>;">
        Status :
        <strong><?= $status; ?></strong>
    </span>

</div>

            <!-- ========================================================= -->
            <!-- CATATAN / REKOMENDASI VERIFIKATOR (MANUAL)                -->
            <!-- ========================================================= -->
            <div class="card-title">Catatan / Rekomendasi untuk Admin</div>

            <!-- PANDUAN -->
            <div class="panduan-box">
                <i class="fas fa-lightbulb" style="color: #fbbf24;"></i>
                <strong>Panduan:</strong> Pilih salah satu template di bawah, lalu sesuaikan isinya.
                <div class="reward-info">
                    Level: <strong><?php echo $level; ?></strong> | 
                    Raihan: <strong><?php echo $raihan; ?></strong> | 
                    Reward Otomatis: <strong>Rp <?php echo $nominal_reward; ?></strong>
                </div>
            </div>

            <!-- ========================================================= -->
            <!-- TEMPLATE CATATAN - SUDAH DIPERBAIKI                       -->
            <!-- ========================================================= -->
            <div class="template-grid">
                
                <!-- TEMPLATE 1: SESUAI -->
                <div class="template-item template-sesuai" onclick="document.getElementById('catatan_verifikator').value = this.querySelector('.template-body').innerText.trim();">
                    <span class="template-title">SESUAI</span>
                    <div class="template-body">HASIL VERIFIKASI: SESUAI

1. Sertifikat: Valid
   - OCR berhasil membaca sertifikat.
   - Keyword ditemukan (<?php echo $jumlah_keyword; ?>/<?php echo count($keywords); ?>): <?php echo implode(', ', $keyword_ditemukan); ?>
   - Sertifikat memenuhi indikator dasar keaslian.

2. Foto: Lengkap
   - Dokumentasi kegiatan terupload dan sesuai.

3. Surat Tugas: Valid
   - Surat tugas dari kampus terupload dan valid.

REKOMENDASI: DISETUJUI
Reward: Rp <?php echo $nominal_reward; ?> (Level: <?php echo $level; ?> | Raihan: <?php echo $raihan; ?>)</div>
                </div>

                <!-- TEMPLATE 2: TIDAK SESUAI / DITOLAK (SUDAH DIPERBAIKI) -->
                <div class="template-item template-tidak" onclick="document.getElementById('catatan_verifikator').value = this.querySelector('.template-body').innerText.trim();">
                    <span class="template-title">TIDAK SESUAI</span>
                    <div class="template-body">HASIL VERIFIKASI: DITOLAK

1. Sertifikat: Tidak Valid
   - OCR gagal membaca isi sertifikat dengan baik.
   - Hanya ditemukan <?php echo $jumlah_keyword; ?> dari <?php echo count($keywords); ?> kata kunci penting.
   - Dokumen tidak sesuai karakteristik sertifikat prestasi.
   - Mohon upload ulang sertifikat dengan kualitas yang lebih baik (resolusi tinggi, jelas, format PDF).

2. Foto: Tidak Valid
   - Foto yang diupload bukan dokumentasi kegiatan lomba.
   - Dokumentasi foto harus berupa foto saat mengikuti lomba/kegiatan.
   - Minimal 3 foto dokumentasi yang jelas.

3. Surat Tugas: Tidak Valid
   - Surat tugas belum diupload atau format tidak sesuai.
   - Surat tugas harus berupa PDF dari kampus/instansi.

REKOMENDASI: DITOLAK
Catatan:
- Sertifikat tidak dapat diverifikasi karena OCR gagal membaca dengan baik.
- Dokumentasi foto belum memenuhi persyaratan minimal (3 foto).
- Surat tugas belum sesuai persyaratan (format PDF).

Keputusan ini bersifat final dan tidak dapat diajukan kembali.</div>
                </div>

            </div>

            <!-- TEXTAREA INPUT (TANPA EMOJI) -->
            <div class="form-group">
                <label>Tulis Catatan di sini (klik template di atas untuk mengisi otomatis)</label>
                <textarea id="catatan_verifikator" name="catatan" rows="16" placeholder="Tulis catatan atau rekomendasi untuk Admin..."><?php if ($data['status'] == 'ditolak'): ?><?php echo $catatan_penolakan_otomatis; ?><?php else: ?>HASIL VERIFIKASI: SESUAI

1. Sertifikat: Valid
2. Foto: Lengkap
3. Surat Tugas: Valid

REKOMENDASI: DISETUJUI
Reward: Rp <?php echo $nominal_reward; ?> (Level: <?php echo $level; ?> | Raihan: <?php echo $raihan; ?>)<?php endif; ?></textarea>
            </div>

            <!-- ========================================================= -->
            <!-- FORM VERIFIKASI (TOMBOB STATUS)                           -->
            <!-- ========================================================= -->
            <div class="card-title">Verifikasi (Kirim ke Admin)</div>
            
            <div class="info-box">
                <i class="fas fa-info-circle"></i> 
                Pilih status di bawah ini untuk mengirim hasil verifikasi ke Admin.
                Admin akan menentukan nominal reward berdasarkan keputusan Anda.
            </div>

            <!-- PILIHAN STATUS (RADIO CARD) -->
            <div class="status-options">
                <div class="status-card active" onclick="pilihStatus('diproses')" id="card-diproses">
                    <i class="fas fa-clock" style="color: #f59e0b;"></i>
                    <div class="status-card-content">
                        <span class="status-name">Diproses</span>
                        <small>Belum diverifikasi</small>
                    </div>
                </div>
                <div class="status-card" onclick="pilihStatus('disetujui')" id="card-disetujui">
                    <i class="fas fa-check-circle" style="color: #10b981;"></i>
                    <div class="status-card-content">
                        <span class="status-name">Disetujui</span>
                        <small>Valid/Asli → Kirim ke Admin</small>
                    </div>
                </div>
                <div class="status-card" onclick="pilihStatus('ditolak')" id="card-ditolak">
                    <i class="fas fa-times-circle" style="color: #ef4444;"></i>
                    <div class="status-card-content">
                        <span class="status-name">Ditolak</span>
                        <small>Tidak Valid → Kirim ke Admin</small>
                    </div>
                </div>
            </div>

            <input type="hidden" name="status" id="status_terpilih" value="diproses">

            <div class="btn-group">
                <button type="submit" name="simpan" class="btn btn-success">
                    <i class="fas fa-paper-plane"></i> Kirim ke Admin
                </button>
                <a href="dashboard.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>

            <div style="margin-top: 12px; font-size: 12px; color: #94a3b8; text-align: center;">
                <i class="fas fa-info-circle"></i> Setelah Anda mengirim, Admin akan menerima hasil verifikasi dan menentukan nominal reward.
            </div>

        </form>
    </div>

</div>

<!-- ========================================================= -->
<!-- JAVASCRIPT UNTUK PILIH STATUS                            -->
<!-- ========================================================= -->
<script>
function pilihStatus(status) {
    // Hapus active dari semua card
    document.querySelectorAll('.status-card').forEach(function(c) {
        c.classList.remove('active');
    });
    // Tambah active ke card yang dipilih
    document.getElementById('card-' + status).classList.add('active');
    // Set value hidden input
    document.getElementById('status_terpilih').value = status;
}
</script>

</body>
</html> 