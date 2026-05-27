-- ============================================================================
-- OTIMIZAÇÕES E ÍNDICES PARA SISPONTO
-- Execute este script em PRODUÇÃO para melhorar performance
-- ============================================================================
-- 
-- PROBLEMA: Sistema fica lento em produção (Locaweb)
-- CAUSA: Falta de índices, N+1 queries, falta de cache, subqueries ineficientes
--
-- ============================================================================

SET search_path TO ponto, public;

-- ============================================================================
-- 1. ÍNDICES CRÍTICOS (PRIMARY & FOREIGN KEYS)
-- ============================================================================

-- Índice em funcionarios para matrícula (muito utilizada em buscas)
CREATE INDEX IF NOT EXISTS idx_funcionarios_matricula ON funcionarios(matricula);

-- Índice em funcionarios para CPF (verificação de duplicidade)
CREATE INDEX IF NOT EXISTS idx_funcionarios_cpf ON funcionarios(cpf);

-- Índice em funcionarios para status (exonerado)
CREATE INDEX IF NOT EXISTS idx_funcionarios_exonerado ON funcionarios(is_exonerado);

-- Índice em funcionarios para setor (filtro de acesso por setor)
CREATE INDEX IF NOT EXISTS idx_funcionarios_setorcom  ON funcionarios(setor);
CREATE INDEX IF NOT EXISTS idx_funcionarios_setor2 ON funcionarios(setor2);

-- Índice em funcionarios para horario (otimização de JOINs)
CREATE INDEX IF NOT EXISTS idx_funcionarios_id_horario ON funcionarios(id_horario);

-- ============================================================================
-- 2. ÍNDICES EM REGISTROS (Tabela mais frequentemente consultada)
-- ============================================================================

-- Índice composto: id_funcionario + data (principal para relatórios)
CREATE INDEX IF NOT EXISTS idx_registros_func_data ON registros(id_funcionario, data DESC);

-- Índice em data para consultas por período
CREATE INDEX IF NOT EXISTS idx_registros_data ON registros(data DESC);

-- Índice para status CRH (consultas de enviados/não enviados)
CREATE INDEX IF NOT EXISTS idx_registros_status_crh ON registros(status_crh, enviado_crh);

-- Índice para buscas de hoje
CREATE INDEX IF NOT EXISTS idx_registros_hoje ON registros(data, id_funcionario);

-- ============================================================================
-- 3. ÍNDICES EM FERIAS (usada frequentemente com EXISTS/LEFT JOIN)
-- ============================================================================

-- Índice composto para buscar férias ativas
CREATE INDEX IF NOT EXISTS idx_ferias_func_status_dates ON ferias(id_funcionario, status, data_inicio, data_fim);

-- Índice em data_inicio e data_fim para range queries
CREATE INDEX IF NOT EXISTS idx_ferias_datas ON ferias(data_inicio, data_fim);

-- ============================================================================
-- 4. ÍNDICES EM PONTO_LIBERADO
-- ============================================================================

-- Índice para buscar liberações por período e setor
CREATE INDEX IF NOT EXISTS idx_ponto_liberado_data_setor ON ponto_liberado(data_hora, setor);

-- ============================================================================
-- 5. ÍNDICES EM USERS (tabela de login)
-- ============================================================================

-- Índice para login (muito usado)
CREATE INDEX IF NOT EXISTS idx_users_name ON users(LOWER(name));

-- Índice para facial descriptor (para login biométrico)
CREATE INDEX IF NOT EXISTS idx_users_facial_descriptor ON users(facial_descriptor);

-- ============================================================================
-- 6. ÍNDICES EM HORARIOS
-- ============================================================================

-- Índice para buscas rápidas
CREATE INDEX IF NOT EXISTS idx_horarios_id ON horarios(id);

-- ============================================================================
-- 7. ÍNDICES EM GEOFENCING_PRESETS
-- ============================================================================

-- Índice para buscas por nome
CREATE INDEX IF NOT EXISTS idx_geofencing_nome ON geofencing_presets(nome_area);

-- ============================================================================
-- 8. ANÁLISE DE QUERYS PROBLEMÁTICAS
-- ============================================================================

-- PROBLEMA: Query com EXISTS em ferias é lenta
-- ANTES:
--   SELECT * FROM funcionarios WHERE EXISTS (SELECT 1 FROM ferias WHERE ...)
-- 
-- DEPOIS: Usar LEFT JOIN e COUNT/GROUP BY
--   SELECT f.* FROM funcionarios f 
--   LEFT JOIN ferias fe ON f.id = fe.id_funcionario AND fe.status = 'deferido'
--   WHERE fe.id IS NOT NULL

-- PROBLEMA: SELECT * FROM horarios sem LIMIT
-- SOLUÇÃO: Sempre usar field-specific queries nas APIs

-- PROBLEMA: Subqueries aninhadas em SELECT
-- EXEMPLO PROBLEMÁTICO:
--   SELECT (SELECT data_fim FROM ferias WHERE id_funcionario = f.id LIMIT 1) as data_fim
--
-- SOLUÇÃO: Usar LEFT JOIN + DISTINCT ou GROUP BY

-- ============================================================================
-- 9. CONFIGURAÇÕES DE PERFORMANCE DO POSTGRESQL
-- ============================================================================

-- Aumentar max_parallel_workers_per_gather para consultas paralelas
-- (Fazer no postgresql.conf do servidor):
-- max_parallel_workers_per_gather = 4
-- max_parallel_workers = 8

-- ============================================================================
-- 10. STATISTICS E ANALYZE
-- ============================================================================

-- Executar ANALYZE em todas as tabelas para atualizar estatísticas
ANALYZE funcionarios;
ANALYZE registros;
ANALYZE ferias;
ANALYZE ponto_liberado;
ANALYZE users;
ANALYZE horarios;
ANALYZE geofencing_presets;

-- ============================================================================
-- 11. QUERY HINTS COMENTADOS PARA DEVELOPMENT
-- ============================================================================

-- Para DEBUG de performance, use EXPLAIN ANALYZE:
-- EXPLAIN ANALYZE SELECT ... FROM registros WHERE data = CURRENT_DATE;

-- Para ver índices em uso:
-- SELECT * FROM pg_stat_user_indexes WHERE schemaname = 'ponto' ORDER BY idx_scan DESC;

-- Para ver tabelas mais lentas (seq scans altos):
-- SELECT schemaname, tablename, seq_scan, seq_tup_read FROM pg_stat_user_tables 
-- WHERE schemaname = 'ponto' ORDER BY seq_scan DESC;

-- ============================================================================
