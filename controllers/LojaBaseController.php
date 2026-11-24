<?php

require_once __DIR__ . '/../models/EstruturaBanco.php';
require_once __DIR__ . '/../models/Vitrine.php';
require_once __DIR__ . '/../models/Carrinho.php';
require_once __DIR__ . '/../models/ClienteSite.php';

/**
 * Base dos controllers da LOJA VIRTUAL (site dos clientes):
 * LojaController, CarrinhoController, PedidoController e ContaController.
 *
 * Diferente do BaseController do painel: NÃO exige login de funcionário.
 * Cuida do que todas as páginas da loja usam: o carrinho, o cliente logado
 * e os dados do cabeçalho (categorias e quantidade no carrinho).
 *
 * Sessão do site:
 *   $_SESSION['carrinho']   = [variacao_id => quantidade]   (Model Carrinho)
 *   $_SESSION['cliente_id'] = id do cliente logado (tabela cliente)
 */
abstract class LojaBaseController
{
    protected Carrinho $carrinho;
    protected ClienteSite $clientes;

    public function __construct()
    {
        (new EstruturaBanco())->atualizar();
        $this->carrinho = new Carrinho();
        $this->clientes = new ClienteSite();
    }

    /** Mostra uma view da pasta views/loja/ com os dados do cabeçalho. */
    protected function tela(string $view, array $dados = []): void
    {
        $dados['csrf']           = sn_csrf();
        $dados['qtdCarrinho']    = $this->carrinho->quantidadeTotal();
        $dados['flash']          = sn_pegar_flash();
        $dados['cliente']        = $this->cliente();
        $dados['categoriasMenu'] = (new Vitrine())->categorias();
        sn_view('loja/' . $view, $dados);
    }

    /** Aceita só POST com token CSRF válido; senão volta para $voltar. */
    protected function exigirPost(array $voltar): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' || !sn_csrf_ok()) {
            sn_flash('erro', 'Sua sessão expirou. Tente novamente.');
            sn_irpara($voltar);
        }
    }

    /* ================= CLIENTE LOGADO ================= */

    private ?array $clienteCache = null;

    /** Cliente logado no site (ou null). */
    protected function cliente(): ?array
    {
        $id = (int) ($_SESSION['cliente_id'] ?? 0);
        if ($id <= 0) {
            return null;
        }
        if ($this->clienteCache === null || (int) $this->clienteCache['id'] !== $id) {
            $this->clienteCache = $this->clientes->buscarComConta($id);
            if (!$this->clienteCache) {
                unset($_SESSION['cliente_id']);
            }
        }
        return $this->clienteCache;
    }

    /** Manda para o login se não houver cliente logado; depois do login volta para $voltar. */
    protected function exigirCliente(array $voltar): array
    {
        $c = $this->cliente();
        if (!$c) {
            $_SESSION['loja_voltar'] = $voltar;
            sn_flash('erro', 'Entre na sua conta para continuar.');
            sn_irpara(['controller' => 'conta', 'action' => 'entrar']);
        }
        return $c;
    }
}
