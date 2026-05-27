<?php
require_once 'config/Database.php';
use Config\Database;

try {
    $conn = Database::getConnection();
    $required = ['lat_permitida', 'long_permitida', 'distancia_max_permitida'];
    
    // Check columns
    $stmt = $conn->query("
        SELECT column_name 
        FROM information_schema.columns 
        WHERE table_schema = 'ponto' AND table_name = 'funcionarios'
    ");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "--- COLUNAS ENCONTRADAS ---\n";
    print_r($columns);
    
    echo "\n--- COLUNAS FALTANTES ---\n";
    $missing = array_diff($required, $columns);
    print_r($missing);

} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
