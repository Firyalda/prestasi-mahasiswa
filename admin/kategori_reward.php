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

// =========================================================
// PROSES TAMBAH DATA
// =========================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah'])) {
    $tingkat = mysqli_real_escape_string($conn, $_POST['tingkat']);
    $kategori = mysqli_real_escape_string($conn, $_POST['kategori']);
    $raihan = mysqli_real_escape_string($conn, $_POST['raihan']);
    $nominal = (int)$_POST['nominal'];
    
    $query = "INSERT INTO kategori_reward (tingkat, kategori, raihan, nominal) 
              VALUES ('$tingkat', '$kategori', '$raihan', $nominal)";
    mysqli_query($conn, $query);
    echo "<script>window.location.href='kategori_reward.php';</script>";
    exit();
}

// =========================================================
// PROSES EDIT DATA (UPDATE)
// =========================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $id = (int)$_POST['id'];
    $tingkat = mysqli_real_escape_string($conn, $_POST['tingkat']);
    $kategori = mysqli_real_escape_string($conn, $_POST['kategori']);
    $raihan = mysqli_real_escape_string($conn, $_POST['raihan']);
    $nominal = (int)$_POST['nominal'];
    
    $query = "UPDATE kategori_reward SET 
              tingkat = '$tingkat',
              kategori = '$kategori',
              raihan = '$raihan',
              nominal = $nominal
              WHERE id = $id";
    mysqli_query($conn, $query);
    echo "<script>window.location.href='kategori_reward.php';</script>";
    exit();
}

// =========================================================
// PROSES HAPUS DATA
// =========================================================
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    mysqli_query($conn, "DELETE FROM kategori_reward WHERE id = $id");
    echo "<script>window.location.href='kategori_reward.php';</script>";
    exit();
}

// =========================================================
// AMBIL DATA EDIT (jika ada parameter edit)
// =========================================================
$data_edit = null;
if (isset($_GET['edit'])) {
    $id_edit = (int)$_GET['edit'];
    $q_edit = mysqli_query($conn, "SELECT * FROM kategori_reward WHERE id = $id_edit");
    $data_edit = mysqli_fetch_assoc($q_edit);
}

// =========================================================
// AMBIL SEMUA DATA KATEGORI REWARD
// =========================================================
$query = "SELECT * FROM kategori_reward ORDER BY 
          FIELD(tingkat, 'Internasional', 'Nasional', 'Provinsi', 'Lokal'),
          FIELD(kategori, 'Individu', 'Tim'),
          FIELD(raihan, 'Juara 1', 'Juara 2', 'Juara 3', 'Gold Medal', 'Silver Medal', 'Bronze Medal', 'Finalis', 'Juara Harapan/Favorit')";
$result = mysqli_query($conn, $query);

include 'includes/sidebar.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kategori Reward - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', system-ui, sans-serif; background: #f0f4f8; color: #1a1a1a; }

        .main-content { margin-left: 260px; padding: 24px 32px; }

        /* TOP BAR - TANPA LOGOUT & TANPA CUACA */
        .top-bar {
            display: flex; justify-content: space-between; align-items: center;
            background: linear-gradient(135deg, #ffffff 0%, #f0f6ff 100%);
            border-radius: 16px; padding: 16px 24px;
            margin-bottom: 24px; border: 1px solid #dce5f0;
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
        .user-info {
            display: flex; align-items: center; gap: 16px;
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

        /* FORM CARD */
        .form-card {
            background: white; border-radius: 14px; border: 1px solid #dce5f0;
            margin-bottom: 24px; overflow: hidden;
            box-shadow: 0 2px 6px rgba(0,0,0,0.02);
        }
        .form-card.edit-form {
            border-left: 4px solid #2a5298;
        }
        .form-header {
            padding: 12px 20px; border-bottom: 1px solid #eef2f7; 
            background: linear-gradient(135deg, #f8faff, #f0f6ff);
            font-weight: 600; color: #1a365d;
        }
        .form-header.edit-header {
            background: linear-gradient(135deg, #e8f0fe, #dbeafe);
        }
        .form-body { padding: 20px; }
        .form-row {
            display: flex; flex-wrap: wrap; gap: 16px; align-items: flex-end;
        }
        .form-group { flex: 1; min-width: 150px; }
        .form-group label { display: block; font-size: 12px; color: #4a6a8a; margin-bottom: 6px; font-weight: 500; }
        .form-group input, .form-group select {
            width: 100%; padding: 8px 12px; border: 1px solid #dce5f0; border-radius: 8px; font-size: 13px;
            transition: 0.2s;
        }
        .form-group input:focus, .form-group select:focus {
            border-color: #2a5298;
            outline: none;
            box-shadow: 0 0 0 3px rgba(42, 82, 152, 0.1);
        }
        .btn-primary {
            background: linear-gradient(135deg, #2a5298, #1a365d);
            color: white; border: none; padding: 8px 20px; border-radius: 8px; cursor: pointer;
            transition: 0.2s;
        }
        .btn-primary:hover { 
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(42, 82, 152, 0.3);
        }
        .btn-secondary {
            background: #f0f4f8; color: #4a6a8a; border: 1px solid #dce5f0; padding: 8px 20px; border-radius: 8px; cursor: pointer;
            text-decoration: none;
            transition: 0.2s;
        }
        .btn-secondary:hover { background: #dce5f0; }
        .btn-success {
            background: linear-gradient(135deg, #059669, #047857);
            color: white; border: none; padding: 8px 20px; border-radius: 8px; cursor: pointer;
            transition: 0.2s;
        }
        .btn-success:hover { 
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(5, 150, 105, 0.3);
        }

        /* TABLE CARD */
        .table-card {
            background: white; border-radius: 14px; border: 1px solid #dce5f0;
            overflow: hidden;
            box-shadow: 0 2px 6px rgba(0,0,0,0.02);
        }
        .table-header {
            padding: 12px 20px; border-bottom: 1px solid #eef2f7; 
            background: linear-gradient(135deg, #f8faff, #f0f6ff);
            display: flex; justify-content: space-between; align-items: center;
        }
        .table-header h3 { 
            font-size: 15px; font-weight: 600; color: #1a365d; 
        }
        .badge-count {
            background: linear-gradient(135deg, #1a365d, #2a5298);
            padding: 4px 14px; border-radius: 20px; font-size: 12px;
            color: #ffffff;
            font-weight: 600;
        }
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table th {
            text-align: left; padding: 10px 16px; 
            background: linear-gradient(135deg, #1a365d, #2a5298);
            color: #ffffff;
            font-weight: 600; font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .data-table td {
            padding: 8px 16px; border-bottom: 1px solid #eef2f7; font-size: 13px;
            color: #1a365d;
        }
        .data-table tr:hover {
            background: #f8faff;
        }
        .data-table tr:last-child td {
            border-bottom: none;
        }
        .reward-nominal { font-weight: 600; color: #2a5298; }
        .btn-edit, .btn-delete {
            padding: 4px 10px; border-radius: 6px; text-decoration: none; font-size: 11px; display: inline-block; margin: 0 2px;
            transition: 0.2s;
        }
        .btn-edit { 
            background: none; border: 1px solid #dce5f0; color: #4a6a8a; 
        }
        .btn-edit:hover { 
            background: linear-gradient(135deg, #2a5298, #1a365d); 
            color: white; 
            border-color: #2a5298;
            transform: translateY(-1px);
        }
        .btn-delete { 
            background: none; border: 1px solid #dce5f0; color: #dc2626; 
        }
        .btn-delete:hover { 
            background: #fee2e2; 
            border-color: #dc2626;
            transform: translateY(-1px);
        }

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
            <h2>Kategori Reward</h2>
            <p>Atur nominal reward berdasarkan tingkat, kategori, dan raihan</p>
        </div>
        <div class="user-info">
            <div class="user-avatar">A</div>
            <span class="user-name"><?php echo htmlspecialchars($_SESSION['nama'] ?? 'Admin'); ?></span>
            <span class="date-divider"></span>
            <span class="date-text"><?php echo date('l, d F Y'); ?></span>
        </div>
    </div>

    <!-- ========================================================= -->
    <!-- FORM TAMBAH REWARD                                         -->
    <!-- ========================================================= -->
    <div class="form-card">
        <div class="form-header">Tambah Kategori Reward</div>
        <div class="form-body">
            <form method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label>Tingkat</label>
                        <select name="tingkat" required>
                            <option value="Lokal">Lokal</option>
                            <option value="Provinsi">Provinsi</option>
                            <option value="Nasional">Nasional</option>
                            <option value="Internasional">Internasional</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Kategori</label>
                        <select name="kategori" required>
                            <option value="Individu">Individu</option>
                            <option value="Tim">Tim</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Raihan</label>
                        <select name="raihan" required>
                            <option value="Juara 1">Juara 1</option>
                            <option value="Juara 2">Juara 2</option>
                            <option value="Juara 3">Juara 3</option>
                            <option value="Gold Medal">Gold Medal</option>
                            <option value="Silver Medal">Silver Medal</option>
                            <option value="Bronze Medal">Bronze Medal</option>
                            <option value="Finalis">Finalis</option>
                            <option value="Juara Harapan/Favorit">Juara Harapan/Favorit</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Nominal (Rp)</label>
                        <input type="number" name="nominal" placeholder="Contoh: 1000000" required>
                    </div>
                    <div class="form-group">
                        <button type="submit" name="tambah" class="btn-primary"><i class="fas fa-plus"></i> Tambah</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- ========================================================= -->
    <!-- FORM EDIT REWARD (Muncul jika ada parameter edit)          -->
    <!-- ========================================================= -->
    <?php if ($data_edit): ?>
    <div class="form-card edit-form">
        <div class="form-header edit-header">
            <i class="fas fa-edit" style="color: #2a5298;"></i> Edit Kategori Reward
        </div>
        <div class="form-body">
            <form method="POST">
                <input type="hidden" name="id" value="<?php echo $data_edit['id']; ?>">
                <div class="form-row">
                    <div class="form-group">
                        <label>Tingkat</label>
                        <select name="tingkat" required>
                            <option value="Lokal" <?php echo $data_edit['tingkat'] == 'Lokal' ? 'selected' : ''; ?>>Lokal</option>
                            <option value="Provinsi" <?php echo $data_edit['tingkat'] == 'Provinsi' ? 'selected' : ''; ?>>Provinsi</option>
                            <option value="Nasional" <?php echo $data_edit['tingkat'] == 'Nasional' ? 'selected' : ''; ?>>Nasional</option>
                            <option value="Internasional" <?php echo $data_edit['tingkat'] == 'Internasional' ? 'selected' : ''; ?>>Internasional</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Kategori</label>
                        <select name="kategori" required>
                            <option value="Individu" <?php echo $data_edit['kategori'] == 'Individu' ? 'selected' : ''; ?>>Individu</option>
                            <option value="Tim" <?php echo $data_edit['kategori'] == 'Tim' ? 'selected' : ''; ?>>Tim</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Raihan</label>
                        <select name="raihan" required>
                            <option value="Juara 1" <?php echo $data_edit['raihan'] == 'Juara 1' ? 'selected' : ''; ?>>Juara 1</option>
                            <option value="Juara 2" <?php echo $data_edit['raihan'] == 'Juara 2' ? 'selected' : ''; ?>>Juara 2</option>
                            <option value="Juara 3" <?php echo $data_edit['raihan'] == 'Juara 3' ? 'selected' : ''; ?>>Juara 3</option>
                            <option value="Gold Medal" <?php echo $data_edit['raihan'] == 'Gold Medal' ? 'selected' : ''; ?>>Gold Medal</option>
                            <option value="Silver Medal" <?php echo $data_edit['raihan'] == 'Silver Medal' ? 'selected' : ''; ?>>Silver Medal</option>
                            <option value="Bronze Medal" <?php echo $data_edit['raihan'] == 'Bronze Medal' ? 'selected' : ''; ?>>Bronze Medal</option>
                            <option value="Finalis" <?php echo $data_edit['raihan'] == 'Finalis' ? 'selected' : ''; ?>>Finalis</option>
                            <option value="Juara Harapan/Favorit" <?php echo $data_edit['raihan'] == 'Juara Harapan/Favorit' ? 'selected' : ''; ?>>Juara Harapan/Favorit</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Nominal (Rp)</label>
                        <input type="number" name="nominal" value="<?php echo $data_edit['nominal']; ?>" required>
                    </div>
                    <div class="form-group" style="display: flex; gap: 8px; align-items: flex-end;">
                        <button type="submit" name="update" class="btn-success"><i class="fas fa-save"></i> Update</button>
                        <a href="kategori_reward.php" class="btn-secondary"><i class="fas fa-times"></i> Batal</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <!-- ========================================================= -->
    <!-- DAFTAR KATEGORI REWARD                                     -->
    <!-- ========================================================= -->
    <div class="table-card">
        <div class="table-header">
            <h3>Daftar Kategori Reward</h3>
            <div class="badge-count">Total: <?php echo mysqli_num_rows($result); ?> data</div>
        </div>
        <div style="overflow-x: auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tingkat</th>
                        <th>Kategori</th>
                        <th>Raihan</th>
                        <th>Nominal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1; 
                    while ($row = mysqli_fetch_assoc($result)): 
                    ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td><?php echo $row['tingkat']; ?></td>
                        <td><?php echo $row['kategori']; ?></td>
                        <td><?php echo $row['raihan']; ?></td>
                        <td class="reward-nominal">Rp <?php echo number_format($row['nominal'], 0, ',', '.'); ?></td>
                        <td>
                            <!-- LINK EDIT (bawa parameter edit) -->
                            <a href="?edit=<?php echo $row['id']; ?>" class="btn-edit">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <!-- LINK HAPUS (bawa parameter hapus) -->
                            <a href="?hapus=<?php echo $row['id']; ?>"
                               class="btn-delete"
                               onclick="hapusData(event, this.href)">
                                <i class="fas fa-trash"></i> Hapus
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    // SweetAlert2 delete confirmation
    function hapusData(event, href) {
        event.preventDefault();
        Swal.fire({
            title: 'Konfirmasi Hapus',
            text: 'Apakah Anda yakin ingin menghapus data ini? Tindakan ini tidak dapat dibatalkan.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6b7a8f',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = href;
            }
        });
    }
</script>

</body>
</html>