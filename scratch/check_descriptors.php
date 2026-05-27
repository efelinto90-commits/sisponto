<?php
require_once 'config/Database.php';
use Config\Database;

$conn = Database::getConnection();
$stmt = $conn->query("SELECT matricula, nome, facial_descriptor FROM funcionarios WHERE facial_descriptor IS NOT NULL LIMIT 5");
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($results as $r) {
    echo "Matricula: " . $r['matricula'] . "\n";
    echo "Nome: " . $r['nome'] . "\n";
    echo "Descriptor Type: " . gettype($r['facial_descriptor']) . "\n";
    echo "Descriptor Start: " . substr(print_r($r['facial_descriptor'], true), 0, 100) . "...\n";
    $decoded = json_decode($r['facial_descriptor'], true);
    echo "Decoded Type: " . gettype($decoded) . "\n";
    if (is_array($decoded)) {
        echo "Decoded Count: " . count($decoded) . "\n";
    }
    echo "-------------------\n";
}
