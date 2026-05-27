<?php
require_once 'config/Database.php';
use Config\Database;

try {
    $conn = Database::getConnection();
    $stmt = $conn->query("SELECT column_name, data_type FROM information_schema.columns WHERE table_schema = 'ponto' AND table_name = 'ferias'");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($columns, JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo $e->getMessage();
}
