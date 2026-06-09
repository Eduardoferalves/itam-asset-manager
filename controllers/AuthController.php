<?php
/**
 * Controller de Autenticação (Camada de Controle - MVC).
 * 
 * [ARQUITETURA] Responsável por coordenar o ciclo de vida da autenticação, isolando
 * o processo de verificação de credenciais e a gestão das variáveis de sessão de segurança.
 * Atua como intermediário entre a requisição HTTP do cliente e o UsuarioModel.
 */
class AuthController {
    /**
     * @var PDO Conexão de banco de dados (Singleton).
     */
    private $db;
    /**
     * @var UsuarioModel Model encarregado da persistência de usuários.
     */
    private $usuarioModel;

    /**
     * [ARQUITETURA] Construtor estabelece a conexão global e injeta a dependência no Model.
     */
    public function __construct() {
        $this->db = Conexao::getConexao();
        $this->usuarioModel = new UsuarioModel($this->db);
    }

    /**
     * Entrega a interface gráfica (View) do formulário de acesso.
     * 
     * @return array Estrutura contendo o nome da View a ser injetada pelo Front Controller.
     */
    public function login() {
        return [
            'view' => 'auth/login'
        ];
    }

    /**
     * Valida as credenciais submetidas e estabelece o estado de segurança da sessão.
     */
    public function autenticar() {
        // [SEGURANÇA] Intercepta tentativas de mutação fora do escopo POST,
        // prevenindo ataques onde credenciais poderiam ser trafegadas via Query String (GET).
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ?modulo=auth&acao=login");
            exit;
        }

        $email = trim($_POST['email'] ?? '');
        $senha = $_POST['senha'] ?? '';

        if (empty($email) || empty($senha)) {
            // [ARQUITETURA] O ciclo da variável $_SESSION ['old_input'] visa recompor o formulário
            // evitando a quebra de UX, enquanto o flash garante o aviso efêmero e se autodestrói.
            $_SESSION['old_input'] = $_POST;
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Preencha todos os campos obrigatórios.'];
            header("Location: ?modulo=auth&acao=login");
            exit;
        }

        $usuario = $this->usuarioModel->findByEmail($email);

        // [SEGURANÇA] Validação de hash criptográfico e mitigação de vulnerabilidades de temporização.
        if ($usuario && password_verify($senha, $usuario['senha_hash'])) {
            // [SEGURANÇA] Rotação obrigatória do Session ID (Session Fixation prevention).
            // Impede que um atacante pré-defina o ID da sessão e sequestre o acesso do usuário logado.
            session_regenerate_id(true);
            
            $_SESSION['usuario'] = [
                'id_usuario' => $usuario['id_usuario'],
                'nome' => $usuario['nome'],
                'email' => $usuario['email']
            ];
            
            // [ARQUITETURA] Flag auxiliar estrita de controle de acesso para middleware customizado.
            $_SESSION['usuario_logado'] = true;

            $_SESSION['flash'] = ['type' => 'success', 'message' => "Bem-vindo, {$usuario['nome']}!"];

            // [SEGURANÇA] Rotação do Token CSRF como mandatório após toda elevação de privilégio ou mutação de estado.
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

            header("Location: ?modulo=ativos&acao=listagem");
            exit;
        } else {
            $_SESSION['old_input'] = $_POST;
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'E-mail ou senha incorretos.'];
            header("Location: ?modulo=auth&acao=login");
            exit;
        }
    }

    /**
     * Destrói a sessão ativa e todas as permissões concedidas.
     */
    public function logout() {
        $_SESSION = [];

        // [SEGURANÇA] Destruição física do cookie associado à sessão local no navegador do cliente,
        // garantindo que a referência da sessão seja desfeita integralmente.
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        session_destroy();

        // [ARQUITETURA] Reinicialização branda unicamente para trafegar a flash message (feedback de saída) 
        // pela infraestrutura arquitetural do Front Controller.
        session_start();
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Você saiu com sucesso do sistema.'];

        header("Location: ?modulo=auth&acao=login");
        exit;
    }
}
