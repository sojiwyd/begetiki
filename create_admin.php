<?php
require 'db.php';

$email = 'admin@autoservice.local';  // замените на свой email
$password = 'admin123';              // замените на свой пароль

$hash = password_hash($password, PASSWORD_DEFAULT);
$sql = "INSERT INTO users (email, password_hash, role) VALUES (:email, :hash, 'admin')
        ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash), role = 'admin'";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':email' => $email, ':hash' => $hash]);
    echo "Админ создан: $email / $password . После проверки удалите create_admin.php!";
} catch (PDOException $e) {
    echo "Ошибка: " . $e->getMessage();
}
