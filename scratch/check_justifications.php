<?php
require_once __DIR__ . '/../config/Database.php';
use Config\Database;

try {
    $conn = Database::getConnection();
    
    // Check justificativas table
    echo "=== JUSTIFICATIVAS TABLE ===\n";
    $sql = "SELECT j.*, f.nome FROM justificativas j JOIN funcionarios f ON j.id_funcionario = f.id ORDER BY j.id DESC LIMIT 10";
    $stmt = $conn->query($sql);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $row) {
        echo "ID: {$row['id']} | Func: {$row['nome']} | DataReg: {$row['data_registro']} | Texto: {$row['texto']} | Status: {$row['status']} | Anexos: {$row['anexos']}\n";
    }
    
    // Check registros table with anexo_justificativa
    echo "\n=== REGISTROS TABLE (anexo_justificativa) ===\n";
    $sql2 = "SELECT r.id, r.data, r.anexo_justificativa, r.justificativa, r.tipo_justificativa, f.nome 
             FROM registros r 
             JOIN funcionarios f ON r.id_funcionario = f.id 
             WHERE r.anexo_justificativa IS NOT NULL AND r.anexo_justificativa <> '' AND r.anexo_justificativa <> '[]' AND r.anexo_justificativa <> 'null'
             ORDER BY r.data DESC LIMIT 10";
    $stmt2 = $conn->query($sql2);
    $rows2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows2 as $row) {
        echo "ID: {$row['id']} | Func: {$row['nome']} | Data: {$row['data']} | Just: {$row['justificativa']} | Tipo: {$row['tipo_justificativa']} | Anexo: {$row['anexo_justificativa']}\n";
    }

    // Check registros table with anexo_comunicado
    echo "\n=== REGISTROS TABLE (anexo_comunicado) ===\n";
    $sql3 = "SELECT r.id, r.data, r.anexo_comunicado, r.comunicado, f.nome 
             FROM registros r 
             JOIN funcionarios f ON r.id_funcionario = f.id 
             WHERE r.anexo_comunicado IS NOT NULL AND r.anexo_comunicado <> '' AND r.anexo_comunicado <> '[]' AND r.anexo_comunicado <> 'null'
             ORDER BY r.data DESC LIMIT 10";
    $stmt3 = $conn->query($sql3);
    $rows3 = $stmt3->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows3 as $row) {
        echo "ID: {$row['id']} | Func: {$row['nome']} | Data: {$row['data']} | Comunicado: {$row['comunicado']} | Anexo: {$row['anexo_comunicado']}\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
