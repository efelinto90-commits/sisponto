<?php
require_once 'config/Database.php';
$conn = Config\Database::getConnection();

$stmt = $conn->query("
    SELECT column_name, data_type 
    FROM information_schema.columns 
    WHERE table_name = 'funcionarios'
");
$cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
file_put_contents('schema_funcionarios.json', json_encode($cols, JSON_PRETTY_PRINT));
echo "OK";
