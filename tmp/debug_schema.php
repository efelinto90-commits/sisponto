<?php
require_once 'c:/xampp/htdocs/sisponto/config/Database.php';
try {
    $conn = Config\Database::getConnection();
    $sql = "SELECT table_name, column_name, data_type, numeric_precision, numeric_scale 
            FROM information_schema.columns 
            WHERE table_schema = 'ponto' 
            AND table_name IN ('funcionarios', 'geofencing_presets')
            AND column_name IN ('lat_permitida', 'long_permitida', 'latitude', 'longitude', 'distancia_max_permitida', 'distancia_max')";
    $stmt = $conn->query($sql);
    $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($res, JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
