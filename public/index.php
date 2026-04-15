<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/../src/Core/Helpers.php';
require_once __DIR__ . '/../src/Core/Database.php';
require_once __DIR__ . '/../src/Core/Csrf.php';
require_once __DIR__ . '/../src/Core/Auth.php';
require_once __DIR__ . '/../src/Core/View.php';

require_once __DIR__ . '/../src/Models/User.php';
require_once __DIR__ . '/../src/Models/Service.php';
require_once __DIR__ . '/../src/Models/Order.php';

require_once __DIR__ . '/../src/Controllers/HomeController.php';
require_once __DIR__ . '/../src/Controllers/AuthController.php';
require_once __DIR__ . '/../src/Controllers/ProfileController.php';
require_once __DIR__ . '/../src/Controllers/OrderController.php';
require_once __DIR__ . '/../src/Controllers/AdminController.php';
require_once __DIR__ . '/../src/Controllers/ApiController.php';

$page = (string)($_GET['page'] ?? 'home');
$action = (string)($_GET['action'] ?? 'index');

$map = [
    'home' => HomeController::class,
    'calendar' => HomeController::class,
    'auth' => AuthController::class,
    'profile' => ProfileController::class,
    'orders' => OrderController::class,
    'order' => OrderController::class,
    'admin' => AdminController::class,
    'api' => ApiController::class,
];

if ($page === 'calendar') {
    $action = 'calendar';
}

$controllerClass = $map[$page] ?? HomeController::class;
$controller = new $controllerClass();

if (!method_exists($controller, $action)) {
    http_response_code(404);
    exit('Страница не найдена.');
}

$controller->$action();
