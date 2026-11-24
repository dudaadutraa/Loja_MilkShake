<?php

require_once __DIR__ . '/Model.php';
require_once __DIR__ . '/Estoque.php';
require_once __DIR__ . '/Variacao.php';

/**
 * Vendas: tabelas venda e venda_item.
 * Inclui as vendas do balcão (painel) e os pedidos do site (status "aberta",
 * com entrega e pagamento na tabela pedido_online — ver Model PedidoOnline).
 */
class Venda extends Model
{
    public function listarPorPeriodo(string $de, string $ate): array
    {
        return $this->todos(
            "SELECT v.id, v.data, v.status, v.valor_total,
                    COALESCE(c.nome, 'Balcão') AS cliente, u.nome AS vendedor,
                    (SELECT COALESCE(SUM(quantidade), 0) FROM venda_item vi WHERE vi.venda_id = v.id) AS itens
               FROM venda v
          LEFT JOIN cliente c ON c.id = v.cliente_id
               JOIN usuario u ON u.id = v.usuario_id
              WHERE v.data BETWEEN ? AND ?
              ORDER BY v.data DESC, v.id DESC",
            [$de, $ate]
        );
    }

    /** Venda com cliente, vendedor e (se for do site) entrega e pagamento. */
    public function buscarCompleta(int $id): ?array
    {
        return $this->um(
            "SELECT v.*, COALESCE(c.nome, 'Balcão') AS cliente, c.cpf_cnpj, c.telefone, c.email, c.endereco, u.nome AS vendedor,
                    po.entrega, po.endereco AS endereco_entrega, po.pagamento, po.pagamento_status, po.cartao_final
               FROM venda v
          LEFT JOIN cliente c ON c.id = v.cliente_id
          LEFT JOIN pedido_online po ON po.venda_id = v.id
               JOIN usuario u ON u.id = v.usuario_id
              WHERE v.id = ?",
            [$id]
        );
    }

    public function itens(int $id): array
    {
        return $this->todos(
            'SELECT vi.*, p.nome AS produto, va.tamanho
               FROM venda_item vi
               JOIN variacao va ON va.id = vi.variacao_id
               JOIN produto p ON p.id = va.produto_id
              WHERE vi.venda_id = ?',
            [$id]
        );
    }

    /**
     * Registra uma venda do balcão (já finalizada) e baixa o estoque.
     * $itens: [['variacao_id', 'quantidade'], ...]. O preço vem sempre do banco (o do momento
     * da venda fica gravado em venda_item, então mudar o preço depois não altera o histórico).
     * Lança RuntimeException se faltar estoque. Devolve ['id' => ..., 'total' => ...].
     */
    public function registrar(int $usuarioId, ?int $clienteId, array $itens): array
    {
        return $this->emTransacao(function () use ($usuarioId, $clienteId, $itens) {
            $this->executar(
                "INSERT INTO venda (usuario_id, cliente_id, data, status, valor_total) VALUES (?, ?, CURDATE(), 'finalizada', 0)",
                [$usuarioId, $clienteId]
            );
            $vendaId = $this->ultimoId();
            $estoque = new Estoque($this->db);
            $variacoes = new Variacao($this->db);
            $total = 0.0;

            foreach (Estoque::emOrdem($itens) as $item) {
                $vid = (int) $item['variacao_id'];
                $qtd = (int) $item['quantidade'];

                // O preço vem sempre do banco, nunca do navegador
                $preco = $variacoes->preco($vid);
                if ($preco === null) {
                    throw new RuntimeException('Um dos itens não existe mais ou o produto foi desativado.');
                }

                $estoque->movimentar($vid, $qtd, 'saida', 'venda', $vendaId);

                $this->executar(
                    'INSERT INTO venda_item (venda_id, variacao_id, quantidade, preco_unitario) VALUES (?, ?, ?, ?)',
                    [$vendaId, $vid, $qtd, $preco]
                );
                $total += $qtd * $preco;
            }

            $this->executar('UPDATE venda SET valor_total = ? WHERE id = ?', [round($total, 2), $vendaId]);
            return ['id' => $vendaId, 'total' => $total];
        });
    }

    /** Pedido do site: marca como entregue/retirado. */
    public function finalizar(int $id): void
    {
        $this->executar("UPDATE venda SET status = 'finalizada' WHERE id = ? AND status = 'aberta'", [$id]);
    }

    /** Pedido do site: marca o pagamento (Pix/dinheiro) como recebido. */
    public function confirmarPagamento(int $id): void
    {
        $this->executar(
            "UPDATE pedido_online po JOIN venda v ON v.id = po.venda_id
                SET po.pagamento_status = 'aprovado'
              WHERE po.venda_id = ? AND v.status <> 'cancelada'",
            [$id]
        );
    }

    /**
     * Cancela a venda: devolve os itens ao estoque (movimento de entrada com origem nesta venda)
     * e marca a venda como "cancelada". A venda NÃO é apagada, para o histórico continuar completo;
     * vendas canceladas não entram no faturamento nem nos relatórios.
     * Lança RuntimeException se a venda não existir ou já estiver cancelada.
     */
    public function cancelar(int $id): void
    {
        $this->emTransacao(function () use ($id) {
            $venda = $this->um('SELECT status FROM venda WHERE id = ? FOR UPDATE', [$id]);
            if (!$venda) {
                throw new RuntimeException('Venda não encontrada.');
            }
            if ($venda['status'] === 'cancelada') {
                throw new RuntimeException('Esta venda já foi cancelada.');
            }

            $estoque = new Estoque($this->db);
            $itens = $this->todos('SELECT variacao_id, SUM(quantidade) AS quantidade FROM venda_item WHERE venda_id = ? GROUP BY variacao_id', [$id]);
            foreach (Estoque::emOrdem($itens) as $item) {
                $estoque->movimentar((int) $item['variacao_id'], (int) $item['quantidade'], 'entrada', 'venda', $id);
            }

            $this->executar("UPDATE venda SET status = 'cancelada' WHERE id = ?", [$id]);
        });
    }
}
