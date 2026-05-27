<?php
require_once '../config/Database.php';

try {
    $conn = Config\Database::getConnection();
    $stmt = $conn->query("SELECT id, name, email, level, setor FROM users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($users, JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
