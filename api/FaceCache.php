<?php
namespace Api;

use Config\Database;
use PDO;

class FaceCache {
    private static $cacheFile = __DIR__ . '/face_descriptors.cache.php';

    public static function refresh() {
        $conn = Database::getConnection();
        $stmt = $conn->prepare("
            SELECT id, matricula, nome, facial_descriptor 
            FROM funcionarios 
            WHERE (facial_descriptor::text IS NOT NULL 
              AND facial_descriptor::text != '' 
              AND facial_descriptor::text != 'null'
              AND facial_descriptor::text != '[]')
              AND (is_exonerado IS NULL OR is_exonerado = false)
        ");
        $stmt->execute();
        $funcionarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $data = [];
        foreach ($funcionarios as $f) {
            $descriptor = json_decode($f['facial_descriptor'], true);
            if (is_array($descriptor) && count($descriptor) >= 128) {
                $data[] = [
                    'id' => $f['id'],
                    'matricula' => $f['matricula'],
                    'nome' => $f['nome'],
                    'descriptor' => $descriptor
                ];
            }
        }

        $content = "<?php\n// Gerado automaticamente em " . date('Y-m-d H:i:s') . "\nreturn " . var_export($data, true) . ";\n";
        file_put_contents(self::$cacheFile, $content);
        
        return count($data);
    }

    public static function get() {
        if (!file_exists(self::$cacheFile)) {
            self::refresh();
        }
        return include self::$cacheFile;
    }
}
