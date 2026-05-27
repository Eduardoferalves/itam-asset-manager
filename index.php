<?php
/**
 * Front Controller do Sistema ITAM
 */

// 1. Configurações de Sessão Segura e Inicialização
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => false, // altere para true se estiver usando HTTPS
    'httponly' => true,
    'samesite' => 'Strict'
]);
session_start();

// 2. Geração do Token CSRF (se não existir)
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// 3. Autoload Simples para Controllers e Models (Vanilla PHP)
spl_autoload_register(function ($class) {
    if (file_exists("controllers/{$class}.php")) {
        require_once "controllers/{$class}.php";
    } elseif (file_exists("models/{$class}.php")) {
        require_once "models/{$class}.php";
    }
});

// Importar conexao.php (Singleton)
require_once 'conexao.php';

// 4. Whitelist de Rotas (Evitar LFI)
$rotas = [
    'auth'       => ['login', 'autenticar', 'logout'],
    'ativos'     => ['listagem', 'cadastro', 'salvar', 'editar', 'atualizar', 'excluir'],
    'manutencao' => ['cadastro', 'salvar'],
    'relatorio'  => ['index', 'exportar']
];

// 5. Captura dos Parâmetros de Módulo e Ação
$modulo = $_GET['modulo'] ?? null;
$acao = $_GET['acao'] ?? null;

// Rota padrão com base no status de autenticação
$usuario_logado = isset($_SESSION['usuario']);

if (!$modulo || !$acao) {
    if ($usuario_logado) {
        $modulo = 'ativos';
        $acao = 'listagem';
    } else {
        $modulo = 'auth';
        $acao = 'login';
    }
}

// 6. Validação da Whitelist
if (!isset($rotas[$modulo]) || !in_array($acao, $rotas[$modulo])) {
    // Rota inválida, redireciona de forma segura
    if ($usuario_logado) {
        header("Location: ?modulo=ativos&acao=listagem");
    } else {
        header("Location: ?modulo=auth&acao=login");
    }
    exit;
}

// 7. Controle de Acesso (Autenticação)
if (!$usuario_logado && $modulo !== 'auth') {
    // Forçar login para áreas restritas
    $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Por favor, faça login para acessar o sistema.'];
    header("Location: ?modulo=auth&acao=login");
    exit;
}

if ($usuario_logado && $modulo === 'auth' && $acao === 'login') {
    // Usuário já logado não precisa ir para tela de login
    header("Location: ?modulo=ativos&acao=listagem");
    exit;
}

// 8. Regra Crítica para Ação 'excluir'
if ($modulo === 'ativos' && $acao === 'excluir' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Operação de exclusão inválida. Apenas requisições POST são permitidas.'];
    header("Location: ?modulo=ativos&acao=listagem");
    exit;
}

// 9. Validação Global do Token CSRF para requisições POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token_post = $_POST['csrf_token'] ?? '';
    if (empty($token_post) || $token_post !== ($_SESSION['csrf_token'] ?? '')) {
        http_response_code(403);
        $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Erro de segurança: Token CSRF inválido ou expirado.'];
        die("Acesso negado: Token CSRF inválido.");
    }
}

// 10. Instanciação do Controller e Execução da Ação
$modulo_mapeado = $modulo === 'ativos' ? 'ativo' : $modulo;
$controllerClass = ucfirst($modulo_mapeado) . 'Controller';
if (class_exists($controllerClass)) {
    $controller = new $controllerClass();
    if (method_exists($controller, $acao)) {
        // Executar ação
        $resultado = $controller->$acao();

        // 11. Processamento e Injeção do Layout (se aplicável)
        // Se a ação não retornou array com view, assume-se que ela realizou um redirect ou exportação direta
        if (is_array($resultado) && isset($resultado['view'])) {
            // Extração de dados retornados pelo controller para o escopo global
            if (isset($resultado['dados']) && is_array($resultado['dados'])) {
                extract($resultado['dados']);
            }

            // REGRA CRÍTICA DE ESCOPO: A variável $flash deve ser declarada no escopo global antes dos requires da view
            $flash = $_SESSION['flash'] ?? null;
            unset($_SESSION['flash']);

            // Centralizando o ciclo de vida do old_input
            $old_input = $_SESSION['old_input'] ?? [];
            unset($_SESSION['old_input']);

            require_once 'views/layout/header.php';
            require_once "views/{$resultado['view']}.php";
            require_once 'views/layout/footer.php';
        }
    } else {
        die("Ação inválida: {$acao}");
    }
} else {
    die("Módulo inválido: {$modulo}");
}
