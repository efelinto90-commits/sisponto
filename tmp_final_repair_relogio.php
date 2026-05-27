<?php
$f = 'c:/xampp/htdocs/sisponto/views/relogio.php';
$c = file_get_contents($f);

// 1. Remove Null Bytes
$c = str_replace("\0", "", $c);

// 2. Fix the extra brace in preConfirm
// Use a more robust regex that ignores spacing
$c = preg_replace('/\}\s+\}\s+return fields;\s+\}\s+\}\);/s', "}\n                    return fields;\n                }\n            });", $c);

// 3. Ensure window.abrirModalFichaIncompleta has func definition
if (strpos($c, 'const func = data;') === false) {
    if (strpos($c, 'return new Promise(async (resolve) => {') !== false) {
        $c = str_replace('return new Promise(async (resolve) => {', "return new Promise(async (resolve) => {\n            const func = data;", $c);
    }
}

// 4. Ensure _carregarHorarios is called
if (strpos($c, '_carregarHorarios();') === false) {
    if (strpos($c, 'window.abrirModalFichaIncompleta = async function (data) {') !== false) {
        $c = str_replace('window.abrirModalFichaIncompleta = async function (data) {', "window.abrirModalFichaIncompleta = async function (data) {\n        if (listaHorarios.length === 0) await _carregarHorarios();", $c);
    }
}

// 5. Ensure helper functions are there
if (strpos($c, 'let listaHorarios = [];') === false) {
    // Add them after _todosFunc
    $c = str_replace('let _todosFunc = null;', "let _todosFunc = null;\n    let listaHorarios = [];\n    async function _carregarHorarios() {\n        try {\n            const res = await fetch('../api/horarios.php');\n            const json = await res.json();\n            if (json.success) listaHorarios = json.data;\n        } catch (e) { console.error(\"Erro carregando horarios\", e); }\n    }", $c);
}

file_put_contents($f, $c);
echo "Final repair for relogio.php completed\n";
?>
