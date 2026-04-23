<?php
require_once 'c:/xampp/htdocs/sisponto/config/Database.php';
use Config\Database;
try {
    $conn = Database::getConnection();
    // Query para PostgreSQL para listar colunas
    $stmt = $conn->query("
        SELECT column_name, data_type 
        FROM information_schema.columns 
        WHERE table_schema = 'ponto' 
          AND table_name = 'horarios'
        ORDER BY ordinal_position
    ");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $col) {
        echo $col['column_name'] . " (" . $col['data_type'] . ")\n";
    }
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
?>
