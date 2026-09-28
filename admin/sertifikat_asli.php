<?php
require_once '../includes/auth.php';
requireAdmin();
require_once '../config/database.php';

$success = '';
$error = '';

// Tambah data sertifikat asli
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah'])) {
    $nomor = mysqli_real_escape_string($conn, $_POST['nomor_sertifikat']);
    $nama = mysqli_real_escape_string($conn, $_POST['nama_mahasiswa']);
    $nim = mysqli_real_escape_string($conn, $_POST['nim']);
    $kegiatan = mysqli_real_escape_string($conn, $_POST['nama_kegiatan']);
    $tingkat = mysqli_real_escape_string($conn, $_POST['tingkat']);
    $juara = mysqli_real_escape_string($conn, $_POST['juara']);
    $tanggal = mysqli_real_escape_string($conn, $_POST['tanggal_terbit']);
    
    // Buat teks untuk QR Code
    $base_url = (isset($_SERVER['HTTPS']) ? "https://" : "http://") . $_SERVER['HTTP_HOST'];
    $folder = str_replace('/admin', '', dirname($_SERVER['SCRIPT_NAME']));
    $qr_text = $base_url . $folder . "/verifikasi_sertifikat.php?nomor=" . urlencode($nomor);
    
    $query = "INSERT INTO sertifikat_asli (nomor_sertifikat, qr_text, nama_mahasiswa, nim, nama_kegiatan, tingkat, juara, tanggal_terbit) 
              VALUES ('$nomor', '$qr_text', '$nama', '$nim', '$kegiatan', '$tingkat', '$juara', '$tanggal')";
    
    if (mysqli_query($conn, $query)) {
        $success = "Sertifikat asli berhasil ditambahkan!";
    } else {
        $error = "Gagal: " . mysqli_error($conn);
    }
}

// Hapus data
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    mysqli_query($conn, "DELETE FROM sertifikat_asli WHERE id = $id");
    header("Location: sertifikat_asli.php");
    exit();
}

$result = mysqli_query($conn, "SELECT * FROM sertifikat_asli ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Sertifikat Asli</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f0e8; padding: 20px; }
        .container { max-width: 1200px; margin: auto; background: white; border-radius: 10px; padding: 25px; }
        h2 { color: #6B1A1A; margin-bottom: 10px; }
        .subtitle { color: #666; margin-bottom: 20px; }
        
        .form-card { background: #f9f9f9; padding: 20px; border-radius: 10px; margin-bottom: 25px; }
        .form-row { display: flex; gap: 15px; flex-wrap: wrap; margin-bottom: 15px; }
        .form-group { flex: 1; min-width: 150px; }
        .form-group label { display: block; font-weight: bold; margin-bottom: 5px; font-size: 13px; }
        .form-group input, .form-group select { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px; }
        
        button { background: #6B1A1A; color: white; border: none; padding: 10px 25px; border-radius: 5px; cursor: pointer; }
        button:hover { background: #8B2A2A; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #8B5A2B; color: white; }
        tr:hover { background: #f9f9f9; }
        
        .badge { background: #d4edda; color: #155724; padding: 4px 10px; border-radius: 20px; font-size: 12px; }
        .btn-hapus { background: #dc3545; color: white; padding: 5px 12px; text-decoration: none; border-radius: 3px; font-size: 12px; }
        .btn-hapus:hover { background: #c82333; }
        
        .alert-success { background: #d4edda; color: #155724; padding: 12px; border-radius: 5px; margin-bottom: 15px; }
        .alert-error { background: #f8d7da; color: #721c24; padding: 12px; border-radius: 5px; margin-bottom: 15px; }
    </style>
</head>
<body>
<div class="container">
    <h2>🏆 DATA SERTIFIKAT ASLI (RESMI)</h2>
    <p class="subtitle">Masukkan data sertifikat yang SAH dan RESMI dari kampus.</p>
    
    <?php if($success): ?>
        <div class="alert-success">✅ <?php echo $success; ?></div>
    <?php endif; ?>
    <?php if($error): ?>
        <div class="alert-error">❌ <?php echo $error; ?></div>
    <?php endif; ?>
    
    <div class="form-card">
        <h3>➕ TAMBAH SERTIFIKAT ASLI</h3>
        <form method="POST">
            <div class="form-row">
                <div class="form-group"><label>Nomor Sertifikat</label><input type="text" name="nomor_sertifikat" placeholder="CERT-2024-001" required></div>
                <div class="form-group"><label>Nama Mahasiswa</label><input type="text" name="nama_mahasiswa" required></div>
                <div class="form-group"><label>NIM</label><input type="text" name="nim" required></div>
            </div>
            <div class="form-row">
                <div class="form-group"><label>Nama Kegiatan</label><input type="text" name="nama_kegiatan" required></div>
                <div class="form-group"><label>Tingkat</label>
                    <select name="tingkat">
                        <option>Lokal</option><option>Provinsi</option><option>Nasional</option><option>Internasional</option>
                    </select>
                </div>
                <div class="form-group"><label>Juara</label>
                    <select name="juara">
                        <option>Juara 1</option><option>Juara 2</option><option>Juara 3</option><option>Finalis</option>
                    </select>
                </div>
                <div class="form-group"><label>Tanggal Terbit</label><input type="date" name="tanggal_terbit" required></div>
            </div>
            <button type="submit" name="tambah">✅ SIMPAN SERTIFIKAT ASLI</button>
        </form>
    </div>
    
    <h3>📋 DAFTAR SERTIFIKAT ASLI</h3>
    <div style="overflow-x: auto;">
        <table>
            <thead>
                <tr><th>No</th><th>Nomor Sertifikat</th><th>Mahasiswa</th><th>Kegiatan</th><th>Tingkat</th><th>Juara</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                <?php $no=1; while($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?php echo $no++; ?></td>
                    <td><span class="badge">✅ <?php echo $row['nomor_sertifikat']; ?></span></td>
                    <td><?php echo $row['nama_mahasiswa']; ?><br><small>(<?php echo $row['nim']; ?>)</small></td>
                    <td><?php echo $row['nama_kegiatan']; ?></td>
                    <td><?php echo $row['tingkat']; ?></td>
                    <td><?php echo $row['juara']; ?></td>
                    <td><a href="?hapus=<?php echo $row['id']; ?>" class="btn-hapus" onclick="return confirm('Yakin hapus?')">Hapus</a></td>
                </tr>
                <?php endwhile; ?>
                <?php if(mysqli_num_rows($result) == 0): ?>
                <tr><td colspan="7" style="text-align:center">Belum ada data sertifikat asli</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>