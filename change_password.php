<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$message = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Проверка CSRF
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $message = 'Ошибка безопасности: неверный CSRF-токен. Запрос отклонён.';
    } else {
        $old = $_POST['old_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $repeat = $_POST['new_password2'] ?? '';

        if (empty($old) || empty($new) || empty($repeat)) {
            $message = 'Заполните все поля.';
        } elseif (strlen($new) < 8) {
            $message = 'Новый пароль должен быть не короче 8 символов.';
        } elseif ($new !== $repeat) {
            $message = 'Пароли не совпадают.';
        } else {
            $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();
            if (!$user || !password_verify($old, $user['password_hash'])) {
                $message = 'Неверный текущий пароль.';
            } else {
                $hash = password_hash($new, PASSWORD_DEFAULT);
                $upd = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
                $upd->execute([$hash, $_SESSION['user_id']]);
                $success = true;
                $message = 'Пароль успешно изменён. <a href="profile.php">В личный кабинет</a>';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Смена пароля — Автосервис</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow">
                <div class="card-body p-4">
                    <h1 class="h4 mb-4">Смена пароля</h1>
                    <?php if ($message): ?>
                        <div class="alert <?= $success ? 'alert-success' : 'alert-danger' ?>"><?= $success ? $message : h($message) ?></div>
                    <?php endif; ?>
                    <?php if (!$success): ?>
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
                        <div class="mb-3">
                            <label class="form-label">Текущий пароль</label>
                            <input type="password" name="old_password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Новый пароль (не менее 8 символов)</label>
                            <input type="password" name="new_password" class="form-control" required minlength="8">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Повторите новый пароль</label>
                            <input type="password" name="new_password2" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Сохранить</button>
                    </form>
                    <?php endif; ?>
                    <p class="mt-3 mb-0"><a href="profile.php">← Личный кабинет</a></p>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
