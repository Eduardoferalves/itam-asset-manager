<?php
/**
 * Controller de Manutenção
 */
class ManutencaoController {
    private $db;
    private $manutencaoModel;
    private $ativoModel;

    public function __construct() {
        // Bloqueio rígido direto no Controller
        if (!isset($_SESSION['usuario'])) {
            header("Location: ?modulo=auth&acao=login");
            exit;
        }

        $this->db = Conexao::getConexao();
        // Mantenha as instâncias dos models que já existem aí
        $this->manutencaoModel = new ManutencaoModel($this->db);
    }

    /**
     * Listagem das manutenções com filtros e buscas
     */
    public function listagem() {
        $patrimonio = isset($_GET['patrimonio']) ? trim($_GET['patrimonio']) : null;
        $ordem_custo = isset($_GET['ordem_custo']) ? $_GET['ordem_custo'] : null;
        $ordem_data = isset($_GET['ordem_data']) ? $_GET['ordem_data'] : null;

        $manutencoes = $this->manutencaoModel->listarComFiltros($patrimonio, $ordem_custo, $ordem_data);

        return [
            'view' => 'manutencao/index',
            'dados' => [
                'manutencoes' => $manutencoes,
                'filtros' => [
                    'patrimonio' => $patrimonio,
                    'ordem_custo' => $ordem_custo,
                    'ordem_data' => $ordem_data
                ]
            ]
        ];
    }

    /**
     * Tela de cadastro de manutenção
     */
    public function cadastro() {
        $id_ativo_selecionado = isset($_GET['id_ativo']) ? (int)$_GET['id_ativo'] : null;

        // Buscar todos os ativos para preencher o dropdown de seleção
        $stmtAtivos = $this->db->query("SELECT id_ativo, patrimonio, status FROM ativo ORDER BY patrimonio ASC");
        $ativos = $stmtAtivos->fetchAll();

        return [
            'view' => 'manutencao/cadastro',
            'dados' => [
                'ativos' => $ativos,
                'id_ativo_selecionado' => $id_ativo_selecionado,
                'modo_leitura' => false
            ]
        ];
    }

    /**
     * Tela de visualização de manutenção (Read-Only / Imutabilidade Financeira)
     */
    public function show() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        $manutencao = $this->manutencaoModel->buscarPorId($id);
        if (!$manutencao) {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Manutenção não encontrada.'];
            header("Location: ?modulo=manutencao&acao=listagem");
            exit;
        }

        $stmtAtivos = $this->db->query("SELECT id_ativo, patrimonio, status FROM ativo ORDER BY patrimonio ASC");
        $ativos = $stmtAtivos->fetchAll();

        return [
            'view' => 'manutencao/cadastro',
            'dados' => [
                'ativos' => $ativos,
                'id_ativo_selecionado' => $manutencao['id_ativo'],
                'manutencao' => $manutencao,
                'modo_leitura' => true
            ]
        ];
    }

    /**
     * Processa a inserção do registro de manutenção
     */
    public function salvar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ?modulo=ativos&acao=listagem");
            exit;
        }

        $dados = $_POST;

        // Validação básica
        if (empty($dados['id_ativo']) || empty($dados['descricao']) || !isset($dados['custo']) || $dados['custo'] === '') {
            $_SESSION['old_input'] = $_POST;
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Preencha todos os campos obrigatórios (Ativo, Descrição e Custo).'];
            $redirectUrl = "?modulo=manutencao&acao=cadastro";
            if (!empty($dados['id_ativo'])) {
                $redirectUrl .= "&id_ativo=" . (int)$dados['id_ativo'];
            }
            header("Location: " . $redirectUrl);
            exit;
        }

        // Validação de custo
        if ((float)$dados['custo'] < 0) {
            $_SESSION['old_input'] = $_POST;
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'O custo da manutenção não pode ser negativo.'];
            header("Location: ?modulo=manutencao&acao=cadastro&id_ativo=" . (int)$dados['id_ativo']);
            exit;
        }

        // Injetar o ID do usuário logado na sessão
        $dados['id_usuario'] = $_SESSION['usuario']['id_usuario'];

        // Salvar manutenção
        try {
            $sucesso = $this->manutencaoModel->salvar($dados);

            if ($sucesso) {
                // UX Premium: Atualizar automaticamente o status do ativo para "Em Manutenção"
                $stmtUpdate = $this->db->prepare("UPDATE ativo SET status = 'Em Manutenção' WHERE id_ativo = :id");
                $stmtUpdate->execute(['id' => (int)$dados['id_ativo']]);

                $_SESSION['flash'] = ['type' => 'success', 'message' => 'Manutenção registrada com sucesso! O ativo foi alterado para "Em Manutenção".'];
                
                // REGRA CRÍTICA: Regenerar token CSRF após POST bem-sucedido
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

                header("Location: ?modulo=ativos&acao=listagem");
                exit;
            } else {
                $_SESSION['old_input'] = $_POST;
                $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Erro ao salvar o registro de manutenção.'];
                header("Location: ?modulo=manutencao&acao=cadastro&id_ativo=" . (int)$dados['id_ativo']);
                exit;
            }
        } catch (PDOException $e) {
            error_log("PDOException em ManutencaoController::salvar - " . $e->getMessage());
            $_SESSION['old_input'] = $_POST;
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Erro de integridade: Verifique se os dados fornecidos (como Categoria ou Ativo) são válidos.'];
            header("Location: ?modulo=manutencao&acao=cadastro&id_ativo=" . (int)$dados['id_ativo']);
            exit;
        }
    }
}
