<?php
$f = 'c:/xampp/htdocs/sis-ponto/views/admin/funcionarios.php';
$c = file_get_contents($f);
$pos = strpos($c, 'Gest');
echo "Hex: " . bin2hex(substr($c, $pos + 4, 15)) . "\n";
echo "Str: " . substr($c, $pos + 4, 15) . "\n";
