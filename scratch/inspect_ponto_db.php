<?php
// Bypass admin login check for this command-line scratch script
define('BYPASS_AUTH', true);
require_once __DIR__ . '/../config/Database.php';
use Config\Database;

try {
    $conn = Database::getConnection();
    echo "Conexão com o banco de dados OK!\n\n";

    echo "=== TABELA: justificativas ===\n";
    $stmt = $conn->query("SELECT id, id_funcionario, data_registro, campo_ponto, texto, status, anexos FROM justificativas ORDER BY id DESC LIMIT 5");
    $justs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (empty($justs)) {
        echo "Nenhuma justificativa cadastrada na tabela 'justificativas'.\n";
    } else {
        print_r($justs);
    }

    echo "\n=== TABELA: registros (Últimos 5) ===\n";
    $stmt = $conn->query("SELECT id, id_funcionario, data, primeiro_ponto, segundo_ponto, terceiro_ponto, quarto_ponto, enviado_crh, status_crh FROM registros ORDER BY id DESC LIMIT 5");
    $regs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    print_r($regs);

} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
