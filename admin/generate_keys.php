<?php
// generate_keys.php - Alternatif menggunakan command line
echo "<h2>🔐 Generate Kunci Digital untuk Verifikasi Sertifikat</h2>";

// Buat folder keys
if (!file_exists('../keys')) {
    mkdir('../keys', 0777, true);
    echo "✅ Folder keys berhasil dibuat<br>";
}

$private_key_file = '../keys/private.key';
$public_key_file = '../keys/public.key';

// Cek apakah OpenSSL command line tersedia
$openssl_path = 'openssl'; // default
$test = shell_exec('openssl version 2>&1');

if (strpos($test, 'OpenSSL') !== false) {
    echo "✅ OpenSSL command line tersedia: " . trim($test) . "<br>";
    
    // Generate private key
    $cmd1 = 'openssl genrsa -out ' . $private_key_file . ' 2048 2>&1';
    exec($cmd1, $output1, $return1);
    
    if ($return1 === 0 && file_exists($private_key_file)) {
        echo "✅ Private key berhasil dibuat<br>";
        
        // Generate public key dari private key
        $cmd2 = 'openssl rsa -in ' . $private_key_file . ' -pubout -out ' . $public_key_file . ' 2>&1';
        exec($cmd2, $output2, $return2);
        
        if ($return2 === 0 && file_exists($public_key_file)) {
            echo "✅ Public key berhasil dibuat<br>";
            echo "<h3 style='color:green;'>✅ Kunci Digital Berhasil Dibuat!</h3>";
            
            // Tampilkan isi file
            echo "<h3>📁 File yang dibuat:</h3>";
            echo "<ul>";
            echo "<li><strong>Private Key (RAHASIA):</strong> keys/private.key - <span style='color:red;'>JANGAN DISEBARKAN!</span></li>";
            echo "<li><strong>Public Key (Publik):</strong> keys/public.key - bisa dibagikan</li>";
            echo "</ul>";
        } else {
            echo "❌ Gagal membuat public key<br>";
            echo "Error: " . implode("<br>", $output2) . "<br>";
        }
    } else {
        echo "❌ Gagal membuat private key<br>";
        echo "Error: " . implode("<br>", $output1) . "<br>";
    }
} else {
    echo "❌ OpenSSL command line tidak tersedia<br>";
    
    // Alternatif: buat manual dengan PHP (fallback)
    echo "<h3>Mencoba metode alternatif...</h3>";
    
    // Cek apakah ekstensi OpenSSL bisa digunakan
    if (extension_loaded('openssl')) {
        echo "✅ Ekstensi OpenSSL tersedia, mencoba generate...<br>";
        
        $config = array(
            "private_key_bits" => 2048,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        );
        
        $resource = openssl_pkey_new($config);
        
        if ($resource) {
            openssl_pkey_export($resource, $private_key);
            file_put_contents($private_key_file, $private_key);
            echo "✅ Private key berhasil dibuat via PHP<br>";
            
            $public_key = openssl_pkey_get_details($resource);
            file_put_contents($public_key_file, $public_key['key']);
            echo "✅ Public key berhasil dibuat via PHP<br>";
            echo "<h3 style='color:green;'>✅ Kunci Digital Berhasil Dibuat!</h3>";
        } else {
            echo "❌ Gagal: " . openssl_error_string() . "<br>";
        }
    } else {
        echo "❌ Ekstensi OpenSSL juga tidak tersedia<br>";
    }
}

echo "<hr>";
echo "<p><a href='verifikasi.php' style='background:#2c7da0; color:white; padding:8px 16px; text-decoration:none; border-radius:8px;'>→ Lanjut ke Verifikasi</a></p>";
?>