<?php
require_once 'c:/xampp/htdocs/sisponto/config/Database.php';

try {
    $conn = Config\Database::getConnection();
    
    // Altera para NUMERIC sem limite de precisão para evitar estouro
    $conn->exec("ALTER TABLE ponto.funcionarios ALTER COLUMN lat_permitida TYPE NUMERIC");
    $conn->exec("ALTER TABLE ponto.funcionarios ALTER COLUMN long_permitida TYPE NUMERIC");
    
    $conn->exec("ALTER TABLE ponto.geofencing_presets ALTER COLUMN latitude TYPE NUMERIC");
    $conn->exec("ALTER TABLE ponto.geofencing_presets ALTER COLUMN longitude TYPE NUMERIC");
    
    echo "Success: Columns altered to NUMERIC (unlimited) to prevent overflow.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
