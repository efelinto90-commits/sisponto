<?php
require_once 'c:\xampp\htdocs\sisponto\config\Database.php';

try {
    $conn = Config\Database::getConnection();
    
    // Add setor column if it doesn't exist
    $conn->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS setor VARCHAR(255) DEFAULT NULL");
    
    echo "Column 'setor' successfully added or already exists.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
