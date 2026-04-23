<?php
require_once 'c:/xampp/htdocs/sisponto/config/Database.php';
$conn = Config\Database::getConnection();

echo "=== REGISTROS DE HOJE (2026-03-11) ===\n";
$stmt = $conn->query("SELECT r.id, r.id_funcionario, f.nome, f.matricula, r.data, r.primeiro_ponto, r.segundo_ponto, r.terceiro_ponto, r.quarto_ponto, r.created_at FROM ponto.registros r JOIN ponto.funcionarios f ON r.id_funcionario = f.id WHERE r.data = '2026-03-11' ORDER BY r.created_at");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo count($rows) . " registros encontrados\n\n";
foreach ($rows as $r) {
    echo "ID:{$r['id']} | {$r['matricula']} - {$r['nome']} | P1:{$r['primeiro_ponto']} P2:{$r['segundo_ponto']} P3:{$r['terceiro_ponto']} P4:{$r['quarto_ponto']} | criado:{$r['created_at']}\n";
}

echo "\n=== TOTAL POR DATA RECENTE ===\n";
$stmt2 = $conn->query("SELECT data, count(*) FROM ponto.registros WHERE data >= '2026-03-01' GROUP BY data ORDER BY data DESC LIMIT 15");
while ($r = $stmt2->fetch(PDO::FETCH_ASSOC)) {
    echo $r['data'] . ": " . $r['count'] . "\n";
}
