<?php
// Limpa/valida todos os facial_descriptor corrompidos no banco
require_once 'c:/xampp/htdocs/sisponto/config/Database.php';
$conn = Config\Database::getConnection();

$stmt = $conn->query("SELECT id, nome, facial_descriptor::text as facial_descriptor FROM ponto.funcionarios WHERE facial_descriptor::text IS NOT NULL AND facial_descriptor::text != '' AND facial_descriptor::text != 'null'");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$fixed = 0;
$valid = 0;
foreach ($rows as $r) {
    $decoded = json_decode($r['facial_descriptor'], true);
    if (!is_array($decoded) || empty($decoded)) {
        echo "INVÁLIDO: {$r['nome']} (id={$r['id']}) — limpando...\n";
        $conn->prepare("UPDATE ponto.funcionarios SET facial_descriptor = NULL, biometria_facial = NULL WHERE id = :id")
             ->execute([':id' => $r['id']]);
        $fixed++;
    } else {
        $valid++;
    }
}

echo "\nVálidos: $valid | Corrigidos (limpos): $fixed\n";
