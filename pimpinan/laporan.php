<?php
session_start();

// Cek login dan role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'superadmin') {
    header("Location: ../login.php");
    exit();
}

require_once '../config/database.php';

$nama = $_SESSION['nama'] ?? 'Pimpinan';

// =========================================================
// FUNGSI HITUNG NILAI AKHIR
// =========================================================
function hitungNilaiAkhir($row) {
    if (!empty($row['nilai_akhir'])) {
        return $row['nilai_akhir'];
    }
    
    $bobot_level = [
        'Lokal' => 1, 
        'Provinsi' => 2, 
        'Nasional' => 3, 
        'Internasional' => 4
    ];
    
    $bobot_raihan = [
        'Juara 1' => 4,
        'Juara 2' => 3,
        'Juara 3' => 2,
        'Finalis' => 1,
        'Silver Medal' => 3,
        'Gold Medal' => 4,
        'Bronze Medal' => 2,
        'Peserta Aktif' => 1,
        'Juara Harapan' => 2,
    ];
    
    $level = $row['level_kompetisi'] ?? 'Lokal';
    $raihan = $row['raihan_prestasi'] ?? 'Juara 3';
    $bobot_raihan_value = $bobot_raihan[$raihan] ?? 2;
    
    $skor = ($bobot_level[$level] * 15) + ($bobot_raihan_value * 10);
    return min($skor, 100);
}

// =========================================================
// AMBIL DATA MAHASISWA BERPRESTASI (YANG SUDAH DITETAPKAN)
// =========================================================

// Total kandidat lolos
$query_total = "SELECT COUNT(DISTINCT user_id) as total FROM pengajuan WHERE status = 'disetujui'";
$result_total = mysqli_query($conn, $query_total);
$total_kandidat = mysqli_fetch_assoc($result_total)['total'] ?? 0;

// Total sudah ditetapkan
$query_ditetapkan = "SELECT COUNT(DISTINCT user_id) as total FROM pengajuan WHERE status = 'disetujui' AND penetapan = 1";
$result_ditetapkan = mysqli_query($conn, $query_ditetapkan);
$total_ditetapkan = mysqli_fetch_assoc($result_ditetapkan)['total'] ?? 0;

// Total menunggu penetapan
$total_menunggu = $total_kandidat - $total_ditetapkan;

// Ambil mahasiswa yang sudah ditetapkan (penetapan = 1)
$query_prestasi = "SELECT p.*, u.nama_lengkap, u.nim, u.prodi, u.fakultas,
                   kr.nominal as reward_nominal
                   FROM pengajuan p 
                   JOIN users u ON p.user_id = u.id 
                   LEFT JOIN kategori_reward kr ON (
                       kr.tingkat = p.level_kompetisi AND 
                       kr.kategori = p.jenis_peserta AND
                       kr.raihan = p.raihan_prestasi
                   )
                   WHERE p.status = 'disetujui' AND p.penetapan = 1
                   ORDER BY p.nilai_akhir DESC, p.reward_nominal DESC";
$result_prestasi = mysqli_query($conn, $query_prestasi);
$prestasi_data = [];
while ($row = mysqli_fetch_assoc($result_prestasi)) {
    $prestasi_data[] = $row;
}

// Jika belum ada yang ditetapkan, ambil yang lolos (status disetujui)
if (count($prestasi_data) == 0) {
    $query_lolos = "SELECT p.*, u.nama_lengkap, u.nim, u.prodi, u.fakultas,
                     kr.nominal as reward_nominal
                     FROM pengajuan p 
                     JOIN users u ON p.user_id = u.id 
                     LEFT JOIN kategori_reward kr ON (
                         kr.tingkat = p.level_kompetisi AND 
                         kr.kategori = p.jenis_peserta AND
                         kr.raihan = p.raihan_prestasi
                     )
                     WHERE p.status = 'disetujui'
                     ORDER BY p.nilai_akhir DESC, p.reward_nominal DESC";
    $result_lolos = mysqli_query($conn, $query_lolos);
    while ($row = mysqli_fetch_assoc($result_lolos)) {
        $prestasi_data[] = $row;
    }
}

$total_prestasi = count($prestasi_data);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Hasil Seleksi - SMB</title>
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
        .card-title { font-size: 15px; font-weight: 600; color: #0f172a; margin-bottom: 12px; }

        /* ===== STATISTIK ===== */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-bottom: 16px;
        }
        .stat-item {
            background: #f8fafc;
            padding: 16px 20px;
            border-radius: 10px;
            text-align: center;
            border-left: 4px solid #8b5cf6;
        }
        .stat-item .number { font-size: 28px; font-weight: 700; color: #0f172a; }
        .stat-item .label { font-size: 13px; color: #64748b; margin-top: 4px; }
        .stat-item.blue { border-left-color: #3b82f6; }
        .stat-item.green { border-left-color: #10b981; }
        .stat-item.yellow { border-left-color: #f59e0b; }

        /* ===== FILTER ===== */
        .filter-row {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: center;
        }
        .filter-row select {
            padding: 8px 14px;
            border: 1.5px solid #e2e8f0;
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            background: #ffffff;
            min-width: 140px;
        }
        .filter-row select:focus { outline: none; border-color: #8b5cf6; }
        .filter-desc { font-size: 13px; color: #94a3b8; margin-top: 10px; }

        /* ===== LAPORAN ===== */
        .laporan-header {
            background: #f8fafc;
            padding: 16px 20px;
            border-radius: 8px;
            border: 1px solid #eef2f6;
            margin-bottom: 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }
        .laporan-header .title {
            font-size: 18px;
            font-weight: 600;
            color: #0f172a;
        }
        .laporan-header .sub {
            font-size: 14px;
            color: #64748b;
        }
        .laporan-header .periode {
            font-size: 14px;
            color: #475569;
            background: #eef2f6;
            padding: 4px 14px;
            border-radius: 20px;
        }

        /* ===== TABEL PRESTASI ===== */
        .table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
            margin-top: 10px;
        }
        .table th {
            text-align: left;
            padding: 10px 12px;
            background: #f8fafc;
            font-weight: 600;
            color: #475569;
            border-bottom: 2px solid #e2e8f0;
        }
        .table td {
            padding: 10px 12px;
            border-bottom: 1px solid #f1f5f9;
        }
        .table tr:hover td { background: #f8fafc; }
        .table td:last-child { text-align: right; font-weight: 600; color: #10b981; }

        .prestasi-detail {
            font-size: 12px;
            color: #64748b;
            margin-top: 2px;
        }
        .prestasi-detail i { margin-right: 4px; color: #94a3b8; }

        .empty-state {
            text-align: center; padding: 40px 0; color: #94a3b8;
        }
        .empty-state i { font-size: 40px; display: block; margin-bottom: 10px; }

        /* ===== TOMBOL AKSI ===== */
        .action-buttons {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 16px;
        }
        .btn-action {
            padding: 10px 24px;
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
        .btn-action:hover { transform: translateY(-2px); }
        .btn-print { background: #0f172a; color: white; }
        .btn-print:hover { background: #1e293b; }
        .btn-pdf { background: #ef4444; color: white; }
        .btn-pdf:hover { background: #dc2626; }
        .btn-excel { background: #10b981; color: white; }
        .btn-excel:hover { background: #059669; }

        @media (max-width: 768px) {
            .sidebar { width: 70px; padding: 16px 12px; }
            .sidebar .logo { font-size: 16px; }
            .sidebar .logo span { display: none; }
            .sidebar .menu li span { display: none; }
            .main-content { margin-left: 70px; padding: 16px; }
            .stats-grid { grid-template-columns: 1fr; }
            .laporan-header { flex-direction: column; align-items: flex-start; gap: 8px; }
            .table { font-size: 12px; }
            .table th, .table td { padding: 6px 8px; }
        }

        @media print {
            body * { visibility: hidden; }
            #laporanContent, #laporanContent * { visibility: visible; }
            #laporanContent { position: absolute; left: 0; top: 0; width: 100%; }
            .laporan-header { display: none; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

<!-- ===== SIDEBAR ===== -->
<?php include 'includes/sidebar.php'; ?>

<!-- ===== MAIN CONTENT ===== -->
<div class="main-content">

    <!-- ===== HEADER ===== -->
    <div class="header no-print">
        <h1>📄 Laporan Hasil Seleksi</h1>
        <div class="user">
            <span><?php echo date('l, d F Y'); ?></span>
            <div class="avatar"><?php echo strtoupper(substr($nama, 0, 1)); ?></div>
            <span><?php echo htmlspecialchars($nama); ?></span>
        </div>
    </div>

    <!-- ===== FILTER PERIODE ===== -->
    <div class="card no-print">
        <div class="filter-row">
            <select id="periodeFilter">
                <option value="2025/2026">2025/2026</option>
                <option value="2026/2027" selected>2026/2027</option>
            </select>
        </div>
        <div class="filter-desc">
            <i class="fas fa-info-circle"></i> Menampilkan laporan hasil seleksi mahasiswa berprestasi per periode
        </div>
    </div>

   <!-- ===== LAPORAN ===== -->
<div id="laporanContent">

    <!-- DAFTAR MAHASISWA BERPRESTASI -->
    <div class="card">
        <div class="laporan-header">
            <div>
                <div class="title">🏆 Mahasiswa Berprestasi</div>
                <div class="sub">Daftar mahasiswa yang telah ditetapkan sebagai penerima prestasi</div>
            </div>
            <div class="periode">Periode <?php echo date('Y'); ?>/<?php echo date('Y')+1; ?></div>
        </div>

        <?php if ($total_prestasi > 0): ?>
        <div style="overflow-x: auto;">
            <table class="table" id="cetakTable">
                <thead>
                    <tr>
                        <th style="width:50px; text-align:center;">No</th>
                        <th style="text-align:center;">NIM</th>
                        <th style="text-align:left;">Nama Mahasiswa</th>
                        <th style="text-align:left;">Program Studi</th>
                        <th style="text-align:center;">Skor</th>
                        <th style="text-align:right;">Reward</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    foreach ($prestasi_data as $row): 
                        $skor = hitungNilaiAkhir($row);
                        $reward = $row['reward_nominal'] ?? 0;
                        $level = $row['level_kompetisi'] ?? '-';
                        $raihan = $row['raihan_prestasi'] ?? '-';
                        $kejuaraan = $row['nama_kejuaraan'] ?? '-';
                    ?>
                    <tr>
                        <td style="text-align:center; font-weight:700;"><?php echo $no++; ?></td>
                        <td style="text-align:center;"><?php echo htmlspecialchars($row['nim']); ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($row['nama_lengkap']); ?></strong>
                            <div class="prestasi-detail">
                                <i class="fas fa-trophy"></i> <?php echo htmlspecialchars($kejuaraan); ?>
                                <span style="margin: 0 6px;">•</span>
                                <i class="fas fa-tag"></i> <?php echo $level; ?>
                                <span style="margin: 0 6px;">•</span>
                                <i class="fas fa-medal"></i> <?php echo $raihan; ?>
                            </div>
                        </td>
                        <td><?php echo htmlspecialchars($row['prodi'] ?? '-'); ?></td>
                        <td style="text-align:center; font-weight:700;"><?php echo $skor; ?>/100</td>
                        <td style="text-align:right; font-weight:600; color:#10b981;">
                            Rp <?php echo number_format($reward, 0, ',', '.'); ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="6" style="text-align:center; font-size:13px; color:#94a3b8; padding:12px;">
                            Total Mahasiswa Berprestasi: <strong><?php echo $total_prestasi; ?></strong> orang
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-file-alt"></i>
            <p>Belum ada mahasiswa yang ditetapkan sebagai penerima prestasi.</p>
            <p style="font-size: 13px; margin-top: 4px;">Silakan tetapkan mahasiswa berprestasi melalui halaman Ranking.</p>
        </div>
        <?php endif; ?>
    </div>

</div>

<style>
    /* ========================================================= */
    /* STYLE KHUSUS CETAK (PRINT)                                */
    /* ========================================================= */
    @media print {
        body * { visibility: hidden; }
        #laporanContent, #laporanContent * { visibility: visible; }
        #laporanContent { 
            position: absolute; 
            left: 0; 
            top: 0; 
            width: 100%; 
            padding: 20px;
            background: white;
        }
        .no-print { display: none !important; }
        .sidebar { display: none !important; }
        .header { display: none !important; }
        .laporan-header { display: none !important; }
        
        #cetakTable {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }
        #cetakTable th {
            background: #f8fafc;
            border: 1px solid #333;
            padding: 8px 10px;
            font-weight: 700;
            text-align: center;
        }
        #cetakTable td {
            border: 1px solid #333;
            padding: 6px 10px;
            vertical-align: middle;
        }
        #cetakTable td:last-child {
            text-align: right;
        }
        #cetakTable td:nth-child(1),
        #cetakTable td:nth-child(2),
        #cetakTable td:nth-child(5) {
            text-align: center;
        }
        #cetakTable tfoot td {
            border: 1px solid #333;
            border-top: 2px solid #333;
            background: #f8fafc;
        }
        
        .prestasi-detail {
            font-size: 10px;
            color: #475569;
        }
        .prestasi-detail i {
            display: none;
        }
        
        .card {
            box-shadow: none !important;
            border: none !important;
            padding: 0 !important;
        }
        
        .stat-item {
            border: 1px solid #ddd !important;
        }
        
        /* JUDUL LAPORAN SAAT CETAK */
        .print-title {
            display: block !important;
            text-align: center;
            margin-bottom: 20px;
        }
        .print-title h2 {
            font-size: 20px;
            font-weight: 700;
            color: #0f172a;
        }
        .print-title p {
            font-size: 13px;
            color: #64748b;
        }
        
        .print-footer {
            display: flex !important;
            justify-content: space-between;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 11px;
            color: #94a3b8;
        }
    }
    
    /* Tambahan untuk print */
    .print-title { display: none; }
    .print-footer { display: none; }
</style>

<!-- TAMBAHKAN JUDUL UNTUK CETAK (HANYA TERLIHAT SAAT PRINT) -->
<div class="print-title">
    <h2>LAPORAN MAHASISWA BERPRESTASI</h2>
    <p>Periode <?php echo date('Y'); ?>/<?php echo date('Y')+1; ?> | Dicetak: <?php echo date('d F Y H:i'); ?></p>
</div>

<!-- TAMBAHKAN FOOTER UNTUK CETAK (HANYA TERLIHAT SAAT PRINT) -->
<div class="print-footer">
    <span>Dicetak oleh: <?php echo htmlspecialchars($nama); ?></span>
    <span>Tanggal: <?php echo date('d F Y'); ?></span>
</div>

    <!-- ===== TOMBOL AKSI ===== -->
    <div class="card no-print">
        <div class="action-buttons">
            <button class="btn-action btn-print" onclick="window.print()">
                <i class="fas fa-print"></i> Cetak Laporan
            </button>
        </div>
    </div>

</div>

</body>
</html>