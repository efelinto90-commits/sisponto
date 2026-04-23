<?php
$f = 'c:/xampp/htdocs/sis-ponto/views/admin/cartao_ponto.php';
$c = file_get_contents($f);

// Check if it's currently UTF-8 or ISO
$newC = mb_convert_encoding($c, 'UTF-8', 'ISO-8859-1');

// If the string changes it means it had ISO chars (which was messing up what the browser parsed initially when writing)
file_put_contents($f, $newC);
echo "Converted to pure UTF-8\n";
