<?php
require_once 'c:/xampp/htdocs/sisponto/config/Database.php';
try {
    $conn = Config\Database::getConnection();
    // Proactive check/add for original geofencing columns
    $conn->exec("ALTER TABLE ponto.funcionarios ADD COLUMN IF NOT EXISTS lat_permitida DECIMAL(11,8)");
    $conn->exec("ALTER TABLE ponto.funcionarios ADD COLUMN IF NOT EXISTS long_permitida DECIMAL(11,8)");
    $conn->exec("ALTER TABLE ponto.funcionarios ADD COLUMN IF NOT EXISTS distancia_max_permitida INT DEFAULT 200");
    
    // List all columns to be sure
    $stmt = $conn->query("SELECT column_name FROM information_schema.columns WHERE table_schema = 'ponto' AND table_name = 'funcionarios'");
    $cols = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Columns: " . implode(", ", $cols);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
