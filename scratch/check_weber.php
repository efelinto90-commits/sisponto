<?php
require_once 'config/Database.php';
use Config\Database;

$conn = Database::getConnection();
$stmt = $conn->prepare("SELECT matricula, nome, facial_descriptor FROM funcionarios WHERE nome ILIKE '%weber%'");
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($results)) {
    echo "Nenhum funcionário encontrado com o nome 'weber'.\n";
} else {
    foreach ($results as $r) {
        echo "Matricula: " . $r['matricula'] . "\n";
        echo "Nome: " . $r['nome'] . "\n";
        echo "Tem Descriptor: " . (!empty($r['facial_descriptor']) ? "SIM" : "NÃO") . "\n";
        if (!empty($r['facial_descriptor'])) {
            echo "Descriptor Length: " . strlen($r['facial_descriptor']) . "\n";
            $decoded = json_decode($r['facial_descriptor'], true);
            echo "Decoded Count: " . (is_array($decoded) ? count($decoded) : "ERRO") . "\n";
        }
        echo "-------------------\n";
    }
}
