<?php
try {
    $pdo = new PDO('pgsql:host=127.0.0.1;port=5432;dbname=sis-ponto', 'postgres', 'master10');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "### REGISTROS ###\n";
    $stmt = $pdo->query("SELECT column_name FROM information_schema.columns WHERE table_schema = 'ponto' AND table_name = 'registros'");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) echo $row['column_name'] . "\n";

    echo "\n### HORARIOS ###\n";
    $stmt = $pdo->query("SELECT column_name FROM information_schema.columns WHERE table_schema = 'ponto' AND table_name = 'horarios'");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) echo $row['column_name'] . "\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
