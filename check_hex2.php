<?php
$f = 'c:/xampp/htdocs/sis-ponto/views/admin/funcionarios.php';
$c = file_get_contents($f);
$pos = strpos($c, 'Gest');
echo "HEX: " . bin2hex(substr($c, $pos, 25)) . "\n";
