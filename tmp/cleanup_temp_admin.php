<?php
require_once 'c:/xampp/htdocs/sisponto/config/Database.php';
use Config\Database;
try {
    $conn = Database::getConnection();
    $stmt = $conn->prepare("DELETE FROM users WHERE name = 'admin_temp'");
    $stmt->execute();
    echo "Usuário admin_temp removido com sucesso.";
} catch (Exception $e) {
    echo $e->getMessage();
}
?>
