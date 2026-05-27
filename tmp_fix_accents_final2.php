<?php
$file = 'c:/xampp/htdocs/sisponto/views/relogio.php';
$content = file_get_contents($file);

$replacements = [
    "\xC3\x83\xC2\xA0" => "à"
];

$content = str_replace(array_keys($replacements), array_values($replacements), $content);

file_put_contents($file, $content);
echo "Correção Bytes Final 2 concluída.\n";
