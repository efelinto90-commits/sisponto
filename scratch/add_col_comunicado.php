<?php
require 'config/Database.php';

try {
    $db = \Config\Database::getConnection();
    $db->exec("ALTER TABLE ponto.registros ADD COLUMN IF NOT EXISTS anexo_comunicado TEXT;");
    echo "Coluna anexo_comunicado adicionada com sucesso ou já existente.\n";
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
