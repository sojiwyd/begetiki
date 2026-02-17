<?php
require_once __DIR__ . '/core/bootstrap.php';
require_once __DIR__ . '/src/Controllers/AuthController.php';

$controller = new AuthController();
$state = $controller->register();

$success = (bool)$state['success'];
$message = (string)$state['message'];
$old = $state['old'] ?? ['email' => ''];

require __DIR__ . '/templates/auth/register.php';
