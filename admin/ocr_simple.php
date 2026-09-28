<?php
$result = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['pdf'])) {
    $uploadDir = dirname(__DIR__) . '/uploads/sertifikat/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
    
    $fileName = time() . '_' . basename($_FILES['pdf']['name']);
    $filePath = $uploadDir . $fileName;
    
    if (move_uploaded_file($_FILES['pdf']['tmp_name'], $filePath)) {
        // Path lengkap Python
        $python = 'C:\Users\ACER\AppData\Local\Programs\Python\Python310\python.exe';
        $script = dirname(__DIR__) . '/ocr/extract_sertifikat.py';
        $command = $python . ' ' . $script . ' ' . $filePath . ' 2>&1';
        
        $output = shell_exec($command);
        $result = json_decode($output, true);
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>OCR Simple Test</title>
</head>
<body>
    <h2>Upload PDF untuk OCR</h2>
    <form method="POST" enctype="multipart/form-data">
        <input type="file" name="pdf" accept=".pdf" required>
        <button type="submit">OCR</button>
    </form>
    
    <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
        <hr>
        <h3>Hasil:</h3>
        <?php if ($result && $result['success']): ?>
            <pre style="background: #d4edda; padding: 10px;"><?php echo htmlspecialchars($result['text']); ?></pre>
        <?php else: ?>
            <pre style="background: #f8d7da; padding: 10px;">GAGAL: <?php echo htmlspecialchars($output ?? 'Tidak ada output'); ?></pre>
        <?php endif; ?>
        
        <hr>
        <h4>Debug Info:</h4>
        <pre>
Command: <?php echo htmlspecialchars($command ?? 'N/A'); ?>
Output: <?php echo htmlspecialchars($output ?? 'N/A'); ?>
        </pre>
    <?php endif; ?>
</body>
</html>