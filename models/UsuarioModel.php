<?php
/**
 * Model de Usuário (Data Access Object - DAO).
 * 
 * [ARQUITETURA] Isolamento da lógica de persistência referente aos usuários do sistema.
 * Responsável por consultar a base de dados de forma segura, abstraindo a infraestrutura
 * de dados da camada de Controle de Autenticação (AuthController).
 */
class UsuarioModel {
    /**
     * @var PDO Conexão injetada ao banco de dados.
     */
    private $db;

    /**
     * Construtor da classe que recebe a dependência de banco de dados.
     * 
     * @param PDO $db Instância da conexão PDO.
     */
    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Busca as credenciais e dados vitais de um usuário a partir do seu endereço de e-mail.
     * 
     * [ARQUITETURA] Assinatura Obrigatória: findByEmail(string $email): ?array
     * 
     * @param string $email Endereço de e-mail fornecido na tentativa de login.
     * @return array|null Array associativo com os dados do usuário, ou nulo se não encontrado.
     */
    public function findByEmail(string $email): ?array {
        // [SEGURANÇA] Utilização de Prepared Statements para neutralizar severamente o risco de SQL Injection.
        // O valor do e-mail não é concatenado, forçando o motor de banco de dados a tratá-lo estritamente como string literal.
        // [REGRA DE NEGÓCIO] Uso do "LIMIT 1" para otimizar o tempo de varredura do motor de banco de dados,
        // interrompendo a busca no momento em que o registro único for localizado (já que o e-mail deve ser unique).
        $stmt = $this->db->prepare("SELECT id_usuario, nome, email, senha_hash FROM usuario WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        $usuario = $stmt->fetch();
        return $usuario ? $usuario : null;
    }
}
