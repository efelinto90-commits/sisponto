<?php
$f = 'c:/xampp/htdocs/sisponto/views/relogio.php';
$content = file_get_contents($f);

// Attempt to remove the injected sections by looking for specific markers I added
// 1. Remove grid preparation
$content = preg_replace('/if \(listaHorarios\.length === 0\)[^;]+_carregarHorarios\(\);\s+return new Promise\(async \(resolve\) => \{\s+const func = data;\s+const diasSemana = \{[^}]+\};\s+const gridHtmlHorarios =[^;]+;\s+const \{ value: formValues \}/s', 'return new Promise(async (resolve) => { const func = data; const { value: formValues }', $content);

// 2. Remove grid HTML from Swal
$content = preg_replace('/<!-- Quadro de Horários Semanal \(Mandatório\) -->.*?<\/div>\s+<\/div>\s+`,/s', '</div>\n                `,', $content);

// 3. Remove grid code from preConfirm
$content = preg_replace('/grade_horarios: \{.*?\}\s+\};/s', '};', $content);
$content = preg_replace('/\/\/ Validação da Grade de Horários.*?return false;\s+\}/s', '', $content);

// 4. Remove helper functions
$content = preg_replace('/let listaHorarios = \[\];.*?async function _carregarHorarios\(\) \{.*?\}\n/s', '', $content);

file_put_contents($f, $content);
echo "Revert attempt completed\n";
?>
