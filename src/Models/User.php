<?php

require_once __DIR__ . '/../../config/Database.php';

class User
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        return $user ?: null;
    }

    public function create(string $email, string $password, string $role = 'client'): bool
    {
        $sql = 'INSERT INTO users (email, password_hash, role) VALUES (:email, :hash, :role)';
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':email' => $email,
            ':hash' => password_hash($password, PASSWORD_DEFAULT),
            ':role' => $role,
        ]);
    }
}
