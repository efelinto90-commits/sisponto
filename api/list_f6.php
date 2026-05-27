<?php
$dir = __DIR__ . '/../uploads/facial/';
$files = glob($dir . 'facial_6_*');
foreach ($files as $file) {
    echo basename($file) . "<br>";
}
?>
