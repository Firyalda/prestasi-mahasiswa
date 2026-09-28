<?php
// logout.php - OOP Controller Entry
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/flash.php';

use App\Controllers\AuthController;

$auth = new AuthController();
$auth->logout();