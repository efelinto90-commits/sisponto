<?php
require_once 'config/Database.php';
$conn = Config\Database::getConnection();
$stmt = $conn->query("SELECT column_name, data_type FROM information_schema.columns WHERE table_name = 'funcionarios'");
while($row = $stmt->fetch()) {
    echo $row['column_name'] . " (" . $row['data_type'] . ")\n";
}
