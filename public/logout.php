<?php

/**
 * logout.php
 * Kullanıcı çıkış işlemi.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../database/Database.php';
require_once __DIR__ . '/../database/repositories/UserRepository.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!empty($_SESSION['user_id'])) {
    $userRepo = new UserRepository();
    $userRepo->setOnlineStatus((int) $_SESSION['user_id'], false);
}

session_unset();

if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

session_destroy();

header('Location: /public/login.php');
exit;
