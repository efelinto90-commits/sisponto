<?php
require_once '../config/Database.php';
require_once 'FaceCache.php';

use Api\FaceCache;

try {
    echo "Iniciando refresh do FaceCache...\n";
    $count = FaceCache::refresh();
    echo "Refresh concluído. $count funcionários indexados.\n";
} catch (Throwable $e) {
    echo "ERRO: " . $e->getMessage() . "\n";
    echo "Stack Trace: \n" . $e->getTraceAsString() . "\n";
}
