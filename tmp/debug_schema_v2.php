<?php
require_once 'c:/xampp/htdocs/sisponto/config/Database.php';
try {
    $conn = Config\Database::getConnection();
    $sql = "SELECT table_name, column_name, data_type, numeric_precision, numeric_scale 
            FROM information_schema.columns 
            WHERE table_schema = 'ponto' 
            AND table_name IN ('funcionarios', 'geofencing_presets')
            ORDER BY table_name, column_name";
    $stmt = $conn->query($sql);
    $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
    file_put_contents('c:/xampp/htdocs/sisponto/tmp/schema_dump.json', json_encode($res, JSON_PRETTY_PRINT));
    echo "Dump saved to tmp/schema_dump.json";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
