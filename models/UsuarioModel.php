<?php
/**
 * Model de UsuÃ¡rio
 */
class UsuarioModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Busca um usuÃ¡rio pelo email
     * Assinatura ObrigatÃ³ria: findByEmail(string $email): ?array
     */
    public function findByEmail(string $email): ?array {
        $stmt = $this->db->prepare("SELECT id_usuario, nome, email, senha_hash FROM usuario WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        $usuario = $stmt->fetch();
        return $usuario ? $usuario : null;
    }
}
