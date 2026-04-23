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
    $campoPonto = '';
    
    for ($i = 0; $i < $numHorarios; $i++) {
        $atual = $horariosPrevistos[$i];
        if ($registroHoje && !empty($registroHoje[$atual['campo']])) continue;

        $limiteInferior = ($i === 0) ? 0 : ($horariosPrevistos[$i]['ts'] + $horariosPrevistos[$i-1]['ts']) / 2;
        $limiteSuperior = ($i === $numHorarios - 1) ? 9999999999 : ($horariosPrevistos[$i]['ts'] + $horariosPrevistos[$i+1]['ts']) / 2;

        if ($agora_ts >= $limiteInferior && $agora_ts < $limiteSuperior) {
            $campoPonto = $atual['campo'];
            break;
        }
    }

    if (empty($campoPonto)) {
        for ($i = $numHorarios - 1; $i >= 0; $i--) {
            if (!$registroHoje || empty($registroHoje[$horariosPrevistos[$i]['campo']])) {
                $campoPonto = $horariosPrevistos[$i]['campo'];
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
    ['09:50', null, 'primeiro_ponto'],
    ['10:10', null, 'segundo_ponto'],
    ['11:30', null, 'segundo_ponto'],
    ['12:15', null, 'segundo_ponto'],
    ['12:45', null, 'terceiro_ponto'],
    ['14:00', null, 'terceiro_ponto'],
    ['16:00', null, 'quarto_ponto'],
    ['18:00', null, 'quarto_ponto'],
    ['10:00', ['primeiro_ponto' => '08:00'], 'segundo_ponto'],
];

foreach ($tests as $t) {
    $res = testAlignment($t[0], $funcionario, $t[1]);
    $status = ($res === $t[2]) ? "PASS" : "FAIL";
    echo "Time: {$t[0]} | Result: $res | Expected: {$t[2]} | $status\n";
}
