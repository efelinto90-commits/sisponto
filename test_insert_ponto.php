<?php
try {
    $matricula = '0020087'; // Adriana from previous test
    $data = [
        'matricula' => $matricula,
        'action' => 'ponto_senha',
        'senha' => '' // Assuming no password for now or if bypassed
    ];
    
    $_SERVER['REQUEST_METHOD'] = 'POST';
    // Mocking input
    $tempFile = tempnam(sys_get_temp_dir(), 'p');
    file_put_contents($tempFile, json_encode($data));
    
    // We need to bypass the file_get_contents('php://input')
    // Let's create a specialized test script for the logic
    require_once 'c:/xampp/htdocs/sisponto/config/Database.php';
    $conn = Config\Database::getConnection();
    
    $stmt = $conn->prepare("SELECT id, nome FROM ponto.funcionarios WHERE matricula = ?");
    $stmt->execute([$matricula]);
    $f = $stmt->fetch();
    
    if (!$f) {
        die("Funcionario não encontrado\n");
    }
    
    $id = $f['id'];
    $dataAtual = date('Y-m-d');
    $horaAtual = date('H:i:s');
    
    echo "Funcionario: " . $f['nome'] . " (ID: $id)\n";
    
    $stmt = $conn->prepare("INSERT INTO ponto.registros (id_funcionario, data, primeiro_ponto, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())");
    $stmt->execute([$id, $dataAtual, $horaAtual]);
    
    echo "Registro inserido com sucesso para a data $dataAtual!\n";
    
    $check = $conn->query("SELECT count(*) FROM ponto.registros")->fetchColumn();
    echo "Total registros agora: $check\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
