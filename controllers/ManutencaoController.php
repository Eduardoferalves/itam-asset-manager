<?php
/**
 * Controller de Manutenção (Camada de Controle - MVC).
 * 
 * [ARQUITETURA] Desacopla a interface com o usuário das lógicas restritivas e complexas
 * relacionadas ao registro contábil / histórico de intervenções feitas nos equipamentos de TI.
 */
class ManutencaoController {
    /** @var PDO Referência isolada de conexão ao banco. */
    private $db;
    /** @var ManutencaoModel Objeto de serviço com regras de acesso a dados da manutenção. */
    private $manutencaoModel;

    public function __construct() {
        // [SEGURANÇA] Bloqueio estrito da classe garantindo que o ciclo de vida do controller seja abortado
        // se a requisição não trafegar pela etapa de Autenticação na sessão do usuário.
        if (!isset($_SESSION['usuario'])) {
            header("Location: ?modulo=auth&acao=login");
            exit;
        }

        $this->db = Conexao::getConexao();
        $this->manutencaoModel = new ManutencaoModel($this->db);
    }

    /**
     * Orquestra a exibição da listagem de intervenções técnicas, permitindo aplicação de ordenação fluida e buscas.
     * 
     * @return array Objeto encapsulado contendo o mapeamento da View correspondente.
     */
    public function listagem() {
        // [ARQUITETURA] Atribuição coesa de parâmetros da requisição provendo falhas nulas se não houver payload na URI.
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
     * Projeta o formulário de cadastro, exigindo contextualização de ativos.
     * 
     * @return array Entidade visual formatada com a coleção referencial de itens.
     */
    public function cadastro() {
        $id_ativo_selecionado = isset($_GET['id_ativo']) ? (int)$_GET['id_ativo'] : null;

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
     * Projeta um endpoint seguro e imutável para revisar auditoria de registros passados.
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
                // [REGRA DE NEGÓCIO] Protege o registro da edição alterando a flag que comanda a View de renderização 
                // para renderizar componentes desativados (disabled), prevenindo violação financeira no front-end.
                'modo_leitura' => true
            ]
        ];
    }

    /**
     * Submete um novo registro contabilístico e dispara triggers na lógica do ativo referenciado.
     */
    public function salvar() {
        // [SEGURANÇA] Somente fluxos submetidos intencionalmente por POST têm permissão para mutação de banco.
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ?modulo=ativos&acao=listagem");
            exit;
        }

        $dados = $_POST;

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

        // [REGRA DE NEGÓCIO] Evita aberrações matemáticas rejeitando qualquer custo reportado com valor negativo.
        if ((float)$dados['custo'] < 0) {
            $_SESSION['old_input'] = $_POST;
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'O custo da manutenção não pode ser negativo.'];
            header("Location: ?modulo=manutencao&acao=cadastro&id_ativo=" . (int)$dados['id_ativo']);
            exit;
        }

        // [SEGURANÇA] Injeta dados sigilosos via backend da sessão de usuário, evitando confianças falhas
        // advindas de campos hidden expostos que sofrem fácil manipulação no HTML.
        $dados['id_usuario'] = $_SESSION['usuario']['id_usuario'];

        try {
            $sucesso = $this->manutencaoModel->salvar($dados);

            if ($sucesso) {
                // [REGRA DE NEGÓCIO] Altera explicitamente o domínio do Ativo após a injeção do evento da manutenção, 
                // consolidando duas responsabilidades cruzadas, oferecendo uma UI sofisticada ao usuário sem interação manual.
                $stmtUpdate = $this->db->prepare("UPDATE ativo SET status = 'Em Manutenção' WHERE id_ativo = :id");
                $stmtUpdate->execute(['id' => (int)$dados['id_ativo']]);

                $_SESSION['flash'] = ['type' => 'success', 'message' => 'Manutenção registrada com sucesso! O ativo foi alterado para "Em Manutenção".'];
                
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
