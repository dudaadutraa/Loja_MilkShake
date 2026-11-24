<?php

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/Relatorio.php';

/**
 * Relatórios de vendas e compras por período.
 * Os mesmos dados (Model Relatorio) saem em três formatos: tela, Excel (CSV) e PDF (impressão).
 */
class RelatorioController extends BaseController
{
    protected string $rota = 'relatorio';

    public function index(): void
    {
        $this->tela('relatorios', $this->dados());
    }

    /** Baixa o relatório como planilha (CSV com ";", que o Excel em português abre direto). */
    public function exportarExcel(): void
    {
        $d = $this->dados();

        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="relatorio_' . $d['de'] . '_a_' . $d['ate'] . '.csv"');

        $saida = fopen('php://output', 'w');
        fwrite($saida, "\xEF\xBB\xBF");   // BOM: faz o Excel mostrar os acentos certos

        $linha = function (array $campos) use ($saida): void {
            fputcsv($saida, $campos, ';', '"', '\\');
        };
        $valor = function ($n): string {
            return number_format((float) $n, 2, ',', '');
        };

        $linha(['Relatório Shake Nature', data_br($d['de']) . ' a ' . data_br($d['ate'])]);
        $linha([]);
        $linha(['Resumo']);
        $linha(['Vendas', (int) $d['vendas']['qtd']]);
        $linha(['Faturamento (R$)', $valor($d['vendas']['total'])]);
        $linha(['Compras/entradas (R$)', $valor($d['compras']['total'])]);
        $linha(['Vendas - compras (R$)', $valor($d['vendas']['total'] - $d['compras']['total'])]);

        $linha([]);
        $linha(['Mais vendidos']);
        $linha(['Produto', 'Tamanho', 'Quantidade', 'Total (R$)']);
        foreach ($d['topProdutos'] as $l) {
            $linha([$l['produto'], $l['tamanho'], (int) $l['qtd'], $valor($l['total'])]);
        }

        $linha([]);
        $linha(['Por categoria']);
        $linha(['Categoria', 'Quantidade', 'Total (R$)']);
        foreach ($d['porCategoria'] as $l) {
            $linha([trim($l['categoria']), (int) $l['qtd'], $valor($l['total'])]);
        }

        $linha([]);
        $linha(['Vendas por dia']);
        $linha(['Dia', 'Vendas', 'Total (R$)']);
        foreach ($d['porDia'] as $l) {
            $linha([data_br($l['data']), (int) $l['qtd'], $valor($l['total'])]);
        }

        $linha([]);
        $linha(['Por vendedor']);
        $linha(['Vendedor', 'Vendas', 'Total (R$)']);
        foreach ($d['porVendedor'] as $l) {
            $linha([$l['vendedor'], (int) $l['qtd'], $valor($l['total'])]);
        }

        fclose($saida);
        exit;
    }

    /**
     * Relatório pronto para PDF: abre a página de impressão e o navegador
     * oferece "Salvar como PDF" (não precisa de biblioteca extra).
     */
    public function exportarPdf(): void
    {
        sn_view('relatorio_pdf', $this->dados());
    }

    /** Período escolhido (padrão: mês atual) e todos os números do relatório. */
    private function dados(): array
    {
        [$de, $ate] = $this->periodo();
        $rel = new Relatorio();

        return [
            'de'           => $de,
            'ate'          => $ate,
            'vendas'       => $rel->resumoVendas($de, $ate),
            'compras'      => $rel->resumoCompras($de, $ate),
            'porDia'       => $rel->vendasPorDia($de, $ate),
            'topProdutos'  => $rel->produtosMaisVendidos($de, $ate),
            'porCategoria' => $rel->vendasPorCategoria($de, $ate),
            'porVendedor'  => $rel->vendasPorVendedor($de, $ate),
        ];
    }
}
