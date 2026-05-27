<?php
require_once 'config/Database.php';
use Config\Database;
try {
    $conn = Database::getConnection();
    $stmt = $conn->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'ponto'");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo json_encode($tables, JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
