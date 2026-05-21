<?php

/**
 * login.php
 *
 * GET  → Formu göster
 * POST → UserRepository::findByEmail() → password_verify() → Session set → chat.php
 *
 * Session yapısı (Kişi 1'in BroadcastServer'ı Hafta 2'de kullanır):
 *   $_SESSION['user_id']  → int
 *   $_SESSION['username'] → string
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../database/Database.php';
require_once __DIR__ . '/../database/repositories/UserRepository.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!empty($_SESSION['user_id'])) {
    header('Location: /public/chat.php');
    exit;
}

$error          = '';
$emailValue     = '';
$justRegistered = isset($_GET['registered']) && $_GET['registered'] === '1';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email    = trim($_POST['email']    ?? '');
    $password = $_POST['password']      ?? '';
    $emailValue = $email;

    if ($email === '' || $password === '') {
        $error = 'E-posta ve şifre alanları boş bırakılamaz.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Geçerli bir e-posta adresi giriniz.';
    } else {

        $userRepo = new UserRepository();
        $user     = $userRepo->findByEmail($email);

        /**
         * Timing attack koruması:
         * Kullanıcı bulunamasa bile password_verify() çalıştırılır.
         * Böylece "bulunamadı" ve "şifre yanlış" durumları eşit sürede döner,
         * e-posta numaralandırması (enumeration) önlenir.
         */
        $dummyHash   = '$2y$10$abcdefghijklmnopqrstuuABC123456789012345678901234567890';
        $hashToCheck = $user ? $user['password'] : $dummyHash;

        if ($user && password_verify($password, $hashToCheck)) {

            // Session fixation saldırısına karşı ID yenile
            session_regenerate_id(true);

            $_SESSION['user_id']  = (int) $user['id'];
            $_SESSION['username'] = $user['username'];

            $userRepo->setOnlineStatus($user['id'], true);

            header('Location: /public/chat.php');
            exit;

        } else {
            // Kasıtlı belirsiz mesaj — hangisinin yanlış olduğu belli edilmez
            $error = 'E-posta adresi veya şifre hatalı.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş Yap — <?= APP_NAME ?></title>
    <link rel="stylesheet" href="/public/css/auth.css">
</head>
<body>
<div class="auth-wrapper">
    <div class="auth-card">

        <div class="auth-header">
            <div class="auth-logo">💬</div>
            <h1><?= APP_NAME ?></h1>
            <p>Hesabınıza giriş yapın</p>
        </div>

        <?php if ($justRegistered): ?>
            <div class="alert alert-success">
                Kayıt başarılı! Şimdi giriş yapabilirsiniz.
            </div>
        <?php endif; ?>

        <?php if ($error !== ''): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error']) && $_GET['error'] === 'session_invalid'): ?>
            <div class="alert alert-error">
                Oturumunuz geçersiz. Lütfen tekrar giriş yapın.
            </div>
        <?php endif; ?>

        <form method="POST" action="" novalidate>

            <div class="form-group">
                <label for="email">E-posta</label>
                <input type="email" id="email" name="email"
                       value="<?= htmlspecialchars($emailValue) ?>"
                       placeholder="ornek@eposta.com" autocomplete="email" required>
            </div>

            <div class="form-group">
                <label for="password">Şifre</label>
                <input type="password" id="password" name="password"
                       placeholder="Şifreniz" autocomplete="current-password" required>
            </div>

            <button type="submit" class="btn-primary">Giriş Yap</button>
        </form>

        <p class="auth-footer">
            Hesabın yok mu? <a href="/public/register.php">Kayıt ol</a>
        </p>

    </div>
</div>
</body>
</html>
