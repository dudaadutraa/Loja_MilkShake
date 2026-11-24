<?php

require_once __DIR__ . '/../models/EstruturaBanco.php';
require_once __DIR__ . '/../models/Usuario.php';

/**
 * Base dos controllers do PAINEL ADMINISTRATIVO.
 * Exige login do funcionário e reúne o que todos os controllers do painel usam:
 * mostrar a view, ler o formulário, validar o envio (CSRF) e redirecionar.
 * O acesso ao banco fica nos Models (pasta models/).
 * (A loja virtual tem a sua própria base: LojaBaseController.)
 */
abstract class BaseController
{
    /** Nome usado na URL (?controller=...) para os redirecionamentos. */
    protected string $rota = '';

    /** Ações deste controller que só o administrador pode executar (ex.: ['excluir']). */
    protected array $acoesAdmin = [];

    /**
     * Todas as páginas do painel exigem login.
     * As permissões são conferidas aqui, no servidor (esconder o botão na tela não basta):
     *  - áreas de sn_so_admin() e ações de $acoesAdmin só abrem para administradores.
     */
    public function __construct()
    {
        sn_exigir_login();
        $this->conferirUsuario();
        (new EstruturaBanco())->atualizar();   // cria colunas/tabelas novas se o SQL de ajustes não foi rodado

        $acao = (string) ($_GET['action'] ?? 'index');
        if (in_array($this->rota, sn_so_admin(), true) || in_array($acao, $this->acoesAdmin, true)) {
            $this->exigirAdmin();
        }
    }

    /**
     * Relê o usuário logado no banco a cada página: se ele foi desativado ou excluído,
     * a sessão é encerrada; se o perfil mudou, a mudança vale na hora.
     */
    private function conferirUsuario(): void
    {
        $user = (new Usuario())->buscar((int) $_SESSION['usuario_id']);

        if (!$user || (int) $user['ativo'] !== 1) {
            sn_sair();
        }

        $_SESSION['perfil'] = $user['perfil'];
        $_SESSION['nome']   = $user['nome'];
    }

    /** Mostra uma view passando as variáveis. */
    protected function tela(string $view, array $dados = []): void
    {
        $dados['csrf'] = sn_csrf();
        sn_view($view, $dados);
    }

    /** Lê uma data AAAA-MM-DD da URL. */
    protected function dataGet(string $campo, string $padrao): string
    {
        $v = (string) ($_GET[$campo] ?? '');
        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $v) ? $v : $padrao;
    }

    /** Período dos filtros (?de=...&ate=...). Padrão: do dia 1º do mês até hoje; datas invertidas são trocadas. */
    protected function periodo(): array
    {
        $de  = $this->dataGet('de', date('Y-m-01'));
        $ate = $this->dataGet('ate', date('Y-m-d'));
        return $de <= $ate ? [$de, $ate] : [$ate, $de];
    }

    /** Lê as linhas de itens enviadas pelo formulário (variacao_id[], quantidade[], custo[]). */
    protected function itensDoPost(bool $comCusto): array
    {
        $ids   = $_POST['variacao_id'] ?? [];
        $qtds  = $_POST['quantidade'] ?? [];
        $custos = $_POST['custo'] ?? [];
        $itens = [];

        foreach ((array) $ids as $i => $vid) {
            $vid = (int) $vid;
            $qtd = (int) ($qtds[$i] ?? 0);
            if ($vid <= 0 || $qtd <= 0) {
                continue;
            }
            $custo = $comCusto ? $this->dinheiro((string) ($custos[$i] ?? '')) : null;
            if ($comCusto && $custo === null) {
                throw new InvalidArgumentException('Informe o custo de todos os itens.');
            }
            if (isset($itens[$vid]) && !$comCusto) {
                $itens[$vid]['quantidade'] += $qtd;
                continue;
            }
            $itens[$comCusto ? $vid . '-' . $i : $vid] = ['variacao_id' => $vid, 'quantidade' => $qtd, 'custo' => $custo];
        }

        if (!$itens) {
            throw new InvalidArgumentException('Adicione pelo menos um item com quantidade.');
        }

        return array_values($itens);
    }

    /** Aceita só POST com token CSRF válido. */
    protected function exigirPost(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            sn_erro(405, 'Método não permitido', 'Esta ação só aceita envio de formulário.');
        }

        if (!sn_csrf_ok()) {
            sn_flash('erro', 'Sessão expirada. Tente novamente.');
            $this->voltar();
        }
    }

    protected function exigirAdmin(): void
    {
        if (!sn_eh_admin()) {
            sn_flash('erro', 'Apenas administradores podem acessar esta área.');
            sn_irpara(['controller' => 'dashboard', 'action' => 'index']);
        }
    }

    protected function voltar(array $extra = []): void
    {
        sn_irpara(array_merge(['controller' => $this->rota, 'action' => 'index'], $extra));
    }

    protected function texto(string $campo): string
    {
        return trim((string) ($_POST[$campo] ?? ''));
    }

    protected function idGet(): int
    {
        return max(0, (int) ($_GET['id'] ?? 0));
    }

    protected function idPost(): int
    {
        return max(0, (int) ($_POST['id'] ?? 0));
    }

    /** Converte "1.234,50" ou "12.5" em float; null se inválido. */
    protected function dinheiro(string $valor): ?float
    {
        $valor = trim(str_replace(['R$', ' '], '', $valor));
        if (strpos($valor, ',') !== false) {
            $valor = str_replace(['.', ','], ['', '.'], $valor);
        }
        return (is_numeric($valor) && (float) $valor >= 0) ? round((float) $valor, 2) : null;
    }

    /** Mensagem amigável para erro de chave estrangeira. */
    protected function emUso(PDOException $e): bool
    {
        return ($e->errorInfo[1] ?? 0) == 1451 || (string) $e->getCode() === '23000';
    }
}
