<?php
/**
 * Ponto de entrada central da aplicação implementando o padrão Front Controller.
 * 
 * Este arquivo atua como o único ponto de entrada HTTP para o sistema ITAM Asset Manager,
 * centralizando as responsabilidades de inicialização, configuração de segurança,
 * roteamento, controle de sessão e delegação para os Controllers apropriados.
 * A centralização garante que as políticas de segurança e a gestão do ciclo de vida
 * das requisições sejam aplicadas uniformemente em toda a aplicação.
 */

// [SEGURANÇA] Endurecimento dos parâmetros do cookie de sessão para mitigar ataques XSS (Cross-Site Scripting)
// e CSRF (Cross-Site Request Forgery). A restrição de escopo e visibilidade do cookie impede que scripts
// no lado do cliente acessem o identificador da sessão.
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => true, // altere para true se estiver usando HTTPS
    'httponly' => true,
    'samesite' => 'Strict'
]);
session_start();

// [SEGURANÇA] Geração de token criptográfico forte e único por sessão para validação posterior 
// das mutações de estado (POST, PUT, DELETE), prevenindo ataques CSRF.
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// [ARQUITETURA] Autoload customizado elimina a necessidade de declarações manuais de 'require' em todo o sistema.
// Segue a convenção de nomenclatura de classes em relação aos diretórios, garantindo carregamento sob demanda.
spl_autoload_register(function ($class) {
    if (file_exists("controllers/{$class}.php")) {
        require_once "controllers/{$class}.php";
    } elseif (file_exists("models/{$class}.php")) {
        require_once "models/{$class}.php";
    }
});

require_once 'conexao.php';

// [SEGURANÇA] Definição estrita (Whitelist) das rotas permitidas para mitigar vulnerabilidades
// de LFI (Local File Inclusion) e acessos indevidos a métodos internos ou restritos.
$rotas = [
    'auth'       => ['login', 'autenticar', 'logout'],
    'ativos'     => ['listagem', 'cadastro', 'salvar', 'editar', 'atualizar', 'excluir'],
    'manutencao' => ['cadastro', 'salvar', 'listagem', 'show'],
    'relatorio'  => ['index', 'exportar']
];

$modulo = $_GET['modulo'] ?? null;
$acao = $_GET['acao'] ?? null;

$usuario_logado = isset($_SESSION['usuario']);

// [REGRA DE NEGÓCIO] Roteamento padrão resiliente: na ausência de parâmetros,
// direciona automaticamente de acordo com o estado atual de autenticação do usuário.
if (!$modulo || !$acao) {
    if ($usuario_logado) {
        $modulo = 'ativos';
        $acao = 'listagem';
    } else {
        $modulo = 'auth';
        $acao = 'login';
    }
}

// [SEGURANÇA] Validação rigorosa da requisição contra o mapa de rotas.
// Qualquer desvio aciona uma resposta segura de redirecionamento, mascarando a estrutura interna da aplicação.
if (!isset($rotas[$modulo]) || !in_array($acao, $rotas[$modulo])) {
    if ($usuario_logado) {
        header("Location: ?modulo=ativos&acao=listagem");
    } else {
        header("Location: ?modulo=auth&acao=login");
    }
    exit;
}

// [ARQUITETURA] Middleware de controle de acesso centralizado.
// Intercepta acessos não autorizados a módulos protegidos antes da instanciação de qualquer Controller.
if (!$usuario_logado && $modulo !== 'auth') {
    $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Por favor, faça login para acessar o sistema.'];
    header("Location: ?modulo=auth&acao=login");
    exit;
}

// [REGRA DE NEGÓCIO] Evita loops de redirecionamento ou fluxo redundante para usuários já autenticados 
// que tentam acessar a tela de login intencionalmente ou acidentalmente.
if ($usuario_logado && $modulo === 'auth' && $acao === 'login') {
    header("Location: ?modulo=ativos&acao=listagem");
    exit;
}

// [SEGURANÇA] Bloqueia acessos diretos via GET para proteger operações críticas de exclusão,
// garantindo que mutações ocorram exclusivamente por submissões de formulários validados.
if ($modulo === 'ativos' && $acao === 'excluir' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Operação de exclusão inválida. Apenas requisições POST são permitidas.'];
    header("Location: ?modulo=ativos&acao=listagem");
    exit;
}

// [SEGURANÇA] Validador CSRF global para interceptar qualquer mutação de estado em nível arquitetural.
// Falhas na correspondência resultam em recusa imediata do processamento, protegendo o sistema contra requisições forjadas.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token_post = $_POST['csrf_token'] ?? '';
    if (empty($token_post) || $token_post !== ($_SESSION['csrf_token'] ?? '')) {
        http_response_code(403);
        $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Erro de segurança: Token CSRF inválido ou expirado.'];
        die("Acesso negado: Token CSRF inválido.");
    }
}

// [ARQUITETURA] Início da fase de Despacho (Dispatch). Mapeia os parâmetros da URL validados
// para suas respectivas instâncias concretas e invoca a ação requisitada de forma dinâmica.
$modulo_mapeado = $modulo === 'ativos' ? 'ativo' : $modulo;
$controllerClass = ucfirst($modulo_mapeado) . 'Controller';

if (class_exists($controllerClass)) {
    $controller = new $controllerClass();
    if (method_exists($controller, $acao)) {
        
        $resultado = $controller->$acao();

        // [ARQUITETURA] O Front Controller finaliza o ciclo renderizando a View ou interrompendo a execução,
        // garantindo que o Controller permaneça desacoplado da inclusão direta de arquivos HTML.
        if (is_array($resultado) && isset($resultado['view'])) {
            
            if (isset($resultado['dados']) && is_array($resultado['dados'])) {
                extract($resultado['dados']);
            }

            // [ARQUITETURA] Gerenciamento efêmero do estado das variáveis de sessão.
            // As variáveis como $flash e $old_input são disponibilizadas no escopo global da View e consumidas da sessão, 
            // garantindo que as notificações e inputs persistam por apenas um ciclo (flash-data) e não poluam a próxima requisição.
            $flash = $_SESSION['flash'] ?? null;
            unset($_SESSION['flash']);

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
