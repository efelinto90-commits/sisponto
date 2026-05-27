<?php
require_once '../config/Database.php';
use Config\Database;
$conn = Database::getConnection();
$stmt = $conn->prepare("SELECT matricula, nome, biometria_facial FROM funcionarios WHERE matricula = '6' OR id = 6");
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ($row) {
    echo "Matricula: " . $row['matricula'] . "<br>";
    echo "Nome: " . $row['nome'] . "<br>";
    echo "Caminho no DB: [" . $row['biometria_facial'] . "]<br>";
} else {
    echo "Funcionário não encontrado.";
}
?>
