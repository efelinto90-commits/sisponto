<?php
require_once __DIR__ . '/config/Database.php';
use Config\Database;

try {
    $conn = Database::getConnection();
    $sql = "ALTER TABLE horarios 
            ADD COLUMN IF NOT EXISTS tolerancia_entrada INTEGER DEFAULT 0,
            ADD COLUMN IF NOT EXISTS tolerancia_saida INTEGER DEFAULT 0;";
    $conn->exec($sql);
    echo "Colunas de tolerância adicionadas com sucesso à tabela horarios!\n";
} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
