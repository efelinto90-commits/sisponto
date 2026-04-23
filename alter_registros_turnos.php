<?php
require_once __DIR__ . '/config/Database.php';
use Config\Database;

try {
    $conn = Database::getConnection();
    $sql = "ALTER TABLE registros 
            ADD COLUMN IF NOT EXISTS status_turno1 VARCHAR(50) DEFAULT '',
            ADD COLUMN IF NOT EXISTS status_turno2 VARCHAR(50) DEFAULT '';";
    $conn->exec($sql);
    echo "Colunas de status_turno adicionadas à tabela 'registros' com sucesso!\n";
} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
