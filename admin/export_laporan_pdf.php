<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once '../config/database.php';

// =========================================================
// AMBIL DATA
// =========================================================
$tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : date('Y');

$bulan_nama = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

// Statistik per bulan
$statistik_bulan = [];
for ($i = 1; $i <= 12; $i++) {
    $query = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'disetujui' THEN 1 ELSE 0 END) as disetujui,
                SUM(CASE WHEN status = 'ditolak' THEN 1 ELSE 0 END) as ditolak,
                SUM(CASE WHEN status = 'disetujui' THEN reward_nominal ELSE 0 END) as total_reward
              FROM pengajuan 
              WHERE YEAR(created_at) = $tahun AND MONTH(created_at) = $i";
    $result = mysqli_query($conn, $query);
    $statistik_bulan[$i] = mysqli_fetch_assoc($result);
}

// Total
$total_query = "SELECT 
                  COUNT(*) as total_pengajuan,
                  SUM(CASE WHEN status = 'disetujui' THEN 1 ELSE 0 END) as total_disetujui,
                  SUM(CASE WHEN status = 'ditolak' THEN 1 ELSE 0 END) as total_ditolak,
                  SUM(CASE WHEN status = 'disetujui' THEN reward_nominal ELSE 0 END) as total_reward
                FROM pengajuan 
                WHERE YEAR(created_at) = $tahun";
$total_result = mysqli_query($conn, $total_query);
$total_data = mysqli_fetch_assoc($total_result);

// =========================================================
// LOAD DOMPDF
// =========================================================
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
} elseif (file_exists(__DIR__ . '/../vendor/dompdf/autoload.inc.php')) {
    require_once __DIR__ . '/../vendor/dompdf/autoload.inc.php';
} else {
    die("❌ DOMPDF tidak ditemukan!");
}

use Dompdf\Dompdf;
use Dompdf\Options;

$options = new Options();
$options->set('defaultFont', 'DejaVu Sans');
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);

// =========================================================
// HTML UNTUK PDF
// =========================================================
$html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Prestasi ' . $tahun . '</title>
    <style>
        body { font-family: "DejaVu Sans", Arial, sans-serif; padding: 20px; }
        .header { text-align: center; border-bottom: 2px solid #2c7da0; padding-bottom: 15px; margin-bottom: 20px; }
        .header h1 { color: #1a1a1a; font-size: 24px; margin: 0; }
        .header p { color: #777; font-size: 13px; margin: 5px 0 0 0; }
        .sub-header { text-align: center; font-size: 14px; color: #555; margin-bottom: 20px; }
        .stat-grid { display: flex; justify-content: space-between; margin-bottom: 20px; }
        .stat-box { text-align: center; border: 1px solid #ddd; border-radius: 8px; padding: 12px 20px; width: 23%; }
        .stat-box .label { font-size: 11px; color: #888; }
        .stat-box .value { font-size: 20px; font-weight: 700; color: #1a1a1a; margin-top: 4px; }
        .stat-box.total { border-left: 4px solid #2c7da0; }
        .stat-box.approved { border-left: 4px solid #2e7d32; }
        .stat-box.rejected { border-left: 4px solid #c62828; }
        .stat-box.reward { border-left: 4px solid #e67e22; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        table th { background: #f8f9fa; padding: 8px 10px; font-size: 11px; border-bottom: 2px solid #ddd; text-align: center; }
        table td { padding: 6px 10px; font-size: 11px; border-bottom: 1px solid #eee; text-align: center; }
        table td:first-child { text-align: left; font-weight: 500; }
        table tfoot td { background: #f8f9fa; font-weight: 700; border-top: 2px solid #ddd; }
        .badge-approve { color: #2e7d32; }
        .badge-reject { color: #c62828; }
        .reward-text { color: #2c7da0; font-weight: 600; }
        .footer { text-align: center; font-size: 10px; color: #999; border-top: 1px solid #ddd; padding-top: 15px; margin-top: 20px; }
        .footer .signature { margin-top: 30px; display: flex; justify-content: space-between; }
        .footer .signature div { text-align: center; width: 45%; }
        .footer .signature .line { margin-top: 40px; border-top: 1px solid #333; width: 100%; padding-top: 5px; font-size: 11px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN PENGAJUAN PRESTASI</h1>
        <p>Sistem Mahasiswa Berprestasi (SMB)</p>
        <p style="font-size: 12px;">Tahun ' . $tahun . '</p>
    </div>
    <div class="sub-header">
        Periode: Januari - Desember ' . $tahun . ' | Dicetak: ' . date('d F Y H:i') . ' WIB
    </div>

    <!-- TABEL -->
    <table>
        <thead>
            <tr>
                <th style="text-align:left;">Bulan</th>
                <th>Total</th>
                <th>Disetujui</th>
                <th>Ditolak</th>
                <th style="text-align:right;">Reward</th>
            </tr>
        </thead>
        <tbody>';

$total_semua = 0;
$total_setuju = 0;
$total_tolak = 0;
$total_reward_all = 0;

for ($i = 1; $i <= 12; $i++) {
    $data = $statistik_bulan[$i];
    $total_semua += $data['total'];
    $total_setuju += $data['disetujui'];
    $total_tolak += $data['ditolak'];
    $total_reward_all += $data['total_reward'];
    
    $html .= '
            <tr>
                <td style="text-align:left;"><strong>' . $bulan_nama[$i-1] . '</strong></td>
                <td>' . $data['total'] . '</td>
                <td>' . ($data['disetujui'] > 0 ? '' . $data['disetujui'] : '0') . '</td>
                <td>' . ($data['ditolak'] > 0 ? '' . $data['ditolak'] : '0') . '</td>
                <td style="text-align:right;">Rp ' . number_format($data['total_reward'] ?? 0, 0, ',', '.') . '</td>
            </tr>';
}

$html .= '
        </tbody>
        <tfoot>
            <tr>
                <td style="text-align:left;"><strong>TOTAL</strong></td>
                <td><strong>' . $total_semua . '</strong></td>
                <td><strong>' . $total_setuju . '</strong></td>
                <td><strong>' . $total_tolak . '</strong></td>
                <td style="text-align:right;"><strong>Rp ' . number_format($total_reward_all, 0, ',', '.') . '</strong></td>
            </tr>
        </tfoot>
    </table>

    <!-- TANDA TANGAN -->
    <div class="footer">
        <div class="signature">
            <div>
                <p>Mengetahui,</p>
                <div class="line">Admin SMB</div>
            </div>
            <div>
                <p>Dicetak pada,</p>
                <div class="line">' . date('d F Y') . '</div>
            </div>
        </div>
        <div style="margin-top: 20px; font-size: 10px; color: #aaa;">
            Dokumen ini dicetak dari Sistem Mahasiswa Berprestasi (SMB)
        </div>
    </div>
</body>
</html>
';

// =========================================================
// GENERATE PDF
// =========================================================
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Download PDF
$dompdf->stream('Laporan_Prestasi_' . $tahun . '.pdf', array('Attachment' => true));
exit;
?>