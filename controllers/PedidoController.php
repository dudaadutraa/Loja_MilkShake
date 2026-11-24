<?php

require_once __DIR__ . '/LojaBaseController.php';
require_once __DIR__ . '/../models/PedidoOnline.php';

/**
 * Fechamento do pedido no site (exige cliente logado).
 *   pedido                      -> 1. entrega (retirar ou receber)
 *   pedido&action=pagamento     -> 2. forma de pagamento
 *   pedido&action=finalizar     -> grava o pedido (POST)
 *   pedido&action=confirmado    -> 3. pedido recebido
 *
 * O pedido vira uma VENDA com status "aberta" no painel, ligada ao cliente,
 * com o estoque já baixado. Entrega e pagamento ficam na tabela pedido_online.
 *
 * PAGAMENTO: o cartão é SIMULADO (valida número, validade e CVV, mas não cobra).
 * Para cobrar de verdade é preciso integrar um gateway (Mercado Pago, PagSeguro...).
 */
class PedidoController extends LojaBaseController
{
    /** Chave Pix da loja mostrada ao cliente. Deixe '' para avisar que a loja envia pelo WhatsApp. */
    private const CHAVE_PIX = '';

    /* ---------- 1. Entrega ---------- */

    public function index(): void
    {
        $cliente = $this->exigirCliente(['controller' => 'pedido']);
        $itens = $this->carrinho->itens();
        if (!$itens) {
            sn_irpara(['controller' => 'carrinho']);
        }

        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
            $this->exigirPost(['controller' => 'pedido']);

            $entrega  = ($_POST['entrega'] ?? '') === 'entrega' ? 'entrega' : 'retirada';
            $endereco = trim((string) ($_POST['endereco'] ?? ''));

            if ($entrega === 'entrega' && mb_strlen($endereco) < 8) {
                sn_flash('erro', 'Informe o endereço completo para a entrega.');
                $_SESSION['checkout'] = ['entrega' => $entrega, 'endereco' => $endereco];
                sn_irpara(['controller' => 'pedido']);
            }

            $_SESSION['checkout'] = ['entrega' => $entrega, 'endereco' => $entrega === 'entrega' ? mb_substr($endereco, 0, 240) : ''];
            sn_irpara(['controller' => 'pedido', 'action' => 'pagamento']);
        }

        $this->tela('checkout_entrega', [
            'itens'    => $itens,
            'total'    => Carrinho::total($itens),
            'checkout' => $_SESSION['checkout'] ?? ['entrega' => 'retirada', 'endereco' => (string) $cliente['endereco']],
        ]);
    }

    /* ---------- 2. Pagamento ---------- */

    public function pagamento(): void
    {
        $this->exigirCliente(['controller' => 'pedido']);
        $itens = $this->carrinho->itens();
        if (!$itens) {
            sn_irpara(['controller' => 'carrinho']);
        }
        if (empty($_SESSION['checkout'])) {
            sn_irpara(['controller' => 'pedido']);
        }

        $this->tela('checkout_pagamento', [
            'itens'    => $itens,
            'total'    => Carrinho::total($itens),
            'checkout' => $_SESSION['checkout'],
            'metodo'   => $_SESSION['checkout_metodo'] ?? 'pix',
        ]);
    }

    public function finalizar(): void
    {
        $cliente = $this->exigirCliente(['controller' => 'pedido', 'action' => 'pagamento']);
        $this->exigirPost(['controller' => 'pedido', 'action' => 'pagamento']);

        $checkout = $_SESSION['checkout'] ?? null;
        $itens = $this->carrinho->itens();
        if (!$checkout || !$itens) {
            sn_irpara(['controller' => 'carrinho']);
        }

        $metodo = (string) ($_POST['metodo'] ?? '');
        if (!in_array($metodo, ['pix', 'cartao', 'dinheiro'], true)) {
            $metodo = 'pix';
        }
        $_SESSION['checkout_metodo'] = $metodo;

        $status = ['pix' => 'pendente', 'cartao' => 'aprovado', 'dinheiro' => 'na_entrega'][$metodo];
        $final  = null;

        if ($metodo === 'cartao') {
            $erro = $this->validarCartao($_POST);
            if ($erro) {
                sn_flash('erro', $erro);
                sn_irpara(['controller' => 'pedido', 'action' => 'pagamento']);
            }
            $final = (string) $_POST['cartao_final'];
        }

        $pedidos = new PedidoOnline();

        // Pedidos do site ficam no nome do primeiro administrador ativo
        $usuarioId = $pedidos->usuarioResponsavel();
        if (!$usuarioId) {
            sn_flash('erro', 'A loja não está recebendo pedidos no momento.');
            sn_irpara(['controller' => 'carrinho']);
        }

        try {
            $vendaId = $pedidos->registrar($usuarioId, $cliente, $itens, $checkout, $metodo, $status, $final);
        } catch (Throwable $e) {
            error_log('[loja:pedido] ' . $e->getMessage());
            sn_flash('erro', $e instanceof RuntimeException ? $e->getMessage() : 'Não foi possível concluir o pedido. Tente novamente.');
            sn_irpara(['controller' => 'carrinho']);
        }

        $this->carrinho->esvaziar();
        unset($_SESSION['checkout'], $_SESSION['checkout_metodo']);
        $_SESSION['ultimo_pedido'] = $vendaId;

        sn_irpara(['controller' => 'pedido', 'action' => 'confirmado']);
    }

    /* ---------- 3. Confirmação ---------- */

    public function confirmado(): void
    {
        $cliente = $this->exigirCliente(['controller' => 'conta']);
        $id = (int) ($_SESSION['ultimo_pedido'] ?? 0);

        $pedido = $id ? (new PedidoOnline())->buscarParaConfirmacao($id, (int) $cliente['id']) : null;

        if (!$pedido) {
            sn_irpara(['controller' => 'conta']);
        }

        $this->tela('confirmado', ['pedido' => $pedido, 'chavePix' => self::CHAVE_PIX]);
    }

    /* ---------- apoio ---------- */

    /**
     * Validação do cartão (simulada: não há cobrança real).
     * O número completo e o CVV são conferidos no navegador e NÃO chegam ao servidor;
     * aqui chegam só os 4 últimos dígitos e a validade. Devolve a mensagem de erro ou null.
     */
    private function validarCartao(array $p): ?string
    {
        $final = (string) ($p['cartao_final'] ?? '');
        $val   = trim((string) ($p['cartao_validade'] ?? ''));

        if (($p['cartao_ok'] ?? '') !== '1' || !preg_match('/^\d{4}$/', $final)) {
            return 'Confira os dados do cartão e tente de novo.';
        }
        if (!preg_match('/^(0[1-9]|1[0-2])\/(\d{2})$/', $val, $m)) {
            return 'Validade inválida. Use MM/AA.';
        }
        if (mktime(23, 59, 59, (int) $m[1] + 1, 0, 2000 + (int) $m[2]) < time()) {
            return 'Este cartão está vencido.';
        }
        return null;
    }
}
