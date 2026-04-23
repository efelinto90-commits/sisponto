<?php
try {
    $pdo = new PDO('pgsql:host=127.0.0.1;port=5432;dbname=sis-ponto', 'postgres', 'master10');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->query("SELECT table_schema, table_name FROM information_schema.tables WHERE table_name = 'registros'");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $s = $row['table_schema'];
        $t = $row['table_name'];
        $count = $pdo->query("SELECT count(*) FROM \"$s\".\"$t\"")->fetchColumn();
        echo "SCHEMA: $s | TABLE: $t | ROWS: $count\n";
        if ($count > 0) {
            $data = $pdo->query("SELECT * FROM \"$s\".\"$t\" LIMIT 1")->fetch(PDO::FETCH_ASSOC);
            print_r($data);
        }
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
