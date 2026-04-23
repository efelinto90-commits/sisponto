<?php
require_once 'config/Database.php';
use Config\Database;

try {
    $conn = Database::getConnection();
    
    echo "HORARIOS:\n";
    $stmt = $conn->query("SELECT * FROM ponto.horarios");
    $horarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($horarios as $h) {
        echo "ID: {$h['id']} | Nome: {$h['nome']} | H: {$h['primeiro_horario']}, {$h['segundo_horario']}, {$h['terceiro_horario']}, {$h['quarto_horario']} | TolE: {$h['tolerancia_entrada']} | TolS: {$h['tolerancia_saida']}\n";
    }
    
    echo "\nREGISTROS (Last 10):\n";
    $stmt = $conn->query("SELECT * FROM ponto.registros ORDER BY id DESC LIMIT 10");
    $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($registros as $r) {
        echo "ID: {$r['id']} | Func: {$r['id_funcionario']} | Data: {$r['data']} | P1: {$r['primeiro_ponto']} | P2: {$r['segundo_ponto']} | P3: {$r['terceiro_ponto']} | P4: {$r['quarto_ponto']}\n";
    }

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
