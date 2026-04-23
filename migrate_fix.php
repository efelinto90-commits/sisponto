<?php
require 'config/Database.php';
$conn = Config\Database::getConnection();
header('Content-Type: text/plain');

try {
    $conn->exec("ALTER TABLE ponto.funcionarios ADD COLUMN IF NOT EXISTS senha TEXT");
    echo "SUCCESS: Column 'senha' ensured.\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

try {
    $stmt = $conn->query("SELECT column_name FROM information_schema.columns WHERE table_schema = 'ponto' AND table_name = 'funcionarios'");
    $cols = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "COLUMNS: " . implode(', ', $cols) . "\n";
} catch (Exception $e) {
    echo "ERROR SCHEMA: " . $e->getMessage() . "\n";
}
?>