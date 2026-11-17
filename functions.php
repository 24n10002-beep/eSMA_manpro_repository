<?php
// includes/functions.php

function baseUrl($path = '') {
    return '/kepsek_eSMA/' . ltrim($path, '/');
}

function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function redirect($url) {
    header("Location: $url");
    exit;
}

function flash($key, $message = null) {
    if ($message !== null) {
        $_SESSION['flash'][$key] = $message;
        return;
    }
    if (isset($_SESSION['flash'][$key])) {
        $msg = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $msg;
    }
    return null;
}

function formatTanggal($date) {
    if (!$date) return '-';
    return date('d/m/Y', strtotime($date));
}

function formatDatetime($dt) {
    if (!$dt) return '-';
    return date('d/m/Y H:i', strtotime($dt));
}