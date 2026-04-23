<?php
require_once 'config/Database.php';
use Config\Database;

try {
    $conn = Database::getConnection();
    
    echo "Checking for geofencing_presets table...\n";
    
    $sql = "CREATE TABLE IF NOT EXISTS geofencing_presets (
        id SERIAL PRIMARY KEY,
        nome_area VARCHAR(255) UNIQUE NOT NULL,
        latitude NUMERIC NOT NULL,
        longitude NUMERIC NOT NULL,
        distancia_max INTEGER DEFAULT 200,
        created_at TIMESTAMP DEFAULT NOW(),
        updated_at TIMESTAMP DEFAULT NOW()
    )";
    
    $conn->exec($sql);
    echo "Table geofencing_presets created or already exists.\n";
    
    // Add comment for documentation
    $conn->exec("COMMENT ON TABLE geofencing_presets IS 'Predefinições de áreas de geofencing para cadastro rápido de funcionários'");

    echo "Migration complete.\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
