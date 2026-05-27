<?php
/**
 * Controller de Manutenção
 */
class ManutencaoController {
    private $db;
    private $manutencaoModel;

    public function __construct() {
        $this->db = Conexao::getConexao();
        $this->manutencaoModel = new ManutencaoModel($this->db);
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
                'id_ativo_selecionado' => $id_ativo_selecionado
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
            $_SESSION['old_input'] = $_POST;
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Erro de integridade: Verifique se os dados fornecidos (como Categoria ou Ativo) são válidos.'];
            header("Location: ?modulo=manutencao&acao=cadastro&id_ativo=" . (int)$dados['id_ativo']);
            exit;
        }
    }
}
