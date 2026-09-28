<?php
echo "<h2>Test Digital Signature</h2>";

// Path ke file keys
$key_path = __DIR__ . '/../keys/private.key';
echo "Path private key: " . $key_path . "<br>";

if (file_exists($key_path)) {
    echo "✅ File private.key DITEMUKAN<br>";
    
    // Baca isi file
    $private_key = file_get_contents($key_path);
    echo "Panjang isi private key: " . strlen($private_key) . " bytes<br>";
    
    // Buat file test PDF
    $test_file = "test.pdf";
    file_put_contents($test_file, "Ini adalah file test untuk signature");
    
    // Coba buat signature
    $certificate_content = file_get_contents($test_file);
    $result = openssl_sign($certificate_content, $signature, $private_key, OPENSSL_ALGO_SHA256);
    
    if ($result) {
        echo "✅ Digital signature BERHASIL dibuat!<br>";
        echo "Signature: " . base64_encode($signature) . "<br>";
    } else {
        echo "❌ Digital signature GAGAL dibuat<br>";
        echo "Error: " . openssl_error_string() . "<br>";
    }
    
    // Hapus file test
    unlink($test_file);
    
} else {
    echo "❌ File private.key TIDAK DITEMUKAN<br>";
    echo "Cek apakah folder keys ada di: " . __DIR__ . "/../keys/<br>";
    
    // Cek folder keys
    if (is_dir(__DIR__ . "/../keys")) {
        echo "Folder keys DITEMUKAN, tapi file private.key tidak ada<br>";
        $files = scandir(__DIR__ . "/../keys");
        echo "Isi folder keys: " . implode(", ", $files);
    } else {
        echo "Folder keys TIDAK DITEMUKAN<br>";
    }
}
?>