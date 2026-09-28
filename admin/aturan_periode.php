<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once '../config/database.php';

$page_title = 'Aturan Periode Pengajuan';

// ===== PROSES UPDATE PERIODE =====
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $periode_mulai = mysqli_real_escape_string($conn, $_POST['periode_mulai']);
    $periode_selesai = mysqli_real_escape_string($conn, $_POST['periode_selesai']);
    $tahun_akademik = mysqli_real_escape_string($conn, $_POST['tahun_akademik']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if ($periode_mulai > $periode_selesai) {
        $message = '❌ Tanggal mulai tidak boleh lebih besar dari tanggal selesai!';
        $message_type = 'error';
    } else {
        if ($is_active == 1) {
            mysqli_query($conn, "UPDATE pengaturan_periode SET is_active = 0 WHERE id != 1");
        }

        $check = mysqli_query($conn, "SELECT * FROM pengaturan_periode LIMIT 1");
        
        if (mysqli_num_rows($check) > 0) {
            $query = "UPDATE pengaturan_periode SET 
                        periode_mulai = '$periode_mulai',
                        periode_selesai = '$periode_selesai',
                        tahun_akademik = '$tahun_akademik',
                        is_active = $is_active
                      WHERE id = 1";
        } else {
            $query = "INSERT INTO pengaturan_periode (periode_mulai, periode_selesai, tahun_akademik, is_active) 
                      VALUES ('$periode_mulai', '$periode_selesai', '$tahun_akademik', $is_active)";
        }
        
        if (mysqli_query($conn, $query)) {
            // =========================================================
            // ===== SIMPAN KE RIWAYAT (TANPA STATUS) =====
            // =========================================================
            $nama_periode = "Periode " . $tahun_akademik;
            
            $query_riwayat = "INSERT INTO achievement_periods 
                                (nama_periode, tahun_akademik, start_date, end_date, created_at) 
                              VALUES 
                                ('$nama_periode', '$tahun_akademik', 
                                 '$periode_mulai', '$periode_selesai', NOW())";
            mysqli_query($conn, $query_riwayat);

            $message = '✅ Periode berhasil disimpan!';
            $message_type = 'success';
            
        } else {
            $message = '❌ Gagal memperbarui periode: ' . mysqli_error($conn);
            $message_type = 'error';
        }
    }
}

// ===== AMBIL DATA PERIODE SAAT INI =====
$query_periode = "SELECT * FROM pengaturan_periode LIMIT 1";
$result_periode = mysqli_query($conn, $query_periode);
$periode = mysqli_fetch_assoc($result_periode);

if (!$periode) {
    $periode = [
        'periode_mulai' => date('Y-m-d'),
        'periode_selesai' => date('Y-m-d', strtotime('+1 year')),
        'tahun_akademik' => date('Y') . '/' . (date('Y') + 1),
        'is_active' => 1
    ];
}

// ===== AMBIL RIWAYAT =====
$query_riwayat = "SELECT * FROM achievement_periods ORDER BY created_at DESC";
$result_riwayat = mysqli_query($conn, $query_riwayat);

include 'includes/sidebar.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aturan Periode Pengajuan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', system-ui, sans-serif; background: #f4f6f9; color: #1a1a1a; }

        .main-content {
            margin-left: 260px;
            padding: 30px 35px;
            min-height: 100vh;
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: white;
            border-radius: 12px;
            padding: 16px 24px;
            margin-bottom: 24px;
            border: 1px solid #e8e8e8;
        }
        .page-title h2 { font-size: 20px; font-weight: 600; color: #1a1a1a; }
        .page-title p { font-size: 13px; color: #777; margin-top: 4px; }
        .user-info { display: flex; align-items: center; gap: 16px; }
        .user-avatar {
            width: 40px; height: 40px; background: #2c7da0; border-radius: 50%;
            display: flex; align-items: center; justify-content: center; color: white; font-weight: 600;
        }
        .user-name { font-weight: 500; color: #1a1a1a; }
        .date-divider { width: 1px; height: 30px; background: #ddd; }
        .date-text { font-size: 13px; color: #888; }

        .periode-card {
            background: white;
            border-radius: 12px;
            padding: 25px 28px;
            border: 1px solid #e8e8e8;
            margin-bottom: 24px;
        }
        .periode-card h3 {
            font-size: 20px;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 4px;
        }

        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; font-size: 13px; font-weight: 600; color: #333; margin-bottom: 5px; }
        .form-group input {
            width: 100%; padding: 10px 14px; border: 1.5px solid #ddd; border-radius: 10px;
            font-size: 14px; background: #fafafa;
        }
        .form-group input:focus { outline: none; border-color: #2c7da0; background: white; }
        .form-group .help-text { font-size: 12px; color: #999; margin-top: 4px; }

        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }

        .checkbox-group { display: flex; align-items: center; gap: 10px; padding: 8px 0; }
        .checkbox-group input[type="checkbox"] { width: 18px; height: 18px; cursor: pointer; accent-color: #2c7da0; }
        .checkbox-group label { font-size: 14px; font-weight: 500; color: #333; cursor: pointer; }

        .note-text { font-size: 12px; color: #999; padding-left: 28px; margin: 6px 0 18px 0; }

        .btn-save {
            background: #2c7da0; color: white; border: none; padding: 12px 30px;
            border-radius: 10px; font-size: 15px; font-weight: 600; cursor: pointer;
            transition: all 0.3s; margin-top: 6px;
        }
        .btn-save:hover { background: #1a5a7a; transform: translateY(-2px); }

        .alert-popup {
            position: fixed;
            top: 30px;
            right: 30px;
            padding: 16px 24px;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 600;
            z-index: 9999;
            box-shadow: 0 8px 30px rgba(0,0,0,0.15);
            animation: slideInRight 0.5s ease-out;
            display: flex;
            align-items: center;
            gap: 12px;
            max-width: 450px;
        }
        .alert-popup.success {
            background: #d4edda;
            color: #155724;
            border-left: 5px solid #28a745;
        }
        .alert-popup.error {
            background: #f8d7da;
            color: #721c24;
            border-left: 5px solid #dc3545;
        }
        .alert-popup .close-btn {
            background: none;
            border: none;
            font-size: 22px;
            cursor: pointer;
            color: inherit;
            opacity: 0.6;
            transition: 0.3s;
            margin-left: 10px;
        }
        .alert-popup .close-btn:hover { opacity: 1; }

        @keyframes slideInRight {
            0% { transform: translateX(100%); opacity: 0; }
            100% { transform: translateX(0); opacity: 1; }
        }
        @keyframes slideOutRight {
            0% { transform: translateX(0); opacity: 1; }
            100% { transform: translateX(100%); opacity: 0; }
        }

        .status-badge { display: inline-block; padding: 4px 14px; border-radius: 20px; font-size: 13px; font-weight: 600; }
        .status-active { background: #d4edda; color: #155724; }
        .status-inactive { background: #f8d7da; color: #721c24; }

        .current-info {
            background: #f8f9fa; padding: 14px 18px; border-radius: 10px;
            margin-bottom: 20px; border-left: 4px solid #2c7da0;
        }
        .current-info p { margin: 3px 0; font-size: 14px; color: #555; }
        .current-info strong { color: #1a1a1a; }

        .riwayat-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
            margin-top: 12px;
        }
        .riwayat-table th {
            text-align: left;
            padding: 10px 12px;
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
            border-bottom: 2px solid #eee;
        }
        .riwayat-table td {
            padding: 10px 12px;
            border-bottom: 1px solid #f0f0f0;
        }
        .riwayat-table tr:hover td { background: #fafafa; }

        .badge-status {
            display: inline-block;
            padding: 3px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge-aktif { background: #d4edda; color: #155724; }
        .badge-dinonaktifkan { background: #f8d7da; color: #721c24; }
        .badge-berakhir { background: #e2e3e5; color: #383d41; }
        .badge-upcoming { background: #fff3cd; color: #856404; }

        .waktu-riwayat {
            font-size: 11px;
            color: #999;
            display: block;
            margin-top: 3px;
        }

        .text-center { text-align: center; }
        .text-muted { color: #999; }

        @media (max-width: 600px) {
            .form-row { grid-template-columns: 1fr; gap: 0; }
            .periode-card { padding: 18px; }
            .main-content { margin-left: 0; padding: 16px; }
            .user-info .user-name { display: none; }
            .alert-popup { top: 10px; right: 10px; left: 10px; max-width: 100%; }
            .riwayat-table { font-size: 12px; }
            .riwayat-table th, .riwayat-table td { padding: 6px 8px; }
        }
    </style>
</head>
<body>

<div class="main-content">

    <!-- TOP BAR -->
    <div class="top-bar">
        <div class="page-title">
            <h2>Aturan Periode Pengajuan</h2>
            <p>Atur periode waktu mahasiswa dapat mengajukan prestasi</p>
        </div>
        <div class="user-info">
            <div class="user-avatar">A</div>
            <span class="user-name"><?php echo htmlspecialchars($_SESSION['nama'] ?? 'Admin'); ?></span>
            <span class="date-divider"></span>
            <span class="date-text"><?php echo date('l, d F Y'); ?></span>
        </div>
    </div>

    <!-- PERIODE SAAT INI -->
    <div class="periode-card">
        <h3>⏳ Periode Saat Ini</h3>
        <div class="current-info">
            <p><strong>📌 Periode Saat Ini:</strong></p>
            <p>🗓️ <?php echo date('d F Y', strtotime($periode['periode_mulai'])); ?> — <?php echo date('d F Y', strtotime($periode['periode_selesai'])); ?></p>
            <p>📚 Tahun Akademik: <strong><?php echo $periode['tahun_akademik']; ?></strong></p>
            <p>Status: <span class="status-badge <?php echo $periode['is_active'] ? 'status-active' : 'status-inactive'; ?>">
                <?php echo $periode['is_active'] ? '✅ Aktif' : '❌ Nonaktif'; ?>
            </span></p>
        </div>

        <form method="POST">
            <div class="form-row">
                <div class="form-group">
                    <label>📅 Tanggal Mulai</label>
                    <input type="date" name="periode_mulai" value="<?php echo $periode['periode_mulai']; ?>" required>
                    <div class="help-text">Tanggal mulai periode pengajuan</div>
                </div>
                <div class="form-group">
                    <label>📅 Tanggal Selesai</label>
                    <input type="date" name="periode_selesai" value="<?php echo $periode['periode_selesai']; ?>" required>
                    <div class="help-text">Tanggal selesai periode pengajuan</div>
                </div>
            </div>

            <div class="form-group">
                <label>📚 Tahun Akademik</label>
                <input type="text" name="tahun_akademik" placeholder="Contoh: 2025/2026" value="<?php echo $periode['tahun_akademik']; ?>" required>
                <div class="help-text">Contoh: 2025/2026</div>
            </div>

            <div class="checkbox-group">
                <input type="checkbox" name="is_active" id="is_active" <?php echo $periode['is_active'] ? 'checked' : ''; ?>>
                <label for="is_active">🟢 Aktifkan Periode Pengajuan</label>
            </div>
            <div class="note-text">
                ⚠️ Jika dinonaktifkan, mahasiswa tidak bisa mengajukan prestasi
            </div>

            <button type="submit" class="btn-save">
                <i class="fas fa-save"></i> Simpan Pengaturan
            </button>
        </form>
    </div>

    <!-- ========================================================= -->
    <!-- ===== RIWAYAT PERIODE (STATUS DIHITUNG OTOMATIS) ===== -->
    <!-- ========================================================= -->
    <div class="periode-card">
        <h3>📋 Riwayat Periode</h3>
        <p style="color: #888; font-size: 14px; margin-bottom: 16px;">Daftar perubahan periode pengajuan</p>

        <table class="riwayat-table">
            <thead>
                <tr>
                    <th style="width:50px;">No</th>
                    <th>Periode</th>
                    <th>Tahun Akademik</th>
                    <th>Status</th>
                    <th>Waktu Perubahan</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                if (mysqli_num_rows($result_riwayat) > 0):
                    while ($row = mysqli_fetch_assoc($result_riwayat)): 
                        // ===== TENTUKAN STATUS BERDASARKAN DATA =====
                        $badge_class = '';
                        $status_display = '';
                        
                        $start = new DateTime($row['start_date']);
                        $end = new DateTime($row['end_date']);
                        $start->setTime(0,0,0);
                        $end->setTime(0,0,0);
                        $hari_ini = new DateTime();
                        $hari_ini->setTime(0,0,0);
                        
                        // Cek apakah ini periode aktif saat ini
                        $is_current_periode = false;
                        if ($periode && $periode['is_active'] == 1) {
                            $periode_mulai = new DateTime($periode['periode_mulai']);
                            $periode_selesai = new DateTime($periode['periode_selesai']);
                            $periode_mulai->setTime(0,0,0);
                            $periode_selesai->setTime(0,0,0);
                            if ($row['start_date'] == $periode['periode_mulai'] && 
                                $row['end_date'] == $periode['periode_selesai']) {
                                $is_current_periode = true;
                            }
                        }
                        
                        if ($is_current_periode && $periode['is_active'] == 1) {
                            $status_display = '✅ Aktif';
                            $badge_class = 'badge-aktif';
                        } elseif ($hari_ini > $end) {
                            $status_display = '📅 Berakhir';
                            $badge_class = 'badge-berakhir';
                        } elseif ($hari_ini < $start) {
                            $status_display = '⏳ Akan Datang';
                            $badge_class = 'badge-upcoming';
                        } else {
                            $status_display = '⛔ Dinonaktifkan';
                            $badge_class = 'badge-dinonaktifkan';
                        }
                        
                        $waktu = date('d/m/Y H:i:s', strtotime($row['created_at']));
                        $hari = date('l', strtotime($row['created_at']));
                ?>
                <tr>
                    <td><?php echo $no++; ?></td>
                    <td>
                        <?php echo date('d/m/Y', strtotime($row['start_date'])); ?> — 
                        <?php echo date('d/m/Y', strtotime($row['end_date'])); ?>
                    </td>
                    <td><?php echo $row['tahun_akademik']; ?></td>
                    <td><span class="badge-status <?php echo $badge_class; ?>"><?php echo $status_display; ?></span></td>
                    <td>
                        <?php echo $waktu; ?>
                        <span class="waktu-riwayat"><?php echo $hari; ?></span>
                    </td>
                </tr>
                <?php 
                    endwhile; 
                else: 
                ?>
                <tr>
                    <td colspan="5" class="text-center text-muted" style="padding: 20px;">Belum ada riwayat periode</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

<!-- ========================================================= -->
<!-- ===== NOTIFIKASI POPUP ===== -->
<!-- ========================================================= -->
<?php if (!empty($message)): ?>
<div id="notifikasiPopup" class="alert-popup <?php echo $message_type; ?>">
    <span><?php echo $message; ?></span>
    <button class="close-btn" onclick="tutupNotifikasi()">&times;</button>
</div>

<script>
    function tutupNotifikasi() {
        const popup = document.getElementById('notifikasiPopup');
        popup.style.animation = 'slideOutRight 0.5s ease-out forwards';
        setTimeout(() => {
            popup.style.display = 'none';
        }, 500);
    }

    setTimeout(() => {
        const popup = document.getElementById('notifikasiPopup');
        if (popup) {
            popup.style.animation = 'slideOutRight 0.5s ease-out forwards';
            setTimeout(() => {
                popup.style.display = 'none';
            }, 500);
        }
    }, 4000);
</script>
<?php endif; ?>

</body>
</html>