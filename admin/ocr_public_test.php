<?php
// File: admin/ocr_public_test.php

// Fungsi panggil Python OCR
function extractTextFromPDF($pdfPath) {
    $pythonScript = dirname(__DIR__) . '/ocr/extract_sertifikat.py';
    // PAKAI PATH LENGKAP PYTHON
    $pythonPath = 'C:\Users\ACER\AppData\Local\Programs\Python\Python310\python.exe';
    $command = '"' . $pythonPath . '" "' . $pythonScript . '" "' . $pdfPath . '" 2>&1';
    $output = shell_exec($command);
    $result = json_decode($output, true);
    
    if ($result && isset($result['success']) && $result['success']) {
        return $result['text'];
    }
    return false;
}

$ocrResult = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['sertifikat'])) {
    $uploadDir = dirname(__DIR__) . '/uploads/sertifikat/';
    
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $fileName = time() . '_' . basename($_FILES['sertifikat']['name']);
    $uploadPath = $uploadDir . $fileName;
    
    if (move_uploaded_file($_FILES['sertifikat']['tmp_name'], $uploadPath)) {
        $extractedText = extractTextFromPDF($uploadPath);
        if ($extractedText) {
            $ocrResult = '<div class="result success"><strong>✅ Hasil OCR:</strong><pre>' . htmlspecialchars($extractedText) . '</pre></div>';
        } else {
            $errorMessage = 'Gagal mengekstrak teks. Cek file PDF atau script Python.';
        }
    } else {
        $errorMessage = 'Gagal mengupload file. Periksa folder uploads/sertifikat.';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test OCR Sertifikat</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f0f0f0; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h2 { color: #333; margin-bottom: 20px; }
        form { margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid #ddd; }
        label { display: block; margin-bottom: 8px; font-weight: bold; }
        input[type="file"] { margin-bottom: 15px; display: block; }
        button { background: #007bff; color: white; border: none; padding: 8px 20px; border-radius: 5px; cursor: pointer; font-size: 14px; }
        button:hover { background: #0056b3; }
        .result { margin-top: 20px; padding: 15px; border-radius: 5px; }
        .success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
        .error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
        pre { white-space: pre-wrap; word-wrap: break-word; background: #f8f9fa; padding: 10px; border-radius: 5px; margin: 10px 0 0 0; }
        .info { background: #e2e3e5; padding: 10px; border-radius: 5px; margin-bottom: 20px; font-size: 14px; }
    </style>
</head>
<body>
    <div class="container">
        <h2>🧪 Test OCR Sertifikat</h2>
        
        <?php if ($errorMessage): ?>
            <div class="result error">❌ <?php echo $errorMessage; ?></div>
        <?php endif; ?>
        
        <div class="info">
            <strong>Info:</strong> Pilih file PDF sertifikat, lalu klik Proses OCR.
        </div>
        
        <form method="POST" enctype="multipart/form-data">
            <label for="sertifikat">Upload File Sertifikat (PDF):</label>
            <input type="file" name="sertifikat" id="sertifikat" accept=".pdf" required>
            <button type="submit">Proses OCR</button>
        </form>
        
        <?php echo $ocrResult; ?>
        
        <?php if ($ocrResult && !$errorMessage): ?>
            <div style="margin-top: 20px; padding: 10px; background: #e9ecef; border-radius: 5px;">
                <strong>✅ Sukses!</strong> Sistem OCR berhasil membaca teks dari sertifikat.
            </div>
        <?php endif; ?>
    </div>
</body>
</html>