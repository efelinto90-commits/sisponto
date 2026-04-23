<?php
require_once 'c:/xampp/htdocs/sisponto/config/Database.php';

function testApi($startDate, $endDate) {
    echo "--- Testing API ($startDate to $endDate) ---" . PHP_EOL;
    $url = "http://localhost:81/sisponto/api/relatorios.php?start_date=$startDate&end_date=$endDate&func_id=";
    $response = file_get_contents($url);
    if ($response === false) {
        echo "Erro ao chamar API via HTTP. Tentando inclusão direta..." . PHP_EOL;
        $_GET['start_date'] = $startDate;
        $_GET['end_date'] = $endDate;
        $_GET['func_id'] = '';
        ob_start();
        include 'c:/xampp/htdocs/sisponto/api/relatorios.php';
        $response = ob_get_clean();
    }
    
    $json = json_decode($response, true);
    if ($json && isset($json['success']) && $json['success']) {
        echo "Sucesso: " . count($json['data']) . " registros encontrados." . PHP_EOL;
        if (count($json['data']) > 0) {
            echo "Amostra do primeiro registro:" . PHP_EOL;
            print_r($json['data'][0]);
        }
    } else {
        echo "Falha: " . $response . PHP_EOL;
    }
}

testApi('2026-03-12', '2026-03-12');
