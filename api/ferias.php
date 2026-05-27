<?php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Acesso negado.']);
    exit;
}

require_once '../config/Database.php';
use Config\Database;

if (!function_exists('autoGerarSequenciaFerias')) {
    /**
     * Gera automaticamente a sequência do próximo período de férias caso o funcionário
     * não possua nenhum período 'aberto' com saldo disponível.
     */
    function autoGerarSequenciaFerias($conn, $funcId) {
        try {
            $stmt = $conn->prepare("SELECT data_admissao, ferias_periodos FROM ponto.funcionarios WHERE id = :id");
            $stmt->execute([':id' => $funcId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row) return false;

            $dataAdmissao = $row['data_admissao'] ?? null;
            $periodosJson = $row['ferias_periodos'] ?? '';
            $periodos = json_decode($periodosJson ?: '[]', true) ?: [];

            // Verifica se possui pelo menos um período aberto com saldo maior que 0
            $hasActiveAberto = false;
            foreach ($periodos as $p) {
                if (($p['status'] ?? 'aberto') === 'aberto' && (int)($p['dias'] ?? 0) > 0) {
                    $hasActiveAberto = true;
                    break;
                }
            }

            if ($hasActiveAberto) {
                return false; // Já possui período ativo com saldo, não precisa gerar
            }

            $newPeriodStr = null;
            if (empty($periodos)) {
                // Lista vazia: usa a data de admissão
                if ($dataAdmissao && preg_match('/^(\d{4})-\d{2}-\d{2}$/', $dataAdmissao, $m)) {
                    $y = (int)$m[1];
                } else {
                    $y = (int)date('Y');
                }
                $newPeriodStr = $y . '/' . ($y + 1);
            } else {
                // Encontra o maior ano de início entre os períodos cadastrados
                $highestYear = null;
                foreach ($periodos as $p) {
                    $pName = $p['periodo'] ?? '';
                    if (preg_match('/^(\d{4})\/(\d{4})$/', $pName, $matches)) {
                        $startYear = (int)$matches[1];
                        if ($highestYear === null || $startYear > $highestYear) {
                            $highestYear = $startYear;
                        }
                    }
                }

                if ($highestYear !== null) {
                    $newPeriodStr = ($highestYear + 1) . '/' . ($highestYear + 2);
                } else {
                    // Fallback se não encontrar padrão YYYY/YYYY
                    if ($dataAdmissao && preg_match('/^(\d{4})-\d{2}-\d{2}$/', $dataAdmissao, $m)) {
                        $y = (int)$m[1];
                    } else {
                        $y = (int)date('Y');
                    }
                    $newPeriodStr = $y . '/' . ($y + 1);
                }
            }

            // Evitar duplicações
            foreach ($periodos as $p) {
                if (($p['periodo'] ?? '') === $newPeriodStr) {
                    return false;
                }
            }

            // Adiciona o novo período
            $periodos[] = [
                'periodo' => $newPeriodStr,
                'dias' => 30,
                'status' => 'aberto',
                'operador' => 'Sistema'
            ];

            // Atualiza no banco
            $stmtUpd = $conn->prepare("UPDATE ponto.funcionarios SET ferias_periodos = :fp, updated_at = NOW() WHERE id = :id");
            $stmtUpd->execute([
                ':fp' => json_encode($periodos),
                ':id' => $funcId
            ]);

            return $newPeriodStr;
        } catch (Exception $e) {
            error_log("Erro em autoGerarSequenciaFerias: " . $e->getMessage());
            return false;
        }
    }
}

try {
    $conn = Database::getConnection();
    $method = $_SERVER['REQUEST_METHOD'];

    $user_name = $_SESSION['user_name'] ?? '';
    $user_setor = $_SESSION['user_setor'] ?? '';
    $user_level = $_SESSION['user_level'] ?? '3';
    // Usa comparação idêntica à de ponto_aprovacao.php (string '1' e nomes/setores em lowercase)
    $isSuper = (
        $user_level == '1' || 
        in_array(strtolower(trim($user_name)), ['corsin', 'crh']) || 
        in_array(strtolower(trim($user_setor)), ['corsin', 'crh'])
    );
    // Libera o bloqueio do arquivo de sessão para permitir requisições simultâneas
    session_write_close();




    // ---- LIST: retorna afastamentos/férias ----
    if ($method === 'GET') {
        $funcId = $_GET['func_id'] ?? null;
        $startDate = $_GET['start_date'] ?? null;
        $endDate = $_GET['end_date'] ?? null;
        
        $sql = "SELECT f.*, func.nome as nome_funcionario, func.matricula 
                FROM ponto.ferias f 
                JOIN ponto.funcionarios func ON f.id_funcionario = func.id 
                WHERE 1=1";
        $params = [];

        if ($funcId) {
            $sql .= " AND f.id_funcionario = :funcId";
            $params[':funcId'] = $funcId;
        }

        if ($startDate) {
            $sql .= " AND f.data_fim >= :start";
            $params[':start'] = $startDate;
        }

        if ($endDate) {
            $sql .= " AND f.data_inicio <= :end";
            $params[':end'] = $endDate;
        }

        $sql .= " ORDER BY f.data_inicio DESC";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input && !empty($_POST)) {
        $input = $_POST;
    }
    $action = $input['action'] ?? '';

    // ---- CREATE / UPDATE ----
    if ($action === 'create' || $action === 'update') {
        // Log para depuração
        file_put_contents('../uploads/debug_ferias.log', date('Y-m-d H:i:s') . " - Action: $action - POST: " . json_encode($_POST) . " - FILES: " . json_encode($_FILES) . "\n", FILE_APPEND);
        
        // Processamento de Múltiplos Anexos
        $anexosAtuais = [];
        $anexoAtualRaw = $_POST['anexo_atual'] ?? $input['anexo_atual'] ?? '';
        
        if (!empty($anexoAtualRaw)) {
            // Remove escapes se existirem (comum em alguns ambientes PHP)
            $anexoAtualRaw = stripslashes($anexoAtualRaw);
            $decoded = json_decode($anexoAtualRaw, true);
            if (is_array($decoded)) {
                $anexosAtuais = $decoded;
            } else if (!empty($anexoAtualRaw) && $anexoAtualRaw !== 'null') {
                $anexosAtuais = [$anexoAtualRaw];
            }
        }

        // Remover anexos marcados para exclusão
        $removidosRaw = $_POST['anexos_removidos'] ?? $input['anexos_removidos'] ?? '[]';
        $removidos = json_decode(stripslashes($removidosRaw), true) ?: [];
        if (!empty($removidos)) {
            $anexosAtuais = array_values(array_filter($anexosAtuais, function($a) use ($removidos) {
                return !in_array($a, $removidos);
            }));
        }

        $files = $_FILES['anexo'] ?? $_FILES['anexo[]'] ?? null;
        if ($files) {
            $fileList = [];
            if (isset($files['name']) && is_array($files['name'])) {
                foreach ($files['name'] as $k => $v) {
                    if ($files['error'][$k] === UPLOAD_ERR_OK) {
                        $fileList[] = [
                            'name' => $files['name'][$k],
                            'tmp_name' => $files['tmp_name'][$k]
                        ];
                    }
                }
            } elseif (isset($files['error']) && $files['error'] === UPLOAD_ERR_OK) {
                $fileList[] = $files;
            }

            $uploadDir = '../uploads/afastamentos/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

            foreach ($fileList as $f) {
                $ext = pathinfo($f['name'], PATHINFO_EXTENSION);
                $filename = uniqid('afast_') . ($ext ? '.' . strtolower($ext) : '');
                if (move_uploaded_file($f['tmp_name'], $uploadDir . $filename)) {
                    $anexosAtuais[] = 'uploads/afastamentos/' . $filename;
                }
            }
        }
        $anexoPath = !empty($anexosAtuais) ? json_encode(array_values(array_unique($anexosAtuais))) : null;

        if ($action === 'create') {
            $stmt = $conn->prepare("INSERT INTO ponto.ferias (id_funcionario, data_inicio, data_fim, observacao, tipo_afastamento, motivo_especifico, periodo_aquisitivo, gestor_solicitante, gestor_setor, anexo, created_at, updated_at, status)
                                    VALUES (:func, :ini, :fim, :obs, :tipo, :espec, :periodo, :g_nome, :g_setor, :anexo, NOW(), NOW(), 'pendente')");
            $stmt->execute([
                ':func' => $input['id_funcionario'],
                ':ini'  => $input['data_inicio'],
                ':fim'  => $input['data_fim'],
                ':obs'  => $input['observacao'] ?? null,
                ':tipo' => $input['tipo_afastamento'] ?? null,
                ':espec'=> $input['motivo_especifico'] ?? null,
                ':periodo'=> $input['periodo_aquisitivo'] ?? null,
                ':g_nome' => $input['gestor_solicitante'] ?? null,
                ':g_setor'=> $input['gestor_setor'] ?? null,
                ':anexo'=> $anexoPath
            ]);
            echo json_encode(['success' => true, 'message' => 'Afastamento solicitado com sucesso! Aguardando deferimento do CRH.']);
        } else {
            $id = $input['id'] ?? null;
            $logPath = '../uploads/debug_ferias.log';
            $logDir = dirname($logPath);
            if (!is_dir($logDir)) mkdir($logDir, 0777, true);
            file_put_contents($logPath, date('Y-m-d H:i:s') . " - Action: update - ID: $id - Input: " . json_encode($input) . "\n", FILE_APPEND);

            if (!$id) {
                file_put_contents($logPath, "  -> Erro: ID do afastamento não fornecido para atualização.\n", FILE_APPEND);
                echo json_encode(['success' => false, 'message' => 'ID do afastamento não fornecido.']);
                exit;
            }

            // 1. Obter o registro antigo antes de fazer qualquer modificação
            $stmtGet = $conn->prepare("SELECT * FROM ponto.ferias WHERE id = :id");
            $stmtGet->execute([':id' => $id]);
            $oldRecord = $stmtGet->fetch(PDO::FETCH_ASSOC);

            if (!$oldRecord) {
                file_put_contents($logPath, "  -> Erro: Registro antigo não encontrado no banco.\n", FILE_APPEND);
                echo json_encode(['success' => false, 'message' => 'Registro de afastamento não encontrado.']);
                exit;
            }

            $funcId = $oldRecord['id_funcionario'];
            $oldStatus = $oldRecord['status'] ?? '';
            $oldTipo = strtolower(trim($oldRecord['tipo_afastamento'] ?? ''));
            $oldIsFerias = ($oldStatus === 'deferido' && ($oldTipo === 'ferias' || $oldTipo === 'férias' || strpos($oldTipo, 'feria') !== false));
            $periodoOld = $oldRecord['periodo_aquisitivo'] ?? null;

            file_put_contents($logPath, "  -> Registro antigo carregado. Status: $oldStatus, Tipo: $oldTipo, PeriodoOld: '$periodoOld', isFerias: " . ($oldIsFerias ? 'true' : 'false') . "\n", FILE_APPEND);

            // 2. Se o registro anterior era férias e estava deferido, restituir os dias ao período original
            if ($oldIsFerias && $periodoOld) {
                $dtIniOld = new DateTime($oldRecord['data_inicio']);
                $dtFimOld = new DateTime($oldRecord['data_fim']);
                $diasOld = (int)$dtIniOld->diff($dtFimOld)->days + 1;

                file_put_contents($logPath, "  -> Restituindo provisoriamente $diasOld dia(s) ao período '$periodoOld' do funcionário $funcId\n", FILE_APPEND);

                // Buscar ferias_periodos do funcionário
                $stmtFunc = $conn->prepare("SELECT ferias_periodos FROM ponto.funcionarios WHERE id = :id");
                $stmtFunc->execute([':id' => $funcId]);
                $func = $stmtFunc->fetch(PDO::FETCH_ASSOC);

                if ($func && !empty($func['ferias_periodos'])) {
                    $periodos = json_decode($func['ferias_periodos'], true) ?: [];
                    $restored = false;

                    foreach ($periodos as &$p) {
                        if (($p['periodo'] ?? '') === $periodoOld) {
                            $p['dias'] = (int)($p['dias'] ?? 0) + $diasOld;
                            if ($p['dias'] > 0 && ($p['status'] ?? '') === 'quitado') {
                                $p['status'] = 'aberto';
                            }
                            $restored = true;
                            break;
                        }
                    }
                    unset($p);

                    if ($restored) {
                        $stmtUpd = $conn->prepare("UPDATE ponto.funcionarios SET ferias_periodos = :fp, updated_at = NOW() WHERE id = :id");
                        $stmtUpd->execute([':fp' => json_encode($periodos), ':id' => $funcId]);
                        file_put_contents($logPath, "  -> Sucesso: Devolvidos $diasOld dia(s) ao período antigo '$periodoOld'.\n", FILE_APPEND);
                    } else {
                        file_put_contents($logPath, "  -> Alerta: Período antigo '$periodoOld' não encontrado nos períodos do funcionário.\n", FILE_APPEND);
                    }
                } else {
                    file_put_contents($logPath, "  -> Alerta: Funcionário sem períodos de férias cadastrados para restituição.\n", FILE_APPEND);
                }
            }

            // 3. Atualizar o registro do afastamento no banco, incluindo o campo periodo_aquisitivo
            $stmt = $conn->prepare("UPDATE ponto.ferias SET data_inicio=:ini, data_fim=:fim, observacao=:obs, tipo_afastamento=:tipo, motivo_especifico=:espec, periodo_aquisitivo=:periodo, anexo=:anexo, status = CASE WHEN status = 'indeferido' THEN 'pendente' ELSE status END, motivo_indeferimento = CASE WHEN status = 'indeferido' THEN NULL ELSE motivo_indeferimento END, updated_at=NOW() WHERE id=:id");
            $stmt->execute([
                ':ini' => $input['data_inicio'], 
                ':fim' => $input['data_fim'], 
                ':obs' => $input['observacao'] ?? null, 
                ':tipo' => $input['tipo_afastamento'] ?? null,
                ':espec' => $input['motivo_especifico'] ?? null,
                ':periodo' => $input['periodo_aquisitivo'] ?? null,
                ':anexo' => $anexoPath,
                ':id' => $id
            ]);

            file_put_contents($logPath, "  -> Registro de afastamento atualizado no banco de dados.\n", FILE_APPEND);

            // 4. Se o novo tipo for férias e o status continua deferido, abater a nova quantidade de dias do novo período
            $newStatus = ($oldStatus === 'indeferido') ? 'pendente' : $oldStatus;
            $newTipo = strtolower(trim($input['tipo_afastamento'] ?? ''));
            $newIsFerias = ($newStatus === 'deferido' && ($newTipo === 'ferias' || $newTipo === 'férias' || strpos($newTipo, 'feria') !== false));
            $periodoNew = $input['periodo_aquisitivo'] ?? null;

            file_put_contents($logPath, "  -> Verificando nova dedução: newStatus: $newStatus, newTipo: $newTipo, periodoNew: '$periodoNew', newIsFerias: " . ($newIsFerias ? 'true' : 'false') . "\n", FILE_APPEND);

            $extraMsg = '';

            if ($newIsFerias && $periodoNew) {
                $dtIniNew = new DateTime($input['data_inicio']);
                $dtFimNew = new DateTime($input['data_fim']);
                $diasNew = (int)$dtIniNew->diff($dtFimNew)->days + 1;

                file_put_contents($logPath, "  -> Deduzindo $diasNew dia(s) do período '$periodoNew' do funcionário $funcId\n", FILE_APPEND);

                // Buscar ferias_periodos do funcionário novamente (atualizado com a devolução anterior)
                $stmtFunc = $conn->prepare("SELECT ferias_periodos FROM ponto.funcionarios WHERE id = :id");
                $stmtFunc->execute([':id' => $funcId]);
                $func = $stmtFunc->fetch(PDO::FETCH_ASSOC);

                if ($func && !empty($func['ferias_periodos'])) {
                    $periodos = json_decode($func['ferias_periodos'], true) ?: [];
                    $diasRestantes = $diasNew;
                    $deduziuAlgum = false;

                    foreach ($periodos as &$p) {
                        if ($diasRestantes <= 0) break;

                        if (($p['periodo'] ?? '') === $periodoNew) {
                            if (($p['status'] ?? 'aberto') === 'aberto' && (int)($p['dias'] ?? 0) > 0) {
                                $disponivel = (int)$p['dias'];
                                $deduzir = min($diasRestantes, $disponivel);
                                $p['dias'] = $disponivel - $deduzir;
                                $diasRestantes -= $deduzir;
                                $deduziuAlgum = true;

                                if ($p['dias'] <= 0) {
                                    $p['status'] = 'quitado';
                                    $p['dias'] = 0;
                                }
                            }
                            break;
                        }
                    }
                    unset($p);

                    if ($deduziuAlgum) {
                        $stmtUpd = $conn->prepare("UPDATE ponto.funcionarios SET ferias_periodos = :fp, updated_at = NOW() WHERE id = :id");
                        $stmtUpd->execute([':fp' => json_encode($periodos), ':id' => $funcId]);
                        
                        $deduziuTotal = $diasNew - $diasRestantes;
                        if ($diasRestantes > 0) {
                            $extraMsg = " ⚠️ {$deduziuTotal} dia(s) abatidos. {$diasRestantes} dia(s) não puderam ser abatidos por saldo insuficiente no período aquisitivo.";
                            file_put_contents($logPath, "  -> Sucesso parcial: Abatidos $deduziuTotal dia(s) de $diasNew. Faltaram $diasRestantes dia(s) por falta de saldo.\n", FILE_APPEND);
                        } else {
                            $extraMsg = " ✅ Saldo de férias atualizado com {$diasNew} dia(s) abatidos do período {$periodoNew}.";
                            file_put_contents($logPath, "  -> Sucesso total: Abatidos todos os $diasNew dia(s) de férias no período '$periodoNew'.\n", FILE_APPEND);
                        }
                    } else {
                        $extraMsg = " ⚠️ Nenhum saldo disponível no período {$periodoNew} para abater {$diasNew} dia(s) de férias.";
                        file_put_contents($logPath, "  -> Alerta: Nenhum saldo disponível no período '$periodoNew' para abater $diasNew dia(s).\n", FILE_APPEND);
                    }
                } else {
                    $extraMsg = " ⚠️ Funcionário sem períodos aquisitivos cadastrados para dedução.";
                    file_put_contents($logPath, "  -> Alerta: Funcionário sem períodos cadastrados para deduzir.\n", FILE_APPEND);
                }
            }

            echo json_encode(['success' => true, 'message' => 'Afastamento atualizado com sucesso!' . $extraMsg]);
        }
        exit;
    }

    // ---- LIST PENDING ----
    if ($action === 'list_pending') {
        if (!$isSuper) {
            // Retorna diagnóstico para facilitar identificação do problema de permissão
            echo json_encode([
                'success' => false, 
                'message' => 'Acesso negado. Nível: ' . $user_level . ' | Nome: ' . $user_name . ' | Setor: ' . $user_setor . ' | isSuper: ' . ($isSuper ? 'true' : 'false')
            ]);
            exit;
        }
        $stmt = $conn->prepare("SELECT f.*, func.nome as nome_funcionario, func.matricula 
                                FROM ponto.ferias f 
                                JOIN ponto.funcionarios func ON f.id_funcionario = func.id 
                                WHERE f.status = 'pendente' 
                                ORDER BY f.created_at ASC");
        $stmt->execute();
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
        exit;
    }

    // ---- APPROVE / DENY ----
    if ($action === 'approve' || $action === 'deny') {
        if (!$isSuper) {
            echo json_encode(['success' => false, 'message' => 'Acesso negado.']);
            exit;
        }
        $newStatus = ($action === 'approve') ? 'deferido' : 'indeferido';
        $motivo = $input['motivo'] ?? null;

        // Buscar o registro antes de alterar status
        $stmtGet = $conn->prepare("SELECT * FROM ponto.ferias WHERE id = :id");
        $stmtGet->execute([':id' => $input['id']]);
        $feriasRecord = $stmtGet->fetch(PDO::FETCH_ASSOC);

        if (!$feriasRecord) {
            echo json_encode(['success' => false, 'message' => 'Registro de afastamento não encontrado.']);
            exit;
        }

        // Atualizar status
        $stmt = $conn->prepare("UPDATE ponto.ferias SET status = :status, motivo_indeferimento = :motivo, updated_at = NOW() WHERE id = :id");
        $stmt->execute([
            ':status' => $newStatus,
            ':motivo' => $motivo,
            ':id'     => $input['id']
        ]);

        $extraMsg = '';

        // ---- ABATIMENTO AUTOMÁTICO DE FÉRIAS ----
        if ($action === 'approve' && strtolower(trim($feriasRecord['tipo_afastamento'] ?? '')) === 'ferias') {
            $funcId        = $feriasRecord['id_funcionario'];
            $periodoAlvo   = $feriasRecord['periodo_aquisitivo'] ?? null;

            // Calcular dias de gozo (inclusivo: início e fim contam)
            $dtIni   = new DateTime($feriasRecord['data_inicio']);
            $dtFim   = new DateTime($feriasRecord['data_fim']);
            $diffDias = (int)$dtIni->diff($dtFim)->days + 1;

            // Buscar ferias_periodos do funcionário
            $stmtFunc = $conn->prepare("SELECT ferias_periodos FROM ponto.funcionarios WHERE id = :id");
            $stmtFunc->execute([':id' => $funcId]);
            $func = $stmtFunc->fetch(PDO::FETCH_ASSOC);

            if ($func && !empty($func['ferias_periodos'])) {
                $periodos = json_decode($func['ferias_periodos'], true) ?: [];
                $diasRestantes = $diffDias;
                $deduziuAlgum  = false;

                foreach ($periodos as &$p) {
                    if ($diasRestantes <= 0) break;

                    // Se temos um período alvo, abater apenas dele; senão abater do primeiro 'aberto'
                    if ($periodoAlvo && ($p['periodo'] ?? '') !== $periodoAlvo) continue;

                    if (($p['status'] ?? 'aberto') === 'aberto' && (int)($p['dias'] ?? 0) > 0) {
                        $disponivel = (int)$p['dias'];
                        $deduzir    = min($diasRestantes, $disponivel);
                        $p['dias']  = $disponivel - $deduzir;
                        $diasRestantes -= $deduzir;
                        $deduziuAlgum   = true;

                        if ($p['dias'] <= 0) {
                            $p['status'] = 'quitado';
                            $p['dias']   = 0;
                        }
                    }
                }
                unset($p);

                if ($deduziuAlgum) {
                    $stmtUpd = $conn->prepare("UPDATE ponto.funcionarios SET ferias_periodos = :fp, updated_at = NOW() WHERE id = :id");
                    $stmtUpd->execute([':fp' => json_encode($periodos), ':id' => $funcId]);

                    $deduziuTotal = $diffDias - $diasRestantes;
                    if ($diasRestantes > 0) {
                        $extraMsg = " ⚠️ {$deduziuTotal} dia(s) abatidos. {$diasRestantes} dia(s) não puderam ser abatidos por saldo insuficiente no período aquisitivo.";
                    } else {
                        $extraMsg = " ✅ {$diffDias} dia(s) de férias abatidos automaticamente do período" . ($periodoAlvo ? " {$periodoAlvo}" : '') . ".";
                    }
                } else {
                    $extraMsg = " ⚠️ Nenhum período aquisitivo disponível para abater {$diffDias} dia(s) de férias.";
                }
            } else {
                $extraMsg = " ⚠️ Funcionário sem períodos aquisitivos cadastrados.";
            }

            // Geração automática de período se necessário (ex: todos quitados ou lista vazia)
            $novoPeriodoCriado = autoGerarSequenciaFerias($conn, $funcId);
            if ($novoPeriodoCriado) {
                $extraMsg .= " 📅 Nova sequência de período gerada automaticamente: {$novoPeriodoCriado} (30 dias saldo, status aberto).";
            }
        }

        echo json_encode(['success' => true, 'message' => 'Status do afastamento atualizado para ' . $newStatus . '.' . $extraMsg]);
        exit;
    }

    // ---- DELETE ----
    if ($action === 'delete') {
        if (!$isSuper) {
            echo json_encode(['success' => false, 'message' => 'Acesso negado: você não tem permissão para excluir afastamentos.']);
            exit;
        }

        $id = $input['id'] ?? null;
        
        // Log de depuração da exclusão
        $logPath = '../uploads/debug_ferias.log';
        $logDir = dirname($logPath);
        if (!is_dir($logDir)) mkdir($logDir, 0777, true);
        file_put_contents($logPath, date('Y-m-d H:i:s') . " - Action: delete - ID: $id\n", FILE_APPEND);

        if (!$id) {
            file_put_contents($logPath, "  -> Erro: ID do afastamento não fornecido.\n", FILE_APPEND);
            echo json_encode(['success' => false, 'message' => 'ID do afastamento não fornecido.']);
            exit;
        }

        // 1. Obter o registro do afastamento antes de deletar
        $stmtGet = $conn->prepare("SELECT * FROM ponto.ferias WHERE id = :id");
        $stmtGet->execute([':id' => $id]);
        $feriasRecord = $stmtGet->fetch(PDO::FETCH_ASSOC);

        if (!$feriasRecord) {
            file_put_contents($logPath, "  -> Erro: Registro de afastamento não encontrado no banco.\n", FILE_APPEND);
            echo json_encode(['success' => false, 'message' => 'Registro de afastamento não encontrado.']);
            exit;
        }

        file_put_contents($logPath, "  -> Registro encontrado: " . json_encode($feriasRecord) . "\n", FILE_APPEND);

        $extraMsg = '';

        // 2. Se o status for 'deferido' e o tipo de afastamento for férias, restituir os dias
        $status = $feriasRecord['status'] ?? '';
        $tipo = strtolower(trim($feriasRecord['tipo_afastamento'] ?? ''));
        
        // Comparação flexível para férias com ou sem acento
        $isFerias = ($tipo === 'ferias' || $tipo === 'férias' || strpos($tipo, 'feria') !== false);

        file_put_contents($logPath, "  -> Validando: Status = '$status', Tipo = '$tipo', isFerias = " . ($isFerias ? 'true' : 'false') . "\n", FILE_APPEND);

        if ($status === 'deferido' && $isFerias) {
            $funcId        = $feriasRecord['id_funcionario'];
            $periodoAlvo   = $feriasRecord['periodo_aquisitivo'] ?? null;

            file_put_contents($logPath, "  -> Processando Restituição: FuncID = $funcId, PeriodoAlvo = '$periodoAlvo'\n", FILE_APPEND);

            if ($periodoAlvo) {
                // Calcular dias a restituir
                $dtIni   = new DateTime($feriasRecord['data_inicio']);
                $dtFim   = new DateTime($feriasRecord['data_fim']);
                $diffDias = (int)$dtIni->diff($dtFim)->days + 1;

                file_put_contents($logPath, "  -> Dias calculados para gozo: $diffDias\n", FILE_APPEND);

                // Buscar ferias_periodos do funcionário
                $stmtFunc = $conn->prepare("SELECT ferias_periodos FROM ponto.funcionarios WHERE id = :id");
                $stmtFunc->execute([':id' => $funcId]);
                $func = $stmtFunc->fetch(PDO::FETCH_ASSOC);

                if ($func && !empty($func['ferias_periodos'])) {
                    $periodos = json_decode($func['ferias_periodos'], true) ?: [];
                    file_put_contents($logPath, "  -> Periodos atuais do funcionário: " . json_encode($periodos) . "\n", FILE_APPEND);
                    
                    $restituiu = false;

                    foreach ($periodos as &$p) {
                        if (($p['periodo'] ?? '') === $periodoAlvo) {
                            $oldDias = $p['dias'] ?? 0;
                            $p['dias'] = (int)($p['dias'] ?? 0) + $diffDias;
                            
                            file_put_contents($logPath, "  -> Encontrado Período: '$periodoAlvo'. Dias antigos: $oldDias, Dias novos: " . $p['dias'] . "\n", FILE_APPEND);

                            // Se o período estava quitado, ele volta a ficar aberto pois agora possui dias de saldo
                            if ($p['dias'] > 0 && ($p['status'] ?? '') === 'quitado') {
                                $p['status'] = 'aberto';
                                file_put_contents($logPath, "  -> Status do período alterado de quitado para aberto.\n", FILE_APPEND);
                            }
                            $restituiu = true;
                            break;
                        }
                    }
                    unset($p);

                    if ($restituiu) {
                        $stmtUpd = $conn->prepare("UPDATE ponto.funcionarios SET ferias_periodos = :fp, updated_at = NOW() WHERE id = :id");
                        $stmtUpd->execute([':fp' => json_encode($periodos), ':id' => $funcId]);
                        $extraMsg = " ✅ {$diffDias} dia(s) de férias restituídos ao período aquisitivo {$periodoAlvo}.";
                        file_put_contents($logPath, "  -> Sucesso: Saldo de férias atualizado no banco.\n", FILE_APPEND);
                    } else {
                        $extraMsg = " ⚠️ Período aquisitivo {$periodoAlvo} não foi encontrado no cadastro do funcionário para restituição.";
                        file_put_contents($logPath, "  -> Alerta: Período aquisitivo {$periodoAlvo} não encontrado nos registros do funcionário.\n", FILE_APPEND);
                    }
                } else {
                    $extraMsg = " ⚠️ Funcionário não possui períodos aquisitivos cadastrados para restituição.";
                    file_put_contents($logPath, "  -> Alerta: Funcionário sem períodos aquisitivos cadastrados no JSON ferias_periodos.\n", FILE_APPEND);
                }
            } else {
                $extraMsg = " ⚠️ Registro de férias não possui um período aquisitivo denominado para restituição.";
                file_put_contents($logPath, "  -> Alerta: periodo_aquisitivo no registro de afastamento está nulo ou vazio.\n", FILE_APPEND);
            }
        } else {
            file_put_contents($logPath, "  -> Ignorando restituição porque status não é 'deferido' ou não é do tipo 'ferias'.\n", FILE_APPEND);
        }

        // 3. Deletar o registro de afastamento
        $conn->prepare("DELETE FROM ponto.ferias WHERE id = :id")->execute([':id' => $id]);
        file_put_contents($logPath, "  -> Registro de afastamento $id excluído fisicamente do banco.\n", FILE_APPEND);

        echo json_encode(['success' => true, 'message' => 'Registro de afastamento removido.' . $extraMsg]);
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Ação inválida.']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}


