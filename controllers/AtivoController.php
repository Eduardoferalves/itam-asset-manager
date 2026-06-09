<?php
/**
 * Controller de Ativos (Camada de Controle - MVC).
 * 
 * [ARQUITETURA] Atua como orquestrador do domínio principal (Ativos).
 * Intercepta as requisições HTTP, coordena validações primitivas e sanitizações da Request,
 * confia regras complexas ao AtivoModel e, por fim, consolida o payload a ser despachado para a View.
 */
class AtivoController {
    /** @var PDO Conexão com o banco de dados. */
    private $db;
    /** @var AtivoModel Instância do DAO para gestão de ativos. */
    private $ativoModel;
    /** @var ApoioModel Instância auxiliar (omita se injetada dinamicamente, mantendo o legado). */
    private $apoioModel;

    public function __construct() {
        // [SEGURANÇA] Middleware acoplado no construtor para blindagem preventiva.
        // Assegura que toda ação (Action) contida neste Controller seja absolutamente restrita a usuários autenticados.
        if (!isset($_SESSION['usuario'])) {
            header("Location: ?modulo=auth&acao=login");
            exit;
        }

        $this->db = Conexao::getConexao();
        $this->ativoModel = new AtivoModel($this->db);
    }

    /**
     * Orquestra a listagem de registros da entidade, incorporando motor de busca por filtros HTTP GET.
     * 
     * @return array Definição da View a renderizar e dicionário de dados (payload).
     */
    public function listagem() {
        // [ARQUITETURA] Recepção higienizada de parâmetros da requisição para preservar a integridade
        // dos valores que serão repassados ao Model.
        $id_categoria = isset($_GET['id_categoria']) && $_GET['id_categoria'] !== '' ? (int)$_GET['id_categoria'] : null;
        $status = isset($_GET['status']) && $_GET['status'] !== '' ? $_GET['status'] : null;
        $patrimonio = isset($_GET['patrimonio']) && $_GET['patrimonio'] !== '' ? trim($_GET['patrimonio']) : null;

        $ativos = $this->ativoModel->listarComFiltros($id_categoria, $status, $patrimonio);

        // [REGRA DE NEGÓCIO] Delegação de acesso secundário focado apenas no auxílio à UI (dropdowns).
        $stmtCat = $this->db->query("SELECT * FROM categoria ORDER BY descricao ASC");
        $categorias = $stmtCat->fetchAll();

        return [
            'view' => 'ativos/listagem',
            'dados' => [
                'ativos' => $ativos,
                'categorias' => $categorias,
                'filtros' => [
                    'id_categoria' => $id_categoria,
                    'status' => $status,
                    'patrimonio' => $patrimonio
                ]
            ]
        ];
    }

    /**
     * Prepara a interface de submissão (formulário) injetando dependências visuais.
     * 
     * @return array Configuração da View com payload nulo para a entidade, indicando criação.
     */
    public function cadastro() {
        $categorias = $this->db->query("SELECT * FROM categoria ORDER BY descricao ASC")->fetchAll();
        $departamentos = $this->db->query("SELECT * FROM departamento ORDER BY nome ASC")->fetchAll();
        $fornecedores = $this->db->query("SELECT * FROM fornecedor ORDER BY nome_empresa ASC")->fetchAll();

        return [
            'view' => 'ativos/cadastro',
            'dados' => [
                'categorias' => $categorias,
                'departamentos' => $departamentos,
                'fornecedores' => $fornecedores,
                'ativo' => null // [REGRA DE NEGÓCIO] Flag de nulidade para adaptar a View a contexto de Inserção.
            ]
        ];
    }

    /**
     * Valida e persiste um novo ativo, protegendo a unicidade dos identificadores de negócio.
     */
    public function salvar() {
        // [SEGURANÇA] Bloqueia o trânsito se não for POST, limitando o canal a submissões seguras de formulário.
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ?modulo=ativos&acao=listagem");
            exit;
        }

        $dados = $_POST;
        
        // [REGRA DE NEGÓCIO] Barreira primária para garantir coerência informacional antes de 
        // invocar o motor do banco de dados. Campos vacantes geram feedback resiliente (preservando state).
        if (empty($dados['patrimonio']) || empty($dados['status']) || empty($dados['data_aquisicao'])) {
            $_SESSION['old_input'] = $_POST;
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Preencha os campos obrigatórios (Patrimônio, Status e Data de Aquisição).'];
            header("Location: ?modulo=ativos&acao=cadastro");
            exit;
        }

        // [REGRA DE NEGÓCIO] Proteção de restrição lógica (Unique Index via aplicação) para evitar 
        // conflitos e Exceptions técnicas ininteligíveis caso dois bens idênticos sejam inseridos.
        if ($this->ativoModel->patrimonioExiste($dados['patrimonio'])) {
            $_SESSION['old_input'] = $_POST;
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Código de Patrimônio já está em uso por outro ativo.'];
            header("Location: ?modulo=ativos&acao=cadastro");
            exit;
        }

        try {
            $sucesso = $this->ativoModel->cadastrar($dados);

            if ($sucesso) {
                $_SESSION['flash'] = ['type' => 'success', 'message' => 'Ativo cadastrado com sucesso!'];
                // [SEGURANÇA] Atualização do token CSRF para invalidar reaproveitamento pós-mutação bem sucedida.
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                header("Location: ?modulo=ativos&acao=listagem");
                exit;
            } else {
                $_SESSION['old_input'] = $_POST;
                $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Erro ao salvar o ativo. Verifique os dados.'];
                header("Location: ?modulo=ativos&acao=cadastro");
                exit;
            }
        } catch (PDOException $e) {
            error_log("PDOException em AtivoController::salvar - " . $e->getMessage());
            $_SESSION['old_input'] = $_POST;
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Erro de integridade: Verifique se os dados fornecidos (como Categoria ou Ativo) são válidos.'];
            header("Location: ?modulo=ativos&acao=cadastro");
            exit;
        }
    }

    /**
     * Fornece o arcabouço da View de edição, instanciando dados pregressos e tabelas de auxílio.
     */
    public function editar() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        $ativo = $this->ativoModel->buscarPorId($id);
        if (!$ativo) {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Ativo não encontrado.'];
            header("Location: ?modulo=ativos&acao=listagem");
            exit;
        }

        $categorias = $this->db->query("SELECT * FROM categoria ORDER BY descricao ASC")->fetchAll();
        $departamentos = $this->db->query("SELECT * FROM departamento ORDER BY nome ASC")->fetchAll();
        $fornecedores = $this->db->query("SELECT * FROM fornecedor ORDER BY nome_empresa ASC")->fetchAll();

        return [
            'view' => 'ativos/cadastro',
            'dados' => [
                'ativo' => $ativo,
                'categorias' => $categorias,
                'departamentos' => $departamentos,
                'fornecedores' => $fornecedores
            ]
        ];
    }

    /**
     * Modifica um registro preexistente avaliando potenciais conflitos de identidade (Patrimônio).
     */
    public function atualizar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ?modulo=ativos&acao=listagem");
            exit;
        }

        $id = isset($_POST['id_ativo']) ? (int)$_POST['id_ativo'] : 0;
        $dados = $_POST;

        $ativo = $this->ativoModel->buscarPorId($id);
        if (!$ativo) {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Ativo não encontrado para atualização.'];
            header("Location: ?modulo=ativos&acao=listagem");
            exit;
        }

        if (empty($dados['patrimonio']) || empty($dados['status']) || empty($dados['data_aquisicao'])) {
            $_SESSION['old_input'] = $_POST;
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Preencha os campos obrigatórios (Patrimônio, Status e Data de Aquisição).'];
            header("Location: ?modulo=ativos&acao=editar&id={$id}");
            exit;
        }

        // [REGRA DE NEGÓCIO] Valida se o patrimônio está ocupado, excluindo da checagem o ID do próprio ativo que está sendo editado.
        if ($this->ativoModel->patrimonioExiste($dados['patrimonio'], $id)) {
            $_SESSION['old_input'] = $_POST;
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Código de Patrimônio já está em uso por outro ativo.'];
            header("Location: ?modulo=ativos&acao=editar&id={$id}");
            exit;
        }

        try {
            $sucesso = $this->ativoModel->atualizar($id, $dados);

            if ($sucesso) {
                $_SESSION['flash'] = ['type' => 'success', 'message' => 'Ativo atualizado com sucesso!'];
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                header("Location: ?modulo=ativos&acao=listagem");
                exit;
            } else {
                $_SESSION['old_input'] = $_POST;
                $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Erro ao atualizar o ativo.'];
                header("Location: ?modulo=ativos&acao=editar&id={$id}");
                exit;
            }
        } catch (PDOException $e) {
            error_log("PDOException em AtivoController::atualizar - " . $e->getMessage());
            $_SESSION['old_input'] = $_POST;
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Erro de integridade: Verifique se os dados fornecidos (como Categoria ou Ativo) são válidos.'];
            header("Location: ?modulo=ativos&acao=editar&id={$id}");
            exit;
        }
    }

    /**
     * Exclui um ativo.
     * 
     * [SEGURANÇA] Destruição de dados forçadamente submetida a método POST, bloqueando acionamentos
     * maliciosos ou casuais gerados por links href diretos ou rastreadores de indexação HTTP.
     */
    public function excluir() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ?modulo=ativos&acao=listagem");
            exit;
        }

        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

        try {
            $sucesso = $this->ativoModel->excluir($id);

            if ($sucesso) {
                $_SESSION['flash'] = ['type' => 'success', 'message' => 'Ativo excluído com sucesso!'];
            } else {
                $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Não foi possível encontrar o ativo selecionado.'];
            }

            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

        } catch (PDOException $e) {
            error_log("PDOException em AtivoController::excluir - " . $e->getMessage());
            
            // [ARQUITETURA] Interceptação refinada (Exception Catching) de violação de chave estrangeira (FK Constraint).
            // A camada de controle traduz a falha técnica SQL nativa para uma orientação comercial útil de UX ao usuário,
            // impedindo a corrupção do histórico atuarial.
            if ($e->getCode() === '23000' || strpos($e->getMessage(), '1217') !== false || strpos($e->getMessage(), '1451') !== false) {
                $_SESSION['flash'] = [
                    'type' => 'danger',
                    'message' => 'Erro: Este ativo possui histórico financeiro de manutenções e não pode ser excluído. Altere o status para \'Inativo\'.'
                ];
            } else {
                $_SESSION['flash'] = [
                    'type' => 'danger',
                    'message' => 'Erro de banco de dados ao tentar excluir o ativo: ' . $e->getMessage()
                ];
            }
        }

        header("Location: ?modulo=ativos&acao=listagem");
        exit;
    }
}
