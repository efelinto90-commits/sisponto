<?php
require 'config/Database.php';

try {
    $db = \Config\Database::getConnection();
    
    // Check if columns exist first
    $stmt = $db->query("SELECT column_name FROM information_schema.columns WHERE table_schema = 'ponto' AND table_name = 'funcionarios'");
    $columns = $stmt->fetchAll(\PDO::FETCH_COLUMN);
    
    $queries = [];
    if (!in_array('turno1_inicio', $columns)) $queries[] = "ALTER TABLE ponto.funcionarios ADD COLUMN turno1_inicio TIME;";
    if (!in_array('turno1_fim', $columns)) $queries[] = "ALTER TABLE ponto.funcionarios ADD COLUMN turno1_fim TIME;";
    if (!in_array('turno2_inicio', $columns)) $queries[] = "ALTER TABLE ponto.funcionarios ADD COLUMN turno2_inicio TIME;";
    if (!in_array('turno2_fim', $columns)) $queries[] = "ALTER TABLE ponto.funcionarios ADD COLUMN turno2_fim TIME;";
    if (!in_array('lat_permitida_2', $columns)) $queries[] = "ALTER TABLE ponto.funcionarios ADD COLUMN lat_permitida_2 NUMERIC;";
    if (!in_array('long_permitida_2', $columns)) $queries[] = "ALTER TABLE ponto.funcionarios ADD COLUMN long_permitida_2 NUMERIC;";
    if (!in_array('distancia_max_permitida_2', $columns)) $queries[] = "ALTER TABLE ponto.funcionarios ADD COLUMN distancia_max_permitida_2 INTEGER;";
    if (!in_array('area_geofencing_2', $columns)) $queries[] = "ALTER TABLE ponto.funcionarios ADD COLUMN area_geofencing_2 VARCHAR(1000);";

    foreach ($queries as $sql) {
        $db->exec($sql);
        echo "Executed: $sql\n";
    }
    
    echo "Migration completed successfully.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
