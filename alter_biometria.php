<?php
require_once 'config/Database.php';
try {
    $conn = \Config\Database::getConnection();
    $conn->exec("ALTER TABLE funcionarios ADD COLUMN biometria TEXT NULL");
    echo "Coluna adicionada.\n";
} catch (Exception $e) {
    if (strpos($e->getMessage(), 'already exists') !== false) {
        echo "A coluna biometria ja existe na tabela funcionarios.\n";
    } else {
        echo "Error: " . $e->getMessage();
    }
}
?>