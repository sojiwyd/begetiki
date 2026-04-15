<?php
declare(strict_types=1);

class View
{
    public static function render(string $template, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        $templatePath = __DIR__ . '/../../templates/' . $template . '.php';
        if (!is_file($templatePath)) {
            http_response_code(500);
            exit('Шаблон не найден: ' . h($template));
        }
        require $templatePath;
    }
}
