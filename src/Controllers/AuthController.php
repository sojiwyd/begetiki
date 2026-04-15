<?php
declare(strict_types=1);

class AuthController
{
    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Csrf::requireValid($_POST['csrf_token'] ?? null);

            $email = trim((string)($_POST['email'] ?? ''));
            $password = (string)($_POST['password'] ?? '');

            if ($email === '' || $password === '') {
                $error = 'Введите email и пароль.';
            } else {
                $userModel = new User();
                $user = $userModel->findByEmail($email);
                if ($user && password_verify($password, $user['password_hash'])) {
                    Auth::login($user);
                    if (($user['role'] ?? 'client') === 'admin') {
                        redirect(base_url('index.php?page=admin&action=dashboard'));
                    }
                    redirect(base_url('index.php'));
                }
                $error = 'Неверный логин или пароль.';
            }
        }

        View::render('auth/login', [
            'error' => $error ?? flash('error'),
            'old' => ['email' => $email ?? ''],
        ]);
    }

    public function register(): void
    {
        $success = false;
        $message = '';
        $email = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Csrf::requireValid($_POST['csrf_token'] ?? null);

            $email = trim((string)($_POST['email'] ?? ''));
            $password = (string)($_POST['password'] ?? '');
            $passwordConfirm = (string)($_POST['password_confirm'] ?? '');

            if ($email === '' || $password === '') {
                $message = 'Заполните email и пароль.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $message = 'Некорректный email.';
            } elseif (strlen($password) < 6) {
                $message = 'Пароль должен быть не короче 6 символов.';
            } elseif ($password !== $passwordConfirm) {
                $message = 'Пароли не совпадают.';
            } else {
                $userModel = new User();
                if ($userModel->findByEmail($email)) {
                    $message = 'Такой email уже зарегистрирован.';
                } else {
                    $userModel->create($email, $password, 'client');
                    $success = true;
                    $message = 'Регистрация успешна. Теперь можно войти.';
                }
            }
        }

        View::render('auth/register', [
            'success' => $success,
            'message' => $message,
            'old' => ['email' => $email],
        ]);
    }

    public function logout(): void
    {
        Auth::logout();
        redirect(base_url('index.php'));
    }
}
