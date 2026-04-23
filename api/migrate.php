<?php
require_once 'c:/xampp/htdocs/sisponto/config/Database.php';
use Config\Database;
try {
    $conn = Database::getConnection();
    // Try to add to public schema if that's what's used
    try {
        $conn->exec("ALTER TABLE funcionarios ADD COLUMN motivo_exoneracao TEXT");
        echo "Coluna adicionada em funcionarios (public or default).\n";
    } catch (Exception $e) {
        echo "Erro (public): " . $e->getMessage() . "\n";
    }
    
    // Try to add to ponto schema
    try {
        $conn->exec("ALTER TABLE ponto.funcionarios ADD COLUMN motivo_exoneracao TEXT");
        echo "Coluna adicionada em ponto.funcionarios.\n";
    } catch (Exception $e) {
        echo "Erro (ponto): " . $e->getMessage() . "\n";
    }
} catch (Exception $e) {
    echo "GENERAL ERROR: " . $e->getMessage();
}
