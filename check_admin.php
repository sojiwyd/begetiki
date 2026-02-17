<?php
// check_admin.php — Скрипт защиты (Middleware)
session_start();

// Проверяем: авторизован ли И является ли админом
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    die("ДОСТУП ЗАПРЕЩЕН. У вас нет прав администратора. <a href='login.php'>Войти</a>");
}
