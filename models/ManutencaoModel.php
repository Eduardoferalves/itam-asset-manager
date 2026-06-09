<?php
/**
 * Model de Manutenção (Data Access Object - DAO).
 * 
 * [ARQUITETURA] Encapsula a lógica de persistência e cálculos analíticos referentes ao
 * ciclo de vida das manutenções, isolando a complexidade dos JOINs e agregações estatísticas.
 */
class ManutencaoModel {
    /**
     * @var PDO Objeto de conexão persistente com o banco de dados.
     */
    private $db;

    /**
     * Construtor da camada de acesso a dados da manutenção.
     * 
     * @param PDO $db Conexão injetada em tempo de execução.
     */
    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Registra o processamento ou custo incorrido em uma manutenção de ativo físico.
     * 
     * [ARQUITETURA] Assinatura Obrigatória: salvar(array $dados): bool
     * 
     * @param array $dados Dicionário associativo devidamente validado pela camada HTTP.
     * @return bool Booleano atestando o sucesso da operação de gravação.
     */
    public function salvar(array $dados): bool {
        // [SEGURANÇA] Parâmetros anonimizados (:alias) isolando a concatenação maliciosa.
        $stmt = $this->db->prepare("INSERT INTO manutencao (descricao, custo, id_ativo, id_usuario) 
                                    VALUES (:descricao, :custo, :id_ativo, :id_usuario)");
        
        // [REGRA DE NEGÓCIO] Castings rígidos de tipo (float, int) previnem truncamentos de dados
        // e garantem que cálculos atuariais subsequentes da manutenção sejam fiéis à casa decimal.
        return $stmt->execute([
            'descricao'  => trim($dados['descricao']),
            'custo'      => (float)$dados['custo'],
            'id_ativo'   => (int)$dados['id_ativo'],
            'id_usuario' => (int)$dados['id_usuario']
        ]);
    }

    /**
     * Obtém uma estrutura de dados de inteligência (KPI) com custos totais alocados por equipamento e departamento.
     * 
     * [ARQUITETURA] Assinatura Obrigatória: obterCustosAgrupados(): array
     * Utiliza funções de agregação (COUNT, SUM) e consolidação nula (COALESCE) na base,
     * movendo o peso computacional das somas matemáticas para o motor de banco de dados,
     * sendo imensamente superior a iterar e calcular arrays multi-dimensionais em PHP (via foreach).
     * 
     * @return array Resultado de agregações (patrimônio, departamento_nome, qtd_manutencoes, custo_total).
     */
    public function obterCustosAgrupados(): array {
        // [REGRA DE NEGÓCIO] A função COALESCE assegura que valores nulos de associações soltas
        // (como um ativo desalocado do departamento, ou que nunca sofreu manutenção)
        // se manifestem como strings semânticas ("Sem Departamento") ou valores zero (0.00).
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
     * Extrai a listagem analítica das manutenções realizadas com suporte a classificação variada e filtro flexível.
     * 
     * @param string|null $patrimonio Termo de pesquisa para correspondência na identificação do bem.
     * @param string|null $ordemCusto Operador estrito direcional (ASC/DESC).
     * @param string|null $ordemData Operador estrito direcional (ASC/DESC).
     * @return array Resultado mapeado de manutenções detalhadas.
     */
    public function listarComFiltros($patrimonio = null, $ordemCusto = null, $ordemData = null) {
        // [REGRA DE NEGÓCIO] INNER JOIN garante que toda manutenção exibida possua de forma
        // irreversível um registro "pai" de ativo associado (integridade de dependência forte).
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

        // [SEGURANÇA] Bloqueio contra injeção SQL no parâmetro ORDER BY. 
        // Como o PDO não consegue tratar as expressões 'ASC'/'DESC' nos Prepared Statements tradicionais,
        // valida-se a instrução de entrada confrontando-a contra uma Whitelist rígida in-array.
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
     * Recupera o escopo integral de informações de uma manutenção exata baseada na sua identificação.
     * 
     * @param int $id ID primário de referência da tabela.
     * @return array|false Recordset isolado com os dados persistidos, ou falso caso inexistente.
     */
    public function buscarPorId($id) {
        $stmt = $this->db->prepare("SELECT * FROM manutencao WHERE id_manutencao = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }
}
