<?php
require_once 'config/Database.php';
echo "Timezone: " . date_default_timezone_get() . "<br>";
echo "PHP Time: " . date('H:i:s') . "<br>";
echo "Date: " . date('Y-m-d') . "<br>";
?>
