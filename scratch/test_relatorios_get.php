<?php
$_GET['start_date'] = date('Y-m-01');
$_GET['end_date'] = date('Y-m-t');
$_GET['strict_sector'] = '1';
$_SERVER['REQUEST_METHOD'] = 'GET';

if (session_status() === PHP_SESSION_NONE) session_start();
$_SESSION['user_level'] = 1;
$_SESSION['user_name'] = 'admin';

include 'api/relatorios.php';
