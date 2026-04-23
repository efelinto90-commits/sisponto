<?php
try {
    require_once 'c:/xampp/htdocs/sisponto/config/Database.php';
    $conn = Config\Database::getConnection();
    $total = $conn->query('SELECT count(*) FROM ponto.registros')->fetchColumn();
    echo 'TOTAL: ' . $total . PHP_EOL;
    $byMonth = $conn->query("SELECT to_char(data,'YYYY-MM') AS mes, count(*) FROM ponto.registros GROUP BY mes ORDER BY mes DESC LIMIT 6")->fetchAll(PDO::FETCH_ASSOC);
    foreach($byMonth as $m) echo $m['mes'] . ': ' . $m['count'] . PHP_EOL;
} catch(Exception $e) {
    echo $e->getMessage();
}
