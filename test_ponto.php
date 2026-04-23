<?php
require_once 'config/Database.php';
use Config\Database;

function testPonto($matricula, $senha) {
    $url = 'http://localhost:81/sisponto/api/ponto.php';
    $data = [
        'action' => 'ponto_matricula',
        'matricula' => $matricula,
        'senha' => $senha,
        'lat' => -23.0,
        'lng' => -45.0
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo "Testing Matricula: $matricula, Password: '$senha'\n";
    echo "HTTP Status: $httpCode\n";
    echo "Response: $response\n\n";
}

// You might need to change the matricula to a real one for accurate testing
// testPonto('1', ''); 
?>
