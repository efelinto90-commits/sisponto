<?php
require_once 'config/Database.php';
use Config\Database;

try {
    $conn = Database::getConnection();
    $stmt = $conn->query("SELECT DISTINCT setor FROM funcionarios WHERE setor IS NOT NULL AND setor != '' UNION SELECT DISTINCT setor2 FROM funcionarios WHERE setor2 IS NOT NULL AND setor2 != ''");
    $setores = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Setores encontrados:\n";
    foreach ($setores as $s) {
        echo "'$s'\n";
    }
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
