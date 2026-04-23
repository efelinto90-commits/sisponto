<?php
try {
    $startDate = date('Y-m-01');
    $endDate = date('Y-m-t');
    
    // Simulate a GET request to api/relatorios.php
    $_GET['start_date'] = $startDate;
    $_GET['end_date'] = $endDate;
    $_SERVER['REQUEST_METHOD'] = 'GET';
    
    ob_start();
    require_once 'c:/xampp/htdocs/sisponto/api/relatorios.php';
    $output = ob_get_clean();
    
    $data = json_decode($output, true);
    
    if ($data && isset($data['success'])) {
        echo "SUCCESS: " . ($data['success'] ? 'TRUE' : 'FALSE') . "\n";
        if (!$data['success']) {
            echo "MESSAGE: " . $data['message'] . "\n";
        } else {
            echo "COUNT: " . count($data['data']) . "\n";
            if (count($data['data']) > 0) {
                print_r($data['data'][0]);
            }
        }
    } else {
        echo "INVALID OUTPUT: " . $output . "\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
