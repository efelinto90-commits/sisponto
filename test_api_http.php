<?php
// Simula exatamente o que o cartão de ponto chama
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost:81/sisponto/api/relatorios.php?start_date=2026-03-01&end_date=2026-03-11&func_id=');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_COOKIE, 'PHPSESSID=test');
$resp = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP: $httpCode\n";
$json = json_decode($resp, true);
if (!$json) {
    echo "RAW (primeiros 500 chars): " . substr($resp, 0, 500) . "\n";
} else {
    echo "success: " . ($json['success'] ? 'TRUE' : 'FALSE') . "\n";
    echo "data count: " . (isset($json['data']) ? count($json['data']) : 'NOT SET') . "\n";
    if (!isset($json['data'])) {
        echo "Keys: " . implode(', ', array_keys($json)) . "\n";
        echo "message: " . ($json['message'] ?? 'N/A') . "\n";
    }
}
