<?php
require_once 'c:\xampp\htdocs\sisponto\config\Database.php';

try {
    $conn = Config\Database::getConnection();
    
    $conn->exec("ALTER TABLE registros ADD COLUMN IF NOT EXISTS enviado_crh BOOLEAN DEFAULT FALSE");
    
    echo "Column 'enviado_crh' successfully added or already exists.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
