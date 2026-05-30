<?php

/**
 * auth_check.php
 *
 * Korumalı her sayfanın tepesine eklenir:
 *   require_once __DIR__ . '/../auth/auth_check.php';
 *
 * Başarılı doğrulama sonrası $currentUser set edilir.
 * Bu değişken hem PHP sayfalarında hem Kişi 1'in
 * WebSocket entegrasyonunda (user_id query string olarak) kullanılır.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['user_id']) || empty($_SESSION['username'])) {
    header('Location: /public/login.php');
    exit;
}

// Hesap silinmiş ama eski session açık kalabilir — DB kontrolü
require_once __DIR__ . '/../database/Database.php';
require_once __DIR__ . '/../database/repositories/UserRepository.php';

$userRepo    = new UserRepository();
$sessionUser = $userRepo->findById((int) $_SESSION['user_id']);

if ($sessionUser === null) {
    session_unset();
    session_destroy();
    header('Location: /public/login.php?error=session_invalid');
    exit;
}

$currentUser = $sessionUser;
