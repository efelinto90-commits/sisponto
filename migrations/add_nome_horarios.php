<?php
require_once __DIR__ . '/../config/Database.php';
use Config\Database;

try {
    $conn = Database::getConnection();
    $conn->exec("ALTER TABLE horarios ADD COLUMN IF NOT EXISTS nome VARCHAR(100) DEFAULT NULL");
    echo "✅ Coluna 'nome' adicionada com sucesso à tabela horarios.\n";
} catch (PDOException $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}
