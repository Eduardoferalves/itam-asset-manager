<?php
/**
 * Model de Apoio para listagens auxiliares (Data Access Object - DAO).
 * 
 * [ARQUITETURA] Esta classe encapsula a lógica de acesso aos dados de entidades de domínio
 * secundárias (Categoria, Departamento, Fornecedor). Isola o Controller das particularidades
 * do banco de dados, centralizando as consultas utilizadas primariamente para popular
 * elementos de interface (ex: select boxes em formulários).
 */
class ApoioModel {
    /**
     * @var PDO Instância da conexão com o banco de dados.
     */
    private $db;

    /**
     * Construtor do Model de Apoio.
     * 
     * [ARQUITETURA] Utiliza Injeção de Dependência (Dependency Injection) para receber 
     * a instância do banco de dados, facilitando testes unitários e mantendo o acoplamento baixo.
     * 
     * @param PDO $db Conexão PDO configurada e ativa.
     */
    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Recupera a listagem completa de categorias.
     * 
     * @return array Conjunto de registros de categorias ordenados alfabeticamente.
     */
    public function listarCategorias(): array {
        // [REGRA DE NEGÓCIO] Ordenação alfabética direta no banco para otimizar o processamento 
        // em memória na camada de aplicação (PHP) e padronizar a exibição nas Views.
        $stmt = $this->db->query("SELECT * FROM categoria ORDER BY descricao ASC");
        return $stmt->fetchAll();
    }

    /**
     * Recupera a listagem completa de departamentos.
     * 
     * @return array Conjunto de registros de departamentos ordenados alfabeticamente.
     */
    public function listarDepartamentos(): array {
        $stmt = $this->db->query("SELECT * FROM departamento ORDER BY nome ASC");
        return $stmt->fetchAll();
    }

    /**
     * Recupera a listagem completa de fornecedores.
     * 
     * @return array Conjunto de registros de fornecedores ordenados pelo nome da empresa.
     */
    public function listarFornecedores(): array {
        $stmt = $this->db->query("SELECT * FROM fornecedor ORDER BY nome_empresa ASC");
        return $stmt->fetchAll();
    }
}
