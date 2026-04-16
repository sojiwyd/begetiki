<?php
declare(strict_types=1);

function h(mixed $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

function base_url(string $path = ''): string
{
    $scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/public/index.php')), '/');
    if ($scriptDir === '') {
        $scriptDir = '/';
    }
    $base = $scriptDir === '/' ? '' : $scriptDir;
    return $base . '/' . ltrim($path, '/');
}

function old(array $source, string $key, mixed $default = ''): mixed
{
    return $source[$key] ?? $default;
}

function flash(string $key, ?string $message = null): ?string
{
    if ($message !== null) {
        $_SESSION['_flash'][$key] = $message;
        return null;
    }

    if (!isset($_SESSION['_flash'][$key])) {
        return null;
    }

    $value = $_SESSION['_flash'][$key];
    unset($_SESSION['_flash'][$key]);
    return $value;
}

function status_badge_class(string $status): string
{
    return match ($status) {
        'new' => 'primary',
        'processing' => 'warning',
        'done' => 'success',
        default => 'secondary',
    };
}

function format_price(float|int|string|null $price): string
{
    return number_format((float)$price, 0, '', ' ') . ' ₽';
}
