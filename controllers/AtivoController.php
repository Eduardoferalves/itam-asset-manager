<?php
/**
 * Controller de Ativos (CRUD)
 */
class AtivoController {
    private $db;
    private $ativoModel;
    private $apoioModel;

    public function __construct() {
        // Bloqueio rígido direto no Controller
        if (!isset($_SESSION['usuario'])) {
            header("Location: ?modulo=auth&acao=login");
            exit;
        }

        $this->db = Conexao::getConexao();
        // Mantenha as instâncias dos models que já existem aí
        $this->ativoModel = new AtivoModel($this->db);
    }

    /**
     * Listagem dos ativos com filtros e buscas
     */
    public function listagem() {
        $id_categoria = isset($_GET['id_categoria']) && $_GET['id_categoria'] !== '' ? (int)$_GET['id_categoria'] : null;
        $status = isset($_GET['status']) && $_GET['status'] !== '' ? $_GET['status'] : null;
        $patrimonio = isset($_GET['patrimonio']) && $_GET['patrimonio'] !== '' ? trim($_GET['patrimonio']) : null;

        // Obter ativos filtrados
        $ativos = $this->ativoModel->listarComFiltros($id_categoria, $status, $patrimonio);

        // Obter categorias para preencher o dropdown do filtro (apoio, sem CRUD)
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
     * Tela de cadastro de ativo (vazio)
     */
    public function cadastro() {
        // Obter dados de apoio para preencher dropdowns (sem CRUD)
        $categorias = $this->db->query("SELECT * FROM categoria ORDER BY descricao ASC")->fetchAll();
        $departamentos = $this->db->query("SELECT * FROM departamento ORDER BY nome ASC")->fetchAll();
        $fornecedores = $this->db->query("SELECT * FROM fornecedor ORDER BY nome_empresa ASC")->fetchAll();

        return [
            'view' => 'ativos/cadastro',
            'dados' => [
                'categorias' => $categorias,
                'departamentos' => $departamentos,
                'fornecedores' => $fornecedores,
                'ativo' => null // Indica novo registro
            ]
        ];
    }

    /**
     * Salva o novo ativo
     */
    public function salvar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ?modulo=ativos&acao=listagem");
            exit;
        }

        $dados = $_POST;
        
        // Validação básica
        if (empty($dados['patrimonio']) || empty($dados['status']) || empty($dados['data_aquisicao'])) {
            $_SESSION['old_input'] = $_POST;
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Preencha os campos obrigatórios (Patrimônio, Status e Data de Aquisição).'];
            header("Location: ?modulo=ativos&acao=cadastro");
            exit;
        }

        // Verificar se patrimônio é único
        if ($this->ativoModel->patrimonioExiste($dados['patrimonio'])) {
            $_SESSION['old_input'] = $_POST;
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Código de Patrimônio já está em uso por outro ativo.'];
            header("Location: ?modulo=ativos&acao=cadastro");
            exit;
        }

        // Cadastrar ativo
        try {
            $sucesso = $this->ativoModel->cadastrar($dados);

            if ($sucesso) {
                $_SESSION['flash'] = ['type' => 'success', 'message' => 'Ativo cadastrado com sucesso!'];
                // REGRA CRÍTICA: Regenerar token CSRF após POST bem-sucedido
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
     * Tela de edição de ativo
     */
    public function editar() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        $ativo = $this->ativoModel->buscarPorId($id);
        if (!$ativo) {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Ativo não encontrado.'];
            header("Location: ?modulo=ativos&acao=listagem");
            exit;
        }

        // Obter dados de apoio para preencher dropdowns (sem CRUD)
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
     * Atualiza um ativo existente
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

        // Validação básica
        if (empty($dados['patrimonio']) || empty($dados['status']) || empty($dados['data_aquisicao'])) {
            $_SESSION['old_input'] = $_POST;
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Preencha os campos obrigatórios (Patrimônio, Status e Data de Aquisição).'];
            header("Location: ?modulo=ativos&acao=editar&id={$id}");
            exit;
        }

        // Verificar se código de patrimônio já é usado por OUTRO ativo
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
                // REGRA CRÍTICA: Regenerar token CSRF após POST bem-sucedido
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
     * Exclui um ativo
     * REGRA CRÍTICA DE SEGURANÇA: Apenas via POST, com tratamento try/catch robusto contra falha de restrição RESTRICT
     */
    public function excluir() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ?modulo=ativos&acao=listagem");
            exit;
        }

        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

        try {
            // Tenta deletar o ativo do banco de dados
            $sucesso = $this->ativoModel->excluir($id);

            if ($sucesso) {
                $_SESSION['flash'] = ['type' => 'success', 'message' => 'Ativo excluído com sucesso!'];
            } else {
                $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Não foi possível encontrar o ativo selecionado.'];
            }

            // REGRA CRÍTICA: Regenerar token CSRF após POST bem-sucedido
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

        } catch (PDOException $e) {
            error_log("PDOException em AtivoController::excluir - " . $e->getMessage());
            // CRITÉRIO DE ACEITE: Capturar violação de restrição de chave externa RESTRICT graciosamente
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
