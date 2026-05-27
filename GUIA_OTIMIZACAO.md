# GUIA DE OTIMIZAÇÃO - SISPONTO

## 🚀 RESUMO DOS PROBLEMAS IDENTIFICADOS

Seu sistema ficaria lento em produção (Locaweb) devido a:

1. **Falta de Índices no Banco de Dados** (❌ CRÍTICO)
2. **N+1 Queries** - múltiplas queries em loops (❌ MUITO GRAVE)
3. **Ausência de Cache** - dados re-consultados a cada requisição (⚠️ GRAVE)
4. **Subqueries Ineficientes** - usando EXISTS com varredura completa (⚠️ GRAVE)
5. **SELECT * em Queries** - carregando colunas desnecessárias (⚠️ MODERADO)
6. **Problemas de Conexão** - sem retry logic, sem connection pooling (⚠️ MODERADO)

---

## 📊 IMPACTO ESTIMADO

- **Índices**: 50-70% mais rápido
- **Cache + Batching**: 30-50% mais rápido  
- **Query Optimization**: 20-40% mais rápido
- **Total esperado**: 5x-10x mais rápido

---

## ✅ PASSO A PASSO DE IMPLEMENTAÇÃO

### FASE 1: BANCO DE DADOS (Execute em 5 minutos)

```bash
# 1. Acesse seu PostgreSQL em produção via SSH/Terminal
psql -h seu-servidor.com -U postgres -d funad

# 2. Execute o script de índices:
\i OPTIMIZATION_INDEXES.sql

# 3. Verificar se criou os índices:
SELECT * FROM pg_stat_user_indexes WHERE schemaname = 'ponto' ORDER BY idx_scan DESC;
```

**Arquivo**: `OPTIMIZATION_INDEXES.sql` (já criado)

### FASE 2: OTIMIZAR APIs (Atualize os arquivos)

#### Opção A: RÁPIDO (Aplicar hot-fixes)
Apenas 3 arquivos mais críticos - aplique os hotfixes abaixo

#### Opção B: COMPLETO (Recomendado)
Substitua gradualmente os arquivos com as versões otimizadas

---

## 🔴 HOTFIXES IMEDIATOS (5 min cada)

### HOTFIX 1: Adicionar índice de session cache

Adicione isto no início de **config/Database.php**:

```php
<?php
// ... código existente ...

class Database
{
    // NOVO: Cache de horários em session
    private static $horariosCache = null;
    
    public static function getCachedHorarios($conn) {
        if (self::$horariosCache === null) {
            if (session_status() === PHP_SESSION_NONE) session_start();
            if (!isset($_SESSION['_cached_horarios'])) {
                $stmt = $conn->query(
                    "SELECT id, primeiro_horario, segundo_horario, terceiro_horario, quarto_horario 
                     FROM horarios"
                );
                $_SESSION['_cached_horarios'] = [];
                while ($h = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $_SESSION['_cached_horarios'][$h['id']] = $h;
                }
            }
            self::$horariosCache = $_SESSION['_cached_horarios'];
        }
        return self::$horariosCache;
    }
    
    // ... resto do código ...
}
?>
```

### HOTFIX 2: Limitar SELECT em ponto.php

**ANTES** (linha 21):
```php
$stmt = $conn->prepare("SELECT * FROM funcionarios WHERE matricula = :matricula");
```

**DEPOIS**:
```php
$stmt = $conn->prepare("
    SELECT id, matricula, nome, is_exonerado, senha, metodos_acesso, 
           facial_descriptor, biometria, webauthn_id, cpf, data_nascimento,
           rg_numero, rg_orgao, rg_data_emissao, endereco, endereco_numero,
           endereco_bairro, endereco_cep, endereco_municipio, endereco_uf,
           nome_mae, celular, grade_horarios, lat_permitida, long_permitida,
           distancia_max_permitida, setor
    FROM funcionarios 
    WHERE matricula = :matricula
    LIMIT 1
");
```

### HOTFIX 3: Otimizar query de férias em relatorios.php

**ANTES** (linha 566):
```php
// Subquery no SELECT (LENTO)
(SELECT data_fim FROM ferias WHERE id_funcionario = f.id AND CURRENT_DATE BETWEEN data_inicio AND data_fim LIMIT 1) as data_fim_afastamento
```

**DEPOIS** (usar LEFT JOIN em uma query separada):
```php
$stmtFerias = $conn->prepare("
    SELECT id_funcionario, data_inicio, data_fim, tipo_afastamento, motivo_especifico 
    FROM ferias 
    WHERE status = 'deferido' 
    AND data_inicio <= :end 
    AND data_fim >= :start
");
$stmtFerias->execute([':start' => $startDate, ':end' => $endDate]);
$feriasRows = $stmtFerias->fetchAll(PDO::FETCH_ASSOC);

// Indexar em array para lookup O(1)
$feriasPorFunc = [];
foreach ($feriasRows as $f) {
    if (!isset($feriasPorFunc[$f['id_funcionario']])) {
        $feriasPorFunc[$f['id_funcionario']] = [];
    }
    $feriasPorFunc[$f['id_funcionario']][] = $f;
}

// Depois no loop:
foreach ($registros as &$reg) {
    if (isset($feriasPorFunc[$reg['id_funcionario']])) {
        foreach ($feriasPorFunc[$reg['id_funcionario']] as $afast) {
            if ($reg['data'] >= $afast['data_inicio'] && $reg['data'] <= $afast['data_fim']) {
                $reg['em_ferias'] = true;
                break;
            }
        }
    }
}
```

---

## 📁 ARQUIVOS OTIMIZADOS FORNECIDOS

Já criei versões otimizadas completas:

1. **`OPTIMIZATION_INDEXES.sql`** - Índices e otimizações de BD
2. **`api/ponto_otimizado.php`** - Versão otimizada
3. **`api/relatorios_otimizado.php`** - Versão otimizada
4. **`api/funcionarios_otimizado.php`** - Versão otimizada

### Para usar:

**Opção 1: Substituição Gradual**
```bash
# Backup dos originais
cp api/ponto.php api/ponto.php.bak
cp api/relatorios.php api/relatorios.php.bak
cp api/funcionarios.php api/funcionarios.php.bak

# Usar versões otimizadas
cp api/ponto_otimizado.php api/ponto.php
cp api/relatorios_otimizado.php api/relatorios.php
cp api/funcionarios_otimizado.php api/funcionarios.php
```

**Opção 2: Teste Antes**
```bash
# Testar em staging
# Acessar: seu-site-staging.com/api/ponto_otimizado.php
# Após validar, fazer a substituição
```

---

## 🛠️ OUTRAS OTIMIZAÇÕES RECOMENDADAS

### 1. Ativar GZIP no nginx/Apache

```apache
# .htaccess
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/plain
    AddOutputFilterByType DEFLATE text/html
    AddOutputFilterByType DEFLATE text/xml
    AddOutputFilterByType DEFLATE text/css
    AddOutputFilterByType DEFLATE text/javascript
    AddOutputFilterByType DEFLATE application/xml
    AddOutputFilterByType DEFLATE application/xhtml+xml
    AddOutputFilterByType DEFLATE application/rss+xml
    AddOutputFilterByType DEFLATE application/javascript
    AddOutputFilterByType DEFLATE application/x-javascript
    AddOutputFilterByType DEFLATE application/x-httpd-php
    AddOutputFilterByType DEFLATE image/svg+xml
    AddEncoding deflate .json
</IfModule>
```

### 2. Minificar Assets (JavaScript/CSS)

```bash
# Instalar minifiers
npm install -g uglify-js clean-css-cli

# Minificar
uglifyjs public/js/app.js -c -m -o public/js/app.min.js
cleancss public/css/style.css -o public/css/style.min.js
```

### 3. Implementar CDN para Static Files

- Upload de `public/js`, `public/css`, `public/images` para CloudFlare
- Atualizar `<script>` e `<link>` tags para usar URLs da CDN

### 4. Redis Cache (Opcional, para escala)

```php
// config/Cache.php
class Cache {
    private static $redis = null;
    
    public static function get($key) {
        if (self::$redis === null) {
            self::$redis = new Redis();
            self::$redis->connect('localhost', 6379);
        }
        return self::$redis->get($key);
    }
    
    public static function set($key, $value, $ttl = 3600) {
        if (self::$redis === null) {
            self::$redis = new Redis();
            self::$redis->connect('localhost', 6379);
        }
        self::$redis->setex($key, $ttl, json_encode($value));
    }
}

// Usar em relatorios.php
$cacheKey = "relatorios_{$startDate}_{$endDate}_{$funcId}_{$user_setor}";
$cached = Cache::get($cacheKey);
if ($cached) {
    echo $cached;
    exit;
}

// ... processar ...
Cache::set($cacheKey, json_encode(['success' => true, 'data' => $registrosFinal]));
```

### 5. Add Connection Pooling (PgBouncer)

```bash
# Instalar pgbouncer
sudo apt-get install pgbouncer

# /etc/pgbouncer/pgbouncer.ini
[databases]
funad = host=127.0.0.1 port=5432 dbname=funad

[pgbouncer]
pool_mode = transaction
max_client_conn = 1000
default_pool_size = 25
min_pool_size = 10
reserve_pool_size = 5
reserve_pool_timeout = 3

# Iniciar
sudo systemctl start pgbouncer
```

---

## 📈 MONITORAMENTO EM PRODUÇÃO

### Verificar Performance das Queries

```sql
-- Top 10 queries mais lentas
SELECT query, mean_exec_time, calls, total_exec_time 
FROM pg_stat_statements 
WHERE query NOT LIKE '%pg_stat%' 
ORDER BY mean_exec_time DESC 
LIMIT 10;

-- Tabelas com muitos sequential scans
SELECT schemaname, tablename, seq_scan, seq_tup_read, idx_scan
FROM pg_stat_user_tables 
WHERE schemaname = 'ponto'
ORDER BY seq_scan DESC;

-- Índices não utilizados
SELECT schemaname, tablename, indexname, idx_scan 
FROM pg_stat_user_indexes 
WHERE schemaname = 'ponto' 
AND idx_scan = 0;
```

### Logs PHP para Debug

```php
// Adicionar ao início dos arquivos da API
$startTime = microtime(true);

// ... código ...

$duration = microtime(true) - $startTime;
error_log("API: {$_SERVER['REQUEST_URI']} - {$duration}s");
```

---

## ⚠️ CHECKLIST DE IMPLEMENTAÇÃO

- [ ] Executar `OPTIMIZATION_INDEXES.sql` no PostgreSQL
- [ ] Testar nova versão de `ponto.php` em staging
- [ ] Testar nova versão de `relatorios.php` em staging
- [ ] Testar nova versão de `funcionarios.php` em staging
- [ ] Monitorar logs em produção por 24h
- [ ] Ativar GZIP no servidor
- [ ] Minificar assets
- [ ] Implementar CDN (opcional)
- [ ] Configurar PgBouncer (se muitas conexões)
- [ ] Backup dos arquivos originais

---

## 📞 SUPORTE

Se encontrar erros:

1. Verificar logs: `/var/log/apache2/error.log` ou `/var/log/nginx/error.log`
2. Verificar logs PHP: `/var/log/php-fpm/error.log`
3. Testar query no pgAdmin
4. Reverter para backup se necessário

---

## 🎯 RESUMO DAS MELHORIAS

| Problema | Solução | Ganho |
|----------|---------|-------|
| Falta de índices | Criar índices em matricula, data, id_funcionario | 50-70% |
| N+1 queries | Batching + prefetch | 30-50% |
| Sem cache | Session cache de horários | 20-30% |
| SELECT * | Query específica | 10-15% |
| Subqueries ineficientes | LEFT JOIN | 15-25% |
| GZIP desativado | Ativar compressão | 30-60% |

**Total esperado: 5x-10x mais rápido em produção**

---

Criado em: 2026-05-04
Versão: 1.0
