<?php
/**
 * Подключение к БД автосервиса
 * База: i923493f_db (Beget)
 */
$host = 'localhost';
$dbname = 'i923493f_db';
$user = 'i923493f_db';
$pass = 'Begetneochen1';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
$opt = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];
try {
    $pdo = new PDO($dsn, $user, $pass, $opt);
} catch (PDOException $e) {
    die('Ошибка подключения к БД: ' . $e->getMessage());
}

/**
 * Экранирование вывода — защита от XSS (2-я пара: Fixing)
 * В index.php оборачиваем вывод в h(): <?= h($p['title']) ?>
 */
function h($str) {
    return htmlspecialchars((string) $str, ENT_QUOTES, 'UTF-8');
}
