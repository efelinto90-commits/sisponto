<?php
require_once 'config/Database.php';
use Config\Database;

try {
    $conn = Database::getConnection();
    
    $sql = "
    ALTER TABLE ponto.funcionarios
    ADD COLUMN IF NOT EXISTS lat_permitida NUMERIC(10,8),
    ADD COLUMN IF NOT EXISTS long_permitida NUMERIC(11,8),
    ADD COLUMN IF NOT EXISTS distancia_max_permitida INTEGER DEFAULT 200;

    COMMENT ON COLUMN ponto.funcionarios.lat_permitida IS 'Latitude permitida para registro de ponto';
    COMMENT ON COLUMN ponto.funcionarios.long_permitida IS 'Longitude permitida para registro de ponto';
    COMMENT ON COLUMN ponto.funcionarios.distancia_max_permitida IS 'Distância máxima permitida em metros';
    ";

    $conn->exec($sql);
    echo "Migração executada com sucesso!\n";

} catch (Exception $e) {
    echo "Erro ao executar migração: " . $e->getMessage() . "\n";
}
