<?php
require_once __DIR__ . '/config/Database.php';

use Config\Database;

try {
    $conn = Database::getConnection();

    // Adiciona as colunas necessárias para o recurso de Câmera Mobile (se elas não existirem)
    $commands = [
        "ALTER TABLE funcionarios ADD COLUMN IF NOT EXISTS foto_perfil TEXT NULL",
        "ALTER TABLE funcionarios ADD COLUMN IF NOT EXISTS codigo_qr VARCHAR(255) NULL",
        "ALTER TABLE funcionarios ADD COLUMN IF NOT EXISTS biometria_facial TEXT NULL",
    ];

    foreach ($commands as $sql) {
        $conn->exec($sql);
        echo "Executado: $sql\n";
    }

    echo "Migração do Banco de Dados concluída com sucesso!";

} catch (PDOException $e) {
    echo "Erro na migração: " . $e->getMessage();
}
