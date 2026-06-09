<?php
/**
 * Model de Ativo (Data Access Object - DAO).
 * 
 * [ARQUITETURA] Entidade central do domínio do sistema ITAM.
 * Este Model concentra a manipulação de leitura e escrita da tabela 'ativo',
 * garantindo consistência referencial (via FKs explícitas nos JOINs) e
 * higienização das instruções que acessam o banco de dados.
 */
class AtivoModel {
    /**
     * @var PDO Instância de conexão ao banco de dados.
     */
    private $db;

    /**
     * Construtor que recebe a injeção da conexão ao banco.
     * 
     * @param PDO $db Conexão instanciada (Singleton).
     */
    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Lista ativos aplicando filtros dinâmicos e agregando tabelas secundárias.
     * 
     * [ARQUITETURA] Assinatura Obrigatória: listarComFiltros(?int $id_categoria, ?string $status, ?string $patrimonio): array
     * Utiliza LEFT JOIN para garantir a exibição do ativo mesmo que os vínculos de chave estrangeira
     * (departamento, fornecedor, categoria) sejam anuláveis ou inexistentes (integridade referencial fraca permitida).
     * 
     * @param int|null $id_categoria Filtro opcional pelo identificador da categoria.
     * @param string|null $status Filtro opcional pelo status atual do ativo.
     * @param string|null $patrimonio Filtro opcional parcial da string de tombamento (patrimônio).
     * @return array Conjunto de registros consolidados para exibição.
     */
    public function listarComFiltros(?int $id_categoria, ?string $status, ?string $patrimonio): array {
        // [REGRA DE NEGÓCIO] Junção de tabelas de metadados para resolver IDs em nomes legíveis na visualização.
        $sql = "SELECT a.*, c.descricao as categoria_nome, d.nome as departamento_nome, f.nome_empresa as fornecedor_nome 
                FROM ativo a
                LEFT JOIN categoria c ON a.id_categoria = c.id_categoria
                LEFT JOIN departamento d ON a.id_departamento = d.id_departamento
                LEFT JOIN fornecedor f ON a.id_fornecedor = f.id_fornecedor
                WHERE 1=1";
        
        $params = [];

        // [SEGURANÇA] Montagem dinâmica das cláusulas de filtro sempre utilizando parâmetros
        // não vinculados diretamente, mantendo a blindagem contra injeções SQL indesejadas.
        if ($id_categoria !== null && $id_categoria !== '') {
            $sql .= " AND a.id_categoria = :id_categoria";
            $params['id_categoria'] = (int)$id_categoria;
        }

        if ($status !== null && $status !== '') {
            $sql .= " AND a.status = :status";
            $params['status'] = $status;
        }

        if ($patrimonio !== null && $patrimonio !== '') {
            // [REGRA DE NEGÓCIO] Uso da cláusula LIKE para permitir pesquisas parciais do patrimônio,
            // flexibilizando a experiência do usuário durante as buscas.
            $sql .= " AND a.patrimonio LIKE :patrimonio";
            $params['patrimonio'] = '%' . $patrimonio . '%';
        }

        // [ARQUITETURA] Ordenar por data de aquisição ou ID decrescente por padrão para visualização moderna,
        // garantindo que os itens inseridos mais recentemente apareçam no topo da lista.
        $sql .= " ORDER BY a.id_ativo DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Busca um ativo específico pelo seu identificador primário.
     * 
     * [ARQUITETURA] Assinatura Obrigatória: buscarPorId(int $id): ?array
     * 
     * @param int $id Chave primária do ativo a ser resgatado.
     * @return array|null Dados da linha encontrada ou nulo.
     */
    public function buscarPorId(int $id): ?array {
        // [SEGURANÇA] Preparação estrita evitando injeção em endpoint de detalhamento.
        $stmt = $this->db->prepare("SELECT * FROM ativo WHERE id_ativo = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $ativo = $stmt->fetch();
        return $ativo ? $ativo : null;
    }

    /**
     * Cadastra um novo registro de ativo físico ou lógico na base.
     * 
     * [ARQUITETURA] Assinatura Obrigatória: cadastrar(array $dados): bool
     * 
     * @param array $dados Dicionário associativo proveniente do formulário higienizado no Controller.
     * @return bool Verdadeiro em caso de sucesso na persistência, falso caso contrário.
     */
    public function cadastrar(array $dados): bool {
        // [REGRA DE NEGÓCIO] A instrução INSERT garante que relacionamentos (chaves estrangeiras)
        // possam ser inseridos como nulos (NULL) caso as entidades dependentes não sejam informadas, 
        // aderindo às regras de integridade referencial do esquema de dados.
        $stmt = $this->db->prepare("INSERT INTO ativo (patrimonio, status, data_aquisicao, id_categoria, id_departamento, id_fornecedor) 
                                    VALUES (:patrimonio, :status, :data_aquisicao, :id_categoria, :id_departamento, :id_fornecedor)");
        
        // [SEGURANÇA] Tratamento e coerção de tipos antes da amarração final, garantindo 
        // que inteiros não sejam interpretados erroneamente e evitando falhas de banco.
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
     * Atualiza os metadados e o estado de um ativo existente.
     * 
     * [ARQUITETURA] Assinatura Obrigatória: atualizar(int $id, array $dados): bool
     * 
     * @param int $id Chave primária do ativo a ser modificado.
     * @param array $dados Dicionário contendo o estado atualizado.
     * @return bool Confirmação booleana do processo.
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
     * Remove de forma definitiva um ativo do sistema.
     * 
     * [ARQUITETURA] Assinatura Obrigatória: excluir(int $id): bool
     * 
     * @param int $id Chave primária para identificação da linha a ser destruída.
     * @return bool Retorna verdadeiro em caso de remoção bem-sucedida.
     */
    public function excluir(int $id): bool {
        // [REGRA DE NEGÓCIO] Exclusão física (Hard Delete). Requer que não existam registros
        // de manutenções associadas (se as FKs estiverem restritas) ou a deleção explodirá 
        // um erro de integridade de banco de dados, protegendo o histórico do sistema.
        $stmt = $this->db->prepare("DELETE FROM ativo WHERE id_ativo = :id");
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Verifica a existência de um número de patrimônio para prevenir duplicidade de inventário.
     * 
     * @param string $patrimonio A etiqueta / string identificadora a ser testada.
     * @param int $id_ativo_ignorar ID opcional para excetuar durante atualizações.
     * @return bool Retorna `true` se o patrimônio já estiver catalogado em outro registro.
     */
    public function patrimonioExiste(string $patrimonio, int $id_ativo_ignorar = 0): bool {
        // [REGRA DE NEGÓCIO] Assegura unicidade em nível de aplicação (antes da restrição física do banco),
        // permitindo que o Controller repasse mensagens compreensíveis (flash messages) ao usuário final 
        // em vez de o sistema abortar violentamente com um erro SQL PDOException (Unique Constraint Violation).
        $sql = "SELECT COUNT(*) FROM ativo WHERE patrimonio = :patrimonio";
        $params = ['patrimonio' => trim($patrimonio)];
        
        if ($id_ativo_ignorar > 0) {
            $sql .= " AND id_ativo != :id";
            $params['id'] = $id_ativo_ignorar;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Realiza a mutação isolada do ciclo de vida (status) de um ativo específico.
     * 
     * @param int $id_ativo Chave primária.
     * @param string $status O novo status normativo.
     * @return bool Se a instrução foi executada.
     */
    public function atualizarStatus(int $id_ativo, string $status): bool {
        // [ARQUITETURA] Método especializado (SRP) focado exclusivamente na alteração de estado.
        // Evita a sobrecarga de trafegar e submeter todos os metadados do ativo na ação.
        $stmt = $this->db->prepare("UPDATE ativo SET status = :status WHERE id_ativo = :id");
        return $stmt->execute(['status' => $status, 'id' => $id_ativo]);
    }

    /**
     * Gera uma listagem sintetizada (Dropdowns / Selects) focada na referência dos ativos.
     * 
     * @return array Conjunto de dicionários curtos id/patrimônio/status.
     */
    public function listarResumoAtivos(): array {
        // [REGRA DE NEGÓCIO] Carga otimizada para comboboxes onde atributos completos não são necessários,
        // economizando largura de banda da rede de banco e uso de memória da requisição.
        $stmt = $this->db->query("SELECT id_ativo, patrimonio, status FROM ativo ORDER BY patrimonio ASC");
        return $stmt->fetchAll();
    }
}
