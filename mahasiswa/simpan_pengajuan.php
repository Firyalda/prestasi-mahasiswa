<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Upload file
    $target_dir = "../uploads/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $sertifikat = time() . "_sertifikat_" . basename($_FILES["sertifikat"]["name"]);
    $foto = time() . "_foto_" . basename($_FILES["foto_dokumentasi"]["name"]);
    $surat = time() . "_surat_" . basename($_FILES["surat_tugas"]["name"]);
    
    move_uploaded_file($_FILES["sertifikat"]["tmp_name"], $target_dir . $sertifikat);
    move_uploaded_file($_FILES["foto_dokumentasi"]["tmp_name"], $target_dir . $foto);
    move_uploaded_file($_FILES["surat_tugas"]["tmp_name"], $target_dir . $surat);
    
    // Simpan ke database
    $query = "INSERT INTO pengajuan (
        user_id, jenis_peserta, level_kompetisi, raihan_prestasi,
        nama_ketua, nim_ketua, no_hp, prodi, nama_kejuaraan,
        kategori_prestasi, bulan_raihan, tahun_raihan,
        sertifikat, foto_dokumentasi, surat_tugas, status
    ) VALUES (
        '{$_SESSION['user_id']}',
        '{$_POST['jenis_peserta']}',
        '{$_POST['level_kompetisi']}',
        '{$_POST['raihan_prestasi']}',
        '{$_POST['nama_ketua']}',
        '{$_POST['nim_ketua']}',
        '{$_POST['no_hp']}',
        '{$_POST['prodi']}',
        '{$_POST['nama_kejuaraan']}',
        '{$_POST['kategori_prestasi']}',
        '{$_POST['bulan_raihan']}',
        '{$_POST['tahun_raihan']}',
        '$sertifikat',
        '$foto',
        '$surat',
        'diproses'
    )";
    
    if (mysqli_query($conn, $query)) {
        header("Location: dashboard.php?success=1");
    } else {
        header("Location: dashboard.php?error=" . urlencode(mysqli_error($conn)));
    }
} else {
    header("Location: dashboard.php");
}
?>