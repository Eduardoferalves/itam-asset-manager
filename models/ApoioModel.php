<?php
/**
 * Model de Apoio para listagens auxiliares
 */
class ApoioModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function listarCategorias(): array {
        $stmt = $this->db->query("SELECT * FROM categoria ORDER BY descricao ASC");
        return $stmt->fetchAll();
    }

    public function listarDepartamentos(): array {
        $stmt = $this->db->query("SELECT * FROM departamento ORDER BY nome ASC");
        return $stmt->fetchAll();
    }

    public function listarFornecedores(): array {
        $stmt = $this->db->query("SELECT * FROM fornecedor ORDER BY nome_empresa ASC");
        return $stmt->fetchAll();
    }
}
