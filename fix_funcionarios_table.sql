-- Adicionando colunas faltantes na tabela funcionarios
SET search_path TO ponto;

ALTER TABLE funcionarios ADD COLUMN IF NOT EXISTS foto_perfil TEXT;
ALTER TABLE funcionarios ADD COLUMN IF NOT EXISTS biometria TEXT;
ALTER TABLE funcionarios ADD COLUMN IF NOT EXISTS biometria_facial TEXT;
ALTER TABLE funcionarios ADD COLUMN IF NOT EXISTS facial_descriptor JSONB;
ALTER TABLE funcionarios ADD COLUMN IF NOT EXISTS codigo_qr TEXT;
ALTER TABLE funcionarios ADD COLUMN IF NOT EXISTS webauthn_id TEXT;
ALTER TABLE funcionarios ADD COLUMN IF NOT EXISTS webauthn_pk TEXT;
ALTER TABLE funcionarios ADD COLUMN IF NOT EXISTS webauthn_counter INTEGER DEFAULT 0;
ALTER TABLE funcionarios ADD COLUMN IF NOT EXISTS metodos_acesso JSONB DEFAULT '["senha"]';
ALTER TABLE funcionarios ADD COLUMN IF NOT EXISTS senha TEXT;
