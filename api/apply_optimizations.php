<?php
require_once __DIR__ . '/../config/Database.php';
use Config\Database;

header('Content-Type: text/plain');

try {
    $conn = Database::getConnection();
    $sql = file_get_contents(__DIR__ . '/../OPTIMIZATION_INDEXES.sql');
    
    // Remove comments and multi-statement execute
    // Note: PDO exec doesn't support multiple statements in some drivers, 
    // but pgsql usually does. We'll split by semicolon just in case.
    
    $statements = explode(';', $sql);
    $count = 0;
    foreach ($statements as $stmt) {
        $stmt = trim($stmt);
        if (empty($stmt) || strpos($stmt, '--') === 0) continue;
        
        try {
            $conn->exec($stmt);
            $count++;
            echo "Executado: " . substr($stmt, 0, 50) . "...\n";
        } catch (Exception $e) {
            echo "ERRO em: " . substr($stmt, 0, 50) . "...\n";
            echo "MENSAGEM: " . $e->getMessage() . "\n\n";
        }
    }
    
    echo "\nTotal de $count comandos executados com sucesso.\n";
} catch (Exception $e) {
    echo "ERRO GERAL: " . $e->getMessage();
}
