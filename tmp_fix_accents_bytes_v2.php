<?php
$file = 'c:/xampp/htdocs/sisponto/views/relogio.php';
$content = file_get_contents($file);

// Mapeamento de sequências de bytes encontradas para os caracteres UTF-8 corretos
$replacements = [
    "\xC3\x83\x4F" => "ÃO", // NÃÃO -> NÃO
    "\xC3\x83s"    => "às",  // Ãs -> às
    "\x3F\x3Fs"    => "às",  // ??s -> às (caso de interrogação literal se houver)
    "Ãs"           => "às",
    "Ã³"           => "ó",
    "Ã¡"           => "á",
    "Ã­"           => "í",
    "Ãº"           => "ú",
    "Ã©"           => "é",
    "Ã£"           => "ã",
    "Ã§"           => "ç",
    "Ãµ"           => "õ",
];

$content = str_replace(array_keys($replacements), array_values($replacements), $content);

// Correção específica para MatrÃ­cula que pode estar como MatrÃ seguida de caracteres ocultos
$content = preg_replace('/Matr\xC3\x83[^\w\s]{1,3}cula/i', 'Matrícula', $content);

file_put_contents($file, $content);
echo "Correção Bytes v2 concluída.";
