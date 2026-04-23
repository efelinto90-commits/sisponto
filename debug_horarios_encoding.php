<?php
require_once 'c:/xampp/htdocs/sisponto/config/Database.php';
use Config\Database;

try {
    $conn = Database::getConnection();
    $stmt = $conn->query("SELECT id, nome FROM horarios");
    echo "ID | NOME | HEX | ENCODING\n";
    echo str_repeat("-", 50) . "\n";
    while($row = $stmt->fetch()) {
        $hex = bin2hex($row['nome']);
        $encoding = mb_detect_encoding($row['nome'], 'UTF-8, ISO-8859-1, Windows-1252', true);
        echo $row['id'] . " | " . $row['nome'] . " | " . $hex . " | " . $encoding . "\n";
    }
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
