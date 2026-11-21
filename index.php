<?php
// index.php
require_once __DIR__ . '/includes/functions.php';   // load duluan
require_once __DIR__ . '/includes/auth.php';

if (isKepsekLoggedIn()) {
    redirect(baseUrl('dashboard.php'));
}
redirect(baseUrl('auth/login.php'));