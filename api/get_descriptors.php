<?php
header('Content-Type: application/json');
header('Cache-Control: public, max-age=300'); // 5 min cache no browser

require_once '../config/Database.php';
require_once 'FaceCache.php';

use Api\FaceCache;

try {
    // Busca os descritores do cache em disco (muito mais rápido que consulta SQL + json_decode no loop)
    $descriptors = FaceCache::get();
    
    echo json_encode(['descriptors' => $descriptors]);
} catch (Exception $e) {
    echo json_encode(['descriptors' => [], 'error' => 'Erro interno: ' . $e->getMessage()]);
}
