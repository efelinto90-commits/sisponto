<?php
require_once 'config/Database.php';
use Config\Database;

try {
    $conn = Database::getConnection();
    $stmt = $conn->query("SELECT column_name, data_type FROM information_schema.columns WHERE table_schema = 'ponto' AND table_name = 'funcionarios'");
    $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Columns in ponto.funcionarios:\n";
    foreach ($cols as $col) {
        echo "- {$col['column_name']} ({$col['data_type']})\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
