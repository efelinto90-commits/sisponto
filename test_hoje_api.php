<?php
// Simula a chamada da API relatorios.php para hoje
require_once 'c:/xampp/htdocs/sisponto/config/Database.php';
$conn = Config\Database::getConnection();

$startDate = '2026-03-11';
$endDate   = '2026-03-11';

$sql = "
    SELECT
        r.id, r.data, f.nome, f.matricula,
        r.primeiro_ponto, r.segundo_ponto, r.terceiro_ponto, r.quarto_ponto,
        r.atrasou_primeiro_ponto, r.atrasou_segundo_ponto, r.atrasou_terceiro_ponto, r.atrasou_quarto_ponto,
        r.justificativa, r.status_turno1, r.status_turno2,
        r.just_ent1, r.just_sai1, r.just_ent2, r.just_sai2,
        h.primeiro_horario, h.segundo_horario, h.terceiro_horario, h.quarto_horario, h.tolerancia_entrada, h.tolerancia_saida
    FROM registros r
    JOIN funcionarios f ON r.id_funcionario = f.id
    LEFT JOIN horarios h ON f.id_horario = h.id
    WHERE r.data BETWEEN :start AND :end
    ORDER BY r.data DESC, f.nome ASC
";

$stmt = $conn->prepare($sql);
$stmt->execute([':start' => $startDate, ':end' => $endDate]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "COUNT: " . count($rows) . "\n";
foreach (array_slice($rows, 0, 5) as $r) {
    echo "{$r['nome']} | {$r['data']} | P1:{$r['primeiro_ponto']} P2:{$r['segundo_ponto']}\n";
}

// Also check if facial_descriptor is set for any funcionario
echo "\n=== FUNCIONÁRIOS COM FACIAL CADASTRADO ===\n";
$stmt2 = $conn->query("SELECT id, nome, matricula FROM ponto.funcionarios WHERE facial_descriptor IS NOT NULL AND facial_descriptor != ''");
while ($r = $stmt2->fetch(PDO::FETCH_ASSOC)) {
    echo "- {$r['matricula']} - {$r['nome']}\n";
}
