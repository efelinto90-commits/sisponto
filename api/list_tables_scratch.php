<?php
require_once __DIR__ . '/../config/Database.php';
use Config\Database;

try {
    $conn = Database::getConnection();
    $stmt = $conn->query("SELECT tablename FROM pg_tables WHERE schemaname = 'ponto'");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo json_encode($tables);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
