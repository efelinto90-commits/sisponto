<?php
$host = '10.20.10.3';
$user = 'postgres';
$pass = 'gres4post';
$db = 'funad';

try {
    $pdo = new PDO("pgsql:host=$host;port=5432;dbname=$db", $user, $pass, [PDO::ATTR_TIMEOUT => 5]);
    echo "SUCCESS: Connected to $host\n";
    $stmt = $pdo->query("SELECT nspname FROM pg_namespace WHERE nspname NOT LIKE 'pg_%' AND nspname != 'information_schema'");
    echo "SCHEMAS:\n";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "- " . $row['nspname'] . "\n";
    }
} catch (Exception $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
}
