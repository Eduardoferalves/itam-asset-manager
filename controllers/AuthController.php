<?php
/**
 * Controller de Autenticação
 */
class AuthController {
    private $db;
    private $usuarioModel;

    public function __construct() {
        $this->db = Conexao::getConexao();
        $this->usuarioModel = new UsuarioModel($this->db);
    }

    /**
     * Exibe a tela de login
     */
    public function login() {
        return [
            'view' => 'auth/login'
        ];
    }

    /**
     * Processa a autenticação do usuário
     */
    public function autenticar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ?modulo=auth&acao=login");
            exit;
        }

        $email = trim($_POST['email'] ?? '');
        $senha = $_POST['senha'] ?? '';

        if (empty($email) || empty($senha)) {
            $_SESSION['old_input'] = $_POST;
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Preencha todos os campos obrigatórios.'];
            header("Location: ?modulo=auth&acao=login");
            exit;
        }

        // Buscar usuário pelo email
        $usuario = $this->usuarioModel->findByEmail($email);

        if ($usuario && password_verify($senha, $usuario['senha_hash'])) {
            // Sucesso! Configura a sessão
            session_regenerate_id(true);
            $_SESSION['usuario'] = [
                'id_usuario' => $usuario['id_usuario'],
                'nome' => $usuario['nome'],
                'email' => $usuario['email']
            ];

            $_SESSION['flash'] = ['type' => 'success', 'message' => "Bem-vindo, {$usuario['nome']}!"];

            // REGRA CRÍTICA: Regenera o token CSRF após cada operação POST bem-sucedida
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

            header("Location: ?modulo=ativos&acao=listagem");
            exit;
        } else {
            // Falha na autenticação
            $_SESSION['old_input'] = $_POST;
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'E-mail ou senha incorretos.'];
            header("Location: ?modulo=auth&acao=login");
            exit;
        }
    }

    /**
     * Encerra a sessão do usuário
     */
    public function logout() {
        // Limpar todos os dados da sessão
        $_SESSION = [];

        // Destruir o cookie de sessão do navegador
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        // Destruir a sessão
        session_destroy();

        // Iniciar uma nova sessão vazia apenas para manter mensagens flash se necessário
        session_start();
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Você saiu com sucesso do sistema.'];

        header("Location: ?modulo=auth&acao=login");
        exit;
    }
}
