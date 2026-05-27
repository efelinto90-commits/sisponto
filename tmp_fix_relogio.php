<?php
$f = 'c:/xampp/htdocs/sisponto/views/test_relogio.php';
$content = file_get_contents($f);

// Fix the extra brace in preConfirm
// Search for the end of the validation block
$content = str_replace(
    "if (!hasHorario) {\n                        Swal.showValidationMessage(`VocÃª deve selecionar seu horÃ¡rio semanal.`);\n                        return false; \n                    }\n                    }",
    "if (!hasHorario) {\n                        Swal.showValidationMessage(`VocÃª deve selecionar seu horÃ¡rio semanal.`);\n                        return false; \n                    }",
    $content
);

// Verify if func = data is present
if (strpos($content, 'const func = data;') === false) {
    $content = str_replace(
        'return new Promise(async (resolve) => {',
        "return new Promise(async (resolve) => {\n            const func = data;",
        $content
    );
}

// Verify if _carregarHorarios is present
if (strpos($content, '_carregarHorarios();') === false) {
    $content = str_replace(
        'window.abrirModalFichaIncompleta = async function (data) {',
        "window.abrirModalFichaIncompleta = async function (data) {\n        if (listaHorarios.length === 0) await _carregarHorarios();",
        $content
    );
}

file_put_contents('c:/xampp/htdocs/sisponto/views/relogio_v4.php', $content);
echo "Fix attempt for relogio_v4.php completed\n";
?>
