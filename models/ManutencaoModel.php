<?php
/**
 * Model de Manutenção
 */
class ManutencaoModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Salva um registro de manutenção para um ativo
     * Assinatura Obrigatória: salvar(array $dados): bool
     */
    public function salvar(array $dados): bool {
        $stmt = $this->db->prepare("INSERT INTO manutencao (descricao, custo, id_ativo, id_usuario) 
                                    VALUES (:descricao, :custo, :id_ativo, :id_usuario)");
        return $stmt->execute([
            'descricao'  => trim($dados['descricao']),
            'custo'      => (float)$dados['custo'],
            'id_ativo'   => (int)$dados['id_ativo'],
            'id_usuario' => (int)$dados['id_usuario']
        ]);
    }

    /**
     * Obtém os custos agregados agrupados por ativo
     * Assinatura Obrigatória: obterCustosAgrupados(): array
     * Retorna: patrimônio, departamento, contagem de manutenções e soma dos custos
     */
    public function obterCustosAgrupados(): array {
        $sql = "SELECT 
                    a.patrimonio, 
                    COALESCE(d.nome, 'Sem Departamento') AS departamento_nome, 
                    COUNT(m.id_manutencao) AS qtd_manutencoes, 
                    COALESCE(SUM(m.custo), 0.00) AS custo_total
                FROM ativo a
                LEFT JOIN departamento d ON a.id_departamento = d.id_departamento
                LEFT JOIN manutencao m ON a.id_ativo = m.id_ativo
                GROUP BY a.id_ativo, a.patrimonio, d.nome
                ORDER BY custo_total DESC, a.patrimonio ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Lista manutenções com filtros de busca e ordenação
     */
    public function listarComFiltros($patrimonio = null, $ordemCusto = null, $ordemData = null) {
        $sql = "SELECT m.*, a.patrimonio 
                FROM manutencao m 
                INNER JOIN ativo a ON m.id_ativo = a.id_ativo
                WHERE 1=1";
        $params = [];

        if (!empty($patrimonio)) {
            $sql .= " AND a.patrimonio LIKE :patrimonio";
            $params['patrimonio'] = "%" . trim($patrimonio) . "%";
        }

        $orderClauses = [];
        
        $ordemCusto = strtoupper($ordemCusto ?? '');
        $ordemData = strtoupper($ordemData ?? '');

        if (in_array($ordemCusto, ['ASC', 'DESC'])) {
            $orderClauses[] = "m.custo " . $ordemCusto;
        }
        if (in_array($ordemData, ['ASC', 'DESC'])) {
            $orderClauses[] = "m.data_reg " . $ordemData;
        }

        if (!empty($orderClauses)) {
            $sql .= " ORDER BY " . implode(", ", $orderClauses);
        } else {
            $sql .= " ORDER BY m.data_reg DESC";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Busca uma manutenção específica pelo ID
     */
    public function buscarPorId($id) {
        $stmt = $this->db->prepare("SELECT * FROM manutencao WHERE id_manutencao = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }
}
