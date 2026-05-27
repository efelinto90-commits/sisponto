<?php
$file = 'c:/xampp/htdocs/sisponto/views/relogio.php';
$content = file_get_contents($file);

// Mapeamento de sequências hexadecimais de "double encoding" para os caracteres UTF-8 corretos
$replacements = [
    "\xC3\x83\xC2\xBA" => "ú",
    "\xC3\x83\xC2\xAD" => "í",
    "\xC3\x83\xC2\xA1" => "á",
    "\xC3\x83\xC2\xA9" => "é",
    "\xC3\x83\xC2\xB3" => "ó",
    "\xC3\x83\xC2\xA3" => "ã",
    "\xC3\x83\xC2\xB5" => "õ",
    "\xC3\x83\xC2\xA7" => "ç",
    "\xC3\x83\xC2\xAA" => "ê",
    "\xC3\x83\xC2\xB4" => "ô",
    "\xC3\x83\xC2\xA2" => "â",
    "\xC3\x83\xC2\x8D" => "Í", // Caso especial de MATRÃÍcula ou similar
    "\xC3\x83\xC2\x81" => "Á",
    "\xC3\x83\xC2\x89" => "É",
    "\xC3\x83\xC2\x93" => "Ó",
    "\xC3\x83\xC2\x9A" => "Ú",
    "\xC3\x83\xC2\x80" => "À",
    "\xC3\x83\xC2\x87" => "Ç",
    "\xC3\x83\x20"     => "à"  /* Caso de Ã seguido de espaço ou similar */
];

$content = str_replace(array_keys($replacements), array_values($replacements), $content);

file_put_contents($file, $content);
echo "Correção HEX concluída via PHP.";
