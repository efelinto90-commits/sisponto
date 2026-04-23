<?php
ini_set('display_errors', 1);
ini_set('memory_limit', '512M');
set_time_limit(0);

function getPdoConnection($config) {
    $dsn = "pgsql:host={$config['host']};port={$config['port']};dbname={$config['dbname']}";
    $conn = new PDO($dsn, $config['user'], $config['password']);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    return $conn;
}

$localConf = json_decode(file_get_contents('c:/xampp/htdocs/sisponto/config/local.json'), true);
$prodConf  = json_decode(file_get_contents('c:/xampp/htdocs/sisponto/config/prod.json'), true);

try {
    $localConn = getPdoConnection($localConf);
    $prodConn  = getPdoConnection($prodConf);
    echo "Conexoes estabelecidas.\n";

    // ----- 1. Mapear matricula -> id_local para funcionarios -----
    echo "Carregando mapa de matrículas (local)...\n";
    $stmtLocal = $localConn->query("SELECT id, matricula FROM ponto.funcionarios WHERE matricula IS NOT NULL");
    $localMap = []; // matricula_prod => id_local
    while ($r = $stmtLocal->fetch()) {
        $localMap[$r['matricula']] = (int)$r['id'];
    }
    echo "  " . count($localMap) . " funcionários locais mapeados.\n";

    // ----- 2. Mapa de matricula -> id na producao -----
    echo "Carregando mapa de matrículas (produção)...\n";
    $stmtProd = $prodConn->query("SELECT id, matricula FROM ponto.funcionarios WHERE matricula IS NOT NULL");
    $prodFuncMap = []; // prod_id => matricula
    while ($r = $stmtProd->fetch()) {
        $prodFuncMap[(int)$r['id']] = $r['matricula'];
    }
    echo "  " . count($prodFuncMap) . " funcionários na produção mapeados.\n";

    // ----- 3. Colunas que existem em AMBOS schemas -----
    $colsLocal = array_column(
        $localConn->query("SELECT column_name FROM information_schema.columns WHERE table_schema='ponto' AND table_name='registros' ORDER BY ordinal_position")->fetchAll(),
        'column_name'
    );
    $colsProd = array_column(
        $prodConn->query("SELECT column_name FROM information_schema.columns WHERE table_schema='ponto' AND table_name='registros' ORDER BY ordinal_position")->fetchAll(),
        'column_name'
    );

    $commonCols = array_values(array_intersect($colsLocal, $colsProd));
    // Remove 'id' from insert columns (will preserve production id via ON CONFLICT)
    echo "Colunas comuns: " . implode(', ', $commonCols) . "\n";

    // ----- 4. Carregar todos os registros da producao -----
    echo "Buscando registros na producao...\n";
    $stmtRegs = $prodConn->query("SELECT " . implode(', ', array_map(fn($c) => "\"$c\"", $commonCols)) . " FROM ponto.registros ORDER BY id");
    
    $inserted = 0;
    $skipped  = 0;
    $noMatch  = 0;
    $batch    = 0;

    $colsStr = implode(', ', array_map(fn($c) => "\"$c\"", $commonCols));
    $placeholders = implode(', ', array_fill(0, count($commonCols), '?'));
    $insertSql = "INSERT INTO ponto.registros ($colsStr) VALUES ($placeholders) ON CONFLICT (id) DO NOTHING";
    $stmtIns = $localConn->prepare($insertSql);

    $localConn->beginTransaction();

    while ($row = $stmtRegs->fetch()) {
        // Ignorar registros sem data (NOT NULL constraint local)
        if (empty($row['data'])) {
            $skipped++;
            continue;
        }

        // Mapear id_funcionario pelo matricula
        $prodFuncId = (int)($row['id_funcionario'] ?? 0);
        $matricula  = $prodFuncMap[$prodFuncId] ?? null;
        
        if (!$matricula) {
            $noMatch++;
            continue;
        }
        
        $localFuncId = $localMap[$matricula] ?? null;
        if (!$localFuncId) {
            $noMatch++;
            continue;
        }
        
        $row['id_funcionario'] = $localFuncId;
        
        // Corrigir formato de data se necessário (Prod: DD/MM/YYYY -> Local: YYYY-MM-DD)
        if (strpos($row['data'], '/') !== false) {
            $parts = explode('/', $row['data']);
            if (count($parts) === 3) {
                $row['data'] = $parts[2] . '-' . $parts[1] . '-' . $parts[0];
            }
        }

        // Nullificar id_horario (pode não existir localmente)
        $row['id_horario'] = null;
        // Nullificar id_falta também
        $row['id_falta'] = null;
        
        // Cast booleans
        foreach (['atrasou_primeiro_ponto','atrasou_segundo_ponto','atrasou_terceiro_ponto','atrasou_quarto_ponto'] as $boolCol) {
            if (isset($row[$boolCol])) {
                $v = $row[$boolCol];
                $row[$boolCol] = ($v === 't' || $v === true || $v === 1 || $v === '1') ? 'true' : 'false';
            }
        }

        try {
            $stmtIns->execute(array_values($row));
            if ($stmtIns->rowCount() > 0) {
                $inserted++;
            } else {
                $skipped++;
            }
        } catch (Exception $e) {
            echo "ERRO: " . $e->getMessage() . "\n";
            echo "ROW: " . print_r($row, true) . "\n";
            $localConn->rollBack();
            die("Abortado.\n");
        }

        $batch++;
        if ($batch % 1000 === 0) {
            $localConn->commit();
            $localConn->beginTransaction();
            echo "  Processados $batch... (inseridos: $inserted, conflitos: $skipped, sem match: $noMatch)\n";
        }
    }

    $localConn->commit();

    // Atualizar a sequência do id
    $localConn->exec("SELECT setval(pg_get_serial_sequence('ponto.registros', 'id'), COALESCE((SELECT MAX(id) FROM ponto.registros), 1))");

    echo "\n=== CONCLUÍDO ===\n";
    echo "Total processados : $batch\n";
    echo "Inseridos         : $inserted\n";
    echo "Conflitos (skip)  : $skipped\n";
    echo "Sem match local   : $noMatch\n";

} catch (Exception $e) {
    echo "ERRO FATAL: " . $e->getMessage() . "\n";
}
