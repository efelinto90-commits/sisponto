<?php
require_once 'config/Database.php';
try {
    $conn = \Config\Database::getConnection();
    $conn->exec("ALTER TABLE registros ADD COLUMN just_ent1 TEXT NULL");
    $conn->exec("ALTER TABLE registros ADD COLUMN just_sai1 TEXT NULL");
    $conn->exec("ALTER TABLE registros ADD COLUMN just_ent2 TEXT NULL");
    $conn->exec("ALTER TABLE registros ADD COLUMN just_sai2 TEXT NULL");
    echo "Colunas de justificativa_individual adicionadas com sucesso.\n";
} catch (Exception $e) {
    if (strpos($e->getMessage(), 'already exists') !== false) {
        echo "As colunas ja existem na tabela registros.\n";
    } else {
        echo "Error: " . $e->getMessage();
    }
}
?>