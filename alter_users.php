<?php
require_once __DIR__ . '/config/Database.php';

use Config\Database;

try {
    $conn = Database::getConnection();
    $conn->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS permissoes TEXT DEFAULT '[]';");
    echo "Coluna 'permissoes' adicionada com sucesso à tabela 'users'.\n";

    // Atualiza o admin principal para ter todas as permissões
    $conn->exec("UPDATE users SET permissoes = '[\"funcionarios\",\"relatorios\",\"horarios\",\"cargos\",\"usuarios\"]' WHERE name = 'suporte'");
    echo "Permissões do suporte atualizadas.\n";
} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
