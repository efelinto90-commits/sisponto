<?php
require_once 'config/Database.php';
$conn = Config\Database::getConnection();
$row = $conn->query("SELECT matricula, nome, biometria_facial, facial_descriptor FROM funcionarios WHERE facial_descriptor IS NOT NULL AND facial_descriptor != '' AND facial_descriptor != '[]' LIMIT 1")->fetch();
if ($row) {
    echo "Matricula: " . $row['matricula'] . "\n";
    echo "Nome: " . $row['nome'] . "\n";
    echo "Biometria Facial (length): " . strlen($row['biometria_facial'] ?? '') . "\n";
    if ($row['biometria_facial']) {
        echo "Biometria Facial (preview): " . substr($row['biometria_facial'], 0, 50) . "...\n";
    }
    echo "Facial Descriptor (preview): " . substr($row['facial_descriptor'], 0, 50) . "...\n";
} else {
    echo "No record found with facial data.\n";
}
