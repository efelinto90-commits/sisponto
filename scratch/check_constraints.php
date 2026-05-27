<?php
require_once 'config/Database.php';
use Config\Database;
try {
    $conn = Database::getConnection();
    $stmt = $conn->query("
        SELECT conname, pg_get_constraintdef(oid) 
        FROM pg_constraint 
        WHERE conrelid = 'ponto.funcionarios'::regclass;
    ");
    $constraints = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($constraints, JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
