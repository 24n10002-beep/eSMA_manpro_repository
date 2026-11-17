<?php
// auth/logout.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Kosongkan session
$_SESSION = [];
session_destroy();

// Redirect ke login
header('Location: /kepsek_eSMA/auth/login.php');
exit;