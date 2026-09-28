<?php
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'admin';
require_once '../config/database.php';
echo "DB Connection: " . (mysqli_ping($conn) ? "OK" : "FAILED");
?>