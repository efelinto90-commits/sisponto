<?php
require_once '../config/Database.php';

try {
    $conn = Config\Database::getConnection();
    $stmt = $conn->query("SELECT * FROM users LIMIT 1");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        echo json_encode(array_keys($row));
    } else {
        // Se a tabela estiver vazia, obter colunas da schema
        $stmt = $conn->query("SELECT column_name FROM information_schema.columns WHERE table_name = 'users'");
        $cols = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo json_encode($cols);
    }
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
