<?php
echo "Mencari DOMPDF...<br>";

$paths = [
    __DIR__ . '/../vendor/autoload.php',
    __DIR__ . '/../vendor/dompdf/autoload.inc.php',
    __DIR__ . '/../vendor/dompdf/dompdf/autoload.inc.php',
    __DIR__ . '/../lib/dompdf/autoload.inc.php',
    __DIR__ . '/../dompdf/autoload.inc.php'
];

foreach ($paths as $path) {
    echo "Cek: " . $path . " → " . (file_exists($path) ? '✅ FOUND' : '❌ NOT FOUND') . "<br>";
}
?>