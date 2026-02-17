<?php
$host = 'localhost';
$db = 'i923493f_db';
$user = 'i923493f_db';
$pass = 'Begetneochen1';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die('Ошибка подключения к БД. Попробуйте позже.');
}
function h($str) {
    return htmlspecialchars((string) $str, ENT_QUOTES, 'UTF-8');
}
