<?php
/**
 * Conexão PDO - Padrão Singleton (Alinhado com os Contratos dos Models)
 */
class Conexao {
    private static $instancia = null;

    private function __construct() {
        // Construtor privado impede instanciação direta externa
    }

    private function __clone() {
        // Evita clonagem da instância
    }

    public static function getConexao(): PDO {
        if (self::$instancia === null) {
            try {
                $host = '127.0.0.1';
                $dbname = 'itam_db';
                $user = 'root';
                $password = '2oo6Br4silia!';

                $dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";
                
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ];

                self::$instancia = new PDO($dsn, $user, $password, $options);
                self::$instancia->exec("SET NAMES 'utf8mb4'");
                self::$instancia->exec("SET sql_mode = 'STRICT_ALL_TABLES'");

            } catch (PDOException $e) {
                error_log("Erro de Conexão PDO: " . $e->getMessage());
                http_response_code(500);
                die("Erro interno do servidor. Nossa equipe já foi notificada.");
            }
        }
        return self::$instancia;
    }
}
