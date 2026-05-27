<?php
require_once 'c:/xampp/htdocs/sisponto/config/Database.php';
$conn = Config\Database::getConnection();

echo "Executando script de otimização..." . PHP_EOL;

$sql = file_get_contents('c:/xampp/htdocs/sisponto/OPTIMIZATION_INDEXES.sql');
// Remove SET search_path e outros comandos incompatíveis se necessário, mas PDO costuma aceitar múltiplos comandos se o driver permitir (PostgreSQL costuma permitir)
// No entanto, é melhor separar por comandos.

$commands = explode(';', $sql);
foreach ($commands as $cmd) {
    $cmd = trim($cmd);
    if (empty($cmd) || strpos($cmd, '--') === 0) continue;
    
    try {
        $conn->exec($cmd);
        echo "OK: " . substr($cmd, 0, 50) . "..." . PHP_EOL;
    } catch (Exception $e) {
        echo "ERRO: " . $e->getMessage() . PHP_EOL;
    }
}

echo "Finalizado." . PHP_EOL;
