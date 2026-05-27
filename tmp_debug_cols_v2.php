<?php
require_once 'config/Database.php';
use Config\Database;

try {
    $conn = Database::getConnection();
    // Consulta direta ao Postgres para não ter erro de truncamento no terminal
    $stmt = $conn->query("
        SELECT column_name, data_type 
        FROM information_schema.columns 
        WHERE table_schema = 'ponto' AND table_name = 'funcionarios'
        ORDER BY column_name
    ");
    $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "COLUMNS_START\n";
    foreach ($cols as $col) {
        echo "{$col['column_name']}|{$col['data_type']}\n";
    }
    echo "COLUMNS_END\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
