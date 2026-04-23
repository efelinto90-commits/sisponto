<?php
$dbs = ['postgres', 'sistema_reservas', 'sistema_restaurante', 'sis-ponto', 'sistema_inventario', 'sistema_transportes', 'sisequipe', 'central_libras'];
foreach ($dbs as $db) {
    try {
        $pdo = new PDO("pgsql:host=127.0.0.1;port=5432;dbname=$db", 'postgres', 'master10');
        $stmt = $pdo->query("SELECT table_schema, table_name FROM information_schema.tables WHERE table_name = 'registros'");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $schema = $row['table_schema'];
            $count = $pdo->query("SELECT count(*) FROM $schema.registros")->fetchColumn();
            if ($count > 0) {
                echo "DATABASE: $db | SCHEMA: $schema | ROWS: $count\n";
            }
        }
    } catch (Exception $e) {}
}
