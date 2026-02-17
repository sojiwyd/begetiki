<?php
require_once __DIR__ . '/core/bootstrap.php';
require_once __DIR__ . '/src/Controllers/AuthController.php';

$controller = new AuthController();
$state = $controller->login();

$error = (string)$state['error'];
$old = $state['old'] ?? ['email' => ''];

require __DIR__ . '/templates/auth/login.php';
