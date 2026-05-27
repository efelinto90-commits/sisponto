<?php
$dir = __DIR__ . '/../uploads/facial/';
$files = glob($dir . '*.pkl');
foreach ($files as $file) {
    if (unlink($file)) {
        echo "Deletado: " . basename($file) . "<br>";
    }
}
echo "Limpeza concluída.";
?>
