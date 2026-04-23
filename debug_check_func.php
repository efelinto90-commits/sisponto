<?php
require_once 'c:/xampp/htdocs/sisponto/config/Database.php';
use Config\Database;

try {
    $conn = Database::getConnection();
    
    echo "--- LISTA DE FUNCIONARIOS (últimos 10) ---\n";
    $stmt = $conn->query("SELECT id, nome, matricula FROM funcionarios ORDER BY id DESC LIMIT 10");
    while($row = $stmt->fetch()) {
        echo "ID: " . $row['id'] . " | Nome: " . $row['nome'] . " | Matricula: " . $row['matricula'] . "\n";
    }

    echo "\n--- BUSCA DIRETA PELA MATRICULA 2236-5 ---\n";
    $stmt = $conn->prepare("SELECT id, nome, matricula FROM funcionarios WHERE matricula LIKE '%2236%'");
    $stmt->execute();
    while($row = $stmt->fetch()) {
        echo "MATCH: ID: " . $row['id'] . " | Nome: " . $row['nome'] . " | Matricula: " . $row['matricula'] . "\n";
    }

} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
