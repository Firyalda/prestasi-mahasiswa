<?php
// Test lengkap dengan debug

$result = '';
$debug = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['pdf'])) {
    $uploadDir = dirname(__DIR__) . '/uploads/sertifikat/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
    
    $fileName = time() . '_' . basename($_FILES['pdf']['name']);
    $filePath = $uploadDir . $fileName;
    
    $debug .= "1. Upload file...\n";
    
    if (move_uploaded_file($_FILES['pdf']['tmp_name'], $filePath)) {
        $debug .= "2. File berhasil diupload ke: " . $filePath . "\n";
        
        // Path lengkap Python
        $pythonPath = 'C:\Users\ACER\AppData\Local\Programs\Python\Python310\python.exe';
        $scriptPath = dirname(__DIR__) . '/ocr/extract_sertifikat.py';
        
        $debug .= "3. Python path: " . $pythonPath . "\n";
        $debug .= "4. Script path: " . $scriptPath . "\n";
        
        // Cek apakah file script ada
        if (file_exists($scriptPath)) {
            $debug .= "5. Script file DITEMUKAN\n";
        } else {
            $debug .= "5. Script file TIDAK DITEMUKAN!\n";
        }
        
        // Cek apakah file PDF ada
        if (file_exists($filePath)) {
            $debug .= "6. PDF file DITEMUKAN\n";
        } else {
            $debug .= "6. PDF file TIDAK DITEMUKAN!\n";
        }
        
        // Jalankan command
        $command = $pythonPath . ' "' . $scriptPath . '" "' . $filePath . '" 2>&1';
        $debug .= "7. Command: " . $command . "\n";
        
        $output = shell_exec($command);
        $debug .= "8. Output: " . $output . "\n";
        
        $result = json_decode($output, true);
        
        if ($result && isset($result['success']) && $result['success']) {
            $debug .= "9. SUKSES! Teks berhasil diekstrak\n";
        } else {
            $debug .= "9. GAGAL! " . ($result['error'] ?? 'Unknown error') . "\n";
        }
    } else {
        $debug .= "2. GAGAL upload file!\n";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>OCR Debug</title>
    <style>
        body { font-family: monospace; padding: 20px; }
        .container { max-width: 900px; margin: 0 auto; }
        .success { background: #d4edda; padding: 10px; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; padding: 10px; border: 1px solid #f5c6cb; }
        pre { background: #f4f4f4; padding: 10px; overflow-x: auto; }
        textarea { width: 100%; height: 200px; font-family: monospace; }
    </style>
</head>
<body>
    <div class="container">
        <h2>OCR Debug - Test Lengkap</h2>
        
        <form method="POST" enctype="multipart/form-data">
            <div style="margin-bottom: 10px;">
                <label><strong>Upload PDF Sertifikat:</strong></label><br>
                <input type="file" name="pdf" accept=".pdf" required>
            </div>
            <button type="submit">Test OCR</button>
        </form>
        
        <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
            <hr>
            <h3>Debug Info:</h3>
            <pre><?php echo htmlspecialchars($debug); ?></pre>
            
            <?php if ($result && $result['success']): ?>
                <div class="success">
                    <h3>✅ HASIL OCR:</h3>
                    <pre><?php echo htmlspecialchars($result['text']); ?></pre>
                </div>
            <?php else: ?>
                <div class="error">
                    <h3>❌ OCR GAGAL</h3>
                    <p><?php echo htmlspecialchars($result['error'] ?? 'Unknown error'); ?></p>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>