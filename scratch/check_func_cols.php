<?php
require_once '../config/Database.php';

try {
    $conn = Config\Database::getConnection();
    $stmt = $conn->query("SELECT column_name FROM information_schema.columns WHERE table_name = 'funcionarios'");
    $cols = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo json_encode($cols);
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
