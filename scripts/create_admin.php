<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/Core/Database.php';

$email = $argv[1] ?? 'admin@autoservice.local';
$password = $argv[2] ?? 'admin123';

$pdo = Database::getConnection();
$hash = password_hash($password, PASSWORD_DEFAULT);
$sql = "INSERT INTO users (email, password_hash, role)
        VALUES (:email, :hash, 'admin')
        ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash), role = 'admin'";
$stmt = $pdo->prepare($sql);
$stmt->execute(['email' => $email, 'hash' => $hash]);

echo "Admin created: {$email}\n";
