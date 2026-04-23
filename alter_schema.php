<?php
require_once 'c:\xampp\htdocs\sisponto\config\Database.php';
$conn = Config\Database::getConnection();

try {
    $conn->exec("ALTER TABLE registros ADD COLUMN IF NOT EXISTS tipo_justificativa VARCHAR(255);");
    $conn->exec("ALTER TABLE registros ADD COLUMN IF NOT EXISTS anexo_justificativa TEXT;");
    echo "Columns added successfully.\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
