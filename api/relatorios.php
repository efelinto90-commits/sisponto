<?php
header('Content-Type: application/json');
require_once '../config/Database.php';

use Config\Database;

try {
    $conn = Database::getConnection();

    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input && !empty($_POST)) {
            $input = $_POST;
        }
        
        if (isset($input['action']) && $input['action'] == 'justificar') {
            $id = $input['id'] ?? null;
            if (!$id) {
                echo json_encode(['success' => false, 'message' => 'ID inválido.']);
                exit;
            }

            $is_virtual = (strpos($id, 'v_') === 0);
            $virtual_data = null;
            $virtual_func = null;

            if ($is_virtual) {
                // Formato: v_{id_funcionario}_{data}
                $parts = explode('_', $id);
                if (count($parts) >= 3) {
                    $virtual_func = $parts[1];
                    $virtual_data = $parts[2];
                } else {
                    echo json_encode(['success' => false, 'message' => 'ID virtual inválido.']);
                    exit;
                }
            }

            // Verificar se o registro está finalizado e se o usuário tem permissão para alterar
            if (session_status() === PHP_SESSION_NONE) session_start();
            $user_name = $_SESSION['user_name'] ?? '';
            $user_setor = $_SESSION['user_setor'] ?? '';
            $user_level = $_SESSION['user_level'] ?? 3;
            $strict = isset($_POST['strict_sector']) && $_POST['strict_sector'] == '1';
            if ($strict) {
                $is_super_api = ($user_level == 1);
            } else {
                $is_super_api = ($user_level == 1) || in_array(strtolower(trim($user_name)), ['corsin', 'crh']) || in_array(strtolower(trim($user_setor)), ['corsin', 'crh']);
            }

            if (!$is_super_api && !$is_virtual) {
                $checkStmt = $conn->prepare("SELECT status_crh FROM registros WHERE id = :id");
                $checkStmt->execute([':id' => $id]);
                $current = $checkStmt->fetch(PDO::FETCH_ASSOC);
                if ($current && $current['status_crh'] === 'deferido') {
                    echo json_encode(['success' => false, 'message' => 'Este registro já foi DEFERIDO pelo CRH e não pode ser alterado por gestores de setor.']);
                    exit;
                }
            }

            // --- NOVA LÓGICA DE MÚLTIPLOS ANEXOS ---
            $anexosAtuais = [];
            if (!$is_virtual) {
                $getOld = $conn->prepare("SELECT anexo_justificativa FROM registros WHERE id = :id");
                $getOld->execute([':id' => $id]);
                $oldRow = $getOld->fetch(PDO::FETCH_ASSOC);
                if ($oldRow && !empty($oldRow['anexo_justificativa'])) {
                    $decoded = json_decode($oldRow['anexo_justificativa'], true);
                    if (is_array($decoded)) {
                        $anexosAtuais = $decoded;
                    } else {
                        $anexosAtuais = [$oldRow['anexo_justificativa']]; // Formato antigo (string simples)
                    }
                }
            }

            // Remover anexos marcados (se enviado pelo frontend)
            if (isset($input['anexos_removidos']) && !empty($input['anexos_removidos'])) {
                $removidosInput = $input['anexos_removidos'];
                $removidos = [];
                
                if (is_array($removidosInput)) {
                    $removidos = $removidosInput;
                } else {
                    $json = json_decode($removidosInput, true);
                    if (is_array($json)) {
                        $removidos = $json;
                    } else {
                        $removidos = explode(',', $removidosInput);
                    }
                }

                $anexosAtuais = array_values(array_filter($anexosAtuais, function($val) use ($removidos) {
                    return !in_array($val, $removidos);
                }));
            }

            // Adicionar Novos Arquivos
            if (isset($_FILES['anexo'])) {
                $files = $_FILES['anexo'];
                // Normalizar estrutura $_FILES para múltiplos ou único
                if (!is_array($files['name'])) {
                    $fileList = [
                        ['name' => $files['name'], 'type' => $files['type'], 'tmp_name' => $files['tmp_name'], 'error' => $files['error'], 'size' => $files['size']]
                    ];
                } else {
                    $fileList = [];
                    foreach ($files['name'] as $k => $v) {
                        $fileList[] = ['name' => $files['name'][$k], 'type' => $files['type'][$k], 'tmp_name' => $files['tmp_name'][$k], 'error' => $files['error'][$k], 'size' => $files['size'][$k]];
                    }
                }

                $uploadDir = '../uploads/justificativas/';
                if (!is_dir($uploadDir)) { mkdir($uploadDir, 0777, true); }

                foreach ($fileList as $f) {
                    if ($f['error'] === UPLOAD_ERR_OK) {
                        if ($f['size'] > 3 * 1024 * 1024) continue; // Pula se > 3MB
                        
                        $ext = pathinfo($f['name'], PATHINFO_EXTENSION);
                        $filename = uniqid('just_') . ($ext ? '.' . strtolower($ext) : '');
                        $destination = $uploadDir . $filename;
                        if (move_uploaded_file($f['tmp_name'], $destination)) {
                            $anexosAtuais[] = 'uploads/justificativas/' . $filename;
                        }
                    }
                }
            }

            // Reaproveitar Anexo do Comunicado (Se solicitado e não repetido)
            if (isset($input['usar_anexo_comunicado']) && $input['usar_anexo_comunicado'] == '1') {
                if (!$is_virtual) {
                    $getAnexo = $conn->prepare("SELECT anexo_comunicado FROM registros WHERE id = :id");
                    $getAnexo->execute([':id' => $id]);
                    $comRow = $getAnexo->fetch(PDO::FETCH_ASSOC);
                    if ($comRow && !empty($comRow['anexo_comunicado'])) {
                        $comPath = $comRow['anexo_comunicado'];
                        if (!in_array($comPath, $anexosAtuais)) {
                            $anexosAtuais[] = $comPath;
                        }
                    }
                }
            }

            $finalAnexoJson = !empty($anexosAtuais) ? json_encode(array_values(array_unique($anexosAtuais))) : null;

            if ($is_virtual) {
                // Criar NOVO registro para dia sem batida
                $sql = "INSERT INTO registros (
                    id_funcionario, data, justificativa, tipo_justificativa, 
                    status_turno1, status_turno2, just_ent1, just_sai1, just_ent2, just_sai2,
                    aviso, anexo_justificativa, created_at, updated_at
                ) VALUES (
                    :func_id, :data, :justificativa, :tipo_justificativa,
                    :st1, :st2, :j_e1, :j_s1, :j_e2, :j_s2,
                    :aviso, :anexo, NOW(), NOW()
                )";
                $params = [
                    ':func_id' => $virtual_func,
                    ':data' => $virtual_data,
                    ':justificativa' => $input['justificativa'] ?? null,
                    ':tipo_justificativa' => $input['tipo_justificativa'] ?? null,
                    ':st1' => $input['status_turno1'] ?? '',
                    ':st2' => $input['status_turno2'] ?? '',
                    ':j_e1' => $input['just_ent1'] ?? null,
                    ':j_s1' => $input['just_sai1'] ?? null,
                    ':j_e2' => $input['just_ent2'] ?? null,
                    ':j_s2' => $input['just_sai2'] ?? null,
                    ':aviso' => $input['aviso'] ?? null,
                    ':anexo' => $finalAnexoJson
                ];
            } else {
                $sql = "UPDATE registros SET 
                    justificativa = :justificativa, tipo_justificativa = :tipo_justificativa, 
                    status_turno1 = :st1, status_turno2 = :st2, 
                    just_ent1 = :j_e1, just_sai1 = :j_s1, just_ent2 = :j_e2, just_sai2 = :j_s2,
                    aviso = :aviso, anexo_justificativa = :anexo,
                    updated_at = NOW() WHERE id = :id";
                
                $params = [
                    ':justificativa' => $input['justificativa'] ?? null,
                    ':tipo_justificativa' => $input['tipo_justificativa'] ?? null,
                    ':st1' => $input['status_turno1'] ?? '',
                    ':st2' => $input['status_turno2'] ?? '',
                    ':j_e1' => $input['just_ent1'] ?? null,
                    ':j_s1' => $input['just_sai1'] ?? null,
                    ':j_e2' => $input['just_ent2'] ?? null,
                    ':j_s2' => $input['just_sai2'] ?? null,
                    ':aviso' => $input['aviso'] ?? null,
                    ':anexo' => $finalAnexoJson,
                    ':id' => $id
                ];
            }
            
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            
            echo json_encode(['success' => true, 'message' => 'Justificativa salva com sucesso!']);
            exit;
        }

        if (isset($input['action']) && $input['action'] == 'enviar_crh') {
            $ids = $input['ids'] ?? [];
            if (!is_array($ids) || empty($ids)) {
                echo json_encode(['success' => false, 'message' => 'Nenhum registro selecionado.']);
                exit;
            }
            
            $realIds = [];
            foreach ($ids as $id) {
                if (strpos($id, 'v_') === 0) {
                    $parts = explode('_', $id);
                    if (count($parts) >= 3) {
                        $fId = $parts[1];
                        $data = $parts[2];
                        
                        // Verificar se já não existe (prevenção contra duplicidade se o usuário clicar rápido ou algo assim)
                        $check = $conn->prepare("SELECT id FROM registros WHERE id_funcionario = ? AND data = ?");
                        $check->execute([$fId, $data]);
                        $dbId = $check->fetchColumn();
                        
                        if (!$dbId) {
                            $ins = $conn->prepare("INSERT INTO registros (id_funcionario, data, enviado_crh, status_crh, created_at, updated_at) VALUES (?, ?, TRUE, 'pendente', NOW(), NOW())");
                            $ins->execute([$fId, $data]);
                            $dbId = $conn->lastInsertId();
                        }
                        
                        if ($dbId) $realIds[] = (int)$dbId;
                    }
                } else {
                    $realIds[] = (int)$id;
                }
            }
            
            if (!empty($realIds)) {
                $placeholders = implode(',', array_fill(0, count($realIds), '?'));
                $stmt = $conn->prepare("UPDATE registros SET enviado_crh = TRUE, status_crh = CASE WHEN status_crh = 'indeferido' THEN 'pendente' ELSE status_crh END, updated_at = NOW() WHERE id IN ($placeholders)");
                $stmt->execute($realIds);
            }
            
            echo json_encode(['success' => true, 'message' => 'Envio ao CRH realizado com sucesso!']);
            exit;
        }

        if (isset($input['action']) && $input['action'] == 'deferir_crh') {
            $id = $input['id'] ?? null;
            if (!$id) {
                echo json_encode(['success' => false, 'message' => 'ID inválido.']);
                exit;
            }

            if (session_status() === PHP_SESSION_NONE) session_start();
            $user_name = $_SESSION['user_name'] ?? 'Sistema';
            $user_setor = $_SESSION['user_setor'] ?? '';
            $user_level = $_SESSION['user_level'] ?? 3;
            
            $is_super_api = ($user_level == 1) || in_array(strtolower(trim($user_name)), ['corsin', 'crh']) || in_array(strtolower(trim($user_setor)), ['corsin', 'crh']);

            // Verificar se já existe uma decisão e se o usuário é admin/gestor para alterar
            $stmt = $conn->prepare("SELECT status_crh, justificativa FROM registros WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($row && !empty($row['status_crh']) && $row['status_crh'] !== 'pendente' && !$is_super_api) {
                echo json_encode(['success' => false, 'message' => 'Apenas administradores ou gestores do CRH podem alterar uma decisão já tomada.']);
                exit;
            }

            $just = $row ? $row['justificativa'] : '';
            
            if (!empty($just) && strpos($just, '[INDEFERIDO PELO CRH]:') !== false) {
                $lines = explode("\n", $just);
                $filteredLines = array_filter($lines, function($line) {
                    return strpos($line, '[INDEFERIDO PELO CRH]:') === false;
                });
                $just = trim(implode("\n", $filteredLines));
            }

            // Obter histórico atual
            $stmtHist = $conn->prepare("SELECT crh_history FROM registros WHERE id = :id");
            $stmtHist->execute([':id' => $id]);
            $histJson = $stmtHist->fetchColumn();
            $history = json_decode($histJson ?: '[]', true);
            
            // Adicionar nova entrada ao histórico
            $history[] = [
                'action' => 'deferido',
                'user' => $user_name,
                'timestamp' => date('Y-m-d H:i:s'),
                'motivo' => ''
            ];

            $stmt = $conn->prepare("UPDATE registros SET status_crh = 'deferido', justificativa = :just, crh_user = :user, crh_updated_at = NOW(), crh_history = :history, updated_at = NOW() WHERE id = :id");
            $stmt->execute([':id' => $id, ':just' => $just, ':user' => $user_name, ':history' => json_encode($history)]);
            echo json_encode(['success' => true, 'message' => 'Justificativa deferida com sucesso.']);
            exit;
        }

        if (isset($input['action']) && $input['action'] == 'indeferir_crh') {
            $id = $input['id'] ?? null;
            $motivo = $input['motivo'] ?? '';
            if (!$id || empty(trim($motivo))) {
                echo json_encode(['success' => false, 'message' => 'Motivo da recusa é obrigatório.']);
                exit;
            }

            if (session_status() === PHP_SESSION_NONE) session_start();
            $user_name = $_SESSION['user_name'] ?? 'Sistema';
            $user_setor = $_SESSION['user_setor'] ?? '';
            $user_level = $_SESSION['user_level'] ?? 3;
            
            $is_super_api = ($user_level == 1) || in_array(strtolower(trim($user_name)), ['corsin', 'crh']) || in_array(strtolower(trim($user_setor)), ['corsin', 'crh']);

            $stmt = $conn->prepare("SELECT status_crh, justificativa FROM registros WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($row && !empty($row['status_crh']) && $row['status_crh'] !== 'pendente' && !$is_super_api) {
                echo json_encode(['success' => false, 'message' => 'Apenas administradores ou gestores do CRH podem alterar uma decisão já tomada.']);
                exit;
            }

            $just = $row ? $row['justificativa'] : '';
            
            if (!empty($just) && strpos($just, '[INDEFERIDO PELO CRH]:') !== false) {
                $lines = explode("\n", $just);
                $filteredLines = array_filter($lines, function($line) {
                    return strpos($line, '[INDEFERIDO PELO CRH]:') === false;
                });
                $just = trim(implode("\n", $filteredLines));
            }

            $reasonTag = "[INDEFERIDO PELO CRH]: " . trim($motivo);
            $newJust = !empty($just) ? $just . "\n" . $reasonTag : $reasonTag;

            // Obter histórico atual
            $stmtHist = $conn->prepare("SELECT crh_history FROM registros WHERE id = :id");
            $stmtHist->execute([':id' => $id]);
            $histJson = $stmtHist->fetchColumn();
            $history = json_decode($histJson ?: '[]', true);
            
            // Adicionar nova entrada ao histórico
            $history[] = [
                'action' => 'indeferido',
                'user' => $user_name,
                'timestamp' => date('Y-m-d H:i:s'),
                'motivo' => trim($motivo)
            ];

            $stmt = $conn->prepare("
                UPDATE registros 
                SET status_crh = 'indeferido',
                    justificativa = :just,
                    crh_user = :user,
                    crh_updated_at = NOW(),
                    crh_history = :history,
                    updated_at = NOW()
                WHERE id = :id
            ");
            $stmt->execute([':id' => $id, ':just' => $newJust, ':user' => $user_name, ':history' => json_encode($history)]);
            echo json_encode(['success' => true, 'message' => 'Justificativa recusada. O turno voltará a constar como falta.']);
            exit;
        }
    }

    // Filtros
    if (session_status() === PHP_SESSION_NONE) session_start();
    
    $startDate = $_GET['start_date'] ?? date('Y-m-01'); // Inicio do mês atual
    $endDate = $_GET['end_date'] ?? date('Y-m-t'); // Fim do mês atual
    $funcId = $_GET['func_id'] ?? '';

    $params = [':start' => $startDate, ':end' => $endDate];
    $sqlFilter = "";

    // Filtro de Segurança por Setor
    $user_name = $_SESSION['user_name'] ?? '';
    $user_setor = $_SESSION['user_setor'] ?? '';
    $user_level = $_SESSION['user_level'] ?? 3;
    $strict = isset($_GET['strict_sector']) && $_GET['strict_sector'] == '1';
    if ($strict) {
        $is_super = ($user_level == 1);
    } else {
        $is_super = ($user_level == 1) || in_array(strtolower(trim($user_name)), ['corsin', 'crh']) || in_array(strtolower(trim($user_setor)), ['corsin', 'crh']);
    }

    if (!$is_super) {
        if (!empty($user_setor)) {
            $sqlFilter .= " AND (f.setor = :user_setor OR f.setor2 = :user_setor)";
            $params[':user_setor'] = $user_setor;
        } else {
            $sqlFilter .= " AND (f.setor IS NULL OR TRIM(f.setor) = '')";
        }
    }

    if (!empty($funcId)) {
        $sqlFilter .= " AND r.id_funcionario = :funcId";
        $params[':funcId'] = $funcId;
    }

    // Filtro de Status CRH
    $filtroCrh = $_GET['filtro_crh'] ?? '';
    if ($filtroCrh === 'enviados') {
        $sqlFilter .= " AND r.enviado_crh = TRUE";
    } elseif ($filtroCrh === 'nao_enviados') {
        $sqlFilter .= " AND (r.enviado_crh = FALSE OR r.enviado_crh IS NULL)";
    }

    $sql = "
        SELECT 
            r.id, r.data, f.id AS id_funcionario, f.nome, f.matricula, f.setor, f.setor2, 
            r.primeiro_ponto, r.segundo_ponto, r.terceiro_ponto, r.quarto_ponto,
            r.atrasou_primeiro_ponto, r.atrasou_segundo_ponto, r.atrasou_terceiro_ponto, r.atrasou_quarto_ponto,
            r.justificativa, r.tipo_justificativa, r.anexo_justificativa, r.status_turno1, r.status_turno2,
            r.just_ent1, r.just_sai1, r.just_ent2, r.just_sai2, r.enviado_crh, r.status_crh, r.aviso, r.comunicado, r.anexo_comunicado,
            r.crh_user, r.crh_updated_at, r.crh_history,
            h.primeiro_horario, h.segundo_horario, h.terceiro_horario, h.quarto_horario, h.tolerancia_entrada, h.tolerancia_saida,
            f.grade_horarios
        FROM registros r
        JOIN funcionarios f ON r.id_funcionario = f.id
        LEFT JOIN horarios h ON f.id_horario = h.id
        WHERE r.data BETWEEN :start AND :end
        $sqlFilter
        ORDER BY r.data DESC, f.nome ASC
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $registrosExistentes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Organizar registros existentes por data e funcionário para consulta rápida
    $mapaRegistros = [];
    foreach ($registrosExistentes as $r) {
        $mapaRegistros[$r['data']][$r['id_funcionario']] = $r;
    }

    // Buscar liberações de ponto no período para processamento de feriados/facultativos
    $stmtLib = $conn->prepare("SELECT * FROM ponto_liberado WHERE DATE(data_hora) BETWEEN :start AND :end");
    $stmtLib->execute([':start' => $startDate, ':end' => $endDate]);
    $liberacoes = $stmtLib->fetchAll(PDO::FETCH_ASSOC);

    // Se um funcionário específico for filtrado, vamos preencher as lacunas do período
    // Caso contrário, se o setor estiver filtrado, podemos também preencher as lacunas para todos do setor
    $funcionariosParaPreencher = [];
    if (!empty($funcId)) {
        $stmtF = $conn->prepare("SELECT f.*, h.primeiro_horario, h.segundo_horario, h.terceiro_horario, h.quarto_horario, h.tolerancia_entrada, h.tolerancia_saida 
                                 FROM funcionarios f LEFT JOIN horarios h ON f.id_horario = h.id WHERE f.id = :id");
        $stmtF->execute([':id' => $funcId]);
        $f = $stmtF->fetch(PDO::FETCH_ASSOC);
        if ($f) $funcionariosParaPreencher[] = $f;
    } else if (!$is_super || !empty($user_setor)) {
        // Se houver filtro de setor (ou for gestor), preenche para todos do setor visível
        $sqlF = "SELECT f.*, h.primeiro_horario, h.segundo_horario, h.terceiro_horario, h.quarto_horario, h.tolerancia_entrada, h.tolerancia_saida 
                 FROM funcionarios f LEFT JOIN horarios h ON f.id_horario = h.id " . ($is_super ? "WHERE 1=1" : "WHERE (f.setor = :s OR f.setor2 = :s)");
        $stmtF = $conn->prepare($sqlF);
        if (!$is_super) $stmtF->execute([':s' => $user_setor]);
        else $stmtF->execute();
        $funcionariosParaPreencher = $stmtF->fetchAll(PDO::FETCH_ASSOC);
    }

    $registrosFinal = [];
    $dataInicio = new DateTime($startDate);
    $dataFim = new DateTime($endDate);
    
    // Gerar registros para cada dia e cada funcionário relevante
    $periodo = new DatePeriod($dataInicio, new DateInterval('P1D'), (clone $dataFim)->modify('+1 day'));

    foreach ($periodo as $d) {
        $dataStr = $d->format('Y-m-d');
        
        foreach ($funcionariosParaPreencher as $f) {
            if (isset($mapaRegistros[$dataStr][$f['id']])) {
                // Já existe registro no banco, usar o do banco
                $registrosFinal[] = $mapaRegistros[$dataStr][$f['id']];
            } else {
                // Criar registro VIRTUAL
                // Mas apenas se o funcionário tiver horário nesse dia (evitar finais de semana se não for escala)
                
                $mapaDias = [
                    'Monday' => 'segunda', 'Tuesday' => 'terca', 'Wednesday' => 'quarta',
                    'Thursday' => 'quinta', 'Friday' => 'sexta', 'Saturday' => 'sabado', 'Sunday' => 'domingo'
                ];
                $diaSemana = $mapaDias[$d->format('l')];
                $grade = json_decode($f['grade_horarios'] ?? '[]', true);
                $temHorario = false;

                // Verificar se há horário na grade ou se o horário padrão tem entradas
                if (isset($grade[$diaSemana]) && !empty($grade[$diaSemana])) {
                    $temHorario = true;
                } else if (!empty($f['primeiro_horario']) || !empty($f['terceiro_horario'])) {
                    // Se não tem grade, mas tem horário fixo, consideramos que trabalha dias úteis (seg-sex)
                    $nDia = (int)$d->format('N'); // 1 (Mon) to 7 (Sun)
                    if ($nDia <= 5) $temHorario = true;
                }

                if ($temHorario) {
                    $registrosFinal[] = [
                        'id' => "v_{$f['id']}_{$dataStr}",
                        'data' => $dataStr,
                        'id_funcionario' => $f['id'],
                        'nome' => $f['nome'],
                        'matricula' => $f['matricula'],
                        'setor' => $f['setor'],
                        'setor2' => $f['setor2'],
                        'primeiro_ponto' => null,
                        'segundo_ponto' => null,
                        'terceiro_ponto' => null,
                        'quarto_ponto' => null,
                        'justificativa' => null,
                        'tipo_justificativa' => null,
                        'anexo_justificativa' => null,
                        'status_turno1' => null,
                        'status_turno2' => null,
                        'enviado_crh' => false,
                        'status_crh' => 'pendente',
                        'atrasou_primeiro_ponto' => false,
                        'atrasou_segundo_ponto' => false,
                        'atrasou_terceiro_ponto' => false,
                        'atrasou_quarto_ponto' => false,
                        'just_ent1' => null,
                        'just_sai1' => null,
                        'just_ent2' => null,
                        'just_sai2' => null,
                        'aviso' => null,
                        'comunicado' => null,
                        'crh_user' => null,
                        'crh_updated_at' => null,
                        'crh_history' => null,
                        'primeiro_horario' => $f['primeiro_horario'],
                        'segundo_horario' => $f['segundo_horario'],
                        'terceiro_horario' => $f['terceiro_horario'],
                        'quarto_horario' => $f['quarto_horario'],
                        'tolerancia_entrada' => $f['tolerancia_entrada'],
                        'tolerancia_saida' => $f['tolerancia_saida'],
                        'grade_horarios' => $f['grade_horarios'],
                        'virtual' => true
                    ];
                }
            }
        }
    }

    // Se NÃO houver filtro de funcionário nem de setor (muito raro nesse report), manter apenas os existentes
    if (empty($registrosFinal)) {
        $registrosFinal = $registrosExistentes;
    } else {
        // Ordenar por data DESC
        usort($registrosFinal, function($a, $b) {
            if ($a['data'] === $b['data']) return strcmp($a['nome'] ?? '', $b['nome'] ?? '');
            return strcmp($b['data'], $a['data']);
        });
    }

    $registros = &$registrosFinal;

    // Buscar todos os horários para lookup rápido no processamento da grade
    $stmtH = $conn->query("SELECT * FROM horarios");
    $todosHorarios = [];
    while($h = $stmtH->fetch(PDO::FETCH_ASSOC)) {
        $todosHorarios[$h['id']] = $h;
    }

    // Buscar períodos de afastamentos ativos no período consultado
    $stmtFerias = $conn->prepare("SELECT id_funcionario, data_inicio, data_fim, tipo_afastamento, motivo_especifico FROM ferias WHERE data_inicio <= :end AND data_fim >= :start");
    $stmtFerias->execute([':start' => $startDate, ':end' => $endDate]);
    $feriasRows = $stmtFerias->fetchAll();

    // Índice por funcionário para lookup rápido
    $feriasPorFunc = [];
    foreach ($feriasRows as $f) {
        $feriasPorFunc[$f['id_funcionario']][] = [
            'ini' => $f['data_inicio'], 
            'fim' => $f['data_fim'],
            'tipo' => $f['tipo_afastamento'],
            'espec' => $f['motivo_especifico']
        ];
    }

    // Processar faltas automáticas
    $hojeStr = date('Y-m-d');
    $agoraStr = date('H:i:s');

    foreach ($registros as &$reg) {
        $regDate = $reg['data'];
        $funcId = $reg['id_funcionario'] ?? null;

        // Associar liberação de ponto se houver
        $reg['liberacao'] = null;
        foreach ($liberacoes as $lib) {
            $libDate = date('Y-m-d', strtotime($lib['data_hora']));
            if ($libDate === $regDate) {
                if ($lib['setor'] === 'TODOS' || $lib['setor'] === ($reg['setor'] ?? '') || $lib['setor'] === ($reg['setor2'] ?? '')) {
                    $reg['liberacao'] = [
                        'id' => $lib['id'],
                        'data_hora' => $lib['data_hora'],
                        'descricao' => $lib['descricao'],
                        'justificativa' => $lib['justificativa'] ?? ''
                    ];
                    break;
                }
            }
        }

        $reg['falta_turno1_entrada'] = false;
        $reg['falta_turno1_saida'] = false;
        $reg['falta_turno2_entrada'] = false;
        $reg['falta_turno2_saida'] = false;
        $reg['em_ferias'] = false;
        $reg['motivo_afastamento'] = null;

        // Verificar se está em afastamento neste dia → ignorar faltas
        if ($funcId && isset($feriasPorFunc[$funcId])) {
            foreach ($feriasPorFunc[$funcId] as $periodo) {
                if ($regDate >= $periodo['ini'] && $regDate <= $periodo['fim']) {
                    $reg['em_ferias'] = true;
                    $reg['motivo_afastamento'] = ($periodo['tipo'] === 'outros') ? ($periodo['espec'] ?: 'Outros') : ($periodo['tipo'] ?: 'Afastamento');
                    break;
                }
            }
        }
        if ($reg['em_ferias'])
            continue;

        // ---- Aplicar Grade de Horários Específica para o Dia ----
        $mapaDias = [
            'Monday' => 'segunda', 'Tuesday' => 'terca', 'Wednesday' => 'quarta',
            'Thursday' => 'quinta', 'Friday' => 'sexta', 'Saturday' => 'sabado', 'Sunday' => 'domingo'
        ];
        $diaSemana = $mapaDias[date('l', strtotime($regDate))];
        $grade = json_decode($reg['grade_horarios'] ?? '[]', true);

        if (isset($grade[$diaSemana]) && !empty($grade[$diaSemana])) {
            $diaData = $grade[$diaSemana];
            
            if (is_array($diaData) && isset($diaData['horario_id']) && !empty($diaData['horario_id'])) {
                $hId = $diaData['horario_id'];
                if (isset($todosHorarios[$hId])) {
                    $hEsp = $todosHorarios[$hId];
                    $reg['primeiro_horario'] = $hEsp['primeiro_horario'];
                    $reg['segundo_horario'] = $hEsp['segundo_horario'];
                    $reg['terceiro_horario'] = $hEsp['terceiro_horario'];
                    $reg['quarto_horario'] = $hEsp['quarto_horario'];
                    $reg['tolerancia_entrada'] = $hEsp['tolerancia_entrada'];
                    $reg['tolerancia_saida'] = $hEsp['tolerancia_saida'];
                }
            } else if (is_array($diaData)) {
                $p1 = $diaData[0] ?? ($diaData['p1'] ?? null);
                $p2 = $diaData[1] ?? ($diaData['p2'] ?? null);
                $p3 = $diaData[2] ?? ($diaData['p3'] ?? null);
                $p4 = $diaData[3] ?? ($diaData['p4'] ?? null);
                
                if ($p1 || $p2 || $p3 || $p4) {
                   $reg['primeiro_horario'] = $p1;
                   $reg['segundo_horario'] = $p2;
                   $reg['terceiro_horario'] = $p3;
                   $reg['quarto_horario'] = $p4;
                }
            }
        }

        // Se o registro é de uma data futura, ignora
        if ($regDate > $hojeStr)
            continue;

        $toleranciaEntrada = isset($reg['tolerancia_entrada']) ? (int) $reg['tolerancia_entrada'] : 15;
        $toleranciaSaida = isset($reg['tolerancia_saida']) ? (int) $reg['tolerancia_saida'] : 15;

        // Turno 1 - Entrada (primeiro_ponto)
        if ($reg['primeiro_horario'] && empty($reg['primeiro_ponto'])) {
            $limite = date('H:i:s', strtotime($reg['primeiro_horario']) + ($toleranciaEntrada * 60));
            if ($regDate < $hojeStr || ($regDate == $hojeStr && $agoraStr > $limite)) {
                $reg['falta_turno1_entrada'] = true;
            }
        } elseif ($reg['primeiro_horario'] && !empty($reg['primeiro_ponto'])) {
            // Recalcular atraso se houver batida
            $pontoSeg = strtotime($reg['primeiro_ponto']);
            $horaSeg = strtotime($reg['primeiro_horario']);
            if ($pontoSeg > ($horaSeg + ($toleranciaEntrada * 60))) {
                $reg['atrasou_primeiro_ponto'] = true;
            } else {
                $reg['atrasou_primeiro_ponto'] = false;
            }
        }

        // Turno 1 - Saída (segundo_ponto)
        if ($reg['segundo_horario'] && empty($reg['segundo_ponto'])) {
            if ($regDate < $hojeStr) {
                $reg['falta_turno1_saida'] = true;
            }
        } elseif ($reg['segundo_horario'] && !empty($reg['segundo_ponto'])) {
            $pontoSeg = strtotime($reg['segundo_ponto']);
            $horaSeg = strtotime($reg['segundo_horario']);
            // Atraso na saída é sair ANTES do horário planejado (exceção à regra de tolerância que geralmente é pra entrada)
            // Mas seguindo o padrão do sistema para 'atrasou_...', verificamos a tolerância de saída se for o caso
            if ($pontoSeg < ($horaSeg - ($toleranciaSaida * 60))) {
                $reg['atrasou_segundo_ponto'] = true;
            } else {
                $reg['atrasou_segundo_ponto'] = false;
            }
        }

        // Turno 2 - Entrada (terceiro_ponto)
        if ($reg['terceiro_horario'] && empty($reg['terceiro_ponto'])) {
            $limite = date('H:i:s', strtotime($reg['terceiro_horario']) + ($toleranciaEntrada * 60));
            if ($regDate < $hojeStr || ($regDate == $hojeStr && $agoraStr > $limite)) {
                $reg['falta_turno2_entrada'] = true;
            }
        } elseif ($reg['terceiro_horario'] && !empty($reg['terceiro_ponto'])) {
            $pontoSeg = strtotime($reg['terceiro_ponto']);
            $horaSeg = strtotime($reg['terceiro_horario']);
            if ($pontoSeg > ($horaSeg + ($toleranciaEntrada * 60))) {
                $reg['atrasou_terceiro_ponto'] = true;
            } else {
                $reg['atrasou_terceiro_ponto'] = false;
            }
        }

        // Turno 2 - Saída (quarto_ponto)
        if ($reg['quarto_horario'] && empty($reg['quarto_ponto'])) {
            if ($regDate < $hojeStr) {
                $reg['falta_turno2_saida'] = true;
            }
        } elseif ($reg['quarto_horario'] && !empty($reg['quarto_ponto'])) {
            $pontoSeg = strtotime($reg['quarto_ponto']);
            $horaSeg = strtotime($reg['quarto_horario']);
            if ($pontoSeg < ($horaSeg - ($toleranciaSaida * 60))) {
                $reg['atrasou_quarto_ponto'] = true;
            } else {
                $reg['atrasou_quarto_ponto'] = false;
            }
        }
    }

    echo json_encode(['success' => true, 'data' => $registros]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erro interno de banco de dados.']);
}
