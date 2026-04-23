<?php
require_once 'c:/xampp/htdocs/sisponto/config/Database.php';
use Config\Database;
try {
    $conn = Database::getConnection();
    $sql = "ALTER TABLE ponto.funcionarios ADD COLUMN IF NOT EXISTS grade_horarios JSONB DEFAULT '{}'";
    $conn->exec($sql);
    echo "Coluna grade_horarios adicionada com sucesso ou já existente.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
