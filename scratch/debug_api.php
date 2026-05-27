<?php
// Mock input for get_meu_ponto
$_SERVER['REQUEST_METHOD'] = 'POST';
$input = [
    'action' => 'get_meu_ponto',
    'matricula' => '000000', // Use a valid matricula or mock it
    'start_date' => date('Y-m-01'),
    'end_date' => date('Y-m-t')
];

// Mock the raw input
$tempFile = tempnam(sys_get_temp_dir(), 'php_input');
file_put_contents($tempFile, json_encode($input));

// We can't easily mock php://input for the script being included, 
// but we can try to run it as a separate process or modify the script temporarily to use a global variable.

// Actually, I'll just check for syntax errors first.
$output = shell_exec('php -l c:\xampp\htdocs\sisponto\api\ponto.php');
echo "Lint check: " . $output . "\n";

// And FaceCache
$output = shell_exec('php -l c:\xampp\htdocs\sisponto\api\FaceCache.php');
echo "Lint check FaceCache: " . $output . "\n";
