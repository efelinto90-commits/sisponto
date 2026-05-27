<?php
require_once '../config/Database.php';
use Config\Database;

try {
    $conn = Database::getConnection();
    $dir = __DIR__ . '/../uploads/facial/';
    $files = glob($dir . 'facial_*');
    
    echo "<h1>Sincronizando Biometria Facial</h1>";
    echo "Encontrados " . count($files) . " arquivos.<br><br>";
    
    $updatedCount = 0;
    foreach ($files as $file) {
        $filename = basename($file);
        // Padrão: facial_{matricula}_{timestamp}.jpeg
        if (preg_match('/facial_([^_]+)_/', $filename, $matches)) {
            $id = $matches[1];
            $pathInDb = 'uploads/facial/' . $filename;
            
            // Atualizar o funcionário que tem esse ID
            $stmt = $conn->prepare("UPDATE funcionarios SET biometria_facial = :path WHERE id = :id");
            $stmt->execute([':path' => $pathInDb, ':id' => $id]);
            
            if ($stmt->rowCount() > 0) {
                echo "ID $id atualizado para: $filename<br>";
                $updatedCount++;
            }
        }
    }
    
    echo "<br><b>Total de registros atualizados: $updatedCount</b>";
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
?>
