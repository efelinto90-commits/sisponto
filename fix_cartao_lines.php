<?php
$f = 'c:/xampp/htdocs/sis-ponto/views/admin/cartao_ponto.php';
$lines = file($f);

foreach ($lines as $k => $line) {
    if (strpos($line, 'OK</span>') !== false) {
        $lines[$k] = '                        saldo = \'<span class="text-[11px] font-bold text-emerald-500 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-lg text-center">✓ OK</span>\';' . "\n";
    }
    if (strpos($line, 'Corrigido</span>') !== false) {
        $lines[$k] = '                        + \'<span title="\' + r.justificativa.replace(/"/g, \'&quot;\') + \'" class="text-[10px] font-bold bg-blue-50 text-blue-700 px-2 py-1 rounded border border-blue-200 text-center">✔ Corrigido</span>\'' . "\n";
    }
}

file_put_contents($f, implode("", $lines));
echo "Fixed lines manually.\n";
