<?php

/**
 * register.php
 *
 * GET  → Formu göster
 * POST → Doğrula → password_hash() → UserRepository::create() → login'e yönlendir
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../database/Database.php';
require_once __DIR__ . '/../database/repositories/UserRepository.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Zaten giriş yapmışsa chat'e gönder
if (!empty($_SESSION['user_id'])) {
    header('Location: /public/chat.php');
    exit;
}

$errors   = [];
$formData = ['username' => '', 'email' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username  = trim($_POST['username']  ?? '');
    $email     = trim($_POST['email']     ?? '');
    $password  = $_POST['password']       ?? '';
    $password2 = $_POST['password2']      ?? '';

    $formData = ['username' => $username, 'email' => $email];

    // ── Doğrulama ─────────────────────────────────────────────────────────
    if ($username === '') {
        $errors['username'] = 'Kullanıcı adı boş bırakılamaz.';
    } elseif (strlen($username) < 3 || strlen($username) > 50) {
        $errors['username'] = 'Kullanıcı adı 3-50 karakter arasında olmalıdır.';
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors['username'] = 'Sadece harf, rakam ve alt çizgi kullanılabilir.';
    }

    if ($email === '') {
        $errors['email'] = 'E-posta adresi boş bırakılamaz.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Geçerli bir e-posta adresi giriniz.';
    }

    if ($password === '') {
        $errors['password'] = 'Şifre boş bırakılamaz.';
    } elseif (strlen($password) < 8) {
        $errors['password'] = 'Şifre en az 8 karakter olmalıdır.';
    }

    if ($password !== $password2) {
        $errors['password2'] = 'Şifreler eşleşmiyor.';
    }

    // ── DB benzersizlik kontrolü ───────────────────────────────────────────
    if (empty($errors)) {
        $userRepo = new UserRepository();

        if ($userRepo->findByUsername($username) !== null) {
            $errors['username'] = 'Bu kullanıcı adı zaten kullanılıyor.';
        }
        if ($userRepo->findByEmail($email) !== null) {
            $errors['email'] = 'Bu e-posta adresi zaten kayıtlı.';
        }
    }

    // ── Kaydet ────────────────────────────────────────────────────────────
    if (empty($errors)) {
        $userRepo = $userRepo ?? new UserRepository();
        $userRepo->create($username, $email, password_hash($password, PASSWORD_BCRYPT));

        header('Location: /public/login.php?registered=1');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kayıt Ol — <?= APP_NAME ?></title>
    <link rel="stylesheet" href="/public/css/auth.css">
</head>
<body>
<div class="auth-wrapper">
    <div class="auth-card">

        <div class="auth-header">
            <div class="auth-logo">💬</div>
            <h1><?= APP_NAME ?></h1>
            <p>Yeni hesap oluştur</p>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <ul>
                    <?php foreach ($errors as $err): ?>
                        <li><?= htmlspecialchars($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="" novalidate>

            <div class="form-group <?= isset($errors['username']) ? 'has-error' : '' ?>">
                <label for="username">Kullanıcı Adı</label>
                <input type="text" id="username" name="username"
                       value="<?= htmlspecialchars($formData['username']) ?>"
                       placeholder="örn: ahmet42" autocomplete="username" maxlength="50" required>
                <?php if (isset($errors['username'])): ?>
                    <span class="field-error"><?= htmlspecialchars($errors['username']) ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group <?= isset($errors['email']) ? 'has-error' : '' ?>">
                <label for="email">E-posta</label>
                <input type="email" id="email" name="email"
                       value="<?= htmlspecialchars($formData['email']) ?>"
                       placeholder="ornek@eposta.com" autocomplete="email" required>
                <?php if (isset($errors['email'])): ?>
                    <span class="field-error"><?= htmlspecialchars($errors['email']) ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group <?= isset($errors['password']) ? 'has-error' : '' ?>">
                <label for="password">Şifre</label>
                <input type="password" id="password" name="password"
                       placeholder="En az 8 karakter" autocomplete="new-password" required>
                <?php if (isset($errors['password'])): ?>
                    <span class="field-error"><?= htmlspecialchars($errors['password']) ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group <?= isset($errors['password2']) ? 'has-error' : '' ?>">
                <label for="password2">Şifre Tekrarı</label>
                <input type="password" id="password2" name="password2"
                       placeholder="Şifrenizi tekrar girin" autocomplete="new-password" required>
                <?php if (isset($errors['password2'])): ?>
                    <span class="field-error"><?= htmlspecialchars($errors['password2']) ?></span>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn-primary">Kayıt Ol</button>
        </form>

        <p class="auth-footer">
            Zaten hesabın var mı? <a href="/public/login.php">Giriş yap</a>
        </p>

    </div>
</div>
</body>
</html>
