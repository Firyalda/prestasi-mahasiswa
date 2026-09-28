<?php
// Test langsung panggil Python tanpa upload file

echo "<h2>Test Python dari PHP</h2>";

// Path lengkap Python
$pythonPath = 'C:\Users\ACER\AppData\Local\Programs\Python\Python310\python.exe';

// Test 1: Python versi
$command1 = $pythonPath . ' --version 2>&1';
$output1 = shell_exec($command1);

echo "<b>Test 1 - Cek versi Python:</b><br>";
echo "<pre>" . htmlspecialchars($output1) . "</pre>";

// Test 2: Python print hello
$command2 = $pythonPath . ' -c "print(\'Hello dari Python\')" 2>&1';
$output2 = shell_exec($command2);

echo "<b>Test 2 - Print Hello:</b><br>";
echo "<pre>" . htmlspecialchars($output2) . "</pre>";

// Test 3: Cek fungsi shell_exec
if (function_exists('shell_exec')) {
    echo "<b>✅ Fungsi shell_exec tersedia</b><br>";
} else {
    echo "<b>❌ Fungsi shell_exec TIDAK tersedia</b><br>";
}

// Test 4: Coba dengan cmd /c
$command3 = 'cmd /c ' . $pythonPath . ' --version 2>&1';
$output3 = shell_exec($command3);
echo "<b>Test 3 - Pakai cmd /c:</b><br>";
echo "<pre>" . htmlspecialchars($output3) . "</pre>";

// Info tambahan
echo "<hr>";
echo "<b>Informasi Server:</b><br>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Server Software: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "<br>";
?>