<?php
require_once 'config/Database.php';
use Config\Database;
try {
    $conn = Database::getConnection();
    $stmt = $conn->query("SELECT r.*, f.nome 
                          FROM registros r 
                          JOIN funcionarios f ON r.id_funcionario = f.id 
                          ORDER BY r.id DESC LIMIT 5");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($rows, JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
