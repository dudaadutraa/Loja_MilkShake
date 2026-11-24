<?php

require_once __DIR__ . '/Model.php';

/**
 * Números do dashboard, usando as tabelas reais do banco:
 * produto, variacao, estoque, venda, cliente, fornecedor.
 * Cada consulta é isolada: se uma falhar, as outras continuam.
 */
class Dashboard extends Model
{
    private array $erros = [];

    public function getErros(): array
    {
        return $this->erros;
    }

    /** Executa uma consulta; em caso de erro guarda a mensagem e devolve $padrao. */
    private function q(string $rotulo, string $sql, string $modo = 'col', $padrao = 0)
    {
        try {
            $st = $this->db->query($sql);
            $r = $modo === 'col' ? $st->fetchColumn() : $st->fetchAll();
            return $r === false ? $padrao : $r;
        } catch (Throwable $e) {
            error_log("[dashboard:$rotulo] " . $e->getMessage());
            $this->erros[$rotulo] = $e->getMessage();
            return $padrao;
        }
    }

    /** Todos os números dos cards e do resumo da semana. */
    public function getNumeros(): array
    {
        $n = [];
        $n['produtos']     = (int) $this->q('produtos', 'SELECT COUNT(*) FROM produto WHERE ativo = 1');
        $n['estoque']      = (int) $this->q('estoque', 'SELECT COALESCE(SUM(quantidade), 0) FROM estoque');
        $n['baixo']        = (int) $this->q('estoque baixo', 'SELECT COUNT(*) FROM estoque e JOIN variacao v ON v.id = e.variacao_id JOIN produto p ON p.id = v.produto_id WHERE p.ativo = 1 AND e.quantidade <= e.minimo');
        $n['entradas']     = (int) $this->q('entradas', 'SELECT COUNT(*) FROM entrada_mercadoria');
        $n['entradas_mes'] = (float) $this->q(
            'entradas do mes',
            "SELECT COALESCE(SUM(valor_total), 0) FROM entrada_mercadoria
              WHERE data >= DATE_FORMAT(CURDATE(), '%Y-%m-01')"
        );
        $n['fornecedores'] = (int) $this->q('fornecedores', 'SELECT COUNT(*) FROM fornecedor');
        $n['vendas']       = (float) $this->q('vendas total', "SELECT COALESCE(SUM(valor_total), 0) FROM venda WHERE status <> 'cancelada'");
        $n['hoje']         = (float) $this->q('vendas hoje', "SELECT COALESCE(SUM(valor_total), 0) FROM venda WHERE status <> 'cancelada' AND data = CURDATE()");
        $n['qtd7']         = (int) $this->q('qtd 7 dias', "SELECT COUNT(*) FROM venda WHERE status <> 'cancelada' AND data >= CURDATE() - INTERVAL 6 DAY");
        $n['sem_atual']    = (float) $this->q('vendas 7 dias', "SELECT COALESCE(SUM(valor_total), 0) FROM venda WHERE status <> 'cancelada' AND data >= CURDATE() - INTERVAL 6 DAY");
        $n['sem_anterior'] = (float) $this->q(
            'vendas 7 anteriores',
            "SELECT COALESCE(SUM(valor_total), 0) FROM venda
              WHERE status <> 'cancelada' AND data BETWEEN CURDATE() - INTERVAL 13 DAY AND CURDATE() - INTERVAL 7 DAY"
        );

        $n['variacao'] = $n['sem_anterior'] > 0
            ? ($n['sem_atual'] - $n['sem_anterior']) / $n['sem_anterior'] * 100
            : null;

        return $n;
    }

    /** Vendas por dia dos últimos 7 dias: [['label' => 'Seg', 'valor' => 10.5], ...]. */
    public function getGrafico(): array
    {
        $linhas = $this->q(
            'grafico',
            "SELECT data AS dia, SUM(valor_total) AS total FROM venda
              WHERE status <> 'cancelada' AND data >= CURDATE() - INTERVAL 6 DAY GROUP BY data",
            'all',
            []
        );

        $porDia = [];
        foreach ($linhas as $r) {
            $porDia[$r['dia']] = (float) $r['total'];
        }

        $nomes = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'];
        $grafico = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = new DateTime("-$i day");
            $grafico[] = [
                'label' => $nomes[(int) $d->format('w')],
                'valor' => $porDia[$d->format('Y-m-d')] ?? 0,
            ];
        }

        return $grafico;
    }

    public function getUltimasVendas(int $limite = 5): array
    {
        $limite = max(1, min(20, $limite));

        return $this->q(
            'ultimas vendas',
            "SELECT v.id, v.valor_total AS total, v.data, COALESCE(c.nome, 'Balcão') AS cliente
               FROM venda v
          LEFT JOIN cliente c ON c.id = v.cliente_id
              WHERE v.status <> 'cancelada'
              ORDER BY v.data DESC, v.id DESC
              LIMIT $limite",
            'all',
            []
        );
    }

    public function getEstoqueBaixo(int $limite = 5): array
    {
        $limite = max(1, min(20, $limite));

        return $this->q(
            'lista estoque baixo',
            "SELECT CONCAT(p.nome, ' - ', v.tamanho) AS nome, e.quantidade AS qtd, e.minimo
               FROM estoque e
               JOIN variacao v ON v.id = e.variacao_id
               JOIN produto p ON p.id = v.produto_id
              WHERE p.ativo = 1 AND e.quantidade <= e.minimo
              ORDER BY e.quantidade ASC
              LIMIT $limite",
            'all',
            []
        );
    }
}
