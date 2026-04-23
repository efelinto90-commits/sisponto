<?php
require_once 'c:\xampp\htdocs\sisponto\config\Database.php';

try {
    $conn = Config\Database::getConnection();
    $stmt = $conn->query("SELECT id, name, level, permissoes, setor FROM users ORDER BY id ASC");
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'data' => $data], JSON_PRETTY_PRINT);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
