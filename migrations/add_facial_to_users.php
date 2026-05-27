<?php
/**
 * Migração: Adiciona coluna facial_descriptor na tabela users
 * Execução: http://localhost/sisponto/migrations/add_facial_to_users.php
 */
require_once '../config/Database.php';
use Config\Database;

try {
    $conn = Database::getConnection();

    // Verificar se a coluna já existe
    $check = $conn->query("
        SELECT column_name FROM information_schema.columns
        WHERE table_name = 'users' AND column_name = 'facial_descriptor'
        LIMIT 1
    ")->fetch();

    if ($check) {
        echo "<p style='color:orange'>⚠️ Coluna <b>facial_descriptor</b> já existe na tabela <b>users</b>. Nenhuma alteração feita.</p>";
    } else {
        $conn->exec("ALTER TABLE users ADD COLUMN facial_descriptor TEXT DEFAULT NULL");
        echo "<p style='color:green'>✅ Coluna <b>facial_descriptor</b> adicionada com sucesso à tabela <b>users</b>.</p>";
    }

} catch (Throwable $e) {
    echo "<p style='color:red'>❌ Erro: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
