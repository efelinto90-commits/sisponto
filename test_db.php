<?php
$databases = ['funad', 'sis-ponto'];
foreach ($databases as $db) {
    echo "Testing $db... ";
    try {
        $c = new PDO("pgsql:host=127.0.0.1;port=5432;dbname=$db", 'postgres', 'master10', [PDO::ATTR_TIMEOUT => 2]);
        echo "SUCCESS!\n";
        $stmt = $c->query("SELECT schema_name FROM information_schema.schemata WHERE schema_name = 'ponto'");
        if ($stmt->fetch()) {
            echo "  Schema 'ponto' found!\n";
            $stmt2 = $c->query("SELECT count(*) FROM ponto.funcionarios");
            echo "  Funcionarios count: " . $stmt2->fetchColumn() . "\n";
        } else {
            echo "  Schema 'ponto' NOT found.\n";
        }
    } catch (Exception $e) {
        echo "FAILED: " . $e->getMessage() . "\n";
    }
}
