<?php
$path = 'c:/xampp/htdocs/sisponto/views/relogio.php';
$content = file_get_contents($path);

// Resetar flags em resetarFluxo
$content = preg_replace(
    '/function resetarFluxo\(\) \{\s+currentFlow = \'identificacao\';/',
    "function resetarFluxo() {\n        currentFlow = 'identificacao';\n        hasSpokenFace = false;\n        hasSpokenBlink = false;",
    $content
);

// Resetar flags em fecharModalBiometria
$content = preg_replace(
    '/window\.fecharModalBiometria = function \(\) \{\s+document\.getElementById\(\'modalBiometria\'\)\.classList\.add\(\'hidden\'\);/',
    "window.fecharModalBiometria = function () {\n        document.getElementById('modalBiometria').classList.add('hidden');\n        hasSpokenFace = false;\n        hasSpokenBlink = false;",
    $content
);

// Resetar flags em _resetCameraUI
$content = preg_replace(
    '/function _resetCameraUI\(\) \{\s+const video\s+= document\.getElementById\(\'videoFeed\'\);/',
    "function _resetCameraUI() {\n        hasSpokenFace = false;\n        hasSpokenBlink = false;\n        const video  = document.getElementById('videoFeed');",
    $content
);

file_put_contents($path, $content);
echo "Replaced voice resets";
