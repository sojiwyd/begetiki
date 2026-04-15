<?php
declare(strict_types=1);

class ProfileController
{
    public function index(): void
    {
        Auth::requireLogin();
        $orderModel = new Order();
        View::render('profile/index', [
            'orders' => $orderModel->findByUser((int)Auth::id()),
            'updatedMessage' => !empty($_GET['updated']) ? 'Запись перенесена.' : null,
        ]);
    }

    public function changePassword(): void
    {
        Auth::requireLogin();
        $message = '';
        $success = false;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Csrf::requireValid($_POST['csrf_token'] ?? null);
            $oldPassword = (string)($_POST['old_password'] ?? '');
            $newPassword = (string)($_POST['new_password'] ?? '');
            $repeat = (string)($_POST['new_password2'] ?? '');

            if ($oldPassword === '' || $newPassword === '' || $repeat === '') {
                $message = 'Заполните все поля.';
            } elseif (strlen($newPassword) < 8) {
                $message = 'Новый пароль должен быть не короче 8 символов.';
            } elseif ($newPassword !== $repeat) {
                $message = 'Пароли не совпадают.';
            } else {
                $userModel = new User();
                $user = $userModel->findById((int)Auth::id());
                if (!$user || !password_verify($oldPassword, $user['password_hash'])) {
                    $message = 'Неверный текущий пароль.';
                } else {
                    $userModel->changePassword((int)Auth::id(), $newPassword);
                    $success = true;
                    $message = 'Пароль успешно изменён.';
                }
            }
        }

        View::render('profile/change_password', [
            'message' => $message,
            'success' => $success,
        ]);
    }
}
