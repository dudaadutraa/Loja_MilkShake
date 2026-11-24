<?php

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/Venda.php';
require_once __DIR__ . '/../models/Pessoa.php';
require_once __DIR__ . '/../models/Variacao.php';
require_once __DIR__ . '/../models/NotaFiscal.php';

/** Vendas (tabelas venda, venda_item) com baixa automática no estoque. */
class VendaController extends BaseController
{
    protected string $rota = 'venda';
    protected array $acoesAdmin = ['cancelar'];   // só o administrador cancela vendas
    private Venda $vendas;

    public function __construct()
    {
        parent::__construct();
        $this->vendas = new Venda();
    }

    public function index(): void
    {
        [$de, $ate] = $this->periodo();
        $vendas = $this->vendas->listarPorPeriodo($de, $ate);
        $validas = array_filter($vendas, function ($v) { return $v['status'] !== 'cancelada'; });

        $this->tela('vendas', [
            'vendas' => $vendas,
            'qtd'    => count($validas),
            'total'  => array_sum(array_column($validas, 'valor_total')),
            'de'     => $de,
            'ate'    => $ate,
        ]);
    }

    public function nova(): void
    {
        $this->tela('venda_form', [
            'clientes'  => (new Pessoa('cliente'))->listarParaSelect(),
            'variacoes' => (new Variacao())->listarParaSelect(),
        ]);
    }

    public function salvar(): void
    {
        $this->exigirPost();

        $clienteId = (int) ($_POST['cliente_id'] ?? 0) ?: null;

        try {
            $itens = $this->itensDoPost(false);
            $venda = $this->vendas->registrar((int) $_SESSION['usuario_id'], $clienteId, $itens);

            sn_flash('ok', 'Venda #' . $venda['id'] . ' registrada: ' . moeda($venda['total']) . '.');
            sn_irpara(['controller' => 'venda', 'action' => 'ver', 'id' => $venda['id']]);
        } catch (InvalidArgumentException | RuntimeException | PDOException $e) {
            sn_flash('erro', $e instanceof PDOException ? 'Não foi possível registrar a venda. Tente novamente.' : $e->getMessage());
            sn_irpara(['controller' => 'venda', 'action' => 'nova']);
        }
    }

    public function ver(): void
    {
        $venda = $this->vendas->buscarCompleta($this->idGet());

        if (!$venda) {
            sn_flash('erro', 'Venda não encontrada.');
            $this->voltar();
        }

        $this->tela('venda_ver', [
            'venda' => $venda,
            'itens' => $this->vendas->itens((int) $venda['id']),
            'nota'  => (new NotaFiscal())->buscarPorVenda((int) $venda['id']),
        ]);
    }

    /** Emite a nota fiscal (simulada) de uma venda finalizada. */
    public function emitirNota(): void
    {
        $this->exigirPost();
        $id = $this->idPost();

        try {
            $numero = (new NotaFiscal())->emitirParaVenda($id);
            sn_flash('ok', 'Nota fiscal nº ' . $numero . ' emitida.');
        } catch (RuntimeException | PDOException $e) {
            sn_flash('erro', $e instanceof RuntimeException ? $e->getMessage() : 'Não foi possível emitir a nota fiscal.');
        }

        sn_irpara(['controller' => 'venda', 'action' => 'ver', 'id' => $id]);
    }

    /** Página de impressão da nota fiscal da venda. */
    public function nota(): void
    {
        $venda = $this->vendas->buscarCompleta($this->idGet());
        $nota  = $venda ? (new NotaFiscal())->buscarPorVenda((int) $venda['id']) : null;

        if (!$nota) {
            sn_flash('erro', 'Esta venda ainda não tem nota fiscal.');
            sn_irpara($venda ? ['controller' => 'venda', 'action' => 'ver', 'id' => $venda['id']] : ['controller' => 'venda']);
        }

        sn_view('nota_fiscal_venda', [
            'venda' => $venda,
            'nota'  => $nota,
            'itens' => $this->vendas->itens((int) $venda['id']),
        ]);
    }

    /** Marca um pedido do site (status "aberta") como finalizado/entregue. */
    public function finalizar(): void
    {
        $this->exigirPost();
        $id = $this->idPost();
        $this->vendas->finalizar($id);
        sn_flash('ok', 'Pedido #' . $id . ' marcado como finalizado.');
        sn_irpara(['controller' => 'venda', 'action' => 'ver', 'id' => $id]);
    }

    /** Pedido do site: marca o pagamento (Pix/dinheiro) como recebido. */
    public function confirmarPagamento(): void
    {
        $this->exigirPost();
        $id = $this->idPost();
        $this->vendas->confirmarPagamento($id);
        sn_flash('ok', 'Pagamento do pedido #' . $id . ' confirmado.');
        sn_irpara(['controller' => 'venda', 'action' => 'ver', 'id' => $id]);
    }

    /** Cancela a venda e devolve os itens ao estoque (somente admin). A venda continua no histórico como "cancelada". */
    public function cancelar(): void
    {
        $this->exigirPost();
        $id = $this->idPost();

        try {
            $this->vendas->cancelar($id);
            sn_flash('ok', 'Venda #' . $id . ' cancelada e itens devolvidos ao estoque.');
        } catch (RuntimeException $e) {
            sn_flash('erro', $e->getMessage());
        } catch (PDOException $e) {
            sn_flash('erro', 'Não foi possível cancelar a venda.');
        }

        sn_irpara(['controller' => 'venda', 'action' => 'ver', 'id' => $id]);
    }
}
