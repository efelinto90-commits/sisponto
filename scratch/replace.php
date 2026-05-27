<?php
$path = 'c:/xampp/htdocs/sisponto/views/relogio.php';
$content = file_get_contents($path);

// Ignora diferenças de whitespaces e newlines na busca
$pattern = '/if \(data\.success\) \{\s*detectionLoopActive = false;\s*speak\(`Ponto registrado\. \$\{data\.funcionario\}`\); Swal\.fire\(\{ \s*title: \'Marcado!\', \s*html: `<b>\$\{data\.funcionario\}<\/b><br>Ponto via Facial às \$\{data\.hora\}`, \s*icon: \'success\', \s*showConfirmButton: true,\s*confirmButtonText: \'Concluir\',\s*confirmButtonColor: \'#0ea5e9\'\s*\}\);\s*window\.fecharModalBiometria\(\);\s*\} else \{/s';

$replacement = <<<EOT
if (data.success) {
                detectionLoopActive = false;
                speak(`Ponto registrado. \${data.funcionario}`); 
                Swal.fire({ 
                    title: 'Marcado!', 
                    html: `<b>\${data.funcionario}</b><br>Ponto via Facial às \${data.hora}`, 
                    icon: 'success', 
                    showConfirmButton: true,
                    confirmButtonText: 'Concluir',
                    confirmButtonColor: '#0ea5e9',
                    timer: 10000,
                    timerProgressBar: true
                }).then(() => {
                    document.getElementById('matricula').value = '';
                    document.getElementById('bio_matricula').value = '';
                    resetarFluxo();
                    _resetCameraUI();
                    window.mudarModoBiometria('facial');
                });
            } else {
EOT;

$newContent = preg_replace($pattern, $replacement, $content);

if ($newContent !== null && $newContent !== $content) {
    file_put_contents($path, $newContent);
    echo "Replaced";
} else {
    echo "Target not found with regex";
}
