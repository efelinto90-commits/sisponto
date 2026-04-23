<?php
require_once 'c:/xampp/htdocs/sisponto/config/Database.php';

function checkRecent($config, $name) {
    try {
        $dsn = "pgsql:host={$config['host']};port={$config['port']};dbname={$config['dbname']}";
        $conn = new PDO($dsn, $config['user'], $config['password']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        echo "--- $name Recent ---" . PHP_EOL;
        $stmt = $conn->query("SELECT r.id, r.data, f.nome, r.created_at FROM ponto.registros r JOIN ponto.funcionarios f ON r.id_funcionario = f.id ORDER BY r.created_at DESC LIMIT 5");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "ID:{$row['id']} | Data:{$row['data']} | Func:{$row['nome']} | Criado:{$row['created_at']}" . PHP_EOL;
        }
        
    } catch (Exception $e) {
        echo "Erro em $name: " . $e->getMessage() . PHP_EOL;
    }
}

$localConf = json_decode(file_get_contents('c:/xampp/htdocs/sisponto/config/local.json'), true);
$prodConf = json_decode(file_get_contents('c:/xampp/htdocs/sisponto/config/prod.json'), true);

checkRecent($localConf, 'Local');
checkRecent($prodConf, 'Prod');
