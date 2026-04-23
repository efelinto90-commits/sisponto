<?php
require_once 'c:/xampp/htdocs/sisponto/config/Database.php';

function getDbConfig($env) {
    $file = "c:/xampp/htdocs/sisponto/config/{$env}.json";
    return json_decode(file_get_contents($file), true);
}

function getPdoConnection($config) {
    $dsn = "pgsql:host={$config['host']};port={$config['port']};dbname={$config['dbname']}";
    $conn = new PDO($dsn, $config['user'], $config['password']);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $schema = $config['schema'] ?? 'ponto';
    $conn->exec("SET search_path TO $schema, public");
    return $conn;
}

try {
    $localConfig = getDbConfig('local');
    $prodConfig = getDbConfig('prod');
    
    $localConn = getPdoConnection($localConfig);
    $prodConn = getPdoConnection($prodConfig);
    
    $table = 'registros';
    echo "Sincronizando tabela $table...\n";
    
    $stmt = $prodConn->query("SELECT * FROM ponto.$table LIMIT 100");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Processando amostra de " . count($rows) . " registros...\n";
    
    $inserted = 0;
    foreach ($rows as $index => $row) {
        $cols = implode(', ', array_map(function($c) { return "\"$c\""; }, array_keys($row)));
        $placeholders = implode(', ', array_fill(0, count($row), '?'));
        
        $sql = "INSERT INTO ponto.$table ($cols) VALUES ($placeholders) ON CONFLICT (id) DO NOTHING";
        try {
            $stmtIns = $localConn->prepare($sql);
            $stmtIns->execute(array_values($row));
            if ($stmtIns->rowCount() > 0) {
                $inserted++;
            } else {
                echo "Linha $index: Pulada (Conflito)\n";
            }
        } catch (Exception $e) {
            echo "ERRO CRITICO na linha $index: " . $e->getMessage() . "\n";
            echo "SQL: $sql\n";
            echo "DATA: " . print_r($row, true);
            die("Parando apos primeiro erro.\n");
        }
    }
    echo "TOTAL INSERIDOS NESTA AMOSTRA: $inserted\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
