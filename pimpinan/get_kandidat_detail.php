<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'superadmin') {
    exit('Unauthorized');
}

require_once '../config/database.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id == 0) {
    exit('<p style="color: #ef4444;">ID tidak valid.</p>');
}

// Ambil data mahasiswa
$query = "SELECT p.*, u.nama_lengkap, u.nim, u.prodi, u.fakultas,
          kr.nominal as reward_nominal
          FROM pengajuan p 
          JOIN users u ON p.user_id = u.id 
          LEFT JOIN kategori_reward kr ON (
              kr.tingkat = p.level_kompetisi AND 
              kr.kategori = p.jenis_peserta AND
              kr.raihan = p.raihan_prestasi
          )
          WHERE u.id = $id AND p.status = 'disetujui'
          ORDER BY p.id DESC LIMIT 1";
$result = mysqli_query($conn, $query);
$data = mysqli_fetch_assoc($result);

if (!$data) {
    exit('<p style="color: #ef4444;">Data tidak ditemukan.</p>');
}

// Fungsi hitung nilai
function hitungNilaiAkhir($row) {
    if (!empty($row['nilai_akhir'])) {
        return $row['nilai_akhir'];
    }
    $bobot_level = ['Lokal' => 1, 'Provinsi' => 2, 'Nasional' => 3, 'Internasional' => 4];
    $bobot_raihan = [
        'Juara 1' => 4, 'Juara 2' => 3, 'Juara 3' => 2, 'Finalis' => 1,
        'Silver Medal' => 3, 'Gold Medal' => 4, 'Bronze Medal' => 2,
        'Peserta Aktif' => 1, 'Juara Harapan' => 2,
    ];
    $level = $row['level_kompetisi'] ?? 'Lokal';
    $raihan = $row['raihan_prestasi'] ?? 'Juara 3';
    $bobot_raihan_value = $bobot_raihan[$raihan] ?? 2;
    $skor = ($bobot_level[$level] * 15) + ($bobot_raihan_value * 10);
    return min($skor, 100);
}

$nilai = hitungNilaiAkhir($data);
$reward = $data['reward_nominal'] ?? 0;
$reward_formatted = number_format($reward, 0, ',', '.');

// Ambil fakultas dari tabel users jika kosong
$fakultas = $data['fakultas'] ?? '-';
if (empty($fakultas) || $fakultas == '-') {
    $query_fakultas = "SELECT fakultas FROM users WHERE id = " . $data['user_id'];
    $result_fakultas = mysqli_query($conn, $query_fakultas);
    $row_fakultas = mysqli_fetch_assoc($result_fakultas);
    $fakultas = $row_fakultas['fakultas'] ?? '-';
}
?>

<!-- MODAL CONTENT -->
<div style="display: grid; grid-template-columns: 140px 1fr; gap: 6px 16px;">
    <div style="font-weight: 500; color: #475569; padding: 4px 0;">Nama Lengkap</div>
    <div style="color: #0f172a; padding: 4px 0; border-bottom: 1px dashed #eef2f6;"><strong><?php echo htmlspecialchars($data['nama_lengkap']); ?></strong></div>
    
    <div style="font-weight: 500; color: #475569; padding: 4px 0;">NIM</div>
    <div style="color: #0f172a; padding: 4px 0; border-bottom: 1px dashed #eef2f6;"><?php echo htmlspecialchars($data['nim']); ?></div>
    
    <div style="font-weight: 500; color: #475569; padding: 4px 0;">Program Studi</div>
    <div style="color: #0f172a; padding: 4px 0; border-bottom: 1px dashed #eef2f6;"><?php echo htmlspecialchars($data['prodi'] ?? '-'); ?></div>
    
    <div style="font-weight: 500; color: #475569; padding: 4px 0;">Fakultas</div>
    <div style="color: #0f172a; padding: 4px 0; border-bottom: 1px dashed #eef2f6;">
        <?php echo htmlspecialchars($fakultas); ?>
    </div>
    
    <div style="font-weight: 500; color: #475569; padding: 4px 0;">Nama Kejuaraan</div>
    <div style="color: #0f172a; padding: 4px 0; border-bottom: 1px dashed #eef2f6;"><strong><?php echo htmlspecialchars($data['nama_kejuaraan']); ?></strong></div>
    
    <div style="font-weight: 500; color: #475569; padding: 4px 0;">Level Kompetisi</div>
    <div style="color: #0f172a; padding: 4px 0; border-bottom: 1px dashed #eef2f6;"><?php echo htmlspecialchars($data['level_kompetisi']); ?></div>
    
    <div style="font-weight: 500; color: #475569; padding: 4px 0;">Kategori Prestasi</div>
    <div style="color: #0f172a; padding: 4px 0; border-bottom: 1px dashed #eef2f6;"><?php echo htmlspecialchars($data['kategori_prestasi'] ?? '-'); ?></div>
    
    <div style="font-weight: 500; color: #475569; padding: 4px 0;">Raihan Prestasi</div>
    <div style="color: #0f172a; padding: 4px 0; border-bottom: 1px dashed #eef2f6;"><strong><?php echo htmlspecialchars($data['raihan_prestasi']); ?></strong></div>
    
    <div style="font-weight: 500; color: #475569; padding: 4px 0;">Skor Akhir</div>
    <div style="color: #0f172a; padding: 4px 0; border-bottom: 1px dashed #eef2f6;"><strong style="color: #8b5cf6;"><?php echo $nilai; ?>/100</strong></div>
    
    <div style="font-weight: 500; color: #475569; padding: 4px 0;">Reward</div>
    <div style="color: #0f172a; padding: 4px 0; border-bottom: 1px dashed #eef2f6;"><strong style="color: #10b981;">Rp <?php echo $reward_formatted; ?></strong></div>
    
    <div style="font-weight: 500; color: #475569; padding: 4px 0;">Status Penetapan</div>
    <div style="color: #0f172a; padding: 4px 0; border-bottom: 1px dashed #eef2f6;">
        <?php if ($data['penetapan'] == 1): ?>
            <span style="display:inline-block; padding:2px 12px; background:#d1fae5; color:#065f46; border-radius:20px; font-size:12px; font-weight:600;">✅ Ditetapkan</span>
        <?php else: ?>
            <span style="display:inline-block; padding:2px 12px; background:#fef3c7; color:#92400e; border-radius:20px; font-size:12px; font-weight:600;">⏳ Menunggu</span>
        <?php endif; ?>
    </div>
</div>

<!-- TOMBOL AKSI DI MODAL -->
<div style="margin-top: 20px; padding-top: 16px; border-top: 1px solid #e2e8f0; display: flex; gap: 12px; flex-wrap: wrap;">
    <?php if ($data['penetapan'] == 0): ?>
        <a href="tetapkan_prestasi.php?id=<?php echo $data['id']; ?>" class="btn-tetapkan-modal">
            <i class="fas fa-check"></i> Tetapkan
        </a>
    <?php endif; ?>
    <button onclick="closeModal()" class="btn-tutup-modal">
        <i class="fas fa-times"></i> Tutup
    </button>
</div>

<style>
    .btn-tetapkan-modal {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #10b981;
        color: white;
        padding: 8px 24px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 14px;
        border: none;
        cursor: pointer;
        text-decoration: none;
        transition: 0.3s;
    }
    .btn-tetapkan-modal:hover { background: #059669; transform: translateY(-2px); }
    
    .btn-tutup-modal {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #e2e8f0;
        color: #475569;
        padding: 8px 24px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 14px;
        border: none;
        cursor: pointer;
        transition: 0.3s;
    }
    .btn-tutup-modal:hover { background: #cbd5e1; transform: translateY(-2px); }
</style>