<?php
require 'config/Database.php'; 
$db = \Config\Database::getConnection(); 
$stmt = $db->query("SELECT column_name, data_type FROM information_schema.columns WHERE table_schema = 'ponto' AND table_name = 'funcionarios' AND (column_name LIKE '%lat%' OR column_name LIKE '%long%' OR column_name LIKE '%permitida%' OR column_name LIKE '%geofencing%')"); 
print_r($stmt->fetchAll());
