<?php
require_once 'config/Database.php';
use Config\Database;

try {
    $conn = Database::getConnection();
    
    echo "PRESETS SALVOS:\n";
    $stmt = $conn->query("SELECT * FROM geofencing_presets ORDER BY nome_area ASC");
    var_dump($stmt->fetchAll(PDO::FETCH_ASSOC));

    echo "\nAREAS ATRIBUIDAS AOS FUNCIONARIOS:\n";
    $stmt2 = $conn->query("SELECT DISTINCT area_geofencing FROM funcionarios ORDER BY area_geofencing ASC");
    var_dump($stmt2->fetchAll(PDO::FETCH_COLUMN));
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
