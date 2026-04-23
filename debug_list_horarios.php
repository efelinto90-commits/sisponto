<?php
require_once 'c:/xampp/htdocs/sisponto/config/Database.php';
use Config\Database;

try {
    $conn = Database::getConnection();
    
    echo "--- HORARIOS DISPONIVEIS ---\n";
    $stmt = $conn->query("SELECT id, nome, primeiro_horario, quarto_horario FROM horarios ORDER BY id ASC");
    while($row = $stmt->fetch()) {
        echo "ID: " . $row['id'] . " | Nome: " . $row['nome'] . " | " . $row['primeiro_horario'] . " - " . $row['quarto_horario'] . "\n";
    }

} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
