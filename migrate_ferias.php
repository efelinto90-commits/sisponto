<?php
require_once 'config/Database.php';
use Config\Database;

try {
    $conn = Database::getConnection();
    
    // Adicionar colunas se não existirem (PostgreSQL)
    $sql = "
        DO $$ 
        BEGIN 
            IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_schema='ponto' AND table_name='ferias' AND column_name='tipo_afastamento') THEN
                ALTER TABLE ponto.ferias ADD COLUMN tipo_afastamento VARCHAR(50);
            END IF;
            
            IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_schema='ponto' AND table_name='ferias' AND column_name='motivo_especifico') THEN
                ALTER TABLE ponto.ferias ADD COLUMN motivo_especifico TEXT;
            END IF;
            
            IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_schema='ponto' AND table_name='ferias' AND column_name='anexo') THEN
                ALTER TABLE ponto.ferias ADD COLUMN anexo TEXT;
            END IF;
        END $$;
    ";
    
    $conn->exec($sql);
    echo "Tabela 'ferias' atualizada com sucesso!";
} catch (Exception $e) {
    echo "Erro ao atualizar tabela: " . $e->getMessage();
}
