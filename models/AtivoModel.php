<?php
/**
 * Model de Ativo
 */
class AtivoModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Lista ativos aplicando filtros opcionais e utilizando LEFT JOIN
     * Assinatura ObrigatÃ³ria: listarComFiltros(?int $id_categoria, ?string $status, ?string $patrimonio): array
     */
    public function listarComFiltros(?int $id_categoria, ?string $status, ?string $patrimonio): array {
        $sql = "SELECT a.*, c.descricao as categoria_nome, d.nome as departamento_nome, f.nome_empresa as fornecedor_nome 
                FROM ativo a
                LEFT JOIN categoria c ON a.id_categoria = c.id_categoria
                LEFT JOIN departamento d ON a.id_departamento = d.id_departamento
                LEFT JOIN fornecedor f ON a.id_fornecedor = f.id_fornecedor
                WHERE 1=1";
        
        $params = [];

        if ($id_categoria !== null && $id_categoria !== '') {
            $sql .= " AND a.id_categoria = :id_categoria";
            $params['id_categoria'] = (int)$id_categoria;
        }

        if ($status !== null && $status !== '') {
            $sql .= " AND a.status = :status";
            $params['status'] = $status;
        }

        if ($patrimonio !== null && $patrimonio !== '') {
            $sql .= " AND a.patrimonio LIKE :patrimonio";
            $params['patrimonio'] = '%' . $patrimonio . '%';
        }

        // Ordenar por data de aquisiÃ§Ã£o decrescente por padrÃ£o para visualizaÃ§Ã£o moderna
        $sql .= " ORDER BY a.id_ativo DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Busca um ativo especÃ­fico pelo seu ID
     * Assinatura ObrigatÃ³ria: buscarPorId(int $id): ?array
     */
    public function buscarPorId(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM ativo WHERE id_ativo = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $ativo = $stmt->fetch();
        return $ativo ? $ativo : null;
    }

    /**
     * Cadastra um novo ativo
     * Assinatura ObrigatÃ³ria: cadastrar(array $dados): bool
     */
    public function cadastrar(array $dados): bool {
        $stmt = $this->db->prepare("INSERT INTO ativo (patrimonio, status, data_aquisicao, id_categoria, id_departamento, id_fornecedor) 
                                    VALUES (:patrimonio, :status, :data_aquisicao, :id_categoria, :id_departamento, :id_fornecedor)");
        return $stmt->execute([
            'patrimonio'      => trim($dados['patrimonio']),
            'status'          => $dados['status'] ?? 'Ativo',
            'data_aquisicao'  => $dados['data_aquisicao'],
            'id_categoria'    => !empty($dados['id_categoria']) ? (int)$dados['id_categoria'] : null,
            'id_departamento' => !empty($dados['id_departamento']) ? (int)$dados['id_departamento'] : null,
            'id_fornecedor'   => !empty($dados['id_fornecedor']) ? (int)$dados['id_fornecedor'] : null,
        ]);
    }

    /**
     * Atualiza um ativo existente
     * Assinatura ObrigatÃ³ria: atualizar(int $id, array $dados): bool
     */
    public function atualizar(int $id, array $dados): bool {
        $stmt = $this->db->prepare("UPDATE ativo SET 
                                    patrimonio = :patrimonio, 
                                    status = :status, 
                                    data_aquisicao = :data_aquisicao, 
                                    id_categoria = :id_categoria, 
                                    id_departamento = :id_departamento, 
                                    id_fornecedor = :id_fornecedor 
                                    WHERE id_ativo = :id");
        return $stmt->execute([
            'id'              => $id,
            'patrimonio'      => trim($dados['patrimonio']),
            'status'          => $dados['status'] ?? 'Ativo',
            'data_aquisicao'  => $dados['data_aquisicao'],
            'id_categoria'    => !empty($dados['id_categoria']) ? (int)$dados['id_categoria'] : null,
            'id_departamento' => !empty($dados['id_departamento']) ? (int)$dados['id_departamento'] : null,
            'id_fornecedor'   => !empty($dados['id_fornecedor']) ? (int)$dados['id_fornecedor'] : null,
        ]);
    }

    /**
     * Exclui um ativo
     * Assinatura ObrigatÃ³ria: excluir(int $id): bool
     */
    public function excluir(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM ativo WHERE id_ativo = :id");
        return $stmt->execute(['id' => $id]);
    }
}
