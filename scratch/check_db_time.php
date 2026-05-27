<?php
require_once '../config/Database.php';
use Config\Database;
try {
    $conn = Database::getConnection();
    $stmt = $conn->query("SELECT NOW() as db_now, CURRENT_SETTING('TIMEZONE') as db_tz, NOW() AT TIME ZONE 'UTC' as db_utc");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "DB Now (Local): " . $row['db_now'] . "\n";
    echo "DB Timezone: " . $row['db_tz'] . "\n";
    echo "DB UTC: " . $row['db_utc'] . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
