<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}
require_once '../config/database.php';

// =========================================================
// WEATHER_WIDGET DIHAPUS
// =========================================================
// require_once __DIR__ . '/../includes/weather_widget.php';

$tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : date('Y');
$tahun_list = [2023, 2024, 2025, 2026, 2027, 2028];

// Statistik per bulan
$statistik_bulan = [];
$bulan_nama = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
$bulan_singkat = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];

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

// Statistik total
$total_query = "SELECT 
                  COUNT(*) as total_pengajuan,
                  SUM(CASE WHEN status = 'disetujui' THEN 1 ELSE 0 END) as total_disetujui,
                  SUM(CASE WHEN status = 'ditolak' THEN 1 ELSE 0 END) as total_ditolak,
                  SUM(CASE WHEN status = 'disetujui' THEN reward_nominal ELSE 0 END) as total_reward
                FROM pengajuan 
                WHERE YEAR(created_at) = $tahun";
$total_result = mysqli_query($conn, $total_query);
$total_data = mysqli_fetch_assoc($total_result);

// Data untuk chart
$chart_total = [];
$chart_disetujui = [];
$chart_ditolak = [];
$max_value = 0;
for ($i = 1; $i <= 12; $i++) {
    $chart_total[] = $statistik_bulan[$i]['total'];
    $chart_disetujui[] = $statistik_bulan[$i]['disetujui'];
    $chart_ditolak[] = $statistik_bulan[$i]['ditolak'];
    $max_value = max($max_value, $statistik_bulan[$i]['total']);
}
$max_value = max($max_value, 1);

// Format tanggal hari ini
$tanggal_hari_ini = date('l, d F Y');

include 'includes/sidebar.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Prestasi - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', system-ui, sans-serif; background: #f0f4f8; color: #1a1a1a; }

        /* MAIN CONTENT */
        .main-content { margin-left: 260px; padding: 24px 32px; }

        /* TOP BAR - TANPA LOGOUT & TANPA CUACA */
        .top-bar {
            background: linear-gradient(135deg, #ffffff 0%, #f0f6ff 100%);
            border-radius: 16px; padding: 16px 24px;
            margin-bottom: 24px; border: 1px solid #dce5f0;
            display: flex; justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 8px rgba(42, 82, 152, 0.06);
        }
        .page-title h2 { 
            font-size: 20px; font-weight: 700; 
            color: #1a365d;
            background: linear-gradient(135deg, #1a365d, #2a5298);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .page-title p { 
            font-size: 13px; color: #4a6a8a; margin-top: 4px; 
            font-weight: 500;
        }

        /* PROFIL + TANGGAL (TANPA LOGOUT) */
        .user-info {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .user-avatar {
            width: 40px; height: 40px; 
            background: linear-gradient(135deg, #2a5298, #1a365d);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center; color: white;
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(26, 54, 93, 0.2);
        }
        .user-name { 
            font-weight: 600; color: #1a365d; 
        }
        .date-divider { width: 1px; height: 30px; background: #dce2ea; }
        .date-text { font-size: 13px; color: #6a7e9e; font-weight: 500; }

        /* STAT CARDS */
        .stat-grid {
            display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 24px;
        }
        .stat-card {
            background: white; border-radius: 14px; padding: 20px;
            border: 1px solid #dce5f0; border-left: 4px solid;
            transition: all 0.3s;
            box-shadow: 0 2px 6px rgba(0,0,0,0.02);
        }
        .stat-card:hover { transform: translateY(-4px); box-shadow: 0 8px 20px rgba(42, 82, 152, 0.12); }
        .stat-card.total { border-left-color: #2a5298; }
        .stat-card.approved { border-left-color: #059669; }
        .stat-card.rejected { border-left-color: #dc2626; }
        .stat-card.reward { border-left-color: #d97706; }
        .stat-value { font-size: 28px; font-weight: 700; margin-top: 8px; }
        .stat-card.total .stat-value { color: #1a365d; }
        .stat-card.approved .stat-value { color: #059669; }
        .stat-card.rejected .stat-value { color: #dc2626; }
        .stat-card.reward .stat-value { color: #d97706; }
        .stat-label { font-size: 13px; color: #6a7e9e; font-weight: 500; }

        /* FILTER */
        .filter-bar {
            background: white; border-radius: 14px; padding: 16px 24px;
            margin-bottom: 24px; border: 1px solid #dce5f0;
            display: flex; justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.02);
        }
        .filter-bar span { 
            color: #1a365d; 
            font-weight: 500;
        }
        .tahun-select {
            padding: 8px 16px; border: 1px solid #dce5f0; border-radius: 8px;
            font-size: 13px; background: white; cursor: pointer;
            transition: 0.2s;
        }
        .tahun-select:focus {
            border-color: #2a5298;
            outline: none;
            box-shadow: 0 0 0 3px rgba(42, 82, 152, 0.1);
        }
        .btn-filter {
            background: linear-gradient(135deg, #2a5298, #1a365d);
            color: white; border: none;
            padding: 8px 20px; border-radius: 8px; cursor: pointer;
            transition: all 0.3s;
        }
        .btn-filter:hover { 
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(42, 82, 152, 0.3);
        }
        .btn-download {
            background: linear-gradient(135deg, #059669, #047857);
            color: white; border: none;
            padding: 8px 20px; border-radius: 8px; cursor: pointer;
            display: inline-flex; align-items: center; gap: 8px;
            transition: all 0.3s;
        }
        .btn-download:hover { 
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(5, 150, 105, 0.3);
        }

        /* CHART */
        .chart-card {
            background: white; border-radius: 14px; padding: 24px;
            margin-bottom: 24px; border: 1px solid #dce5f0;
            box-shadow: 0 2px 6px rgba(0,0,0,0.02);
        }
        .chart-header {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 20px; flex-wrap: wrap; gap: 15px;
        }
        .chart-header h3 { 
            font-size: 16px; font-weight: 600; color: #1a365d; 
        }
        .chart-header h3 i { color: #2a5298; }
        .legend {
            display: flex; gap: 20px; flex-wrap: wrap;
        }
        .legend-item {
            display: flex; align-items: center; gap: 8px; font-size: 12px;
            color: #1a365d;
        }
        .legend-color { width: 14px; height: 14px; border-radius: 4px; }
        .legend-color.total { background: #2a5298; }
        .legend-color.disetujui { background: #059669; }
        .legend-color.ditolak { background: #dc2626; }
        canvas { max-height: 400px; width: 100%; }

        /* TABLE */
        .table-card {
            background: white; border-radius: 14px; border: 1px solid #dce5f0;
            overflow: hidden;
            box-shadow: 0 2px 6px rgba(0,0,0,0.02);
        }
        .table-header {
            padding: 12px 20px; border-bottom: 1px solid #eef2f7;
            background: linear-gradient(135deg, #f8faff, #f0f6ff);
            display: flex; justify-content: space-between;
            align-items: center;
        }
        .table-header h3 { 
            font-size: 15px; font-weight: 600; color: #1a365d; 
        }
        .table-header h3 i { color: #2a5298; }
        .badge-count {
            background: linear-gradient(135deg, #1a365d, #2a5298);
            padding: 4px 14px; border-radius: 20px; font-size: 12px;
            color: #ffffff;
            font-weight: 600;
        }
        .data-table {
            width: 100%; border-collapse: collapse;
        }
        .data-table th {
            padding: 10px 12px; 
            background: linear-gradient(135deg, #1a365d, #2a5298);
            color: #ffffff;
            font-weight: 600; font-size: 12px;
            border-bottom: 1px solid #eef2f7;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .data-table td {
            padding: 10px 12px; border-bottom: 1px solid #eef2f7;
            font-size: 12px;
            text-align: center;
            color: #1a365d;
        }
        .data-table td:first-child { text-align: left; font-weight: 500; color: #1a365d; }
        .data-table tr:hover {
            background: #f8faff;
        }
        .data-table tr:last-child td {
            border-bottom: none;
        }
        .badge-approved {
            background: #d1fae5; color: #059669;
            padding: 3px 10px; border-radius: 20px; display: inline-block; font-size: 11px;
            font-weight: 600;
        }
        .badge-rejected {
            background: #fee2e2; color: #dc2626;
            padding: 3px 10px; border-radius: 20px; display: inline-block; font-size: 11px;
            font-weight: 600;
        }
        .reward-text { color: #2a5298; font-weight: 600; }
        tfoot td {
            background: #f8faff;
            font-weight: 700;
            border-top: 2px solid #2a5298;
            color: #1a365d;
        }
        tfoot .reward-text { color: #2a5298; }

        
    </style>
</head>
<body>

<!-- ========================================================= -->
<!-- MAIN CONTENT                                              -->
<!-- ========================================================= -->
<div class="main-content">

    <!-- ========================================================= -->
    <!-- TOP BAR - TANPA LOGOUT & TANPA CUACA                       -->
    <!-- ========================================================= -->
    <div class="top-bar">
        <div class="page-title">
            <h2>Laporan Prestasi</h2>
            <p>Statistik pengajuan prestasi mahasiswa</p>
        </div>
        <div class="user-info">
            <div class="user-avatar">A</div>
            <span class="user-name"><?php echo htmlspecialchars($_SESSION['nama'] ?? 'Admin'); ?></span>
            <span class="date-divider"></span>
            <span class="date-text"><?php echo date('l, d F Y'); ?></span>
        </div>
    </div>

    <!-- ========================================================= -->
    <!-- WEATHER WIDGET DIHAPUS                                     -->
    <!-- ========================================================= -->

    <!-- ========================================================= -->
    <!-- STAT CARDS                                                -->
    <!-- ========================================================= -->
    <div class="stat-grid">
        <div class="stat-card total">
            <div class="stat-label">Total Pengajuan</div>
            <div class="stat-value"><?php echo $total_data['total_pengajuan'] ?? 0; ?></div>
        </div>
        <div class="stat-card approved">
            <div class="stat-label">Disetujui</div>
            <div class="stat-value"><?php echo $total_data['total_disetujui'] ?? 0; ?></div>
        </div>
        <div class="stat-card rejected">
            <div class="stat-label">Ditolak</div>
            <div class="stat-value"><?php echo $total_data['total_ditolak'] ?? 0; ?></div>
        </div>
        <div class="stat-card reward">
            <div class="stat-label">Total Reward</div>
            <div class="stat-value">Rp <?php echo number_format($total_data['total_reward'] ?? 0, 0, ',', '.'); ?></div>
        </div>
    </div>

    <!-- ========================================================= -->
    <!-- FILTER TAHUN                                              -->
    <!-- ========================================================= -->
    <div class="filter-bar">
        <div style="display: flex; gap: 12px; align-items: center;">
            <span><i class="fas fa-calendar-alt"></i> Pilih Tahun</span>
            <form method="GET" style="display: flex; gap: 10px;">
                <select name="tahun" class="tahun-select">
                    <?php foreach ($tahun_list as $thn): ?>
                        <option value="<?php echo $thn; ?>" <?php echo $tahun == $thn ? 'selected' : ''; ?>><?php echo $thn; ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn-filter"><i class="fas fa-sync-alt"></i> Tampilkan</button>
            </form>
        </div>
        <form method="GET" action="export_laporan_pdf.php" style="display: inline-block;">
            <input type="hidden" name="tahun" value="<?php echo $tahun; ?>">
            <button type="submit" class="btn-download">
                <i class="fas fa-download"></i> Download PDF
            </button>
        </form>
    </div>

    <!-- ========================================================= -->
    <!-- CHART                                                    -->
    <!-- ========================================================= -->
    <div class="chart-card">
        <div class="chart-header">
            <h3><i class="fas fa-chart-line"></i> Grafik Pengajuan Tahun <?php echo $tahun; ?></h3>
            <div class="legend">
                <div class="legend-item"><div class="legend-color total"></div><span>Total Pengajuan</span></div>
                <div class="legend-item"><div class="legend-color disetujui"></div><span>Disetujui</span></div>
                <div class="legend-item"><div class="legend-color ditolak"></div><span>Ditolak</span></div>
            </div>
        </div>
        <canvas id="prestasiChart"></canvas>
    </div>

    <!-- ========================================================= -->
    <!-- TABEL LAPORAN BULANAN                                     -->
    <!-- ========================================================= -->
    <div class="table-card">
        <div class="table-header">
            <h3><i class="fas fa-table"></i> Laporan Bulanan Tahun <?php echo $tahun; ?></h3>
            <div class="badge-count">Total: <?php echo array_sum($chart_total); ?> pengajuan</div>
        </div>
        <div style="overflow-x: auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 100px;">Bulan</th>
                        <th style="width: 60px;">Total</th>
                        <th style="width: 80px;">Disetujui</th>
                        <th style="width: 60px;">Ditolak</th>
                        <th style="width: 100px;">Reward</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $total_semua = 0; $total_setuju = 0; $total_tolak = 0; $total_reward_all = 0;
                    for ($i = 1; $i <= 12; $i++): 
                        $data = $statistik_bulan[$i];
                        $total_semua += $data['total'];
                        $total_setuju += $data['disetujui'];
                        $total_tolak += $data['ditolak'];
                        $total_reward_all += $data['total_reward'];
                    ?>
                    <tr>
                        <td style="text-align: left;"><strong><?php echo $bulan_nama[$i-1]; ?></strong></td>
                        <td style="text-align: center;"><?php echo $data['total']; ?></td>
                        <td style="text-align: center;">
                            <?php if ($data['disetujui'] > 0): ?>
                                <span class="badge-approved">✅ <?php echo $data['disetujui']; ?></span>
                            <?php else: ?>
                                0
                            <?php endif; ?>
                        </td>
                        <td style="text-align: center;">
                            <?php if ($data['ditolak'] > 0): ?>
                                <span class="badge-rejected">❌ <?php echo $data['ditolak']; ?></span>
                            <?php else: ?>
                                0
                            <?php endif; ?>
                        </td>
                        <td class="reward-text" style="text-align: right;">Rp <?php echo number_format($data['total_reward'] ?? 0, 0, ',', '.'); ?></td>
                    </tr>
                    <?php endfor; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td style="text-align: left;"><strong>TOTAL</strong></td>
                        <td style="text-align: center;"><strong><?php echo $total_semua; ?></strong></td>
                        <td style="text-align: center;"><strong><?php echo $total_setuju; ?></strong></td>
                        <td style="text-align: center;"><strong><?php echo $total_tolak; ?></strong></td>
                        <td class="reward-text" style="text-align: right;"><strong>Rp <?php echo number_format($total_reward_all, 0, ',', '.'); ?></strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<script>
    const ctx = document.getElementById('prestasiChart').getContext('2d');
    
    const gradientTotal = ctx.createLinearGradient(0, 0, 0, 400);
    gradientTotal.addColorStop(0, '#2a5298');
    gradientTotal.addColorStop(1, '#1a365d');
    
    const gradientDisetujui = ctx.createLinearGradient(0, 0, 0, 400);
    gradientDisetujui.addColorStop(0, '#059669');
    gradientDisetujui.addColorStop(1, '#047857');
    
    const gradientDitolak = ctx.createLinearGradient(0, 0, 0, 400);
    gradientDitolak.addColorStop(0, '#dc2626');
    gradientDitolak.addColorStop(1, '#b91c1c');
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($bulan_singkat); ?>,
            datasets: [
                { label: 'Total Pengajuan', data: <?php echo json_encode($chart_total); ?>, backgroundColor: gradientTotal, borderRadius: 10, barPercentage: 0.7, categoryPercentage: 0.8 },
                { label: 'Disetujui', data: <?php echo json_encode($chart_disetujui); ?>, backgroundColor: gradientDisetujui, borderRadius: 10, barPercentage: 0.7, categoryPercentage: 0.8 },
                { label: 'Ditolak', data: <?php echo json_encode($chart_ditolak); ?>, backgroundColor: gradientDitolak, borderRadius: 10, barPercentage: 0.7, categoryPercentage: 0.8 }
            ]
        },
        options: { 
            responsive: true, maintainAspectRatio: true,
            plugins: { 
                legend: { position: 'top', labels: { font: { size: 11, weight: 'bold' }, boxWidth: 12, padding: 15 } },
                tooltip: { backgroundColor: '#1a1a1a', titleColor: '#fff', bodyColor: '#ddd', padding: 10, cornerRadius: 8, callbacks: { label: function(context) { return context.dataset.label + ': ' + context.raw + ' pengajuan'; } } }
            }, 
            scales: { 
                y: { beginAtZero: true, max: <?php echo $max_value + 1; ?>, ticks: { stepSize: 1, precision: 0 }, title: { display: true, text: 'Jumlah Pengajuan', font: { size: 11 } } }, 
                x: { title: { display: true, text: 'Bulan', font: { size: 11 } } }
            }
        }
    });
    
</script>
</body>
</html>