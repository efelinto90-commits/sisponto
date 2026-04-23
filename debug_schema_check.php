<?php
require_once 'c:/xampp/htdocs/sisponto/config/Database.php';

function checkSchema($config, $name) {
    try {
        $dsn = "pgsql:host={$config['host']};port={$config['port']};dbname={$config['dbname']}";
        $conn = new PDO($dsn, $config['user'], $config['password']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        echo "--- $name Schema ---" . PHP_EOL;
        $stmt = $conn->prepare("SELECT column_name, data_type FROM information_schema.columns WHERE table_schema = 'ponto' AND table_name = 'registros' AND column_name = 'data'");
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "Coluna 'data': " . ($row['data_type'] ?? 'N/A') . PHP_EOL;
        
    } catch (Exception $e) {
        echo "Erro em $name: " . $e->getMessage() . PHP_EOL;
    }
}

$localConf = json_decode(file_get_contents('c:/xampp/htdocs/sisponto/config/local.json'), true);
$prodConf = json_decode(file_get_contents('c:/xampp/htdocs/sisponto/config/prod.json'), true);

checkSchema($localConf, 'Local');
checkSchema($prodConf, 'Prod');
