<?php
require_once 'config/Database.php';
use Config\Database;

try {
    $conn = Database::getConnection();
    $out = "";
    
    $out .= "=== HORARIOS ===\n";
    $stmt = $conn->query("SELECT * FROM ponto.horarios");
    $horarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($horarios as $h) {
        $out .= json_encode($h) . "\n";
    }
    
    $out .= "\n=== REGISTROS (Last 20) ===\n";
    $stmt = $conn->query("SELECT r.*, f.nome as func_nome FROM ponto.registros r JOIN ponto.funcionarios f ON r.id_funcionario = f.id ORDER BY r.id DESC LIMIT 20");
    $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($registros as $r) {
        $out .= json_encode($r) . "\n";
    }

    file_put_contents('tmp_ponto_debug.txt', $out);
    echo "SUCCESS\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
