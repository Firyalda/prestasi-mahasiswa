<?php
// verifikasi_sertifikat.php

session_start();

// Hanya admin yang bisa akses
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: admin/login.php");
    exit();
}

require_once 'config/database.php';
require_once 'includes/certificate_validator.php';

$validator = new CertificateValidator($conn);
$result = null;
$certificateNumber = '';

// Proses QR Code
if (isset($_GET['nomor']) && !empty($_GET['nomor'])) {
    $certificateNumber = $_GET['nomor'];
    $qrResult = $validator->verifyViaQRCode($certificateNumber);
    if ($qrResult['verified']) {
        $result = [
            'verified' => true,
            'method_used' => 'QR Code',
            'score' => 100,
            'data' => $qrResult['data'],
            'warnings' => [],
            'recommendation' => 'LOLOS'
        ];
    } else {
        $result = [
            'verified' => false,
            'method_used' => 'QR Code',
            'score' => 0,
            'data' => null,
            'warnings' => ['Nomor sertifikat tidak ditemukan dalam database'],
            'recommendation' => 'TIDAK DITEMUKAN'
        ];
    }
}

// Proses Upload AI OCR
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['certificate_image'])) {
    $file = $_FILES['certificate_image'];
    $uploadPath = 'uploads/temp/' . time() . '_' . basename($file['name']);
    
    if (!file_exists('uploads/temp')) {
        mkdir('uploads/temp', 0777, true);
    }
    
    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        $aiResult = $validator->validateCertificate($uploadPath, 0);
        @unlink($uploadPath);
        $result = [
            'verified' => $aiResult['verified'],
            'method_used' => 'AI OCR',
            'score' => $aiResult['score'],
            'data' => $aiResult['data'],
            'warnings' => $aiResult['warnings'],
            'recommendation' => $aiResult['recommendation']
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Keaslian Sertifikat - Admin</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Segoe UI', Arial, sans-serif; 
            background: linear-gradient(135deg, #8B5A2B 0%, #6B1A1A 100%); 
            min-height: 100vh; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            padding: 20px; 
        }
        .container { max-width: 600px; width: 100%; }
        .card { 
            background: white; 
            border-radius: 20px; 
            padding: 30px; 
            text-align: center; 
            box-shadow: 0 20px 60px rgba(0,0,0,0.3); 
        }
        .logo { font-size: 50px; margin-bottom: 15px; }
        h1 { color: #6B1A1A; font-size: 22px; margin-bottom: 10px; }
        .subtitle { color: #666; font-size: 13px; margin-bottom: 25px; }
        .admin-badge { background: #6B1A1A; color: white; padding: 5px 15px; border-radius: 20px; font-size: 12px; display: inline-block; margin-bottom: 15px; }
        
        .method-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            border-bottom: 2px solid #eee;
        }
        .tab-btn {
            padding: 10px 20px;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 14px;
            color: #666;
        }
        .tab-btn.active {
            color: #6B1A1A;
            border-bottom: 2px solid #6B1A1A;
        }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        
        .result-asli { 
            background: #d4edda; 
            border: 2px solid #28a745; 
            border-radius: 12px; 
            padding: 20px; 
            margin-top: 20px; 
        }
        .result-palsu { 
            background: #f8d7da; 
            border: 2px solid #dc3545; 
            border-radius: 12px; 
            padding: 20px; 
            margin-top: 20px; 
        }
        .result-warning { 
            background: #fff3cd; 
            border: 2px solid #ffc107; 
            border-radius: 12px; 
            padding: 20px; 
            margin-top: 20px; 
        }
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        .badge-qr { background: #28a745; color: white; }
        .badge-ai { background: #17a2b8; color: white; }
        
        .info { text-align: left; margin-top: 15px; border-top: 1px solid #ddd; padding-top: 15px; }
        .info p { margin: 8px 0; font-size: 14px; }
        .info strong { display: inline-block; width: 120px; }
        
        .score-bar {
            background: #e0e0e0;
            border-radius: 10px;
            height: 20px;
            margin: 10px 0;
        }
        .score-fill {
            border-radius: 10px;
            height: 20px;
            width: 0%;
            transition: width 0.5s ease;
        }
        
        input[type="text"], input[type="file"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 14px;
            margin: 10px 0;
        }
        button {
            background: #8B5A2B;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            cursor: pointer;
            width: 100%;
            font-size: 14px;
        }
        button:hover { background: #6B1A1A; }
        .back-link { display: inline-block; margin-top: 20px; color: #8B5A2B; text-decoration: none; }
    </style>
</head>
<body>
<div class="container">
    <div class="card">
        <div class="logo">🔍</div>
        <h1>VERIFIKASI KEASLIAN SERTIFIKAT</h1>
        <p class="subtitle">Sistem Verifikasi Hybrid (QR Code + AI OCR)</p>
        <div class="admin-badge">🔒 HANYA UNTUK ADMIN</div>
        
        <div class="method-tabs">
            <button class="tab-btn active" onclick="showTab('qr')">📱 Scan QR Code</button>
            <button class="tab-btn" onclick="showTab('upload')">📤 Upload Sertifikat</button>
        </div>
        
        <div id="tab-qr" class="tab-content active">
            <form method="GET" action="">
                <input type="text" name="nomor" placeholder="Masukkan Nomor Sertifikat" 
                       value="<?php echo htmlspecialchars($certificateNumber); ?>">
                <button type="submit">🔍 VERIFIKASI</button>
            </form>
            <p style="font-size: 12px; color: #666; margin-top: 10px;">📱 Scan QR Code pada sertifikat, atau masukkan nomor sertifikat</p>
        </div>
        
        <div id="tab-upload" class="tab-content">
            <form method="POST" enctype="multipart/form-data">
                <input type="file" name="certificate_image" accept="image/*" required>
                <button type="submit">🤖 VERIFIKASI DENGAN AI OCR</button>
            </form>
            <p style="font-size: 12px; color: #666; margin-top: 10px;">🤖 AI akan menganalisis sertifikat dan memberikan skor keaslian</p>
        </div>
        
        <?php if ($result): ?>
            <div class="result-<?php 
                echo $result['verified'] ? 'asli' : ($result['score'] >= 40 ? 'warning' : 'palsu'); 
            ?>">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
                    <div style="font-size: 18px; font-weight: bold;">
                        <?php if ($result['verified']): ?>
                            ✅ SERTIFIKAT ASLI
                        <?php elseif ($result['score'] >= 40): ?>
                            ⚠️ SERTIFIKAT MERAGUKAN
                        <?php else: ?>
                            ❌ SERTIFIKAT TIDAK TERVERIFIKASI
                        <?php endif; ?>
                    </div>
                    <span class="badge <?php echo $result['method_used'] == 'QR Code' ? 'badge-qr' : 'badge-ai'; ?>">
                        <?php echo $result['method_used']; ?>
                    </span>
                </div>
                
                <?php if ($result['method_used'] == 'AI OCR'): ?>
                    <div class="score-bar">
                        <div class="score-fill" style="width: <?php echo $result['score']; ?>%; 
                             background: <?php echo $result['score'] >= 70 ? '#28a745' : ($result['score'] >= 40 ? '#ffc107' : '#dc3545'); ?>;">
                        </div>
                    </div>
                    <p><strong>Skor Keaslian:</strong> <?php echo $result['score']; ?>%</p>
                <?php endif; ?>
                
                <div class="info">
                    <?php if ($result['method_used'] == 'QR Code' && !empty($result['data'])): ?>
                        <p><strong>Nomor Sertifikat</strong> : <?php echo $result['data']['certificate_number']; ?></p>
                        <p><strong>Atas Nama</strong> : <?php echo htmlspecialchars($result['data']['student_name']); ?></p>
                        <p><strong>NIM</strong> : <?php echo $result['data']['student_nim']; ?></p>
                        <p><strong>Prestasi</strong> : <?php echo htmlspecialchars($result['data']['achievement_title']); ?></p>
                        <p><strong>Tingkat</strong> : <?php echo $result['data']['level']; ?></p>
                        <p><strong>Juara</strong> : <?php echo $result['data']['rank']; ?></p>
                    <?php elseif ($result['method_used'] == 'AI OCR' && !empty($result['data'])): ?>
                        <p><strong>Nama Terdeteksi</strong> : <?php echo htmlspecialchars($result['data']['nama'] ?? 'Tidak terdeteksi'); ?></p>
                        <p><strong>Juara Terdeteksi</strong> : <?php echo htmlspecialchars($result['data']['juara'] ?? 'Tidak terdeteksi'); ?></p>
                        <p><strong>Tingkat Terdeteksi</strong> : <?php echo htmlspecialchars($result['data']['tingkat'] ?? 'Tidak terdeteksi'); ?></p>
                    <?php else: ?>
                        <p>Tidak ada data yang berhasil diekstrak</p>
                    <?php endif; ?>
                </div>
                
                <?php if (!empty($result['warnings'])): ?>
                    <div style="margin-top: 15px; padding: 10px; background: rgba(0,0,0,0.05); border-radius: 8px; text-align: left;">
                        <strong>⚠️ Catatan:</strong>
                        <ul style="margin-left: 20px; margin-top: 5px;">
                            <?php foreach($result['warnings'] as $warning): ?>
                                <li><?php echo $warning; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <p style="margin-top: 15px;">
                    <strong>Rekomendasi:</strong> 
                    <span style="color: <?php 
                        echo $result['recommendation'] == 'LOLOS' ? '#28a745' : 
                            ($result['recommendation'] == 'PERLU DICEK MANUAL' ? '#ffc107' : '#dc3545'); 
                    ?>;">
                        <?php echo $result['recommendation']; ?>
                    </span>
                </p>
            </div>
        <?php endif; ?>
        
        <a href="admin/dashboard.php" class="back-link">← Kembali ke Dashboard Admin</a>
    </div>
</div>

<script>
function showTab(tab) {
    document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
    document.getElementById('tab-' + tab).classList.add('active');
    event.target.classList.add('active');
}
</script>
</body>
</html>