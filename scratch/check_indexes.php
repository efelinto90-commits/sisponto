<?php
require_once 'config/Database.php';
use Config\Database;
try {
    $conn = Database::getConnection();
    $stmt = $conn->query("
        SELECT 
            indexname, 
            indexdef 
        FROM 
            pg_indexes 
        WHERE 
            schemaname = 'ponto' 
            AND tablename = 'funcionarios';
    ");
    $indexes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($indexes, JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
