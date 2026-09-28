<?php
$hasil = '';
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
        
        // Ambil JSON dari output
        preg_match('/\{[^{}]*"success":(true|false),[^{}]*\}/', $output, $matches);
        if (isset($matches[0])) {
            $result = json_decode($matches[0], true);
            if ($result && $result['success']) {
                $hasil = $result['text'];
            } else {
                $error = $result['error'] ?? 'Gagal ekstrak teks';
            }
        } else {
            $error = 'Tidak bisa membaca output OCR';
        }
    } else {
        $error = 'Gagal upload file';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>OCR Sertifikat</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f0f2f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 25px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h2 { color: #1a73e8; margin-bottom: 20px; }
        .info { background: #e8f0fe; padding: 12px; border-radius: 8px; margin-bottom: 20px; }
        .success { background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 8px; color: #155724; }
        .error { background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 8px; color: #721c24; }
        pre { white-space: pre-wrap; background: #f8f9fa; padding: 15px; border-radius: 8px; margin-top: 10px; }
        button { background: #1a73e8; color: white; border: none; padding: 10px 25px; border-radius: 5px; cursor: pointer; font-size: 14px; }
        button:hover { background: #1557b0; }
    </style>
</head>
<body>
    <div class="container">
        <h2>📄 OCR Verifikasi Sertifikat</h2>
        
        <div class="info">
            <strong>Petunjuk:</strong> Upload file PDF sertifikat, sistem akan membaca teksnya secara otomatis.
        </div>
        
        <form method="POST" enctype="multipart/form-data">
            <input type="file" name="pdf" accept=".pdf" required style="margin-bottom: 15px; display: block;">
            <button type="submit">Proses OCR</button>
        </form>
        
        <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
            <hr style="margin: 20px 0;">
            <?php if ($hasil): ?>
                <div class="success">
                    <strong>✅ Hasil OCR:</strong>
                    <pre><?php echo htmlspecialchars($hasil); ?></pre>
                </div>
            <?php else: ?>
                <div class="error">
                    <strong>❌ Error:</strong><br>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>