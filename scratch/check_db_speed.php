<?php
require_once 'config/Database.php';
use Config\Database;

$start = microtime(true);
echo "Conectando ao banco...\n";
try {
    $conn = Database::getConnection();
    echo "Conectado. Tempo: " . (microtime(true) - $start) . "s\n";

    $startDate = date('Y-m-01');
    $endDate = date('Y-m-t');
    
    echo "Buscando registros ($startDate a $endDate)...\n";
    $queryStart = microtime(true);
    
    $sql = "SELECT count(*) FROM registros WHERE data BETWEEN ? AND ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$start, $endDate]);
    $count = $stmt->fetchColumn();
    echo "Total registros no mês: $count. Tempo: " . (microtime(true) - $queryStart) . "s\n";

    echo "Buscando funcionários...\n";
    $fStart = microtime(true);
    $stmtF = $conn->query("SELECT count(*) FROM funcionarios");
    $fCount = $stmtF->fetchColumn();
    echo "Total funcionários: $fCount. Tempo: " . (microtime(true) - $fStart) . "s\n";

} catch (Exception $e) {
    echo "ERRO: " . $e->getMessage() . "\n";
}
echo "Tempo total: " . (microtime(true) - $start) . "s\n";
