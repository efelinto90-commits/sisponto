<?php
require_once 'c:/xampp/htdocs/sisponto/config/Database.php';
$conn = Config\Database::getConnection();

$tables = ['ponto.funcionarios', 'ponto.registros', 'ponto.ferias', 'ponto.users'];
echo "Verificando índices..." . PHP_EOL;

foreach ($tables as $table) {
    echo "\nTabela: $table\n";
    $stmt = $conn->prepare("SELECT indexname, indexdef FROM pg_indexes WHERE schemaname = 'ponto' AND tablename = :table");
    $tableName = str_replace('ponto.', '', $table);
    $stmt->execute([':table' => $tableName]);
    $indexes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($indexes)) {
        echo "  [AVISO] Nenhum índice encontrado para esta tabela!\n";
    } else {
        foreach ($indexes as $idx) {
            echo "  - " . $idx['indexname'] . PHP_EOL;
        }
    }
}
