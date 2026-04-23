<?php
require_once 'config/Database.php';
use Config\Database;

function testAlignment($agora_time, $funcionario, $registroHoje = null) {
    $agora_ts = strtotime($agora_time);
    $todosPontos = [
        ['campo' => 'primeiro_ponto', 'previsto' => $funcionario['primeiro_horario']],
        ['campo' => 'segundo_ponto', 'previsto' => $funcionario['segundo_horario']],
        ['campo' => 'terceiro_ponto', 'previsto' => $funcionario['terceiro_horario']],
        ['campo' => 'quarto_ponto', 'previsto' => $funcionario['quarto_horario']],
    ];

    $horariosPrevistos = [];
    foreach ($todosPontos as $p) {
        if (!empty($p['previsto'])) {
            $horariosPrevistos[] = [
                'campo' => $p['campo'],
                'ts' => strtotime($p['previsto']),
                'previsto' => $p['previsto']
            ];
        }
    }

    $numHorarios = count($horariosPrevistos);
    $alvoIndex = -1;
    $campoPonto = '';
    $horaEsperada = '';

    for ($i = 0; $i < $numHorarios; $i++) {
        $limiteInferior = ($i === 0) ? 0 : ($horariosPrevistos[$i]['ts'] + $horariosPrevistos[$i-1]['ts']) / 2;
        $limiteSuperior = ($i === $numHorarios - 1) ? 9999999999 : ($horariosPrevistos[$i]['ts'] + $horariosPrevistos[$i+1]['ts']) / 2;

        if ($agora_ts >= $limiteInferior && $agora_ts < $limiteSuperior) {
            $alvoIndex = $i;
            break;
        }
    }

    if ($alvoIndex !== -1) {
        for ($i = $alvoIndex; $i < $numHorarios; $i++) {
            $campo = $horariosPrevistos[$i]['campo'];
            if (!$registroHoje || empty($registroHoje[$campo])) {
                $campoPonto = $campo;
                $horaEsperada = $horariosPrevistos[$i]['previsto'];
                break;
            }
        }
    }

    if (empty($campoPonto)) {
        for ($i = $numHorarios - 1; $i >= 0; $i--) {
            $campo = $horariosPrevistos[$i]['campo'];
            if (!$registroHoje || empty($registroHoje[$campo])) {
                $campoPonto = $campo;
                $horaEsperada = $horariosPrevistos[$i]['previsto'];
                break;
            }
        }
    }
    
    return $campoPonto;
}

$funcionario = [
    'primeiro_horario' => '08:00',
    'segundo_horario' => '12:00',
    'terceiro_horario' => '13:00',
    'quarto_horario' => '17:00'
];

$tests = [
    ['08:05', null, 'primeiro_ponto'],
    ['10:10', null, 'segundo_ponto'],
    ['08:30', ['primeiro_ponto' => '08:00'], 'segundo_ponto'], // MUST be segundo_ponto now
    ['11:30', ['primeiro_ponto' => '08:00'], 'segundo_ponto'],
    ['12:40', ['primeiro_ponto' => '08:00', 'segundo_ponto' => '12:00'], 'terceiro_ponto'],
];

foreach ($tests as $t) {
    $res = testAlignment($t[0], $funcionario, $t[1]);
    $status = ($res === $t[2]) ? "PASS" : "FAIL";
    echo "Time: {$t[0]} | Result: $res | Expected: {$t[2]} | $status\n";
}
