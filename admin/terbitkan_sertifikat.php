<?php
// admin/terbitkan_sertifikat.php

session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once '../config/database.php';
require_once '../includes/certificate_validator.php';

$validator = new CertificateValidator($conn);
$success = '';
$error = '';
$qrFile = null;
$certificateNumber = null;

function generateCertificateNumber() {
    return 'SMB/' . date('Y') . '/' . date('m') . '/' . strtoupper(uniqid());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pengajuan_id'])) {
    $pengajuanId = (int)$_POST['pengajuan_id'];
    
    $query = "SELECT p.*, u.nama_lengkap, u.nim FROM pengajuan p 
              JOIN users u ON p.user_id = u.id 
              WHERE p.id = $pengajuanId";
    $result = mysqli_query($conn, $query);
    $pengajuan = mysqli_fetch_assoc($result);
    
    if ($pengajuan) {
        $certificateNumber = generateCertificateNumber();
        $baseUrl = "http://" . $_SERVER['HTTP_HOST'] . "/prestasi-mahasiswa";
        $verificationUrl = $baseUrl . "/verifikasi_sertifikat.php?nomor=" . urlencode($certificateNumber);
        
        $qrFilename = $validator->generateQRCode($verificationUrl);
        
        if ($qrFilename) {
            $insert = "INSERT INTO official_certificates 
                       (certificate_number, qr_code_text, student_name, student_nim, 
                        achievement_title, level, rank, issue_date, pengajuan_id) 
                       VALUES (
                           '$certificateNumber', 
                           '$certificateNumber',
                           '{$pengajuan['nama_lengkap']}', 
                           '{$pengajuan['nim_ketua']}',
                           '{$pengajuan['nama_kejuaraan']}', 
                           '{$pengajuan['level_kompetisi']}',
                           '{$pengajuan['raihan_prestasi']}', 
                           NOW(), 
                           $pengajuanId
                       )";
            
            if (mysqli_query($conn, $insert)) {
                mysqli_query($conn, "UPDATE pengajuan SET status = 'disetujui', certificate_number = '$certificateNumber' WHERE id = $pengajuanId");
                $success = "Sertifikat berhasil diterbitkan!";
                $qrFile = $qrFilename;
            } else {
                $error = "Gagal menyimpan: " . mysqli_error($conn);
            }
        } else {
            $error = "Gagal generate QR Code";
        }
    } else {
        $error = "Data pengajuan tidak ditemukan";
    }
}

$pengajuanList = mysqli_query($conn, "SELECT p.*, u.nama_lengkap FROM pengajuan p 
                                       JOIN users u ON p.user_id = u.id 
                                       WHERE p.status = 'diproses' 
                                       ORDER BY p.created_at DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terbitkan Sertifikat - Admin</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #f5f5f5; }
        .sidebar { position: fixed; left: 0; top: 0; width: 260px; height: 100vh; background: linear-gradient(180deg, #8B5A2B 0%, #6B1A1A 100%); color: white; padding: 20px; }
        .sidebar h3 { margin-bottom: 30px; }
        .sidebar a { display: block; color: white; text-decoration: none; padding: 10px; margin: 5px 0; border-radius: 8px; }
        .sidebar a:hover { background: rgba(255,255,255,0.2); }
        .content { margin-left: 280px; padding: 20px; }
        .card { background: white; border-radius: 12px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        h2 { color: #6B1A1A; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #8B5A2B; color: white; }
        .btn { padding: 8px 15px; border-radius: 5px; text-decoration: none; display: inline-block; }
        .btn-success { background: #28a745; color: white; border: none; cursor: pointer; }
        .btn-success:hover { background: #218838; }
        .alert-success { background: #d4edda; color: #155724; padding: 10px; border-radius: 8px; margin-bottom: 15px; }
        .alert-error { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 8px; margin-bottom: 15px; }
        .qr-preview { text-align: center; margin-top: 20px; }
        .qr-preview img { max-width: 200px; border: 1px solid #ddd; padding: 10px; border-radius: 10px; }
    </style>
</head>
<body>
    <div class="sidebar">
        <h3>👨‍💼 SMB Admin</h3>
        <a href="dashboard.php">📊 Dashboard</a>
        <a href="verifikasi.php">✅ Verifikasi</a>
        <a href="terbitkan_sertifikat.php" style="background: rgba(255,255,255,0.2);">📄 Terbitkan Sertifikat</a>
        <a href="data_mahasiswa.php">👨‍🎓 Data Mahasiswa</a>
        <a href="../logout.php" style="margin-top: 50px;">🚪 Logout</a>
    </div>
    
    <div class="content">
        <div class="card">
            <h2>📄 Terbitkan Sertifikat & QR Code</h2>
            
            <?php if ($success): ?>
                <div class="alert-success">✅ <?php echo $success; ?></div>
                <?php if ($qrFile): ?>
                    <div class="qr-preview">
                        <h3>QR Code Sertifikat:</h3>
                        <img src="../uploads/qrcodes/<?php echo $qrFile; ?>" alt="QR Code">
                        <p><strong>Nomor Sertifikat:</strong> <?php echo $certificateNumber; ?></p>
                        <p><a href="../uploads/qrcodes/<?php echo $qrFile; ?>" download class="btn btn-success">📥 Download QR Code</a></p>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert-error">❌ <?php echo $error; ?></div>
            <?php endif; ?>
            
            <h3>Daftar Pengajuan Menunggu Verifikasi</h3>
            <table>
                <thead>
                    <tr><th>No</th><th>Mahasiswa</th><th>Kejuaraan</th><th>Level</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                    <?php $no=1; while($row = mysqli_fetch_assoc($pengajuanList)): ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td><?php echo $row['nama_lengkap']; ?></td>
                        <td><?php echo $row['nama_kejuaraan']; ?></td>
                        <td><?php echo $row['level_kompetisi']; ?></td>
                        <td>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="pengajuan_id" value="<?php echo $row['id']; ?>">
                                <button type="submit" class="btn btn-success">📄 Terbitkan Sertifikat + QR</button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>