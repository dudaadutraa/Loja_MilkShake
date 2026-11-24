<?php

require_once __DIR__ . '/Model.php';

/**
 * Consultas do cardápio da LOJA VIRTUAL (o que o cliente vê):
 * só produtos e categorias ativos e que tenham pelo menos uma variação.
 */
class Vitrine extends Model
{
    /** Categorias que aparecem no menu da loja. */
    public function categorias(): array
    {
        return $this->todos(
            'SELECT DISTINCT c.id, c.nome
               FROM categoria c
               JOIN produto p ON p.categoria_id = c.id AND p.ativo = 1
               JOIN variacao v ON v.produto_id = p.id
              WHERE c.ativo = 1
              ORDER BY c.nome'
        );
    }

    /** Produtos do cardápio, com faixa de preço, tamanhos e estoque. Filtros opcionais por categoria e busca. */
    public function produtos(int $categoria = 0, string $busca = ''): array
    {
        $sql = "SELECT p.id, p.nome, p.descricao, p.imagem, c.id AS categoria_id, c.nome AS categoria,
                       MIN(v.preco) AS preco_min, MAX(v.preco) AS preco_max,
                       COUNT(v.id) AS qtd_variacoes,
                       GROUP_CONCAT(v.tamanho ORDER BY v.preco, v.tamanho SEPARATOR ' · ') AS tamanhos,
                       COALESCE(SUM(e.quantidade), 0) AS estoque
                  FROM produto p
                  JOIN categoria c ON c.id = p.categoria_id AND c.ativo = 1
                  JOIN variacao v ON v.produto_id = p.id
             LEFT JOIN estoque e ON e.variacao_id = v.id
                 WHERE p.ativo = 1";
        $params = [];
        if ($categoria > 0) {
            $sql .= ' AND c.id = ?';
            $params[] = $categoria;
        }
        if ($busca !== '') {
            $sql .= ' AND (p.nome LIKE ? OR p.descricao LIKE ? OR c.nome LIKE ?)';
            array_push($params, "%{$busca}%", "%{$busca}%", "%{$busca}%");
        }
        $sql .= ' GROUP BY p.id ORDER BY c.nome, (COALESCE(SUM(e.quantidade), 0) > 0) DESC, p.nome';

        return $this->todos($sql, $params);
    }

    /** Produto ativo com o nome da categoria (página do produto). */
    public function produto(int $id): ?array
    {
        return $this->um(
            'SELECT p.*, c.nome AS categoria
               FROM produto p JOIN categoria c ON c.id = p.categoria_id
              WHERE p.id = ? AND p.ativo = 1',
            [$id]
        );
    }

    /** Tamanhos do produto com preço e estoque. */
    public function tamanhos(int $produtoId): array
    {
        return $this->todos(
            'SELECT v.id, v.tamanho, v.preco, COALESCE(e.quantidade, 0) AS estoque
               FROM variacao v LEFT JOIN estoque e ON e.variacao_id = v.id
              WHERE v.produto_id = ?
              ORDER BY v.preco, v.tamanho',
            [$produtoId]
        );
    }

    /** Até 4 outros produtos da mesma categoria ("Da mesma categoria"). */
    public function outrosDaCategoria(int $categoriaId, int $menosProdutoId): array
    {
        return $this->todos(
            'SELECT p.id, p.nome, p.descricao, p.imagem, MIN(v.preco) AS preco_min
               FROM produto p JOIN variacao v ON v.produto_id = p.id
              WHERE p.ativo = 1 AND p.categoria_id = ? AND p.id <> ?
              GROUP BY p.id ORDER BY RAND() LIMIT 4',
            [$categoriaId, $menosProdutoId]
        );
    }

    /** Nome e descrição do produto para desenhar a ilustração. */
    public function dadosParaIlustracao(int $id): ?array
    {
        return $this->um('SELECT id, nome, descricao FROM produto WHERE id = ?', [$id]);
    }
}
