<?php
require_once '../config/Database.php';
use Config\Database;
$conn = Database::getConnection();
$stmt = $conn->query("SELECT id, matricula, nome FROM funcionarios WHERE id = 6");
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ($row) {
    echo "ID: {$row['id']}<br>Matricula: {$row['matricula']}<br>Nome: {$row['nome']}";
} else {
    echo "ID 6 não encontrado.";
}
?>
