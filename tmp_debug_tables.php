<?php
require_once 'config/Database.php';
use Config\Database;

try {
    $conn = Database::getConnection();
    $stmt = $conn->query("SELECT table_schema, table_name FROM information_schema.tables WHERE table_name = 'funcionarios'");
    $tables = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Found tables named 'funcionarios':\n";
    foreach ($tables as $t) {
        echo "- Schema: {$t['table_schema']}, Table: {$t['table_name']}\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
