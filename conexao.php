<?php
/**
 * Gerenciador de Conexão com Banco de Dados implementando o Padrão Singleton.
 * 
 * Esta classe isola e centraliza a criação e a configuração do objeto PDO.
 * O padrão Singleton garante uma única conexão ativa durante todo o ciclo de vida da requisição (HTTP Request),
 * otimizando o consumo de recursos e conexões de rede com o servidor MySQL.
 */
class Conexao {
    /**
     * @var PDO|null Instância estática encapsulada da conexão PDO.
     */
    private static $instancia = null;

    /**
     * [ARQUITETURA] Construtor privado que impede a instanciação direta por meio da palavra-chave 'new'.
     * Força os consumidores a utilizarem o ponto de acesso global `getConexao()`.
     */
    private function __construct() {
    }

    /**
     * [ARQUITETURA] Bloqueio do método mágico de clonagem.
     * Assegura que o padrão Singleton não possa ser contornado duplicando o objeto na memória.
     */
    private function __clone() {
    }

    /**
     * Retorna a instância única e configurada do PDO.
     * 
     * Implementa o carregamento preguiçoso (Lazy Loading), estabelecendo a conexão com o banco de dados
     * apenas no momento exato em que a primeira interação com o banco é necessária pelo sistema.
     * 
     * @return PDO A instância global da conexão ao banco de dados.
     */
    public static function getConexao(): PDO {
        // [REGRA DE NEGÓCIO] Evita a sobrecarga de múltiplas conexões verificando o cache de instância.
        if (self::$instancia === null) {
            try {
                $host = '127.0.0.1';
                $dbname = 'itam_db';
                $user = 'root';
                $password = 'coloque aqui sua senha do banco de dados';

                // [SEGURANÇA] Definição explícita do charset na DSN (utf8mb4) previne vulnerabilidades 
                // envolvendo anomalias de codificação de caracteres que poderiam evadir sanitizações.
                $dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";
                
                // [SEGURANÇA] Flags estritas de segurança do PDO:
                // - ERRMODE_EXCEPTION: Garante que as falhas lancem exceções, evitando a execução silenciosa de queries com defeito.
                // - FETCH_ASSOC: Previne duplicação de dados na memória em arrays mistos, facilitando conversões JSON.
                // - EMULATE_PREPARES: Desligado obriga o próprio motor do banco (MySQL) a compilar o statement separadamente 
                //   dos dados de entrada, neutralizando integralmente vetores de SQL Injection (First e Second-Order).
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ];

                self::$instancia = new PDO($dsn, $user, $password, $options);
                
                // [REGRA DE NEGÓCIO] Impõe estrita integridade nos dados armazenados,
                // forçando o MySQL a rejeitar inserções truncadas ou dados de tipos incompatíveis.
                self::$instancia->exec("SET NAMES 'utf8mb4'");
                self::$instancia->exec("SET sql_mode = 'STRICT_ALL_TABLES'");

            } catch (PDOException $e) {
                // [SEGURANÇA] Oculta do usuário final o detalhamento da stack trace e das credenciais,
                // registrando a exceção de maneira segura nos logs da aplicação para posterior auditoria.
                error_log("Erro de Conexão PDO: " . $e->getMessage());
                http_response_code(500);
                die("Erro interno do servidor. Nossa equipe já foi notificada.");
            }
        }
        return self::$instancia;
    }
}
