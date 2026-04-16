<?php
declare(strict_types=1);

$password = $argv[1] ?? 'admin123';
if (strlen($password) < 6) {
    fwrite(STDERR, "Password must be at least 6 characters.\n");
    exit(1);
}
echo password_hash($password, PASSWORD_DEFAULT) . PHP_EOL;
