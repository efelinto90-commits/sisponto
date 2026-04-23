<?php
require_once 'config/Database.php';
use Config\Database;

try {
    $conn = Database::getConnection();
    
    // Add crh_history column if it doesn't exist
    $sql = "ALTER TABLE registros ADD COLUMN IF NOT EXISTS crh_history JSONB DEFAULT '[]'::jsonb";
    
    $conn->exec($sql);
    
    echo "COLUNA crh_history ADICIONADA COM SUCESSO EM ponto.registros (OU JÁ EXISTIA).\n";
    
} catch (Exception $e) {
    echo "ERRO NA MIGRAÇÃO: " . $e->getMessage() . "\n";
}
