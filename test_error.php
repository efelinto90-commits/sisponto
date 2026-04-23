<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

try {
    $url = 'http://localhost:81/sisponto/api/db_manager.php?action=sync_to_local';
    
    $data = array('tabelas' => array('cargos'));
    $options = array(
        'http' => array(
            'header'  => "Content-type: application/json\r\n",
            'method'  => 'POST',
            'content' => json_encode($data)
        )
    );
    $context  = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    
    echo "RESULT:\n";
    echo $result;
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
