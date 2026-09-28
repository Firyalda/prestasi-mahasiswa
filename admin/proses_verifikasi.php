<?php
require_once '../includes/auth.php';
requireAdmin();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    
    // Ambil data pengajuan
    $query = "SELECT level_kompetisi, jenis_peserta, raihan_prestasi FROM pengajuan WHERE id = $id";
    $result = mysqli_query($conn, $query);
    $pengajuan = mysqli_fetch_assoc($result);
    
    if ($pengajuan && $action === 'setuju') {
        // CARI NOMINAL REWARD OTOMATIS DARI DATABASE
        $reward_query = "SELECT nominal FROM kategori_reward 
                         WHERE tingkat = '{$pengajuan['level_kompetisi']}' 
                         AND kategori = '{$pengajuan['jenis_peserta']}'
                         AND raihan = '{$pengajuan['raihan_prestasi']}'";
        $reward_result = mysqli_query($conn, $reward_query);
        $reward = mysqli_fetch_assoc($reward_result);
        
        $nominal = $reward ? $reward['nominal'] : 0;
        
        // Update status dan reward
        mysqli_query($conn, "UPDATE pengajuan SET status = 'disetujui', reward_nominal = $nominal WHERE id = $id");
        
    } elseif ($action === 'tolak') {
        mysqli_query($conn, "UPDATE pengajuan SET status = 'ditolak', reward_nominal = 0 WHERE id = $id");
    }
    
    header("Location: verifikasi.php");
    exit();
}
?>