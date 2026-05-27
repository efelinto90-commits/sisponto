<?php
require_once 'config/Database.php';
use Config\Database;

try {
    $conn = Database::getConnection();
    
    echo "Iniciando limpeza agressiva de setores (removendo tabs e normalizando case)...\n";
    
    // Usando REGEXP_REPLACE para remover espaços em branco de qualquer tipo no início e fim
    $sql1 = "UPDATE funcionarios SET setor = UPPER(REGEXP_REPLACE(setor, '^\s+|\s+$', '', 'g')) WHERE setor IS NOT NULL AND setor != ''";
    $count1 = $conn->exec($sql1);
    echo "Setor 1 atualizado: $count1 registros.\n";
    
    $sql2 = "UPDATE funcionarios SET setor2 = UPPER(REGEXP_REPLACE(setor2, '^\s+|\s+$', '', 'g')) WHERE setor2 IS NOT NULL AND setor2 != ''";
    $count2 = $conn->exec($sql2);
    echo "Setor 2 atualizado: $count2 registros.\n";
    
    echo "Limpeza concluída.\n";
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
