<?php
require_once '../config/Database.php';
use Config\Database;

try {
    $conn = Database::getConnection();
    $conn->exec("
        CREATE TABLE IF NOT EXISTS ferias (
            id          SERIAL PRIMARY KEY,
            id_funcionario INTEGER NOT NULL REFERENCES funcionarios(id) ON DELETE CASCADE,
            data_inicio DATE NOT NULL,
            data_fim    DATE NOT NULL,
            observacao  TEXT,
            created_at  TIMESTAMP DEFAULT NOW(),
            updated_at  TIMESTAMP DEFAULT NOW()
        )
    ");
    echo "Tabela 'ferias' criada com sucesso!";
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
