<?php
/**
 * Script para atualizar o cache de biometria facial
 * Pode ser chamado via CRON ou manualmente após cadastros em massa.
 */
require_once __DIR__ . '/FaceCache.php';
use Api\FaceCache;

header('Content-Type: text/plain');

try {
    echo "Iniciando atualização do cache facial...\n";
    $count = FaceCache::refresh();
    echo "Sucesso! Cache atualizado com $count funcionários identificáveis.\n";
} catch (Exception $e) {
    echo "ERRO ao atualizar cache: " . $e->getMessage();
}
