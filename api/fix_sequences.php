<?php
require_once __DIR__ . '/../config/Database.php';
use Config\Database;

header('Content-Type: text/plain');

try {
    $conn = Database::getConnection();
    
    $tables = ['registros', 'funcionarios', 'users', 'horarios', 'geofencing_presets'];
    $schema = 'ponto';
    
    echo "Iniciando correção de sequências no schema '$schema'...\n\n";
    
    foreach ($tables as $table) {
        try {
            // Pega o nome da sequência
            $seqNameResult = $conn->query("SELECT pg_get_serial_sequence('$schema.$table', 'id')");
            $seqName = $seqNameResult->fetchColumn();
            
            if (!$seqName) {
                echo "[-] Tabela $table: Sequência não encontrada para a coluna 'id'.\n";
                continue;
            }
            
            // Pega o valor máximo atual
            $maxIdResult = $conn->query("SELECT MAX(id) FROM $schema.$table");
            $maxId = $maxIdResult->fetchColumn();
            
            if ($maxId === null) {
                echo "[!] Tabela $table: Tabela vazia, pulando...\n";
                continue;
            }
            
            // Atualiza a sequência
            $newVal = $maxId + 1;
            $conn->exec("SELECT setval('$seqName', $maxId)");
            
            echo "[+] Tabela $table: Sequência '$seqName' atualizada para $maxId (próximo ID: $newVal).\n";
            
        } catch (Exception $e) {
            echo "[X] Erro ao processar tabela $table: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\nConcluído!";
    
} catch (Exception $e) {
    echo "ERRO GERAL: " . $e->getMessage();
}
