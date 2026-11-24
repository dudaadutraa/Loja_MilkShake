<?php
/**
 * SHAKE NATURE - FUNÇÕES DE APOIO (helpers/funcoes.php)
 * Carregado no topo do index.php (antes de qualquer controller).
 */

if (!defined('RAIZ')) {
    define('RAIZ', dirname(__DIR__));
}

if (!defined('DEBUG')) {
    // ?debug=1 mostra o erro detalhado, mas só no computador local (XAMPP), nunca no site publicado
    $local = in_array(explode(':', $_SERVER['HTTP_HOST'] ?? '')[0], ['localhost', '127.0.0.1'], true);
    define('DEBUG', $local && isset($_GET['debug']));
}

if (!defined('BASE_URL')) {
    $dir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
    define('BASE_URL', $dir . '/');
}

ini_set('display_errors', DEBUG ? '1' : '0');
error_reporting(E_ALL);

/* ================= FUNÇÕES GLOBAIS ================= */

function h($valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

function moeda($valor): string
{
    return 'R$ ' . number_format((float) $valor, 2, ',', '.');
}

function data_br($data): string
{
    if (empty($data)) {
        return '-';
    }
    $t = strtotime((string) $data);
    return $t ? date(strlen((string) $data) > 10 ? 'd/m/Y H:i' : 'd/m/Y', $t) : '-';
}

function sn_url(array $parametros = []): string
{
    return BASE_URL . 'index.php' . ($parametros ? '?' . http_build_query($parametros) : '');
}

function sn_asset(string $caminho): string
{
    $caminho = ltrim($caminho, '/');
    $arquivo = __DIR__ . '/../public/' . $caminho;
    $versao  = is_file($arquivo) ? '?v=' . filemtime($arquivo) : '';
    return BASE_URL . 'public/' . $caminho . $versao;
}

function sn_logado(): bool
{
    return !empty($_SESSION['usuario_id']);
}

function sn_param(string $nome): string
{
    return strtolower(preg_replace('/[^a-zA-Z0-9_]/', '', (string) ($_GET[$nome] ?? '')));
}

function sn_irpara(array $parametros = []): void
{
    header('Location: ' . sn_url($parametros));
    exit;
}

function sn_flash(string $tipo, string $msg): void
{
    $_SESSION['flash'] = ['tipo' => $tipo, 'msg' => $msg];
}

function sn_pegar_flash(): ?array
{
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

function sn_csrf(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function sn_csrf_ok(): bool
{
    $token = (string) ($_POST['csrf'] ?? '');
    return !empty($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $token);
}

/** Itens do menu lateral: chave => [rótulo, link, ícone SVG]. */
/** Áreas do painel que só o administrador acessa (o vendedor fica com Dashboard, Vendas, Entradas, Clientes e Estoque). */
function sn_so_admin(): array
{
    return ['produto', 'variacao', 'categoria', 'fornecedor', 'relatorio', 'usuario'];
}

function sn_eh_admin(): bool
{
    return ($_SESSION['perfil'] ?? '') === 'admin';
}

function sn_menu(): array
{
    $menu = [
        'dashboard'  => ['Dashboard',    sn_url(['controller' => 'dashboard', 'action' => 'index']),
                         '<path d="M3 10.5 12 3l9 7.5V20a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1z"/>'],
        'produto'    => ['Produtos',     sn_url(['controller' => 'produto', 'action' => 'index']),
                         '<path d="M12 3 3 7.5v9L12 21l9-4.5v-9z"/><path d="M3 7.5 12 12l9-4.5M12 12v9"/>'],
        'variacao'   => ['Variações',    sn_url(['controller' => 'variacao', 'action' => 'index']),
                         '<rect x="3" y="4" width="18" height="5" rx="1.5"/><rect x="3" y="11" width="13" height="4" rx="1.5"/><rect x="3" y="17" width="8" height="3" rx="1.5"/>'],
        'categoria'  => ['Categorias',   sn_url(['controller' => 'categoria', 'action' => 'index']),
                         '<path d="M3 12V4a1 1 0 0 1 1-1h8l9 9-9 9z"/><circle cx="7.5" cy="7.5" r="1.5"/>'],
        'venda'      => ['Vendas',       sn_url(['controller' => 'venda', 'action' => 'index']),
                         '<path d="M5 20v-6M12 20V6M19 20v-9"/>'],
        'entrada'    => ['Entradas',     sn_url(['controller' => 'entrada', 'action' => 'index']),
                         '<path d="M12 3v12M7 10l5 5 5-5"/><path d="M4 17v3h16v-3"/>'],
        'estoque'    => ['Estoque',      sn_url(['controller' => 'estoque', 'action' => 'index']),
                         '<path d="M12 3 3 7.5v9L12 21l9-4.5v-9z"/><path d="M3 7.5 12 12l9-4.5"/>'],
        'fornecedor' => ['Fornecedores', sn_url(['controller' => 'fornecedor', 'action' => 'index']),
                         '<path d="M2 6h12v10H2zM14 9h4l3 3v4h-7"/><circle cx="7" cy="18" r="1.8"/><circle cx="17" cy="18" r="1.8"/>'],
        'cliente'    => ['Clientes',     sn_url(['controller' => 'cliente', 'action' => 'index']),
                         '<circle cx="12" cy="8" r="4"/><path d="M4 21c0-4.4 3.6-7 8-7s8 2.6 8 7"/>'],
        'relatorio'  => ['Relatórios',   sn_url(['controller' => 'relatorio', 'action' => 'index']),
                         '<path d="M6 3h9l4 4v14H6z"/><path d="M9 17v-3M12 17v-5M15 17v-2"/>'],
    ];

    $menu['usuario'] = ['Usuários', sn_url(['controller' => 'usuario', 'action' => 'index']),
        '<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20c0-3.6 2.9-6 6.5-6s6.5 2.4 6.5 6"/><path d="M16 4.5a3.5 3.5 0 0 1 0 7M18 14c2.2.6 3.5 2.6 3.5 6"/>'];

    // O vendedor só vê no menu as áreas que ele pode abrir
    if (!sn_eh_admin()) {
        $menu = array_diff_key($menu, array_flip(sn_so_admin()));
    }

    // Abre a loja virtual (site dos clientes) em outra aba
    $menu['site'] = ['Visitar site', sn_url(['controller' => 'loja', 'action' => 'index']),
        '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c2.5 2.7 3.8 5.7 3.8 9s-1.3 6.3-3.8 9c-2.5-2.7-3.8-5.7-3.8-9S9.5 5.7 12 3z"/>'];

    return $menu;
}

/** Endereços fixos usados em vários lugares. */
function sn_url_login(): string
{
    return sn_url(['controller' => 'auth', 'action' => 'form']);
}

function sn_url_sair(): string
{
    return sn_url(['controller' => 'auth', 'action' => 'logout']);
}

function sn_url_dashboard(): string
{
    return sn_url(['controller' => 'dashboard', 'action' => 'index']);
}

/** Bloqueia a página se ninguém estiver logado (usado nos controllers). */
function sn_exigir_login(): void
{
    if (!sn_logado()) {
        sn_irpara(['controller' => 'auth', 'action' => 'form']);
    }
}

/** Carrega uma view da pasta /views passando variáveis. */
function sn_view(string $nome, array $dados = []): void
{
    $arquivo = RAIZ . '/views/' . $nome . '.php';

    if (!is_file($arquivo)) {
        throw new RuntimeException("View '{$nome}.php' não encontrada.");
    }

    extract($dados, EXTR_SKIP);
    require $arquivo;
}

function sn_erro(int $codigo, string $titulo, string $msg, string $detalhe = ''): void
{
    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    http_response_code($codigo);

    echo '<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8">'
        . '<meta name="viewport" content="width=device-width, initial-scale=1">'
        . '<title>' . h($titulo) . ' - Shake Nature</title><style>'
        . 'body{font-family:Arial,sans-serif;background:#F8EEDC;color:#A07F63;display:flex;min-height:100vh;align-items:center;justify-content:center;margin:0}'
        . '.c{background:#FAF3E6;padding:32px 40px;border-radius:28px;max-width:560px;text-align:center;box-shadow:0 20px 60px rgba(214,170,110,.25)}'
        . 'h1{color:#EE8A45}small{display:block;margin-top:12px;word-break:break-word;text-align:left;white-space:pre-wrap}'
        . 'a{display:inline-block;margin-top:16px;padding:12px 28px;border-radius:999px;background:#F2BA68;color:#fff;text-decoration:none;font-weight:bold}'
        . '</style></head><body><div class="c">'
        . '<h1>' . $codigo . ' - ' . h($titulo) . '</h1><p>' . h($msg) . '</p>'
        . ($detalhe !== '' ? '<small>' . h($detalhe) . '</small>' : '')
        . '<a href="' . h(sn_url()) . '">Voltar</a></div></body></html>';
    exit;
}

function sn_sair(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], (bool) $p['secure'], (bool) $p['httponly']);
    }

    session_destroy();
    sn_irpara(['controller' => 'auth', 'action' => 'form']);
}


// Ilustrações dos milkshakes e endereço da imagem de cada produto
require_once __DIR__ . '/ilustracao_produto.php';
