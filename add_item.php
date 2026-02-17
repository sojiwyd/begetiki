<?php
require 'db.php';
require 'check_admin.php';

$message = '';
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $message = '<div class="alert alert-danger">Ошибка CSRF. Обновите страницу и попробуйте снова.</div>';
    } else {
        $title = trim($_POST['title'] ?? '');
        $price = $_POST['price'] ?? '';
        $desc = trim($_POST['description'] ?? '');
        $img = null;

        if (empty($title)) {
            $message = '<div class="alert alert-danger">Заполните название!</div>';
        } else {
            $uploadOk = true;
            if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] !== UPLOAD_ERR_NO_FILE) {
                $file = $_FILES['image_file'];

                if ($file['error'] !== UPLOAD_ERR_OK) {
                    $uploadOk = false;
                    $message = '<div class="alert alert-danger">Ошибка загрузки файла.</div>';
                }

                if ($uploadOk && (int)$file['size'] > 5 * 1024 * 1024) {
                    $uploadOk = false;
                    $message = '<div class="alert alert-danger">Файл слишком большой. Максимум 5 МБ.</div>';
                }

                if ($uploadOk) {
                    $finfo = new finfo(FILEINFO_MIME_TYPE);
                    $mime = $finfo->file($file['tmp_name']);
                    $allowed = [
                        'image/jpeg' => 'jpg',
                        'image/png' => 'png',
                        'image/gif' => 'gif',
                        'image/webp' => 'webp',
                    ];

                    if (!isset($allowed[$mime])) {
                        $uploadOk = false;
                        $message = '<div class="alert alert-danger">Разрешены только JPG, PNG, GIF, WEBP.</div>';
                    } else {
                        $uploadDir = __DIR__ . '/uploads/';
                        if (!is_dir($uploadDir)) {
                            mkdir($uploadDir, 0755, true);
                        }
                        $fileName = 'service_' . bin2hex(random_bytes(8)) . '.' . $allowed[$mime];
                        $destination = $uploadDir . $fileName;
                        if (!move_uploaded_file($file['tmp_name'], $destination)) {
                            $uploadOk = false;
                            $message = '<div class="alert alert-danger">Не удалось сохранить файл.</div>';
                        } else {
                            $img = 'uploads/' . $fileName;
                        }
                    }
                }
            }

            if ($uploadOk) {
                $sql = "INSERT INTO services (title, description, price, image_url) VALUES (:t, :d, :p, :i)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':t' => $title,
                    ':d' => $desc,
                    ':p' => $price ?: 0,
                    ':i' => $img,
                ]);
                $message = '<div class="alert alert-success">Успешно добавлено!</div>';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Добавить услугу — Автосервис</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
    <div class="container">
        <h1>Новая услуга</h1>
        <a href="admin_panel.php" class="btn btn-secondary mb-3">← Назад</a>
        <?= $message ?>
        <form method="POST" enctype="multipart/form-data" class="card p-4">
            <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
            <input type="text" name="title" class="form-control mb-2" placeholder="Название" required>
            <input type="number" name="price" class="form-control mb-2" placeholder="Цена" step="0.01">
            <input type="file" name="image_file" class="form-control mb-2" accept="image/jpeg,image/png,image/gif,image/webp">
            <textarea name="description" class="form-control mb-2" placeholder="Описание"></textarea>
            <button type="submit" class="btn btn-success">Сохранить</button>
        </form>
    </div>
</body>
</html>
