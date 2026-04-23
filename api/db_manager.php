<?php
// Desativar exibição de erros HTML que corrompem o JSON
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

if (ob_get_level()) { ob_end_clean(); }
ob_start();
header('Content-Type: application/json');

// Catch Fatal Errors and return as JSON
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== NULL && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        while (ob_get_level()) { ob_end_clean(); }
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false, 
            'message' => "Fatal Error: " . $error['message'] . " in " . $error['file'] . " on line " . $error['line'],
            'raw_error' => $error
        ]);
    }
});

session_start();

// Requires admin login (Level 1)
if (!isset($_SESSION['user_id']) || ($_SESSION['user_level'] ?? '') != '1') {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Acesso negado. Apenas administradores podem gerenciar o banco.']);
    exit;
}

require_once __DIR__ . '/../config/Database.php';
use Config\Database;

// Função utilitária para enviar JSON limpo
function sendJsonResponse($data) {
    while (ob_get_level() > 0) {
        if (!ob_end_clean()) break;
    }
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

$action = $_GET['action'] ?? ($_POST['action'] ?? '');
$localFile = __DIR__ . '/../config/local.json';
$prodFile = __DIR__ . '/../config/prod.json';

function getDbConfig($env) {
    global $localFile, $prodFile;
    $file = $env === 'local' ? $localFile : $prodFile;
    if (file_exists($file)) {
        return json_decode(file_get_contents($file), true) ?? [];
    }
    if ($env === 'local') {
        return [
            'host' => '127.0.0.1',
            'port' => '5432',
            'dbname' => 'sis-ponto',
            'user' => 'postgres',
            'password' => '',
            'schema' => 'ponto'
        ];
    }
    return [];
}

function getPdoConnection($config) {
    if (empty($config['host']) || empty($config['dbname'])) {
        throw new Exception("Configuração incompleta.");
    }
    $dsn = "pgsql:host={$config['host']};port={$config['port']};dbname={$config['dbname']}";
    $conn = new PDO($dsn, $config['user'], $config['password']);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    try {
        $schema = $config['schema'] ?? 'ponto';
        $conn->exec("SET search_path TO $schema, public");
    } catch (Exception $e) {}
    return $conn;
}

// ---- EXPORT ----
// kept for legacy functionality
if ($action === 'export') {
    try {
        $conn = Database::getConnection();
        $schema = $_GET['schema'] ?? 'ponto';
        $stmt = $conn->query("SELECT tablename FROM pg_tables WHERE schemaname = '$schema' ORDER BY tablename");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $sql = "-- SisPonto DB Export\n-- Gerado em: " . date('Y-m-d H:i:s') . "\n-- Schema: $schema\n\n";
        $sql .= "SET search_path TO $schema;\n\n";

        foreach ($tables as $table) {
            $sql .= "-- Tabela: $table\n";
            $colQuery = $conn->query("
                SELECT c.column_name, c.data_type, c.character_maximum_length, c.column_default, c.is_nullable,
                       (SELECT true FROM information_schema.table_constraints tc 
                        JOIN information_schema.key_column_usage kcu ON tc.constraint_name = kcu.constraint_name 
                        WHERE tc.table_schema = c.table_schema AND tc.table_name = c.table_name 
                        AND kcu.column_name = c.column_name AND tc.constraint_type = 'PRIMARY KEY') as is_pk
                FROM information_schema.columns c
                WHERE c.table_schema = '$schema' AND c.table_name = '$table'
                ORDER BY c.ordinal_position;
            ")->fetchAll();

            if (!empty($colQuery)) {
                $sql .= "CREATE TABLE IF NOT EXISTS \"$table\" (\n";
                $colDefs = [];
                $pk = [];
                foreach ($colQuery as $c) {
                    $type = $c['data_type'];
                    if (strpos((string) $c['column_default'], 'nextval') !== false) {
                        $type = ($type === 'bigint') ? 'BIGSERIAL' : 'SERIAL';
                        $c['column_default'] = null;
                    }
                    $def = "  \"{$c['column_name']}\" $type";
                    if ($c['character_maximum_length'] && $type !== 'text')
                        $def .= "({$c['character_maximum_length']})";
                    if ($c['is_nullable'] === 'NO' && strpos($type, 'SERIAL') === false)
                        $def .= " NOT NULL";
                    if ($c['column_default'])
                        $def .= " DEFAULT {$c['column_default']}";
                    if ($c['is_pk'])
                        $pk[] = "\"{$c['column_name']}\"";
                    $colDefs[] = $def;
                }
                if (!empty($pk)) {
                    $colDefs[] = "  PRIMARY KEY (" . implode(', ', $pk) . ")";
                }
                $sql .= implode(",\n", $colDefs) . "\n);\n\n";
            }

            $rows = $conn->query("SELECT * FROM \"$table\"")->fetchAll();
            if (empty($rows)) {
                $sql .= "-- (sem dados)\n\n";
                continue;
            }
            foreach ($rows as $row) {
                $cols = implode(', ', array_map(function($c) { return "\"$c\""; }, array_keys($row)));
                $vals = implode(', ', array_map(function ($v) use ($conn) {
                    if ($v === null) return 'NULL';
                    if (is_bool($v)) return $v ? 'TRUE' : 'FALSE';
                    return $conn->quote($v);
                }, array_values($row)));
                $sql .= "INSERT INTO \"$table\" ($cols) VALUES ($vals) ON CONFLICT DO NOTHING;\n";
            }
            $sql .= "\n";
        }
        header('Content-Type: application/sql');
        header('Content-Disposition: attachment; filename="sis-ponto-export-' . date('Ymd-His') . '.sql"');
        echo $sql;
        exit;
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Erro ao exportar: ' . $e->getMessage()]);
        exit;
    }
}

// ---- IMPORT ----
// kept for legacy functionality
if ($action === 'import' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_FILES['sql_file'])) {
        echo json_encode(['success' => false, 'message' => 'Nenhum arquivo enviado.']);
        exit;
    }
    $sqlContent = file_get_contents($_FILES['sql_file']['tmp_name']);
    if (empty($sqlContent)) {
        echo json_encode(['success' => false, 'message' => 'Arquivo SQL vazio.']);
        exit;
    }
    try {
        $conn = Database::getConnection();
        $conn->exec($sqlContent);
        echo json_encode(['success' => true, 'message' => 'SQL importado com sucesso!']);
    } catch (Exception $e) {
        ob_end_clean();
        echo json_encode(['success' => false, 'message' => 'Erro ao importar SQL: ' . $e->getMessage()]);
    }
    exit;
}

// ---- INFO: Retorna informações ----
if ($action === 'info') {
    try {
        $localConfig = getDbConfig('local');
        $prodConfig = getDbConfig('prod');
        $connLocal = null;
        $connProd = null;

        try {
            $connLocal = getPdoConnection($localConfig);
        } catch (Exception $e) {
            try {
                $connLocal = Database::getConnection();
            } catch (Exception $e) {}
        }

        $tablesLocalData = [];
        if ($connLocal) {
            try {
                $schemaLocal = $localConfig['schema'] ?? 'ponto';
                $stmt = $connLocal->prepare("
                    SELECT schemaname, tablename 
                    FROM pg_tables 
                    WHERE schemaname = :schema 
                      AND tablename NOT LIKE 'pg_%' 
                      AND tablename NOT LIKE 'sql_%'
                    ORDER BY tablename
                ");
                $stmt->execute([':schema' => $schemaLocal]);
                $tablesLocal = $stmt->fetchAll();
                
                $seen = [];
                foreach ($tablesLocal as $row) {
                    $t = $row['tablename'];
                    if (in_array($t, $seen)) continue;
                    $seen[] = $t;
                    $s = $row['schemaname'];
                    try {
                        $tablesLocalData[$t] = (int) $connLocal->query("SELECT COUNT(*) FROM \"$s\".\"$t\"")->fetchColumn();
                    } catch (Exception $e) { $tablesLocalData[$t] = '?'; }
                }
            } catch (Exception $e) {}
        }

        $tablesProdData = [];
        try {
            if (!empty($prodConfig['host']) && !empty($prodConfig['dbname'])) {
                $connProd = getPdoConnection($prodConfig);
                $schemaProd = $prodConfig['schema'] ?? 'ponto';
                $stmt = $connProd->prepare("
                    SELECT schemaname, tablename 
                    FROM pg_tables 
                    WHERE schemaname = :schema 
                      AND tablename NOT LIKE 'pg_%' 
                      AND tablename NOT LIKE 'sql_%'
                    ORDER BY tablename
                ");
                $stmt->execute([':schema' => $schemaProd]);
                $tablesProd = $stmt->fetchAll();
                
                $seen = [];
                foreach ($tablesProd as $row) {
                    $t = $row['tablename'];
                    if (in_array($t, $seen)) continue;
                    $seen[] = $t;
                    $s = $row['schemaname'];
                    try {
                        $tablesProdData[$t] = (int) $connProd->query("SELECT COUNT(*) FROM \"$s\".\"$t\"")->fetchColumn();
                    } catch (Exception $e) { $tablesProdData[$t] = '?'; }
                }
            }
        } catch (Exception $e) {}

        sendJsonResponse([
            'success' => true,
            'tables_local' => $tablesLocalData,
            'tables_prod' => $tablesProdData,
            'local' => $localConfig,
            'prod' => $prodConfig
        ]);
    } catch (Exception $e) {
        sendJsonResponse(['success' => false, 'message' => $e->getMessage()]);
    }
}

// ---- SAVE Local / Prod CONFIG ----
if (strpos($action, 'save_') === 0 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $env = str_replace('save_', '', $action);
    $file = $env === 'local' ? $localFile : $prodFile;
    $input = json_decode(file_get_contents('php://input'), true);

    $config = [
        'host' => $input['host'] ?? '',
        'port' => $input['port'] ?? '5432',
        'dbname' => $input['dbname'] ?? '',
        'user' => $input['user'] ?? '',
        'password' => $input['password'] ?? '',
        'schema' => $input['schema'] ?? 'ponto',
    ];

    if (file_put_contents($file, json_encode($config, JSON_PRETTY_PRINT))) {
        sendJsonResponse(['success' => true, 'message' => "Configuração salva!"]);
    } else {
        sendJsonResponse(['success' => false, 'message' => 'Falha ao salvar configuração.']);
    }
}

// ---- TEST CONNECTION ----
if ($action === 'test_conexao') {
    $env = $_GET['env'] ?? 'local';
    try {
        $config = getDbConfig($env);
        $conn = getPdoConnection($config);
        $conn->query("SELECT 1");
        sendJsonResponse(['success' => true, 'message' => 'Conexão OK']);
    } catch (Exception $e) {
        sendJsonResponse(['success' => false, 'message' => 'Erro de conexão: ' . $e->getMessage()]);
    }
}

// ---- SYNC LOGIC ----
if ($action === 'sync_to_prod' || $action === 'sync_to_local') {
    $isToProd = ($action === 'sync_to_prod');
    
    $localConfig = getDbConfig('local');
    $prodConfig = getDbConfig('prod');

    if (empty($localConfig['host']) || empty($prodConfig['host'])) {
        sendJsonResponse(['success' => false, 'message' => 'Ambas as configurações (Local e Produção) precisam estar preenchidas.']);
    }

    // Aumentar tempo limite e memória para sincronização de grandes volumes
    set_time_limit(0);
    ini_set('memory_limit', '512M');

    try {
        $localConn = null;
        try {
            $localConn = getPdoConnection($localConfig);
        } catch(Exception $e) {
            $localConn = Database::getConnection();
        }

        $prodConn = getPdoConnection($prodConfig);

        $sourceConn = $isToProd ? $localConn : $prodConn;
        $destConn = $isToProd ? $prodConn : $localConn;

        $input = json_decode(file_get_contents('php://input'), true);
        $tabelas = $input['tabelas'] ?? [];

        if (empty($tabelas)) {
            sendJsonResponse(['success' => false, 'message' => 'Nenhuma tabela selecionada.']);
        }

        $destSchema = $isToProd ? ($prodConfig['schema'] ?? 'ponto') : ($localConfig['schema'] ?? 'ponto');

        try {
            $destConn->exec("CREATE SCHEMA IF NOT EXISTS $destSchema");
        } catch (Throwable $e) {}

        // Desativa verificações de foreign keys no destino
        try {
            $destConn->exec("SET session_replication_role = 'replica';");
        } catch (Throwable $e) {}

        $log = [];
        foreach ($tabelas as $table) {
            $lastError = null;
            $count = 0;
            $count = 0;
            $srcSchema = $isToProd ? ($localConfig['schema'] ?? 'ponto') : ($prodConfig['schema'] ?? 'ponto');
            // Criação da estrutura (se nao existir) baseada no source
            $colQuery = [];
            // Tenta achar a estrutura da tabela usando o catálogo nativo (muito mais confiável)
            try {
                $stmt = $sourceConn->prepare("
                    SELECT 
                        a.attname AS column_name,
                        t.typname AS data_type,
                        NOT a.attnotnull AS is_nullable_bool,
                        COALESCE(pg_get_expr(ad.adbin, ad.adrelid), '') AS column_default,
                        CASE 
                            WHEN t.typname = 'varchar' THEN (a.atttypmod - 4)
                            WHEN t.typname = 'bpchar' THEN (a.atttypmod - 4)
                            ELSE NULL
                        END AS character_maximum_length,
                        n.nspname AS table_schema,
                        (SELECT count(*) > 0 FROM pg_index i 
                         WHERE i.indrelid = c.oid AND i.indisprimary 
                           AND a.attnum = ANY(i.indkey)) as is_pk
                    FROM pg_attribute a
                    JOIN pg_class c ON a.attrelid = c.oid
                    JOIN pg_namespace n ON c.relnamespace = n.oid
                    JOIN pg_type t ON a.atttypid = t.oid
                    LEFT JOIN pg_attrdef ad ON a.attrelid = ad.adrelid AND a.attnum = ad.adnum
                    WHERE c.relname = :table
                      AND n.nspname = :schema
                      AND a.attnum > 0
                      AND NOT a.attisdropped
                    ORDER BY a.attnum
                ");
                $stmt->execute(['table' => $table, 'schema' => $srcSchema]);
                $colQuery = $stmt->fetchAll();

                if (!empty($colQuery)) {
                    $firstSchema = $colQuery[0]['table_schema'];
                    $colQuery = array_values(array_filter($colQuery, function($cq) use ($firstSchema) { 
                        return $cq['table_schema'] === $firstSchema; 
                    }));
                    // Traduz tipos para nomes comuns do information_schema para manter compatibilidade com o resto do script
                    foreach($colQuery as &$cq) {
                        if ($cq['data_type'] === 'bool') $cq['data_type'] = 'boolean';
                        if ($cq['data_type'] === 'int4') $cq['data_type'] = 'integer';
                        if ($cq['data_type'] === 'int8') $cq['data_type'] = 'bigint';
                        if ($cq['data_type'] === 'float8') $cq['data_type'] = 'double precision';
                        if ($cq['data_type'] === 'varchar') $cq['data_type'] = 'character varying';
                        $cq['is_nullable'] = $cq['is_nullable_bool'] ? 'YES' : 'NO';
                    }
                }
            } catch (Exception $e) {
                // Ignore query error for structure if table doesn't exist in source
            }

            if (!empty($colQuery)) {
                $destSchema = $isToProd ? ($prodConfig['schema'] ?? 'ponto') : ($localConfig['schema'] ?? 'ponto');
                $createSql = "CREATE TABLE IF NOT EXISTS $destSchema.\"$table\" (\n";
                $colDefs = [];
                $pk = [];
                $srcSchema = $colQuery[0]['table_schema'] ?? 'ponto';
                foreach ($colQuery as $c) {
                    $type = $c['data_type'];
                    // Mapeamento de serial
                    if (strpos((string) $c['column_default'], 'nextval') !== false) {
                        $type = ($type === 'bigint') ? 'BIGSERIAL' : 'SERIAL';
                        $c['column_default'] = null; // não precisa por default function em serial
                    }

                    $def = "  \"{$c['column_name']}\" $type";
                    if ($c['character_maximum_length'] && !in_array($type, ['text', 'integer', 'bigint', 'boolean', 'timestamp', 'date']))
                        $def .= "({$c['character_maximum_length']})";
                    if ($c['is_nullable'] === 'NO' && strpos($type, 'SERIAL') === false)
                        $def .= " NOT NULL";
                    if ($c['column_default'])
                        $def .= " DEFAULT {$c['column_default']}";

                    if ($c['is_pk'])
                        $pk[] = "\"{$c['column_name']}\"";
                    $colDefs[] = $def;
                }

                if (!empty($pk)) {
                    $colDefs[] = "  PRIMARY KEY (" . implode(', ', $pk) . ")";
                }

                $createSql .= implode(",\n", $colDefs) . "\n);";
                try {
                    $destConn->exec($createSql);
                } catch (Throwable $e) {
                    $lastError = "Falha ao criar tabela: " . $e->getMessage();
                }
            } else {
                $lastError = "Estrutura da tabela não encontrada na origem (esquema $srcSchema).";
            }

            // Sync de dados
            $rows = [];
            $count = 0;
            // Se já tem erro de criação, capturaríamos ele, mas o ideal é resetar count
            // mas manter o $lastError se ele veio do bloco acima.
            
            // Pegar as colunas, tipos e tamanhos existentes no destino para evitar erros de tipo, truncamento e colunas inexistentes
            $destColMeta = [];
            try {
                $destSchemaInfo = $isToProd ? ($prodConfig['schema'] ?? 'ponto') : ($localConfig['schema'] ?? 'ponto');
                $qc = $destConn->prepare("SELECT column_name, data_type, character_maximum_length FROM information_schema.columns WHERE table_schema = :schema AND table_name = :table");
                $qc->execute([':schema' => $destSchemaInfo, ':table' => $table]);
                $destColMeta = $qc->fetchAll(PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);
            } catch (Exception $e) {}

            // Se não houver erro crítico de estrutura até agora, tentamos ler os dados
            if (!$lastError) {
                try {
                    $schemaPart = isset($srcSchema) ? "\"$srcSchema\"." : "";
                    $stmtRows = $sourceConn->query("SELECT * FROM $schemaPart\"$table\"");
                    
                    // Inicia transação para acelerar a inserção no destino
                    $destConn->beginTransaction();
                    $batchCount = 0;

                    while ($row = $stmtRows->fetch()) {
                        // Filtra as colunas da origem que realmente existem no destino
                        $rowFiltered = [];
                        if (!empty($destColMeta)) {
                            foreach ($row as $k => $v) {
                                if (isset($destColMeta[$k])) {
                                    $rowFiltered[$k] = $v;
                                }
                            }
                        } else {
                            $rowFiltered = $row;
                        }

                        if (empty($rowFiltered)) continue;

                        $colsList = array_keys($rowFiltered);
                        $cols = implode(', ', array_map(function($c) { return "\"$c\""; }, $colsList));
                        
                        $valsArr = [];
                        foreach ($rowFiltered as $k => $v) {
                            if ($v === null) {
                                $valsArr[] = 'NULL';
                                continue;
                            }

                            $meta = $destColMeta[$k] ?? [];
                            $targetType = $meta['data_type'] ?? '';
                            $maxLen = $meta['character_maximum_length'] ?? null;
                            $lowerV = is_string($v) ? strtolower($v) : $v;

                            if ($targetType === 'boolean') {
                                if (is_bool($v)) {
                                    $valsArr[] = $v ? 'TRUE' : 'FALSE';
                                } elseif ($lowerV === 't' || $lowerV === 'true' || $lowerV === '1' || $v === 1 || $v === true) {
                                    $valsArr[] = 'TRUE';
                                } elseif ($lowerV === 'f' || $lowerV === 'false' || $lowerV === '0' || $v === 0 || $v === false) {
                                    $valsArr[] = 'FALSE';
                                } else {
                                    $valsArr[] = $v ? 'TRUE' : 'FALSE';
                                }
                            } elseif ($targetType === 'date' && is_string($v) && strpos($v, '/') !== false) {
                                // Converte DD/MM/YYYY para YYYY-MM-DD
                                $parts = explode('/', $v);
                                if (count($parts) === 3) {
                                    $v = $parts[2] . '-' . $parts[1] . '-' . $parts[0];
                                }
                                $valsArr[] = $destConn->quote((string)$v);
                            } else {
                                // Trata outros tipos e codificação UTF-8
                                $encodedStr = $v;
                                if (is_string($v)) {
                                    if (function_exists('mb_convert_encoding')) {
                                        $encodedStr = @mb_convert_encoding($v, 'UTF-8', 'UTF-8, ISO-8859-1');
                                    }
                                    // Trata truncamento se for varchar/char
                                    if ($maxLen > 0 && strlen((string)$encodedStr) > $maxLen) {
                                        $encodedStr = mb_substr((string)$encodedStr, 0, $maxLen, 'UTF-8');
                                    }
                                }
                                $valsArr[] = $destConn->quote((string)$encodedStr);
                            }
                        }
                        $vals = implode(', ', $valsArr);

                        try {
                            $destSchemaIns = $isToProd ? ($prodConfig['schema'] ?? 'ponto') : ($localConfig['schema'] ?? 'ponto');
                            $stmtIns = $destConn->prepare("INSERT INTO $destSchemaIns.\"$table\" ($cols) VALUES ($vals) ON CONFLICT DO NOTHING");
                            $stmtIns->execute();
                            if ($stmtIns->rowCount() > 0) {
                                $count++;
                            }

                            $batchCount++;
                            // Commit a cada 1000 registros para não sobrecarregar a transação
                            if ($batchCount >= 1000) {
                                $destConn->commit();
                                $destConn->beginTransaction();
                                $batchCount = 0;
                            }
                        } catch (Throwable $e) {
                            if (!$lastError) {
                                $lastError = $e->getMessage();
                            }
                        }
                    }
                    // Commit final da tabela
                    if ($destConn->inTransaction()) {
                        $destConn->commit();
                    }
                } catch (Exception $e) {
                    if ($destConn->inTransaction()) {
                        $destConn->rollBack();
                    }
                    $lastError = "A tabela não existe na origem ou não pôde ser lida: " . $e->getMessage();
                }
            }

            // sync sequencias
            try {
                $destSchemaSeq = $isToProd ? ($prodConfig['schema'] ?? 'ponto') : ($localConfig['schema'] ?? 'ponto');
                $destConn->exec("
                    DO \$\$
                    DECLARE
                        seq_name text;
                        max_val int;
                    BEGIN
                        FOR seq_name IN (SELECT pg_get_serial_sequence('$destSchemaSeq.\"$table\"', 'id')) LOOP
                            IF seq_name IS NOT NULL THEN
                                EXECUTE 'SELECT COALESCE(MAX(id), 0) FROM $destSchemaSeq.\"$table\"' INTO max_val;
                                EXECUTE 'SELECT setval(''' || seq_name || ''', GREATEST(max_val, 1))';
                            END IF;
                        END LOOP;
                    END \$\$;
                ");
            } catch (Throwable $e) {}

            $msg = "$table: $count registros sincronizados";
            if ($lastError) {
                $msg .= " <br><span style='color:red; font-size:10px;'>Erro: " . htmlspecialchars($lastError) . "</span>";
            }
            $log[] = $msg;
        }

        // Reativa verificações de foreign keys no destino
        try {
            $destConn->exec("SET session_replication_role = 'origin';");
        } catch (Throwable $e) {}

        sendJsonResponse(['success' => true, 'message' => implode('<br><br>', $log)]);

    } catch (Throwable $e) {
        sendJsonResponse(['success' => false, 'message' => 'Erro de conexão ou execução: ' . $e->getMessage()]);
    }
    exit;
}

sendJsonResponse(['success' => false, 'message' => 'Ação inválida.']);
