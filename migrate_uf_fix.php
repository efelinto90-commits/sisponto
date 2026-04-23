<?php
require_once 'config/Database.php';
use Config\Database;

try {
    $conn = Database::getConnection();
    $queries = [
        "ALTER TABLE ponto.funcionarios ALTER COLUMN endereco_uf TYPE VARCHAR(10)",
        "ALTER TABLE ponto.funcionarios ALTER COLUMN naturalidade_uf TYPE VARCHAR(10)",
        "ALTER TABLE ponto.funcionarios ALTER COLUMN naturalidade_uf_conjuge TYPE VARCHAR(10)",
        "ALTER TABLE ponto.funcionarios ALTER COLUMN ctps_uf TYPE VARCHAR(10)"
    ];
    
    foreach ($queries as $q) {
        echo "Running: $q\n";
        $conn->exec($q);
    }
    echo "SUCCESS\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
