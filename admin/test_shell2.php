<?php
echo "<h2>Test Shell Exec</h2>";

// Test 1: Perintah sederhana
$test1 = shell_exec('echo Hello');
echo "<b>Test 1 (echo):</b> <pre>" . htmlspecialchars($test1) . "</pre>";

// Test 2: Panggil Python langsung
$test2 = shell_exec('py -3.10 -c "print(\'Hello from Python\')" 2>&1');
echo "<b>Test 2 (Python):</b> <pre>" . htmlspecialchars($test2) . "</pre>";

// Test 3: Panggil script Python kita dengan file PDF yang sudah ada
$pythonScript = dirname(__DIR__) . '/ocr/extract_sertifikat.py';
$pdfPath = dirname(__DIR__) . '/uploads/sertifikat/1780245902_sertifikat.pdf';
$command = 'py -3.10 "' . $pythonScript . '" "' . $pdfPath . '" 2>&1';
$test3 = shell_exec($command);
echo "<b>Test 3 (OCR Script):</b> <pre>" . htmlspecialchars($test3) . "</pre>";

// Test 4: Tampilkan command yang dijalankan
echo "<b>Command yang dijalankan:</b><br>";
echo "<code>" . htmlspecialchars($command) . "</code><br>";

// Test 5: Cek apakah file Python script ada
if (file_exists($pythonScript)) {
    echo "<b>✅ File Python script ditemukan di:</b> " . $pythonScript . "<br>";
} else {
    echo "<b>❌ File Python script TIDAK ditemukan di:</b> " . $pythonScript . "<br>";
}

// Test 6: Cek apakah file PDF ada
if (file_exists($pdfPath)) {
    echo "<b>✅ File PDF ditemukan di:</b> " . $pdfPath . "<br>";
} else {
    echo "<b>❌ File PDF TIDAK ditemukan di:</b> " . $pdfPath . "<br>";
}

// Test 7: Cek fungsi shell_exec aktif atau tidak
if (function_exists('shell_exec')) {
    echo "<b>✅ Fungsi shell_exec tersedia</b><br>";
} else {
    echo "<b>❌ Fungsi shell_exec TIDAK tersedia (diblokir oleh hosting)</b><br>";
}
?>