<?php
require_once '../config/Database.php';
echo "PHP Default Timezone: " . date_default_timezone_get() . "\n";
echo "PHP Time: " . date('Y-m-d H:i:s') . "\n";
echo "UTC Time: " . gmdate('Y-m-d H:i:s') . "\n";
echo "Timestamp: " . time() . "\n";
echo "OS Time: " . shell_exec('time /t') . "\n";
echo "OS Date: " . shell_exec('date /t') . "\n";
?>
