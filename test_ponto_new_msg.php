<?php
require_once 'c:/xampp/htdocs/sisponto/config/Database.php';
use Config\Database;

// Simular a chamada da API ponto.php (lógica principal) para o funcionário 2236-5
function simularPonto($matricula, $horaSimulada) {
    try {
        $conn = Database::getConnection();
        $stmt = $conn->prepare("SELECT f.*, h.primeiro_horario, h.segundo_horario, h.terceiro_horario, h.quarto_horario, h.tolerancia_entrada, h.tolerancia_saida 
                                FROM funcionarios f LEFT JOIN horarios h ON f.id_horario = h.id 
                                WHERE f.matricula = :m");
        $stmt->execute([':m' => $matricula]);
        $funcionario = $stmt->fetch();

        if (!$funcionario) return "Matrícula não encontrada.";

        $todosPontos = [
            ['campo' => 'primeiro_ponto', 'previsto' => $funcionario['primeiro_horario'], 'is_entrada' => true],
            ['campo' => 'segundo_ponto', 'previsto' => $funcionario['segundo_horario'], 'is_entrada' => false],
            ['campo' => 'terceiro_ponto', 'previsto' => $funcionario['terceiro_horario'], 'is_entrada' => true],
            ['campo' => 'quarto_ponto', 'previsto' => $funcionario['quarto_horario'], 'is_entrada' => false],
        ];

        $agora_ts = strtotime($horaSimulada);
        $temHorarioVinculado = false;
        $campoPonto = '';

        foreach ($todosPontos as $ponto) {
            $previsto = $ponto['previsto'];
            if (empty($previsto)) continue;
            $temHorarioVinculado = true;
            $campoPonto = 'found'; // simplificado para teste de existencia
            break;
        }

        if (empty($campoPonto)) {
            if (!$temHorarioVinculado) {
                return "SUCESSO: Nenhum horário de expediente vinculado ao seu perfil. Procure o RH.";
            } else {
                return "Todos os pontos de hoje já foram registrados.";
            }
        }
        return "Ponto disponível para registro.";

    } catch (Exception $e) {
        return "Erro: " . $e->getMessage();
    }
}

echo simularPonto('2236-5', '11:16') . "\n";
