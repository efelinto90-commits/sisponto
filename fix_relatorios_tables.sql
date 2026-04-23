-- Adicionando colunas faltantes na tabela registros para justificação
SET search_path TO ponto;

ALTER TABLE registros ADD COLUMN IF NOT EXISTS justificativa TEXT;
ALTER TABLE registros ADD COLUMN IF NOT EXISTS status_turno1 VARCHAR(100);
ALTER TABLE registros ADD COLUMN IF NOT EXISTS status_turno2 VARCHAR(100);
ALTER TABLE registros ADD COLUMN IF NOT EXISTS just_ent1 TEXT;
ALTER TABLE registros ADD COLUMN IF NOT EXISTS just_sai1 TEXT;
ALTER TABLE registros ADD COLUMN IF NOT EXISTS just_ent2 TEXT;
ALTER TABLE registros ADD COLUMN IF NOT EXISTS just_sai2 TEXT;

-- Adicionando colunas de tolerância na tabela horarios
ALTER TABLE horarios ADD COLUMN IF NOT EXISTS tolerancia_entrada INTEGER DEFAULT 15;
ALTER TABLE horarios ADD COLUMN IF NOT EXISTS tolerancia_saida INTEGER DEFAULT 15;
