<?php
require_once __DIR__ . '/config/Database.php';
use Config\Database;

try {
    $conn = Database::getConnection();
    $sql = "ALTER TABLE registros ADD COLUMN IF NOT EXISTS justificativa TEXT;";
    $conn->exec($sql);
    echo "Coluna 'justificativa' adicionada à tabela 'registros' com sucesso!\n";
} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
