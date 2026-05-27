<?php
require_once 'config/Database.php';
use Config\Database;

try {
    $conn = Database::getConnection();
    
    echo "Sectores encontrados com 'COMAP' (setor):\n";
    $stmt = $conn->query("SELECT DISTINCT setor FROM funcionarios WHERE setor ILIKE '%COMAP%'");
    $list = $stmt->fetchAll(PDO::FETCH_COLUMN);
    var_dump($list);

    echo "\nSectores encontrados com 'COMAP' (setor2):\n";
    $stmt2 = $conn->query("SELECT DISTINCT setor2 FROM funcionarios WHERE setor2 ILIKE '%COMAP%'");
    $list2 = $stmt2->fetchAll(PDO::FETCH_COLUMN);
    var_dump($list2);

    // Limpeza de acentos agudos indesejados e aspas simples
    echo "\nExecutando limpeza de acento agudo (´) e aspas (')...\n";
    
    $sql = "UPDATE funcionarios SET 
            setor = REPLACE(REPLACE(setor, '´', ''), '''', ''),
            setor2 = REPLACE(REPLACE(setor2, '´', ''), '''', '')
            WHERE setor LIKE '%´%' OR setor LIKE '%''%' OR setor2 LIKE '%´%' OR setor2 LIKE '%''%'";
            
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    echo "Total de linhas afetadas pela limpeza geral: " . $stmt->rowCount() . "\n";
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
