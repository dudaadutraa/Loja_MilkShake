<?php

require_once __DIR__ . '/Model.php';
require_once __DIR__ . '/Estoque.php';
require_once __DIR__ . '/NotaFiscal.php';

/**
 * Entradas de mercadoria (compras de fornecedor):
 * tabelas entrada_mercadoria, entrada_item e nota_fiscal_entrada.
 * Toda entrada soma no estoque.
 */
class Entrada extends Model
{
    public function listar(): array
    {
        return $this->todos(
            "SELECT e.*, f.nome AS fornecedor,
                    (SELECT COALESCE(SUM(quantidade), 0) FROM entrada_item i WHERE i.entrada_id = e.id) AS itens,
                    nf.numero AS nf_numero
               FROM entrada_mercadoria e
               JOIN fornecedor f ON f.id = e.fornecedor_id
          LEFT JOIN nota_fiscal_entrada nf ON nf.entrada_id = e.id
              ORDER BY e.data DESC, e.id DESC
              LIMIT 200"
        );
    }

    public function buscar(int $id): ?array
    {
        return $this->um(
            'SELECT e.*, f.nome AS fornecedor, nf.numero AS nf_numero, nf.serie AS nf_serie, nf.chave_acesso
               FROM entrada_mercadoria e
               JOIN fornecedor f ON f.id = e.fornecedor_id
          LEFT JOIN nota_fiscal_entrada nf ON nf.entrada_id = e.id
              WHERE e.id = ?',
            [$id]
        );
    }

    public function itens(int $id): array
    {
        return $this->todos(
            'SELECT i.*, p.nome AS produto, v.tamanho
               FROM entrada_item i
               JOIN variacao v ON v.id = i.variacao_id
               JOIN produto p ON p.id = v.produto_id
              WHERE i.entrada_id = ?',
            [$id]
        );
    }

    /**
     * Registra a entrada, os itens, soma no estoque e grava a nota fiscal (opcional).
     * $itens: [['variacao_id', 'quantidade', 'custo'], ...]
     * $nota:  null ou ['numero', 'modelo', 'serie', 'chave']
     * Devolve ['id' => ..., 'total' => ...].
     */
    public function registrar(int $fornecedorId, string $data, array $itens, ?array $nota): array
    {
        return $this->emTransacao(function () use ($fornecedorId, $data, $itens, $nota) {
            $this->executar(
                "INSERT INTO entrada_mercadoria (fornecedor_id, data, status, valor_total) VALUES (?, ?, 'confirmar', 0)",
                [$fornecedorId, $data]
            );
            $entradaId = $this->ultimoId();
            $estoque = new Estoque($this->db);
            $total = 0.0;

            foreach (Estoque::emOrdem($itens) as $item) {
                $this->executar(
                    'INSERT INTO entrada_item (entrada_id, variacao_id, quantidade, custo_unitario) VALUES (?, ?, ?, ?)',
                    [$entradaId, $item['variacao_id'], $item['quantidade'], $item['custo']]
                );
                $estoque->movimentar((int) $item['variacao_id'], (int) $item['quantidade'], 'entrada', 'entrada', $entradaId);
                $total += $item['quantidade'] * $item['custo'];
            }

            $this->executar('UPDATE entrada_mercadoria SET valor_total = ? WHERE id = ?', [round($total, 2), $entradaId]);

            // Nota fiscal (opcional)
            if ($nota !== null) {
                (new NotaFiscal($this->db))->registrarParaEntrada(
                    $entradaId, $nota['numero'], $nota['modelo'], $nota['serie'], $nota['chave'], $data, $total
                );
            }

            return ['id' => $entradaId, 'total' => $total];
        });
    }

    /**
     * Exclui uma entrada (ex.: lançada por engano) e tira do estoque o que ela colocou.
     * Se parte das unidades já foi vendida, lança RuntimeException e não exclui.
     * O histórico de estoque ganha uma "saída" com origem nesta entrada, para ficar registrado.
     */
    public function excluir(int $id): void
    {
        $itens = $this->todos(
            'SELECT i.variacao_id, SUM(i.quantidade) AS quantidade, CONCAT(p.nome, " ", v.tamanho) AS nome
               FROM entrada_item i
               JOIN variacao v ON v.id = i.variacao_id
               JOIN produto p ON p.id = v.produto_id
              WHERE i.entrada_id = ?
              GROUP BY i.variacao_id, p.nome, v.tamanho',
            [$id]
        );

        if (!$itens) {
            throw new RuntimeException('Entrada não encontrada.');
        }

        $this->emTransacao(function () use ($id, $itens) {
            $estoque = new Estoque($this->db);

            foreach (Estoque::emOrdem($itens) as $item) {
                $vid = (int) $item['variacao_id'];
                $qtd = (int) $item['quantidade'];
                $temAgora = $estoque->travar($vid);

                if ($temAgora < $qtd) {
                    throw new RuntimeException(
                        'Não dá para excluir: a entrada colocou ' . $qtd . ' de ' . $item['nome']
                        . ', mas só há ' . $temAgora . ' no estoque (o resto já foi vendido).'
                    );
                }

                $estoque->movimentar($vid, $qtd, 'saida', 'entrada', $id);
            }

            // os itens e a nota fiscal da entrada são apagados junto (ON DELETE CASCADE)
            $this->executar('DELETE FROM entrada_mercadoria WHERE id = ?', [$id]);
        });
    }
}
