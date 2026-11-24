<?php

require_once __DIR__ . '/Model.php';

/** Números da tela Relatórios (vendas e compras por período). */
class Relatorio extends Model
{
    /** Quantidade e total das vendas no período (vendas canceladas não entram em nenhum número). */
    public function resumoVendas(string $de, string $ate): ?array
    {
        return $this->um(
            "SELECT COUNT(*) AS qtd, COALESCE(SUM(valor_total), 0) AS total FROM venda WHERE status <> 'cancelada' AND data BETWEEN ? AND ?",
            [$de, $ate]
        );
    }

    /** Quantidade e total das entradas (compras) no período. */
    public function resumoCompras(string $de, string $ate): ?array
    {
        return $this->um(
            'SELECT COUNT(*) AS qtd, COALESCE(SUM(valor_total), 0) AS total FROM entrada_mercadoria WHERE data BETWEEN ? AND ?',
            [$de, $ate]
        );
    }

    public function vendasPorDia(string $de, string $ate): array
    {
        return $this->todos(
            "SELECT data, COUNT(*) AS qtd, SUM(valor_total) AS total FROM venda WHERE status <> 'cancelada' AND data BETWEEN ? AND ? GROUP BY data ORDER BY data",
            [$de, $ate]
        );
    }

    public function produtosMaisVendidos(string $de, string $ate): array
    {
        return $this->todos(
            "SELECT p.nome AS produto, va.tamanho, SUM(vi.quantidade) AS qtd, SUM(vi.quantidade * vi.preco_unitario) AS total
               FROM venda_item vi
               JOIN venda v ON v.id = vi.venda_id
               JOIN variacao va ON va.id = vi.variacao_id
               JOIN produto p ON p.id = va.produto_id
              WHERE v.status <> 'cancelada' AND v.data BETWEEN ? AND ?
              GROUP BY va.id, p.nome, va.tamanho
              ORDER BY qtd DESC
              LIMIT 10",
            [$de, $ate]
        );
    }

    public function vendasPorCategoria(string $de, string $ate): array
    {
        return $this->todos(
            "SELECT c.nome AS categoria, SUM(vi.quantidade) AS qtd, SUM(vi.quantidade * vi.preco_unitario) AS total
               FROM venda_item vi
               JOIN venda v ON v.id = vi.venda_id
               JOIN variacao va ON va.id = vi.variacao_id
               JOIN produto p ON p.id = va.produto_id
               JOIN categoria c ON c.id = p.categoria_id
              WHERE v.status <> 'cancelada' AND v.data BETWEEN ? AND ?
              GROUP BY c.id, c.nome
              ORDER BY total DESC",
            [$de, $ate]
        );
    }

    public function vendasPorVendedor(string $de, string $ate): array
    {
        return $this->todos(
            "SELECT u.nome AS vendedor, COUNT(*) AS qtd, SUM(v.valor_total) AS total
               FROM venda v JOIN usuario u ON u.id = v.usuario_id
              WHERE v.status <> 'cancelada' AND v.data BETWEEN ? AND ?
              GROUP BY u.id, u.nome
              ORDER BY total DESC",
            [$de, $ate]
        );
    }
}
