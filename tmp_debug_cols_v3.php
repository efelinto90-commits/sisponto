<?php
require_once 'config/Database.php';
use Config\Database;

try {
    $conn = Database::getConnection();
    $stmt = $conn->query("
        SELECT column_name, data_type 
        FROM information_schema.columns 
        WHERE table_schema = 'ponto' AND table_name = 'funcionarios'
        ORDER BY column_name
    ");
    $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $out = "";
    foreach ($cols as $col) {
        $out .= "{$col['column_name']}|{$col['data_type']}\n";
    }
    file_put_contents('tmp_schema_out.txt', $out);
    echo "SUCCESS\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
