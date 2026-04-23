<?php
require_once 'config/Database.php';
use Config\Database;
try {
    $conn = Database::getConnection();
    $stmt = $conn->query("SELECT matricula FROM funcionarios LIMIT 10");
    $rows = $stmt->fetchAll();
    echo "Matrículas encontradas:\n";
    foreach ($rows as $row) {
        echo "- " . $row['matricula'] . "\n";
    }
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
