<?php
echo "PHP version: " . phpversion() . "<br>";
echo "Server Time (raw): " . date('Y-m-d H:i:s') . "<br>";
echo "Timezone (raw): " . date_default_timezone_get() . "<br>";
require_once 'config/Database.php';
echo "Timezone (after Database.php): " . date_default_timezone_get() . "<br>";
echo "Server Time (after Database.php): " . date('Y-m-d H:i:s') . "<br>";
?>
