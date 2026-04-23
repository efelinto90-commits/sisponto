<?php
require_once 'config/Database.php';
use Config\Database;

try {
    $conn = Database::getConnection();
    $conn->exec("ALTER TABLE ponto.funcionarios ADD COLUMN IF NOT EXISTS deficiencia_grau CHARACTER VARYING(50)");
    echo "SUCCESS: Column deficiencia_grau added to ponto.funcionarios on PRODUCTION\n";
} catch (Exception $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
}
