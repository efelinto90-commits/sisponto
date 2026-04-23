<?php
$host = '10.20.10.3';
$user = 'postgres';
$pass = 'gres4post';
$db = 'funad';

try {
    $pdo = new PDO("pgsql:host=$host;port=5432;dbname=$db", $user, $pass, [PDO::ATTR_TIMEOUT => 5]);
    $stmt = $pdo->query("SELECT column_name FROM information_schema.columns WHERE table_schema = 'ponto' AND table_name = 'registros' ORDER BY ordinal_position");
    echo "PROD COLUMNS:\n";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "- " . $row['column_name'] . "\n";
    }
} catch (Exception $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
}
