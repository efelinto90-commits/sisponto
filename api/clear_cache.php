<?php
$dir = __DIR__ . '/../uploads/facial/';
$files = glob($dir . '*.pkl');
foreach ($files as $file) {
    if (is_file($file)) {
        unlink($file);
        echo "Deletado: " . basename($file) . "<br>";
    }
}
echo "Cache limpo com sucesso. O DeepFace irá re-indexar as faces na próxima requisição.";
?>
