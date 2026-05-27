<?php
require_once 'config/Database.php';
use Config\Database;

try {
    $conn = Database::getConnection();
    $stmt = $conn->query("SELECT column_name FROM information_schema.columns WHERE table_schema = 'ponto' AND table_name = 'funcionarios'");
    $cols = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Colunas de funcionarios:\n";
    print_r($cols);
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
