<?php

namespace Config;

date_default_timezone_set('America/Sao_Paulo');

use PDO;
use PDOException;

class Database
{
    // Configurações do Banco de Dados PostgreSQL
    private static $host = 'funad.ddns.net';
    private static $port = '5433';
    private static $dbname = 'funad';
    private static $user = 'postgres'; // Mude para o seu usuário do banco
    private static $password = 'gres4post'; // Mude para a sua senha do banco
    private static $schema = 'ponto'; // O schema utilizado no banco

    private static $conn = null;

    // Construtor privado para evitar instanciamento direto (Singleton)
    private function __construct()
    {
    }

    public static function getConnection()
    {
        if (self::$conn === null) {
            try {
                $dsn = "pgsql:host=" . self::$host . ";port=" . self::$port . ";dbname=" . self::$dbname;
                self::$conn = new PDO($dsn, self::$user, self::$password);

                // Definir os atributos de erro e timezone
                self::$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

                // Mudar para o schema correto se necessário
                self::$conn->exec("SET search_path TO " . self::$schema);

                // Mudar timezone do banco de dados para a sessão atual
                self::$conn->exec("SET TIME ZONE 'America/Sao_Paulo'");

            } catch (PDOException $e) {
                // Em produção, isso deve ser gravado em um log ao invés de exibido diretamente
                throw new PDOException("Erro de conexão com o banco de dados local: " . $e->getMessage());
            }
        }
        return self::$conn;
    }
}
