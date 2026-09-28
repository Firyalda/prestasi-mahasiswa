<?php
session_start();
echo "<h2>🔍 Cek File Upload</h2>";

// Cek apakah user login
if (!isset($_SESSION['user_id'])) {
    echo "❌ Anda belum login. Silakan login terlebih dahulu.";
    exit();
}

require_once '../config/database.php';

$user_id = $_SESSION['user_id'];

// Ambil 1 data pengajuan terbaru dari user yang login
$query = "SELECT sertifikat, foto_dokumentasi, surat_tugas FROM pengajuan WHERE user_id = $user_id ORDER BY id DESC LIMIT 1";
$result = mysqli_query($conn, $query);

if (!$result) {
    echo "❌ Error query: " . mysqli_error($conn);
    exit();
}

$data = mysqli_fetch_assoc($result);

if ($data) {
    echo "<h3>📋 Data dari database:</h3>";
    echo "Sertifikat: <b>" . htmlspecialchars($data['sertifikat']) . "</b><br>";
    echo "Foto: <b>" . htmlspecialchars($data['foto_dokumentasi']) . "</b><br>";
    echo "Surat Tugas: <b>" . htmlspecialchars($data['surat_tugas']) . "</b><br><br>";
    
    echo "<h3>📁 Cek file di folder:</h3>";
    
    // Cek file sertifikat
    if (!empty($data['sertifikat'])) {
        $sertifikat_path = "../uploads/sertifikat/" . $data['sertifikat'];
        echo "Sertifikat path: " . $sertifikat_path . "<br>";
        if (file_exists($sertifikat_path)) {
            echo "✅ File sertifikat ADA<br>";
            echo "<a href='$sertifikat_path' target='_blank'>📄 Klik untuk lihat sertifikat</a><br><br>";
        } else {
            echo "❌ File sertifikat TIDAK ADA<br><br>";
        }
    } else {
        echo "⚠️ Tidak ada file sertifikat di database<br><br>";
    }
    
    // Cek file foto
    if (!empty($data['foto_dokumentasi'])) {
        $foto_path = "../uploads/foto/" . $data['foto_dokumentasi'];
        echo "Foto path: " . $foto_path . "<br>";
        if (file_exists($foto_path)) {
            echo "✅ File foto ADA<br>";
            echo "<a href='$foto_path' target='_blank'>📸 Klik untuk lihat foto</a><br><br>";
        } else {
            echo "❌ File foto TIDAK ADA<br><br>";
        }
    } else {
        echo "⚠️ Tidak ada file foto di database<br><br>";
    }
    
    // Tampilkan semua file yang ada di folder uploads/sertifikat
    echo "<h3>📂 Semua file di folder uploads/sertifikat:</h3>";
    if (is_dir("../uploads/sertifikat/")) {
        $files = scandir("../uploads/sertifikat/");
        foreach ($files as $file) {
            if ($file != '.' && $file != '..') {
                echo "📄 <a href='../uploads/sertifikat/$file' target='_blank'>$file</a><br>";
            }
        }
    } else {
        echo "❌ Folder uploads/sertifikat tidak ditemukan<br>";
    }
    
    echo "<h3>📂 Semua file di folder uploads/foto:</h3>";
    if (is_dir("../uploads/foto/")) {
        $files = scandir("../uploads/foto/");
        foreach ($files as $file) {
            if ($file != '.' && $file != '..') {
                echo "📸 <a href='../uploads/foto/$file' target='_blank'>$file</a><br>";
            }
        }
    } else {
        echo "❌ Folder uploads/foto tidak ditemukan<br>";
    }
    
} else {
    echo "❌ Tidak ada data pengajuan untuk user ini.<br>";
    echo "Coba buat pengajuan baru terlebih dahulu.";
}
?>