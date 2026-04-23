<?php
require_once 'c:/xampp/htdocs/sisponto/config/Database.php';

function checkCount($config, $name, $date) {
    try {
        $dsn = "pgsql:host={$config['host']};port={$config['port']};dbname={$config['dbname']}";
        $conn = new PDO($dsn, $config['user'], $config['password']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $stmt = $conn->prepare("SELECT count(*) FROM ponto.registros WHERE data = :date");
        $stmt->execute([':date' => $date]);
        echo "$name $date: " . $stmt->fetchColumn() . PHP_EOL;
        
        if ($name === 'Local') {
             // Check total count too
             $stmt = $conn->query("SELECT count(*) FROM ponto.registros");
             echo "$name Total: " . $stmt->fetchColumn() . PHP_EOL;
        }
    } catch (Exception $e) {
        echo "Erro em $name: " . $e->getMessage() . PHP_EOL;
    }
}

$localConf = json_decode(file_get_contents('c:/xampp/htdocs/sisponto/config/local.json'), true);
$prodConf = json_decode(file_get_contents('c:/xampp/htdocs/sisponto/config/prod.json'), true);

$date = '2026-03-12';

checkCount($localConf, 'Local', $date);
checkCount($prodConf, 'Prod', $date);
