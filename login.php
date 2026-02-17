<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // Проверяем пароль
    if ($user && password_verify($pass, $user['password_hash'])) {
        // --- ВАЖНЫЕ ИЗМЕНЕНИЯ (RBAC) ---
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role']; // Сохраняем "браслет" (роль)
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // CSRF для форм
        // ---------------------------------

        // Маршрутизация: Админа — в панель, остальных — на главную
        if ($user['role'] === 'admin') {
            header("Location: admin_panel.php");
        } else {
            header("Location: index.php");
        }
        exit;
    } else {
        $error = 'Неверный логин или пароль';
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Вход — Автосервис</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow">
                <div class="card-body p-4">
                    <h1 class="h4 mb-4">Вход в систему</h1>
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger"><?= h($error) ?></div>
                    <?php endif; ?>
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required value="<?= isset($_POST['email']) ? h($_POST['email']) : '' ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Пароль</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Войти</button>
                    </form>
                    <p class="mt-3 mb-0 text-center"><a href="register.php">Регистрация</a> · <a href="index.php">На главную</a></p>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
