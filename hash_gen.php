<?php
$pass = $_GET['pass'] ?? 'admin123';

if (strlen($pass) < 6) {
    die('Пароль должен быть не короче 6 символов. Использование: hash_gen.php?pass=ваш_пароль');
}

$hash = password_hash($pass, PASSWORD_DEFAULT);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Генератор хеша пароля</title>
    <style>body{font-family:sans-serif;padding:20px;} code{background:#eee;padding:2px 6px;word-break:break-all;}</style>
</head>
<body>
    <h1>Хеш пароля</h1>
    <p>Пароль: <strong><?= htmlspecialchars($pass) ?></strong></p>
    <p>Скопируйте хеш ниже и вставьте в phpMyAdmin в поле <code>password_hash</code>:</p>
    <p><code><?= htmlspecialchars($hash) ?></code></p>
    <hr>
    <p><small>Пример SQL: <code>INSERT INTO users (email, password_hash, role) VALUES ('admin@mail.ru', '<?= htmlspecialchars($hash) ?>', 'admin');</code></small></p>
    <p><strong>После создания админа удалите hash_gen.php с сервера!</strong></p>
</body>
</html>
