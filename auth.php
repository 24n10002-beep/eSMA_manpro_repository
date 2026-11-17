<?php
// includes/auth.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';

function isKepsekLoggedIn() {
    return isset($_SESSION['kepsek_id']) && isset($_SESSION['kepsek_role']);
}

function requireKepsek() {
    if (!isKepsekLoggedIn()) {
        redirect(baseUrl('auth/login.php'));
    }
}