<?php
session_start();
require 'db.php';

$message = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass = $_POST['password'] ?? '';
    $pass2 = $_POST['password2'] ?? '';

    if (empty($email) || empty($pass)) {
        $message = 'Заполните email и пароль.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Некорректный email.';
    } elseif (strlen($pass) < 6) {
        $message = 'Пароль не менее 6 символов.';
    } elseif ($pass !== $pass2) {
        $message = 'Пароли не совпадают.';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $message = 'Такой email уже зарегистрирован.';
        } else {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (email, password_hash, role) VALUES (:email, :hash, 'client')";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':email' => $email,
                ':hash'  => $hash
            ]);
            $success = true;
            $message = 'Регистрация успешна. <a href="login.php">Войти</a>';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Регистрация — Автосервис</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow">
                <div class="card-body p-4">
                    <h1 class="h4 mb-4">Регистрация</h1>
                    <?php if ($message): ?>
                        <div class="alert <?= $success ? 'alert-success' : 'alert-danger' ?>"><?= $success ? $message : h($message) ?></div>
                    <?php endif; ?>
                    <?php if (!$success): ?>
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required value="<?= isset($_POST['email']) ? h($_POST['email']) : '' ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Пароль</label>
                            <input type="password" name="password" class="form-control" required minlength="6">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Пароль ещё раз</label>
                            <input type="password" name="password2" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-success w-100">Зарегистрироваться</button>
                    </form>
                    <?php endif; ?>
                    <p class="mt-3 mb-0 text-center"><a href="login.php">Войти</a> · <a href="index.php">На главную</a></p>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
