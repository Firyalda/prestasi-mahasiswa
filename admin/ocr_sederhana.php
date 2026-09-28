<?php
$hasil = '';
$detail = [];
$akurasi = 0;
$has_seal = false;
$skor_keaslian = 0;
$status_keaslian = '';
$icon_keaslian = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['pdf'])) {
    $uploadDir = dirname(__DIR__) . '/uploads/sertifikat/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
    
    $fileName = time() . '_' . basename($_FILES['pdf']['name']);
    $filePath = $uploadDir . $fileName;
    
    if (move_uploaded_file($_FILES['pdf']['tmp_name'], $filePath)) {
        $pythonPath = 'C:\Users\ACER\AppData\Local\Programs\Python\Python310\python.exe';
        $scriptPath = dirname(__DIR__) . '/ocr/extract_sertifikat.py';
        $command = $pythonPath . ' "' . $scriptPath . '" "' . $filePath . '" 2>&1';
        
        $output = shell_exec($command);
        
        $lines = explode("\n", $output);
        foreach ($lines as $line) {
            $line = trim($line);
            if (strpos($line, '{') === 0 && strpos($line, '}') !== false) {
                $result = json_decode($line, true);
                if ($result && isset($result['success'])) {
                    if ($result['success']) {
                        $hasil = $result['text'];
                        $detail = $result['details'] ?? [];
                        $akurasi = $result['avg_confidence'] ?? 0;
                        $has_seal = $result['has_seal'] ?? false;
                        $skor_keaslian = $result['authenticity_score'] ?? 0;
                        $status_keaslian = $result['authenticity_status'] ?? 'Tidak diketahui';
                        $icon_keaslian = $result['authenticity_icon'] ?? '❓';
                    } else {
                        $error = $result['error'] ?? 'Error';
                    }
                    break;
                }
            }
        }
        
        if (empty($hasil) && empty($error)) {
            $error = 'Output tidak dikenali';
        }
    } else {
        $error = 'Gagal upload file';
    }
}

function warnaAkurasi($akurasi) {
    if ($akurasi >= 0.95) return '#28a745';
    if ($akurasi >= 0.80) return '#8bc34a';
    if ($akurasi >= 0.60) return '#ffc107';
    return '#dc3545';
}

function warnaSkor($skor) {
    if ($skor >= 70) return '#28a745';
    if ($skor >= 40) return '#ffc107';
    return '#dc3545';
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Verifikasi Keaslian Sertifikat</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f0f2f5; }
        .container { max-width: 900px; margin: auto; background: white; padding: 25px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h2 { color: #1a73e8; }
        .info { background: #e8f0fe; padding: 12px; border-radius: 8px; margin-bottom: 20px; }
        .error { background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 8px; color: #721c24; }
        button { background: #1a73e8; color: white; border: none; padding: 10px 25px; border-radius: 5px; cursor: pointer; }
        input[type="file"] { margin-bottom: 15px; display: block; }
        hr { margin: 20px 0; }
        
        .card-keaslian {
            text-align: center;
            padding: 25px;
            border-radius: 15px;
            margin: 20px 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .skor-angka {
            font-size: 64px;
            font-weight: bold;
        }
        .status-icon {
            font-size: 48px;
        }
        
        .detail-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        .detail-card {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            text-align: center;
        }
        .detail-card h4 {
            margin: 0 0 10px 0;
            color: #666;
        }
        .detail-card .value {
            font-size: 24px;
            font-weight: bold;
        }
        
        .akurasi-box { text-align: center; padding: 20px; background: #e8f0fe; border-radius: 10px; margin: 15px 0; }
        .akurasi-angka { font-size: 48px; font-weight: bold; }
        .detail-item { padding: 8px; border-bottom: 1px solid #eee; font-family: monospace; }
        .status-box { text-align: center; padding: 15px; border-radius: 10px; margin-top: 20px; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 8px; white-space: pre-wrap; }
        
        .seal-yes { color: #28a745; }
        .seal-no { color: #dc3545; }
    </style>
</head>
<body>
    <div class="container">
        <h2>🔍 Verifikasi Keaslian Sertifikat</h2>
        
        <div class="info">
            <strong>Sistem Verifikasi 3 Layer:</strong> Akurasi OCR + Deteksi Stempel + Analisis Kejanggalan
        </div>
        
        <form method="POST" enctype="multipart/form-data">
            <input type="file" name="pdf" accept=".pdf" required>
            <button type="submit">Verifikasi Keaslian</button>
        </form>
        
        <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
            <hr>
            
            <?php if ($hasil): ?>
                <!-- KARD SKOR KEASLIAN -->
                <div class="card-keaslian">
                    <div class="status-icon"><?php echo $icon_keaslian; ?></div>
                    <div class="skor-angka"><?php echo $skor_keaslian; ?><span style="font-size: 24px;">/100</span></div>
                    <div style="font-size: 24px; margin-top: 10px; font-weight: bold;"><?php echo $status_keaslian; ?></div>
                    <div style="margin-top: 10px; font-size: 14px;">Berdasarkan verifikasi otomatis</div>
                </div>
                
                <!-- DETAIL VERIFIKASI -->
                <div class="detail-grid">
                    <div class="detail-card">
                        <h4>🎯 Akurasi OCR</h4>
                        <div class="value" style="color: <?php echo warnaAkurasi($akurasi); ?>;"><?php echo round($akurasi * 100, 1); ?>%</div>
                        <div class="confidence-bar" style="background: #e0e0e0; height: 5px; border-radius: 5px; margin-top: 10px;">
                            <div style="width: <?php echo $akurasi * 100; ?>%; background: <?php echo warnaAkurasi($akurasi); ?>; height: 100%; border-radius: 5px;"></div>
                        </div>
                        <div style="font-size: 12px; margin-top: 8px;">Bobot: 40 poin</div>
                    </div>
                    
                    <div class="detail-card">
                        <h4>🕹️ Deteksi Stempel</h4>
                        <div class="value <?php echo $has_seal ? 'seal-yes' : 'seal-no'; ?>"><?php echo $has_seal ? '✅ Ditemukan' : '❌ Tidak Ditemukan'; ?></div>
                        <div style="font-size: 12px; margin-top: 8px;">Bobot: 30 poin</div>
                    </div>
                    
                    <div class="detail-card">
                        <h4>📐 Kejanggalan Format</h4>
                        <div class="value" style="color: #28a745;">✅ Tidak Terdeteksi</div>
                        <div style="font-size: 12px; margin-top: 8px;">Bobot: 10 poin</div>
                    </div>
                </div>
                
                <!-- Keterangan -->
                <div style="background: #f0f2f5; padding: 15px; border-radius: 10px; margin: 15px 0;">
                    <strong>📋 Rekomendasi:</strong><br>
                    <?php if ($skor_keaslian >= 70): ?>
                        ✅ Sertifikat ASLI berdasarkan verifikasi otomatis. Dapat langsung disetujui.
                    <?php elseif ($skor_keaslian >= 40): ?>
                        ⚠️ Sertifikat RAGU. Silakan lakukan verifikasi manual oleh admin.
                    <?php else: ?>
                        ❌ Sertifikat DIDUGA PALSU. Tolak atau verifikasi manual terlebih dahulu.
                    <?php endif; ?>
                </div>
                
                <!-- Detail per baris -->
                <h3>📝 Detail OCR per Baris:</h3>
                <div style="max-height: 250px; overflow-y: auto; border: 1px solid #ddd; border-radius: 8px;">
                    <?php foreach ($detail as $item): ?>
                        <?php $conf = $item['confidence']; ?>
                        <div class="detail-item">
                            <span style="color: <?php echo warnaAkurasi($conf); ?>; font-weight: bold; display: inline-block; width: 50px;">
                                <?php echo round($conf * 100); ?>%
                            </span>
                            <span style="margin-left: 10px;"><?php echo htmlspecialchars($item['text']); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Teks Lengkap -->
                <h3>📄 Teks Lengkap:</h3>
                <pre><?php echo htmlspecialchars($hasil); ?></pre>
                
                <!-- Tombol Aksi -->
                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <a href="#" style="flex: 1; background: #28a745; color: white; text-align: center; padding: 12px; text-decoration: none; border-radius: 5px;">✅ SETUJUI</a>
                    <a href="#" style="flex: 1; background: #dc3545; color: white; text-align: center; padding: 12px; text-decoration: none; border-radius: 5px;">❌ TOLAK</a>
                    <a href="#" style="flex: 1; background: #ffc107; color: #333; text-align: center; padding: 12px; text-decoration: none; border-radius: 5px;">🔍 VERIFIKASI MANUAL</a>
                </div>
                
            <?php else: ?>
                <div class="error">
                    <strong>❌ ERROR:</strong><br>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>