<?php
require_once __DIR__ . '/../config/Database.php';
$db = Config\Database::getConnection();


$s = $db->prepare('SELECT id, nome, foto_perfil, biometria_facial, created_at, updated_at FROM funcionarios WHERE id = 832');
$s->execute();
$row = $s->fetch();
print_r($row);

if ($row) {
    // Check direct root URLs (without /sisponto/)
    $urls = [
        'http://funad.ddns.net:81/' . $row['foto_perfil'],
        'http://funad.ddns.net:8080/' . $row['foto_perfil'],
        'http://funad.ddns.net:5000/' . $row['foto_perfil'], // check deepface port too
    ];
    
    foreach ($urls as $url) {
        echo "\nChecking URL: $url\n";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        echo "HTTP Code: $http_code\n";
    }
}









?>
