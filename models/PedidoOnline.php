<?php

require_once __DIR__ . '/Model.php';
require_once __DIR__ . '/Estoque.php';

/**
 * Pedido feito pela LOJA VIRTUAL.
 * O pedido vira uma venda com status "aberta" (tabelas venda e venda_item), com o
 * estoque já baixado; entrega e pagamento ficam na tabela pedido_online.
 */
class PedidoOnline extends Model
{
    /** Funcionário em nome de quem os pedidos do site são registrados (o primeiro administrador ativo). */
    public function usuarioResponsavel(): ?int
    {
        $u = $this->um("SELECT id FROM usuario WHERE ativo = 1 ORDER BY perfil = 'admin' DESC, id LIMIT 1");
        return $u ? (int) $u['id'] : null;
    }

    /**
     * Grava o pedido inteiro numa transação e devolve o número (id da venda).
     * $itens:    itens do carrinho (Carrinho::itens)
     * $cliente:  cliente logado (ClienteSite::buscarComConta)
     * $checkout: ['entrega' => 'retirada'|'entrega', 'endereco' => '...']
     * Lança RuntimeException se algum item esgotou enquanto o cliente comprava.
     */
    public function registrar(
        int $usuarioId,
        array $cliente,
        array $itens,
        array $checkout,
        string $metodo,
        string $statusPagamento,
        ?string $cartaoFinal
    ): int {
        return $this->emTransacao(function () use ($usuarioId, $cliente, $itens, $checkout, $metodo, $statusPagamento, $cartaoFinal) {
            $this->executar(
                "INSERT INTO venda (usuario_id, cliente_id, data, status, valor_total) VALUES (?, ?, CURDATE(), 'aberta', 0)",
                [$usuarioId, (int) $cliente['id']]
            );
            $vendaId = $this->ultimoId();
            $estoque = new Estoque($this->db);
            $total = 0.0;

            foreach (Estoque::emOrdem($itens, 'id') as $i) {
                $vid = (int) $i['id'];
                $qtd = (int) $i['quantidade'];

                // Trava o estoque e confere de novo (outro cliente ou o balcão pode ter vendido a última unidade)
                $estoque->movimentar($vid, $qtd, 'saida', 'venda', $vendaId);
                $this->executar(
                    'INSERT INTO venda_item (venda_id, variacao_id, quantidade, preco_unitario) VALUES (?, ?, ?, ?)',
                    [$vendaId, $vid, $qtd, $i['preco']]
                );
                $total += $qtd * (float) $i['preco'];
            }

            $this->executar('UPDATE venda SET valor_total = ? WHERE id = ?', [round($total, 2), $vendaId]);
            $this->executar(
                'INSERT INTO pedido_online (venda_id, entrega, endereco, pagamento, pagamento_status, cartao_final) VALUES (?, ?, ?, ?, ?, ?)',
                [$vendaId, $checkout['entrega'], $checkout['endereco'] ?: null, $metodo, $statusPagamento, $cartaoFinal]
            );

            // Guarda o endereço no cadastro se o cliente ainda não tinha
            if ($checkout['entrega'] === 'entrega' && trim((string) $cliente['endereco']) === '') {
                $this->executar('UPDATE cliente SET endereco = ? WHERE id = ?', [$checkout['endereco'], $cliente['id']]);
            }

            return $vendaId;
        });
    }

    /** Dados da tela "Pedido recebido" (só se o pedido for do cliente). */
    public function buscarParaConfirmacao(int $vendaId, int $clienteId): ?array
    {
        return $this->um(
            'SELECT v.id, v.valor_total, po.entrega, po.endereco, po.pagamento, po.pagamento_status, po.cartao_final
               FROM venda v JOIN pedido_online po ON po.venda_id = v.id
              WHERE v.id = ? AND v.cliente_id = ?',
            [$vendaId, $clienteId]
        );
    }
}
