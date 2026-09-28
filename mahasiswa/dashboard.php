<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/flash.php';

use App\Core\Session;
use App\Models\User;
use App\Models\Pengajuan;

$session = new Session();
if (!$session->has('user_id') || $session->get('role') !== 'mahasiswa') {
    header("Location: ../login.php");
    exit();
}

$user_id = (int) $session->get('user_id');

$userModel = new User();
$pengajuanModel = new Pengajuan();

$user_query = $userModel->find($user_id);
$foto_profil = $user_query['foto_profil'] ?? '';
$nama_lengkap = $user_query['nama_lengkap'] ?? 'Mahasiswa';
$nim = $user_query['nim'] ?? '-';

$pengajuanList = $pengajuanModel->where('user_id', $user_id);
$total_pengajuan = count($pengajuanList);
$total_diproses  = count(array_filter($pengajuanList, fn($item) => $item['status'] === 'diproses'));
$total_disetujui = count(array_filter($pengajuanList, fn($item) => $item['status'] === 'disetujui'));
$total_ditolak   = count(array_filter($pengajuanList, fn($item) => $item['status'] === 'ditolak'));

// Format tanggal (Friday, 12 June 2026)
$tanggal_hari_ini = date('l, d F Y');

// =========================================================
// CEK STATUS PERIODE AKTIF
// =========================================================
$query_periode = "SELECT * FROM pengaturan_periode LIMIT 1";
$result_periode = mysqli_query($conn, $query_periode);
$periode = mysqli_fetch_assoc($result_periode);

$periode_aktif = false;
$tanggal_mulai = null;
$tanggal_selesai = null;
$sekarang = new DateTime();

if ($periode) {
    try {
        $tanggal_mulai = new DateTime($periode['periode_mulai']);
        $tanggal_selesai = new DateTime($periode['periode_selesai']);
        $hari_ini = new DateTime();
        
        $tanggal_selesai->setTime(23, 59, 59);
        
        if ($periode['is_active'] == 1 && $hari_ini >= $tanggal_mulai && $hari_ini <= $tanggal_selesai) {
            $periode_aktif = true;
        }
    } catch (Exception $e) {
        // Abaikan error
    }
}

// =========================================================
// HITUNG SISA HARI UNTUK NOTIFIKASI DEADLINE
// =========================================================
$sisa_hari = 0;
$status_warning = '';
$warna_notif = '';
$bg_notif = '';
$border_color = '';

if ($periode && $periode['is_active'] == 1 && $periode_aktif) {
    $tanggal_selesai = new DateTime($periode['periode_selesai']);
    $hari_ini = new DateTime();
    $tanggal_selesai->setTime(0, 0, 0);
    $hari_ini->setTime(0, 0, 0);
    $sisa_hari = $hari_ini->diff($tanggal_selesai)->days;
    
    if ($sisa_hari <= 7) {
        if ($sisa_hari <= 1) {
            $status_warning = '🚨 HARI TERAKHIR!';
            $warna_notif = '#c62828';
            $bg_notif = '#fce4ec';
            $border_color = '#c62828';
        } elseif ($sisa_hari <= 3) {
            $status_warning = '⚠️ PERINGATAN!';
            $warna_notif = '#e67e22';
            $bg_notif = '#fff3e0';
            $border_color = '#e67e22';
        } else {
            $status_warning = '⏳ SEGERA BERAKHIR';
            $warna_notif = '#f5a623';
            $bg_notif = '#fff3cd';
            $border_color = '#f5a623';
        }
    } else {
        $status_warning = '✅ Periode Pengajuan Aktif';
        $warna_notif = '#2e7d32';
        $bg_notif = '#e8f5e9';
        $border_color = '#2e7d32';
    }
}


include 'includes/sidebar.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Mahasiswa - SMB</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/burger-menu.css">
    <?php render_flash_script(); ?>
    <style>

        .menu-logout { margin-top: 40px; }

        .main-content { margin-left: 260px; padding: 24px 32px; min-height: 100vh; }

        .top-bar {
            background: white;
            border-radius: 12px;
            padding: 16px 24px;
            margin-bottom: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: 1px solid #e8e8e8;
        }
        .page-title h1 { font-size: 20px; font-weight: 600; color: #1a1a1a; }
        .page-title p { font-size: 13px; color: #666; margin-top: 4px; }
        .user-info { display: flex; align-items: center; gap: 16px; }
        .user-avatar {
            width: 40px; height: 40px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            background: #2c7da0; overflow: hidden; color: white; font-weight: 600;
        }
        .user-avatar img { width: 100%; height: 100%; object-fit: cover; }
        .user-name { margin: 0 10px; font-weight: 500; color: #333; }
        .tanggal { font-size: 13px; color: #666; background: #f0f2f5; padding: 6px 14px; border-radius: 20px; }

        /* NOTIFIKASI GABUNGAN */
        .notif-card {
            border-radius: 12px;
            padding: 16px 20px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
            border-left: 5px solid;
        }
        .notif-card .judul { font-weight: 700; font-size: 16px; }
        .notif-card .sub { font-size: 14px; margin-top: 3px; line-height: 1.5; }

        .btn-notif {
            padding: 7px 22px;
            border-radius: 30px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            display: inline-block;
            transition: 0.3s;
            border: none;
            cursor: pointer;
        }
        .btn-notif:hover { transform: translateY(-2px); }
        .btn-notif-primary { background: #2e7d32; color: white; }
        .btn-notif-yellow { background: #f5a623; color: white; }
        .btn-notif-outline { background: transparent; color: #2e7d32; border: 2px solid #2e7d32; }
        .btn-notif-outline-yellow { background: transparent; color: #f5a623; border: 2px solid #f5a623; }

        /* STATS */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 24px;
        }
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            border: 1px solid #e8e8e8;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-left: 4px solid #2c7da0;
        }
        .stat-title { font-size: 12px; color: #666; margin-bottom: 4px; }
        .stat-number { font-size: 28px; font-weight: 700; color: #1a1a1a; }
        .stat-icon { font-size: 28px; color: #2c7da0; opacity: 0.6; }

        /* MENU */
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 24px;
        }
        .menu-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            border: 1px solid #e8e8e8;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s;
        }
        .menu-card:hover { transform: translateY(-3px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .menu-info h3 { font-size: 16px; font-weight: 600; margin-bottom: 8px; }
        .menu-info p { font-size: 13px; color: #666; }
        .menu-link {
            background: #2c7da0;
            color: white;
            padding: 6px 16px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 12px;
            font-weight: 500;
            transition: all 0.3s;
        }
        .menu-link:hover { background: #1f5e7a; }
        .menu-link.disabled { background: #ccc; cursor: not-allowed; pointer-events: none; }

        /* INFO */
        .info-card {
            background: white;
            border-radius: 12px;
            border: 1px solid #e8e8e8;
            overflow: hidden;
            margin-top: 24px;
        }
        .info-header { padding: 12px 20px; border-bottom: 1px solid #eee; background: #f8f9fa; font-weight: 600; }
        .info-body { padding: 20px; }
        .info-item {
            display: flex;
            padding: 8px 0;
            border-bottom: 1px dashed #f0f0f0;
        }
        .info-label { width: 120px; font-weight: 500; color: #666; }
        .info-value { flex: 1; color: #333; }

        /* MODAL */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }
        .modal-overlay.show { display: flex; }
        .modal-content {
            background: white;
            border-radius: 20px;
            padding: 35px 40px;
            max-width: 520px;
            width: 92%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            animation: slideDown 0.4s ease-out;
            position: relative;
        }
        .modal-close {
            position: absolute;
            top: 12px;
            right: 18px;
            background: none;
            border: none;
            font-size: 30px;
            color: #aaa;
            cursor: pointer;
            transition: 0.3s;
        }
        .modal-close:hover { color: #333; }
        @keyframes slideDown {
            0% { transform: translateY(-50px); opacity: 0; }
            100% { transform: translateY(0); opacity: 1; }
        }

    </style>
</head>
<body>



<!-- ========================================================= -->
<!-- MAIN CONTENT                                              -->
<!-- ========================================================= -->
<div class="main-content">

    <!-- TOP BAR -->
    <div class="top-bar">
        <div class="page-title">
            <h1>Dashboard Mahasiswa</h1>
            <p>Selamat datang, <?php echo htmlspecialchars($nama_lengkap); ?>!</p>
        </div>
        <div class="user-info">
            <div class="user-avatar">
                <?php if (!empty($foto_profil) && file_exists("../uploads/profil/" . $foto_profil)): ?>
                    <img src="../uploads/profil/<?php echo $foto_profil; ?>" alt="Foto Profil">
                <?php else: ?>
                    <i class="fas fa-user"></i>
                <?php endif; ?>
            </div>
            <span class="user-name"><?php echo htmlspecialchars($nama_lengkap); ?></span>
            <div class="tanggal"><?php echo $tanggal_hari_ini; ?></div>
        </div>
    </div>

    <!-- ========================================================= -->
    <!-- NOTIFIKASI GABUNGAN (SATU CARD)                          -->
    <!-- ========================================================= -->
    <?php if ($periode && $periode['is_active'] == 1 && $periode_aktif): ?>
        <div class="notif-card" style="background: <?php echo $bg_notif; ?>; border-left-color: <?php echo $border_color; ?>;">
            <div>
                <div class="judul" style="color: <?php echo $warna_notif; ?>;">
                    <?php echo $status_warning; ?>
                </div>
                <div class="sub" style="color: <?php echo $warna_notif; ?>;">
                    <?php echo date('d F Y', strtotime($periode['periode_mulai'])); ?> — 
                    <?php echo date('d F Y', strtotime($periode['periode_selesai'])); ?>
                    (Sisa <strong><?php echo $sisa_hari; ?> hari</strong>)
                    <?php if ($sisa_hari <= 7 && $sisa_hari > 0): ?>
                        <br><strong>⏳ Segera ajukan prestasi Anda!</strong>
                    <?php endif; ?>
                </div>
            </div>
            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                <a href="pengajuan.php" class="btn-notif <?php echo ($sisa_hari <= 7) ? 'btn-notif-yellow' : 'btn-notif-primary'; ?>">
                    📝 Ajukan
                </a>
                <button onclick="bukaPopup()" class="btn-notif <?php echo ($sisa_hari <= 7) ? 'btn-notif-outline-yellow' : 'btn-notif-outline'; ?>">
                    📨 Lihat
                </button>
            </div>
        </div>

    <?php elseif ($periode && $periode['is_active'] == 0): ?>
        <!-- PERIODE DINONAKTIFKAN -->
        <div class="notif-card" style="background: #fff3e0; border-left-color: #e67e22;">
            <div>
                <div class="judul" style="color: #e67e22;">⏸️ Periode Dinonaktifkan</div>
                <div class="sub" style="color: #e67e22;">
                    Periode pengajuan sedang dinonaktifkan oleh admin.
                    Periode terakhir: <?php echo date('d F Y', strtotime($periode['periode_mulai'])); ?> — <?php echo date('d F Y', strtotime($periode['periode_selesai'])); ?>
                </div>
            </div>
            <button onclick="bukaPopup()" class="btn-notif" style="background: #e67e22; color: white; padding: 7px 22px; border-radius: 30px; border: none; cursor: pointer;">📨 Lihat</button>
        </div>

    <?php elseif ($periode && !$periode_aktif && $periode['is_active'] == 1): ?>
        <!-- PERIODE TELAH BERAKHIR -->
        <div class="notif-card" style="background: #ffebee; border-left-color: #c62828;">
            <div>
                <div class="judul" style="color: #c62828;">⛔ Periode Telah Berakhir</div>
                <div class="sub" style="color: #c62828;">
                    Periode pengajuan telah berakhir pada <?php echo date('d F Y', strtotime($periode['periode_selesai'])); ?>.
                    Silakan tunggu periode berikutnya.
                </div>
            </div>
            <button onclick="bukaPopup()" class="btn-notif" style="background: #c62828; color: white; padding: 7px 22px; border-radius: 30px; border: none; cursor: pointer;">📨 Lihat</button>
        </div>

    <?php else: ?>
        <!-- BELUM ADA PERIODE -->
        <div class="notif-card" style="background: #fff3cd; border-left-color: #ffc107;">
            <div>
                <div class="judul" style="color: #856404;">⚠️ Belum Ada Periode</div>
                <div class="sub" style="color: #856404;">Belum ada pengaturan periode. Silakan hubungi admin.</div>
            </div>
        </div>
    <?php endif; ?>

    <!-- ========================================================= -->
    <!-- STATISTIK PENGAJUAN                                       -->
    <!-- ========================================================= -->
    <div class="stats-grid">
        <div class="stat-card">
            <div>
                <div class="stat-title">Total Pengajuan</div>
                <div class="stat-number"><?php echo $total_pengajuan; ?></div>
            </div>
            <div class="stat-icon"><i class="fas fa-file-alt"></i></div>
        </div>
        <div class="stat-card">
            <div>
                <div class="stat-title">Diproses</div>
                <div class="stat-number"><?php echo $total_diproses; ?></div>
            </div>
            <div class="stat-icon"><i class="fas fa-clock"></i></div>
        </div>
        <div class="stat-card">
            <div>
                <div class="stat-title">Disetujui</div>
                <div class="stat-number"><?php echo $total_disetujui; ?></div>
            </div>
            <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
        </div>
        <div class="stat-card">
            <div>
                <div class="stat-title">Ditolak</div>
                <div class="stat-number"><?php echo $total_ditolak; ?></div>
            </div>
            <div class="stat-icon"><i class="fas fa-times-circle"></i></div>
        </div>
    </div>

    <!-- ========================================================= -->
    <!-- MENU CEPAT                                                -->
    <!-- ========================================================= -->
    <div class="menu-grid">
        <div class="menu-card">
            <div class="menu-info">
                <h3>📝 Ajukan Prestasi</h3>
                <p>Isi form pengajuan prestasi mahasiswa</p>
            </div>
            <?php if ($periode_aktif): ?>
                <a href="pengajuan.php" class="menu-link">Ajukan</a>
            <?php else: ?>
                <span class="menu-link disabled">Tidak Aktif</span>
            <?php endif; ?>
        </div>
        <div class="menu-card">
            <div class="menu-info">
                <h3>📋 Riwayat Pengajuan</h3>
                <p>Lihat status pengajuan prestasi Anda</p>
            </div>
            <a href="riwayat.php" class="menu-link">Lihat</a>
        </div>
    </div>

    <!-- ========================================================= -->
    <!-- INFORMASI AKUN                                            -->
    <!-- ========================================================= -->
    <div class="info-card">
        <div class="info-header">Informasi Akun</div>
        <div class="info-body">
            <div class="info-item">
                <span class="info-label">Nama Lengkap</span>
                <span class="info-value">: <?php echo htmlspecialchars($nama_lengkap); ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">NIM</span>
                <span class="info-value">: <?php echo htmlspecialchars($nim); ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Role</span>
                <span class="info-value">: Mahasiswa</span>
            </div>
        </div>
    </div>

</div>

<!-- ========================================================= -->
<!-- MODAL POPUP NOTIFIKASI (BACKGROUND BLUR)                  -->
<!-- ========================================================= -->
<div id="modalNotifikasi" class="modal-overlay">
    <div class="modal-content">
        <button onclick="tutupPopup()" class="modal-close">&times;</button>

        <div style="text-align: center;">
            <span style="font-size: 45px;">📨</span>
            <h2 style="color: #1a1a2e; font-size: 22px; margin-top: 5px;">Notifikasi</h2>
            <p style="color: #999; font-size: 13px;">Pengingat Deadline Pengajuan</p>
        </div>

        <div style="border-top: 2px solid #f5a623; padding-top: 18px;">

            <p style="font-size: 15px; color: #333; margin-bottom: 15px;">
                Kepada Yth. <strong><?php echo htmlspecialchars($nama_lengkap); ?></strong><br>
                <span style="font-size: 13px; color: #888;">NIM: <?php echo htmlspecialchars($nim); ?></span>
            </p>

            <?php if ($periode): ?>
            <div style="background: #fff8e1; padding: 14px 18px; border-radius: 10px; border-left: 5px solid #ffc107; margin-bottom: 15px;">
                <div style="font-weight: 600; color: #856404; font-size: 12px;">📅 Periode</div>
                <div style="font-weight: 600; color: #1a1a2e; font-size: 15px;">
                    <?php echo date('d F Y', strtotime($periode['periode_mulai'])); ?> — 
                    <?php echo date('d F Y', strtotime($periode['periode_selesai'])); ?>
                </div>
                <div style="margin-top: 5px; display: flex; flex-wrap: wrap; gap: 5px 20px; font-size: 14px; color: #555;">
                    <span>📚 Tahun: <strong><?php echo $periode['tahun_akademik']; ?></strong></span>
                    <?php if ($periode_aktif): ?>
                        <span>⏳ Sisa: <strong><?php echo $sisa_hari; ?> hari</strong></span>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php
            $status_popup = '';
            $warna_popup = '';
            $pesan_popup = '';
            $tombol_popup = '';
            $link_popup = '';
            $bg_status = '#f8f9fa';

            if ($periode) {
                if ($periode_aktif && $sisa_hari <= 7) {
                    $status_popup = '⚠️ SEGERA BERAKHIR';
                    $warna_popup = '#c62828';
                    $bg_status = '#fce4ec';
                    $pesan_popup = 'Periode pengajuan akan berakhir dalam ' . $sisa_hari . ' hari. Segera ajukan prestasi Anda!';
                    $tombol_popup = '📝 AJUKAN SEKARANG';
                    $link_popup = 'pengajuan.php';
                } elseif ($periode_aktif && $sisa_hari > 7) {
                    $status_popup = '✅ Masih Tersedia';
                    $warna_popup = '#2e7d32';
                    $bg_status = '#e8f5e9';
                    $pesan_popup = 'Periode pengajuan masih tersedia. Sisa ' . $sisa_hari . ' hari lagi.';
                    $tombol_popup = '📝 AJUKAN SEKARANG';
                    $link_popup = 'pengajuan.php';
                } elseif ($periode['is_active'] == 0) {
                    $status_popup = '⛔ Periode Dinonaktifkan';
                    $warna_popup = '#e67e22';
                    $bg_status = '#fff3e0';
                    $pesan_popup = 'Periode pengajuan sedang dinonaktifkan oleh admin. Anda tidak dapat mengajukan prestasi saat ini.';
                    $tombol_popup = '⛔ TIDAK DAPAT MENGAJUKAN';
                    $link_popup = '#';
                } elseif (!$periode_aktif && $sekarang > $tanggal_selesai) {
                    $status_popup = '⛔ Periode Telah Berakhir';
                    $warna_popup = '#6c757d';
                    $bg_status = '#e2e3e5';
                    $pesan_popup = 'Periode pengajuan telah berakhir pada ' . date('d F Y', strtotime($periode['periode_selesai'])) . '.';
                    $tombol_popup = '📋 LIHAT RIWAYAT';
                    $link_popup = 'riwayat.php';
                } elseif ($sekarang < $tanggal_mulai) {
                    $status_popup = '⏳ Periode Belum Dimulai';
                    $warna_popup = '#e67e22';
                    $bg_status = '#fff3e0';
                    $pesan_popup = 'Periode pengajuan akan dibuka pada ' . date('d F Y', strtotime($periode['periode_mulai'])) . '.';
                    $tombol_popup = '⏳ BELUM DIBUKA';
                    $link_popup = '#';
                }
            } else {
                $status_popup = '⚠️ Belum Ada Periode';
                $warna_popup = '#e67e22';
                $bg_status = '#fff3e0';
                $pesan_popup = 'Belum ada pengaturan periode. Silakan hubungi admin.';
                $tombol_popup = '🏠 KEMBALI';
                $link_popup = 'dashboard.php';
            }
            ?>

            <div style="background: <?php echo $bg_status; ?>; padding: 14px 18px; border-radius: 10px; border-left: 5px solid <?php echo $warna_popup; ?>; margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
                <span style="font-weight: 700; color: <?php echo $warna_popup; ?>; font-size: 16px;"><?php echo $status_popup; ?></span>
                <?php if ($periode_aktif): ?>
                <span style="font-size: 24px; font-weight: 700; color: <?php echo $warna_popup; ?>; background: white; padding: 2px 16px; border-radius: 30px;">
                    <?php echo $sisa_hari; ?> <small style="font-size: 12px; font-weight: 400; color: #888;">hari</small>
                </span>
                <?php endif; ?>
            </div>

            <div style="background: #f8f9fa; padding: 12px 16px; border-radius: 8px; border-left: 4px solid #f5a623; margin-bottom: 18px; font-size: 14px; color: #444; line-height: 1.7;">
                <?php echo $pesan_popup; ?>
                <?php if ($periode_aktif): ?>
                    <br><strong>Dokumen yang diajukan setelah periode berakhir tidak akan diproses.</strong>
                <?php endif; ?>
            </div>

            <div style="text-align: center; display: flex; flex-wrap: wrap; gap: 10px; justify-content: center;">
                <?php if ($link_popup != '#'): ?>
                    <a href="<?php echo $link_popup; ?>" style="display: inline-block; background: #f5a623; color: white; padding: 10px 30px; border-radius: 30px; text-decoration: none; font-weight: 600; font-size: 14px; transition: 0.3s;">
                        <?php echo $tombol_popup; ?>
                    </a>
                <?php else: ?>
                    <span style="display: inline-block; background: #e0e0e0; color: #999; padding: 10px 30px; border-radius: 30px; font-weight: 600; font-size: 14px; cursor: not-allowed;">
                        <?php echo $tombol_popup; ?>
                    </span>
                <?php endif; ?>
                <button onclick="tutupPopup()" style="display: inline-block; background: transparent; color: #f5a623; padding: 10px 30px; border-radius: 30px; border: 2px solid #f5a623; font-weight: 600; font-size: 14px; cursor: pointer; transition: 0.3s;">
                    🏠 Kembali
                </button>
            </div>

            <div style="background: #e3f2fd; padding: 10px 14px; border-radius: 8px; text-align: center; color: #0d47a1; font-size: 12px; margin-top: 18px; border-left: 4px solid #1976d2;">
                ⚠️ Pastikan semua dokumen pendukung sudah lengkap sebelum mengajukan.
            </div>

        </div>

        <div style="margin-top: 18px; padding-top: 15px; border-top: 2px solid #f0f0f0; text-align: center; color: #999; font-size: 12px; line-height: 1.8;">
            Hormat kami,<br>
            <strong style="color: #1a1a2e;">Tim Kemahasiswaan</strong><br>
            Universitas Harkat Negeri<br>
            📅 <?php echo date('d F Y'); ?>
        </div>

    </div>
</div>

<!-- ========================================================= -->
<!-- SCRIPT JAVASCRIPT                                         -->
<!-- ========================================================= -->
<script>
    function bukaPopup() {
        const modal = document.getElementById('modalNotifikasi');
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }

    function tutupPopup() {
        const modal = document.getElementById('modalNotifikasi');
        modal.classList.remove('show');
        document.body.style.overflow = 'auto';
    }

    // Tutup popup saat klik di luar area popup
    document.addEventListener('click', function(event) {
        const modal = document.getElementById('modalNotifikasi');
        if (event.target === modal) {
            tutupPopup();
        }
    });

    // Tutup popup dengan tombol ESC
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            tutupPopup();
        }
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="../assets/js/burger-menu.js"></script>
</body>
</html>