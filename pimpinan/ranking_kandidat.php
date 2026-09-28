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
    // Jika sudah ada nilai_akhir, pakai itu
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
// AMBIL DATA RANKING DENGAN REWARD
// =========================================================

// Total kandidat lolos
$query_total = "SELECT COUNT(*) as total FROM pengajuan WHERE status = 'disetujui'";
$result_total = mysqli_query($conn, $query_total);
$total_kandidat = mysqli_fetch_assoc($result_total)['total'] ?? 0;

// Ambil semua kandidat lolos dengan reward dari kategori_reward
// Ambil data per mahasiswa (1 mahasiswa = 1 baris)
$query_ranking = "SELECT 
                    u.id as user_id,
                    u.nama_lengkap,
                    u.nim,
                    u.prodi,
                    p.nilai_akhir,
                    p.reward_nominal,
                    p.nama_kejuaraan as prestasi,
                    p.level_kompetisi,
                    p.raihan_prestasi,
                    p.id as pengajuan_id
                  FROM pengajuan p 
                  JOIN users u ON p.user_id = u.id 
                  WHERE p.status = 'disetujui' 
                  ORDER BY p.nilai_akhir DESC, p.reward_nominal DESC";
$result_ranking = mysqli_query($conn, $query_ranking);
$ranking_data = [];
while ($row = mysqli_fetch_assoc($result_ranking)) {
    $ranking_data[] = $row;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ranking Kandidat - SMB</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; background: #f1f4f8; display: flex; min-height: 100vh; }

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
            width: 38px; height: 38px; background: #8b5cf6; border-radius: 50%;
            display: flex; align-items: center; justify-content: center; font-weight: 600; color: white; font-size: 16px;
        }

        .card { background: white; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); padding: 20px 24px; margin-bottom: 20px; }
        .card-title { font-size: 15px; font-weight: 600; color: #0f172a; margin-bottom: 12px; }

        .filter-row {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: center;
            margin-bottom: 4px;
        }
        .filter-row input {
            flex: 1;
            min-width: 200px;
            padding: 8px 14px;
            border: 1.5px solid #e2e8f0;
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            background: #ffffff;
            transition: 0.2s;
        }
        .filter-row input:focus {
            outline: none;
            border-color: #8b5cf6;
            box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.08);
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
        .filter-desc { font-size: 13px; color: #94a3b8; margin-top: 6px; }

        .ranking-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-top: 4px;
        }

        .ranking-item {
            display: flex;
            align-items: center;
            background: #ffffff;
            border-radius: 10px;
            padding: 14px 18px;
            border: 1px solid #eef2f6;
            transition: 0.2s;
            gap: 14px;
        }
        .ranking-item:hover { border-color: #cbd5e1; box-shadow: 0 2px 8px rgba(0,0,0,0.04); }

        .rank-number {
            font-size: 20px;
            font-weight: 700;
            min-width: 44px;
            text-align: center;
            color: #94a3b8;
        }
        .rank-number.gold { color: #f59e0b; }
        .rank-number.silver { color: #94a3b8; }
        .rank-number.bronze { color: #d97706; }

        .candidate-info { flex: 1; min-width: 0; }
        .candidate-name { font-size: 15px; font-weight: 600; color: #0f172a; }
        .candidate-detail { font-size: 13px; color: #64748b; margin-top: 2px; }
        .candidate-detail i { width: 16px; color: #94a3b8; margin-right: 2px; }
        .candidate-achievement { font-size: 13px; color: #475569; margin-top: 2px; }
        .candidate-achievement .check { color: #10b981; margin-right: 4px; }
        .candidate-reward { font-size: 13px; color: #10b981; font-weight: 600; margin-top: 2px; }

        .candidate-score {
            font-size: 18px;
            font-weight: 700;
            color: #0f172a;
            min-width: 64px;
            text-align: center;
        }
        .candidate-score small { font-size: 12px; font-weight: 400; color: #94a3b8; }

        .btn-lihat {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #8b5cf6;
            color: white;
            border: none;
            padding: 5px 16px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 13px;
            cursor: pointer;
            transition: 0.2s;
            text-decoration: none;
            min-width: 84px;
            justify-content: center;
        }
        .btn-lihat:hover { background: #7c3aed; transform: translateY(-1px); }

        .total-info {
            text-align: center;
            font-size: 14px;
            color: #64748b;
            padding-top: 12px;
            border-top: 1px solid #eef2f6;
            margin-top: 4px;
        }
        .total-info strong { color: #0f172a; }

        .empty-state { text-align: center; padding: 40px 0; color: #94a3b8; }
        .empty-state i { font-size: 36px; display: block; margin-bottom: 8px; }

        @media (max-width: 768px) {
            .sidebar { width: 70px; padding: 16px 12px; }
            .sidebar .logo { font-size: 16px; }
            .sidebar .logo span { display: none; }
            .sidebar .menu li span { display: none; }
            .main-content { margin-left: 70px; padding: 16px; }
            .ranking-item { flex-wrap: wrap; gap: 6px; }
            .candidate-score { min-width: 56px; font-size: 16px; }
            .btn-lihat { min-width: 70px; font-size: 12px; padding: 4px 12px; }
        }
        @media (max-width: 480px) {
            .ranking-item { flex-direction: column; text-align: center; }
            .candidate-info { padding: 4px 0; }
        }
    </style>
</head>
<body>

<?php include 'includes/sidebar.php'; ?>

<div class="main-content">

    <div class="header">
        <h1>🏆 Ranking Kandidat</h1>
        <div class="user">
            <span><?php echo date('l, d F Y'); ?></span>
            <div class="avatar"><?php echo strtoupper(substr($nama, 0, 1)); ?></div>
            <span><?php echo htmlspecialchars($nama); ?></span>
        </div>
    </div>

    <div class="card">
        <div class="filter-row">
            <input type="text" id="searchInput" placeholder="Cari nama, NIM, atau prodi..." onkeyup="filterRanking()">
            <select id="periodeFilter">
                <option value="all">Semua Periode</option>
                <option value="2025/2026">2025/2026</option>
                <option value="2026/2027" selected>2026/2027</option>
            </select>
        </div>
        <div class="filter-desc">
            <i class="fas fa-info-circle"></i> Daftar mahasiswa yang lolos verifikasi, diurutkan berdasarkan nilai
        </div>
    </div>

    <div class="card">
        <div class="card-title">📋 Daftar Kandidat Lolos Verifikasi</div>
        
        <?php if (count($ranking_data) > 0): ?>
            <?php 
            $rank = 1;
            foreach ($ranking_data as $row): 
                $nilai = hitungNilaiAkhir($row);
                $reward = $row['reward_nominal'] ?? 0;
                $reward_formatted = number_format($reward, 0, ',', '.');
                
                $rank_class = '';
                if ($rank == 1) $rank_class = 'gold';
                elseif ($rank == 2) $rank_class = 'silver';
                elseif ($rank == 3) $rank_class = 'bronze';
                
                $rank_label = '';
                if ($rank == 1) $rank_label = '🥇';
                elseif ($rank == 2) $rank_label = '🥈';
                elseif ($rank == 3) $rank_label = '🥉';
                else $rank_label = $rank . '.';
            ?>
            <div class="ranking-item" data-name="<?php echo strtolower($row['nama_lengkap']); ?>" data-nim="<?php echo $row['nim']; ?>" data-prodi="<?php echo strtolower($row['prodi'] ?? ''); ?>">
                <div class="rank-number <?php echo $rank_class; ?>"><?php echo $rank_label; ?></div>
                <div class="candidate-info">
                    <div class="candidate-name"><?php echo htmlspecialchars($row['nama_lengkap']); ?></div>
                    <div class="candidate-detail">
                        <i class="fas fa-id-card"></i> <?php echo $row['nim']; ?> &middot;
                        <i class="fas fa-graduation-cap"></i> <?php echo htmlspecialchars($row['prodi'] ?? '-'); ?>
                    </div>
                    <div class="candidate-achievement">
                        <span class="check">✔</span> <?php echo htmlspecialchars($row['prestasi']); ?> — <?php echo $row['level_kompetisi'] ?? 'Nasional'; ?>
                    </div>
                    <?php if ($reward > 0): ?>
                    <div class="candidate-reward">
                        💰 Rp <?php echo $reward_formatted; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="candidate-score">
                    <?php echo $nilai; ?><small>/100</small>
                </div>
                <!-- ========================================================= -->
<!-- TOMBOL LIHAT (BUKA MODAL)                                  -->
<!-- ========================================================= -->
<div>
    <button type="button" class="btn-lihat" onclick="openModal(<?php echo $row['user_id']; ?>)">
        <i class="fas fa-eye"></i> Lihat
    </button>
</div>
            </div>
            <?php 
                $rank++;
            endforeach; 
            ?>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <p>Belum ada kandidat yang lolos verifikasi.</p>
            </div>
        <?php endif; ?>

        <div class="total-info">
            Total: <strong><?php echo $total_kandidat; ?></strong> kandidat
        </div>
    </div>

</div>

<!-- ========================================================= -->
<!-- MODAL DETAIL KANDIDAT                                     -->
<!-- ========================================================= -->
<div id="modalDetail" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; justify-content: center; align-items: center;">
    <div style="background: white; border-radius: 16px; max-width: 700px; width: 90%; max-height: 90vh; overflow-y: auto; padding: 30px 35px; position: relative; box-shadow: 0 20px 60px rgba(0,0,0,0.3);">
        
        <!-- TOMBOL CLOSE -->
        <button onclick="closeModal()" style="position: absolute; top: 15px; right: 20px; background: none; border: none; font-size: 28px; color: #94a3b8; cursor: pointer; transition: 0.3s;">
            <i class="fas fa-times"></i>
        </button>
        
        <!-- JUDUL -->
        <h2 style="font-size: 22px; font-weight: 700; color: #0f172a; margin-bottom: 20px; border-bottom: 2px solid #e2e8f0; padding-bottom: 15px;">
            <i class="fas fa-user-graduate" style="color: #8b5cf6;"></i> Detail Kandidat
        </h2>
        
        <!-- CONTENT MODAL (DIISI VIA AJAX) -->
        <div id="modalContent">
            <div style="text-align: center; padding: 30px 0;">
                <i class="fas fa-spinner fa-spin" style="font-size: 36px; color: #8b5cf6;"></i>
                <p style="color: #94a3b8; margin-top: 10px;">Memuat data...</p>
            </div>
        </div>
        
    </div>
</div>

<!-- ========================================================= -->
<!-- SCRIPT MODAL                                              -->
<!-- ========================================================= -->
<script>
    function openModal(userId) {
        // Tampilkan modal
        document.getElementById('modalDetail').style.display = 'flex';
        document.getElementById('modalContent').innerHTML = `
            <div style="text-align: center; padding: 30px 0;">
                <i class="fas fa-spinner fa-spin" style="font-size: 36px; color: #8b5cf6;"></i>
                <p style="color: #94a3b8; margin-top: 10px;">Memuat data...</p>
            </div>
        `;
        
        // Fetch data via AJAX
        fetch('get_kandidat_detail.php?id=' + userId)
            .then(response => response.text())
            .then(data => {
                document.getElementById('modalContent').innerHTML = data;
            })
            .catch(error => {
                document.getElementById('modalContent').innerHTML = `
                    <div style="text-align: center; padding: 30px 0; color: #ef4444;">
                        <i class="fas fa-exclamation-circle" style="font-size: 36px;"></i>
                        <p>Gagal memuat data. Silakan coba lagi.</p>
                    </div>
                `;
            });
    }
    
    function closeModal() {
        document.getElementById('modalDetail').style.display = 'none';
    }
    
    // Tutup modal saat klik di luar area modal
    document.getElementById('modalDetail').addEventListener('click', function(e) {
        if (e.target === this) {
            closeModal();
        }
    });
    
    // Tutup modal dengan tombol ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal();
        }
    });
</script>

<style>
    .btn-lihat {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #8b5cf6;
        color: white;
        border: none;
        padding: 5px 16px;
        border-radius: 6px;
        font-weight: 600;
        font-size: 13px;
        cursor: pointer;
        transition: 0.2s;
        text-decoration: none;
        min-width: 84px;
        justify-content: center;
    }
    .btn-lihat:hover { background: #7c3aed; transform: translateY(-1px); }
    
    /* Animasi modal */
    #modalDetail {
        animation: fadeIn 0.3s ease;
    }
    #modalDetail > div {
        animation: slideIn 0.3s ease;
    }
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    @keyframes slideIn {
        from { transform: translateY(-20px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }
</style>

<script>
    function filterRanking() {
        const input = document.getElementById('searchInput');
        const filter = input.value.toLowerCase();
        const items = document.querySelectorAll('.ranking-item');
        let visible = 0;
        
        items.forEach(item => {
            const name = item.dataset.name || '';
            const nim = item.dataset.nim || '';
            const prodi = item.dataset.prodi || '';
            const match = name.includes(filter) || nim.includes(filter) || prodi.includes(filter);
            item.style.display = match ? 'flex' : 'none';
            if (match) visible++;
        });
        
        const totalEl = document.querySelector('.total-info');
        if (totalEl) {
            totalEl.innerHTML = `Menampilkan: <strong>${visible}</strong> dari <strong><?php echo $total_kandidat; ?></strong> kandidat`;
        }
    }
</script>

</body>
</html>