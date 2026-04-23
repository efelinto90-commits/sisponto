<?php
require_once 'c:/xampp/htdocs/sisponto/config/Database.php';
try {
    $conn = Config\Database::getConnection();
    $conn->exec("ALTER TABLE ponto.funcionarios ADD COLUMN IF NOT EXISTS area_geofencing VARCHAR(255)");
    echo "Success: Column area_geofencing ensured.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
