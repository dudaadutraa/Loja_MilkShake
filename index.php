<?php
/**
 * SHAKE NATURE - INDEX.PHP (roteador)
 *
 * Endereços:  index.php?controller=NOME&action=METODO
 *   index.php                                  -> login (controller=auth&action=form)
 *   index.php?controller=dashboard&action=index -> dashboard
 *   index.php?controller=auth&action=logout     -> sair
 * Acrescente &debug=1 no endereço para ver a mensagem de erro detalhada.
 */

ob_start();   // garante que os redirecionamentos (header Location) sempre funcionem
session_set_cookie_params(['path' => '/', 'httponly' => true, 'samesite' => 'Lax']);
session_start();
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

define('RAIZ', __DIR__);
date_default_timezone_set('America/Sao_Paulo');   // datas e horas no horário de Brasília

require_once __DIR__ . '/helpers/funcoes.php';   // funções de apoio (URLs, sessão, views...)
require_once __DIR__ . '/config/db.php';         // conexão com o banco

try {
    Database::getConnection();
} catch (Throwable $e) {
    sn_erro(500, 'Banco de dados indisponível', 'Não foi possível conectar ao banco de dados. Tente novamente em instantes.', DEBUG ? $e->getMessage() : '');
}


// Roteamento simples via GET
// (padrão é o login do vendedor; depois do login vai para controller=dashboard&action=index)
$controller = $_GET['controller'] ?? 'auth';
$action = $_GET['action'] ?? ($controller === 'auth' ? 'form' : 'index');

// Endereços antigos continuam funcionando: ?sair=1 e ?pagina=dashboard
if (isset($_GET['sair'])) {
    $controller = 'auth';
    $action = 'logout';
} elseif (!isset($_GET['controller']) && isset($_GET['pagina'])) {
    $apelidos = ['login' => 'auth', 'produtos' => 'produto', 'variacoes' => 'variacao', 'vendas' => 'venda', 'entradas' => 'entrada',
                 'fornecedores' => 'fornecedor', 'categorias' => 'categoria', 'clientes' => 'cliente',
                 'relatorios' => 'relatorio', 'usuarios' => 'usuario'];
    $controller = $apelidos[$_GET['pagina']] ?? (string) $_GET['pagina'];
    $action = $controller === 'auth' ? 'form' : 'index';
}

// Só letras e números (evita carregar arquivo de caminho arbitrário)
if (!preg_match('/^[a-zA-Z][a-zA-Z0-9]*$/', (string) $controller) || !preg_match('/^[a-zA-Z][a-zA-Z0-9]*$/', (string) $action)) {
    sn_erro(400, 'Requisição inválida', 'O endereço digitado não é válido.');
}

$controller = strtolower($controller);


try {
    // Carregar controller
    switch ($controller) {
        case 'auth':
            require_once __DIR__ . '/controllers/AuthController.php';
            $c = new AuthController();
            break;

        case 'dashboard':
            require_once __DIR__ . '/controllers/DashboardController.php';
            $c = new DashboardController();
            break;


        // CRUD PRODUTOS E VENDAS
        case 'produto':
            require_once __DIR__ . '/controllers/ProdutoController.php';
            $c = new ProdutoController();
            break;

        case 'variacao':   // tamanhos e preços de cada produto
            require_once __DIR__ . '/controllers/VariacaoController.php';
            $c = new VariacaoController();
            break;

        case 'entrada':
            require_once __DIR__ . '/controllers/EntradaController.php';
            $c = new EntradaController();
            break;

        case 'venda':
            require_once __DIR__ . '/controllers/VendaController.php';
            $c = new VendaController();
            break;

        case 'estoque':
            require_once __DIR__ . '/controllers/EstoqueController.php';
            $c = new EstoqueController();
            break;

        case 'relatorio':
            require_once __DIR__ . '/controllers/RelatorioController.php';
            $c = new RelatorioController();
            break;

        case 'usuario':
            require_once __DIR__ . '/controllers/UsuarioController.php';
            $c = new UsuarioController();
            break;

        case 'categoria':
            require_once __DIR__ . '/controllers/CategoriaController.php';
            $c = new CategoriaController();
            break;

        case 'fornecedor':
            require_once __DIR__ . '/controllers/FornecedorController.php';
            $c = new FornecedorController();
            break;

        case 'cliente':
            require_once __DIR__ . '/controllers/ClienteController.php';
            $c = new ClienteController();
            break;


        // ==================== LOJA VIRTUAL (CLIENTE) ====================

        case 'loja':       // cardápio e página do produto
        case 'carrinho':   // carrinho de compras
        case 'pedido':     // entrega, pagamento e confirmação
        case 'conta':      // login, cadastro e área do cliente do site
            $classe = ucfirst($controller) . 'Controller';
            require_once __DIR__ . '/controllers/' . $classe . '.php';
            $c = new $classe();
            break;


        // ==================== API ====================

        case 'cep':        // busca de endereço pelo CEP (ViaCEP), responde em JSON
            require_once __DIR__ . '/controllers/CepController.php';
            $c = new CepController();
            break;


        // Caminho padrão caso o controller não exista
        default:
            sn_erro(404, 'Página não encontrada', "A página '{$controller}' não existe.");
    }


    // Executar ação (só métodos públicos; protegidos/privados e __construct não são chamáveis pela URL)
    if (strpos($action, '__') === 0 || !is_callable([$c, $action])) {
        sn_erro(404, 'Ação não encontrada', "A ação '{$action}' não existe em '{$controller}'.");
    }

    $c->$action();

    ob_end_flush();

} catch (Throwable $e) {
    error_log('[Shake Nature] ' . $e->getMessage() . ' em ' . $e->getFile() . ':' . $e->getLine());

    sn_erro(
        500,
        'Erro interno',
        'Algo deu errado ao abrir esta página. Tente novamente.',
        DEBUG ? $e->getMessage() . "\n" . $e->getFile() . ':' . $e->getLine() : ''
    );
}
