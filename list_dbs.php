<?php
try {
    $c = new PDO('pgsql:host=127.0.0.1;port=5432;dbname=postgres', 'postgres', 'master10');
    $stmt = $c->query("SELECT datname FROM pg_database WHERE datistemplate = false");
    while ($row = $stmt->fetch()) {
        echo $row['datname'] . "\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
