<?php

require_once __DIR__ . '/Model.php';
require_once __DIR__ . '/Estoque.php';

/** Tabela variacao: cada TAMANHO de um produto, com preço, SKU e estoque. */
class Variacao extends Model
{
    /** Variação com o estoque mínimo (formulário de edição). */
    public function buscarComMinimo(int $id): ?array
    {
        return $this->um(
            'SELECT v.*, COALESCE(e.minimo, 0) AS minimo
               FROM variacao v LEFT JOIN estoque e ON e.variacao_id = v.id
              WHERE v.id = ?',
            [$id]
        );
    }

    /** Variações com produto e estoque. $produtoId = 0 traz todas. */
    public function listar(int $produtoId = 0): array
    {
        $sql = 'SELECT v.*, p.nome AS produto_nome, p.ativo AS produto_ativo,
                       COALESCE(e.quantidade, 0) AS quantidade, COALESCE(e.minimo, 0) AS minimo
                  FROM variacao v
                  JOIN produto p ON p.id = v.produto_id
             LEFT JOIN estoque e ON e.variacao_id = v.id';
        $params = [];
        if ($produtoId > 0) {
            $sql .= ' WHERE v.produto_id = ?';
            $params[] = $produtoId;
        }
        $sql .= ' ORDER BY p.nome, v.preco, v.tamanho';

        return $this->todos($sql, $params);
    }

    /** Tamanhos de produtos ativos para os formulários de venda e entrada. */
    public function listarParaSelect(): array
    {
        return $this->todos(
            "SELECT v.id, v.tamanho, v.preco, p.id AS produto_id, p.nome AS produto,
                    COALESCE(e.quantidade, 0) AS quantidade
               FROM variacao v
               JOIN produto p ON p.id = v.produto_id
          LEFT JOIN estoque e ON e.variacao_id = v.id
              WHERE p.ativo = 1
              ORDER BY p.nome, v.preco, v.tamanho"
        );
    }

    /** Preço atual de uma variação de produto ATIVO (null se não existir ou se o produto estiver desativado). */
    public function preco(int $id): ?float
    {
        $v = $this->um('SELECT v.preco FROM variacao v JOIN produto p ON p.id = v.produto_id WHERE v.id = ? AND p.ativo = 1', [$id]);
        return $v ? (float) $v['preco'] : null;
    }

    /** Já existe este tamanho no produto? (evita dois "300ml" no mesmo milkshake) */
    public function tamanhoRepetido(int $produtoId, string $tamanho, int $ignorarId = 0): bool
    {
        return (bool) $this->um(
            'SELECT 1 AS r FROM variacao WHERE produto_id = ? AND LOWER(REPLACE(tamanho, " ", "")) = LOWER(REPLACE(?, " ", "")) AND id <> ?',
            [$produtoId, $tamanho, $ignorarId]
        );
    }

    /**
     * Cadastra ($id = 0) ou atualiza a variação e grava o estoque mínimo.
     * Lança PDOException (código 1062) se o SKU já existir.
     */
    public function salvar(int $id, int $produtoId, string $tamanho, string $sku, float $preco, int $minimo): int
    {
        return $this->emTransacao(function () use ($id, $produtoId, $tamanho, $sku, $preco, $minimo) {
            if ($id > 0) {
                $this->executar(
                    'UPDATE variacao SET produto_id = ?, tamanho = ?, sku = ?, preco = ? WHERE id = ?',
                    [$produtoId, mb_substr($tamanho, 0, 10), mb_substr($sku, 0, 50), $preco, $id]
                );
            } else {
                $this->executar(
                    'INSERT INTO variacao (produto_id, tamanho, sku, preco) VALUES (?, ?, ?, ?)',
                    [$produtoId, mb_substr($tamanho, 0, 10), mb_substr($sku, 0, 50), $preco]
                );
                $id = $this->ultimoId();
            }

            // Garante a linha de estoque e atualiza o mínimo
            (new Estoque($this->db))->definirMinimo($id, $minimo);
            return $id;
        });
    }

    /** Exclui a variação (e a linha de estoque zerada). Lança PDOException se já tiver vendas/entradas. */
    public function excluir(int $id): void
    {
        $this->emTransacao(function () use ($id) {
            $this->executar('DELETE FROM estoque WHERE variacao_id = ? AND quantidade = 0', [$id]);
            $this->executar('DELETE FROM variacao WHERE id = ?', [$id]);
        });
    }
}
