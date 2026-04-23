<?php
$host = '10.20.10.3';
$user = 'postgres';
$pass = 'gres4post';
$db = 'funad';

try {
    $pdo = new PDO("pgsql:host=$host;port=5432;dbname=$db", $user, $pass, [PDO::ATTR_TIMEOUT => 5]);
    $count = $pdo->query("SELECT count(*) FROM ponto.registros")->fetchColumn();
    echo "PROD (ponto.registros): $count\n";
    
    // Check if there are other schemas with 'registros'
    $stmt = $pdo->query("SELECT table_schema FROM information_schema.tables WHERE table_name = 'registros' AND table_schema NOT LIKE 'pg_%'");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $s = $row['table_schema'];
        if ($s !== 'ponto') {
            $c = $pdo->query("SELECT count(*) FROM \"$s\".registros")->fetchColumn();
            echo "PROD ($s.registros): $c\n";
        }
    }
} catch (Exception $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
}
