<?php
session_start();
include_once '../includes/functions.php';
include_once '../config/database.php';

// Cek login admin
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

$response = ['success' => false, 'message' => '', 'text' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['sertifikat'])) {
    $uploadDir = '../uploads/sertifikat/';
    
    // Buat folder jika belum ada
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $fileName = time() . '_' . basename($_FILES['sertifikat']['name']);
    $uploadPath = $uploadDir . $fileName;
    
    // Upload file
    if (move_uploaded_file($_FILES['sertifikat']['tmp_name'], $uploadPath)) {
        // Ekstrak teks dengan OCR
        $extractedText = extractTextFromPDF($uploadPath);
        
        if ($extractedText) {
            $response['success'] = true;
            $response['message'] = 'OCR berhasil!';
            $response['text'] = nl2br(htmlspecialchars($extractedText));
            $response['file_path'] = $uploadPath;
            
            // Simpan log
            saveOCRLog($uploadPath, $extractedText, 'SUCCESS');
        } else {
            $response['message'] = 'Gagal mengekstrak teks dari sertifikat';
            saveOCRLog($uploadPath, '', 'FAILED');
        }
    } else {
        $response['message'] = 'Gagal upload file';
    }
}

// Tampilkan response sebagai JSON (untuk AJAX) atau HTML
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    header('Content-Type: application/json');
    echo json_encode($response);
} else {
    // Tampilkan halaman HTML
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>OCR Verifikasi Sertifikat</title>
        <style>
            body { font-family: Arial; padding: 20px; }
            .container { max-width: 800px; margin: auto; }
            .result { margin-top: 20px; padding: 15px; border: 1px solid #ccc; border-radius: 5px; }
            .success { background: #d4edda; border-color: #c3e6cb; }
            .error { background: #f8d7da; border-color: #f5c6cb; }
            .text-result { background: #f8f9fa; padding: 15px; border-radius: 5px; margin-top: 10px; white-space: pre-wrap; }
        </style>
    </head>
    <body>
        <div class="container">
            <h2>OCR Verifikasi Sertifikat</h2>
            <form method="POST" enctype="multipart/form-data">
                <div>
                    <label>Upload Sertifikat (PDF):</label>
                    <input type="file" name="sertifikat" accept=".pdf" required>
                </div>
                <div style="margin-top: 10px;">
                    <button type="submit">Proses OCR</button>
                </div>
            </form>
            
            <?php if ($response['success']): ?>
            <div class="result success">
                <h3>✅ <?php echo $response['message']; ?></h3>
                <p><strong>File:</strong> <?php echo basename($response['file_path']); ?></p>
                <p><strong>Hasil OCR:</strong></p>
                <div class="text-result"><?php echo $response['text']; ?></div>
            </div>
            <?php elseif ($response['message']): ?>
            <div class="result error">
                <h3>❌ <?php echo $response['message']; ?></h3>
            </div>
            <?php endif; ?>
        </div>
    </body>
    </html>
    <?php
}
?>