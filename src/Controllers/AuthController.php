<?php

require_once __DIR__ . '/../Models/User.php';

class AuthController
{
    public function register(): array
    {
        $state = [
            'success' => false,
            'message' => '',
            'old' => ['email' => ''],
        ];

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $state;
        }

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? ($_POST['password2'] ?? '');
        $state['old']['email'] = $email;

        if ($email === '' || $password === '') {
            $state['message'] = 'Заполните email и пароль.';
            return $state;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $state['message'] = 'Некорректный email.';
            return $state;
        }

        if (strlen($password) < 6) {
            $state['message'] = 'Пароль не менее 6 символов.';
            return $state;
        }

        if ($password !== $passwordConfirm) {
            $state['message'] = 'Пароли не совпадают.';
            return $state;
        }

        $userModel = new User();
        if ($userModel->findByEmail($email)) {
            $state['message'] = 'Такой email уже зарегистрирован.';
            return $state;
        }

        try {
            $userModel->create($email, $password, 'client');
            $state['success'] = true;
            $state['message'] = 'Регистрация успешна. <a href="login.php">Войти</a>';
        } catch (PDOException $e) {
            $state['message'] = 'Ошибка БД. Попробуйте позже.';
        }

        return $state;
    }

    public function login(): array
    {
        $state = [
            'error' => '',
            'old' => ['email' => ''],
        ];

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $state;
        }

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $state['old']['email'] = $email;

        if ($email === '' || $password === '') {
            $state['error'] = 'Введите email и пароль.';
            return $state;
        }

        $userModel = new User();
        $user = $userModel->findByEmail($email);

        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int)$user['id'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

            if ($user['role'] === 'admin') {
                header('Location: admin_panel.php');
            } else {
                header('Location: index.php');
            }
            exit;
        }

        $state['error'] = 'Неверный логин или пароль';
        return $state;
    }
}
