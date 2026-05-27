<?php
require_once '../config/Database.php';
use Config\Database;
$conn = Database::getConnection();
$stmt = $conn->query("SELECT id, matricula, nome, biometria_facial FROM funcionarios WHERE biometria_facial IS NOT NULL LIMIT 20");
echo "<table border=1><tr><th>ID</th><th>Matricula</th><th>Nome</th><th>Caminho</th></tr>";
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "<tr><td>{$row['id']}</td><td>{$row['matricula']}</td><td>{$row['nome']}</td><td>{$row['biometria_facial']}</td></tr>";
}
echo "</table>";
?>
