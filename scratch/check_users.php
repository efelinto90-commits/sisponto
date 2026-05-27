<?php
require_once 'config/Database.php';
use Config\Database;

try {
    $conn = Database::getConnection();
    $names = ['EDILSON', 'EUNICE'];
    foreach ($names as $name) {
        $stmt = $conn->prepare("SELECT id, nome, setor, setor2 FROM funcionarios WHERE nome ILIKE :name");
        $stmt->execute([':name' => "%$name%"]);
        $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "Busca por $name:\n";
        print_r($res);
        
        if (!empty($res)) {
            foreach ($res as $f) {
                $stmtReg = $conn->prepare("SELECT id, data, comunicado FROM registros WHERE id_funcionario = :id AND (comunicado IS NOT NULL AND comunicado != '') ORDER BY data DESC LIMIT 5");
                $stmtReg->execute([':id' => $f['id']]);
                $regs = $stmtReg->fetchAll(PDO::FETCH_ASSOC);
                echo "Comunicados de " . $f['nome'] . ":\n";
                print_r($regs);
            }
        }
        echo "------------------\n";
    }
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
