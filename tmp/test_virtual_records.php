<?php
// Mocking session and GET for api/relatorios.php testing
session_start();
$_SESSION['user_level'] = 1; // Admin
$_SESSION['user_name'] = 'suporte';
$_SESSION['user_setor'] = 'TI';

$_GET['start_date'] = '2026-04-01';
$_GET['end_date'] = '2026-04-05';
// We'll need a valid func_id. Let's find one first.
require_once 'config/Database.php';
$conn = Config\Database::getConnection();
$f = $conn->query("SELECT id FROM funcionarios LIMIT 1")->fetch();
if ($f) {
    $_GET['func_id'] = $f['id'];
} else {
    die("No employees found.");
}

// Redirecting output to capture it
ob_start();
chdir('api');
include 'relatorios.php';
chdir('..');
$output = ob_get_clean();
$output = trim($output);

// Find the last line which should be the JSON
$lines = explode("\n", $output);
$jsonStr = end($lines);
$data = json_decode($jsonStr, true);
if ($data && $data['success']) {
    echo "Success: Found " . count($data['data']) . " records.\n";
    foreach ($data['data'] as $r) {
        echo "Date: {$r['data']} | ID: {$r['id']} | Virtual: " . ($r['virtual'] ? 'YES' : 'NO') . " | Name: {$r['nome']}\n";
    }
} else {
    echo "Error: " . ($data['message'] ?? 'Unknown error') . "\n";
    echo "Raw output: " . substr($output, 0, 500) . "\n";
}
