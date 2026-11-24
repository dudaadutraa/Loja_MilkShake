<?php

require_once __DIR__ . '/Model.php';

/**
 * Tabela produto (painel administrativo).
 * O cardápio da loja virtual usa o Model Vitrine.
 */
class Produto extends Model
{
    public function buscar(int $id): ?array
    {
        return $this->um('SELECT * FROM produto WHERE id = ?', [$id]);
    }

    /** Produto com o nome da categoria (cabeçalho da tela de variações). */
    public function buscarComCategoria(int $id): ?array
    {
        return $this->um(
            'SELECT p.*, c.nome AS categoria_nome FROM produto p JOIN categoria c ON c.id = p.categoria_id WHERE p.id = ?',
            [$id]
        );
    }

    /** Lista da tela Produtos: categoria, faixa de preço, nº de variações e estoque total. */
    public function listarComResumo(string $busca = ''): array
    {
        $sql = "SELECT p.*, c.nome AS categoria_nome,
                       COUNT(v.id) AS qtd_variacoes,
                       MIN(v.preco) AS preco_min, MAX(v.preco) AS preco_max,
                       COALESCE(SUM(e.quantidade), 0) AS estoque_total
                  FROM produto p
                  JOIN categoria c ON c.id = p.categoria_id
             LEFT JOIN variacao v ON v.produto_id = p.id
             LEFT JOIN estoque e ON e.variacao_id = v.id";
        $params = [];
        if ($busca !== '') {
            $sql .= ' WHERE p.nome LIKE ? OR p.descricao LIKE ? OR c.nome LIKE ?';
            $params = ["%{$busca}%", "%{$busca}%", "%{$busca}%"];
        }
        $sql .= ' GROUP BY p.id ORDER BY c.nome, p.nome';

        return $this->todos($sql, $params);
    }

    /** Lista simples para o campo "Produto" do cadastro de variações. */
    public function listarParaSelect(): array
    {
        return $this->todos('SELECT id, nome, ativo FROM produto ORDER BY nome');
    }

    public function inserir(int $categoriaId, string $nome, ?string $descricao): int
    {
        $this->executar(
            'INSERT INTO produto (categoria_id, nome, descricao, ativo) VALUES (?, ?, ?, 1)',
            [$categoriaId, $nome, $descricao]
        );
        return $this->ultimoId();
    }

    public function atualizar(int $id, int $categoriaId, string $nome, ?string $descricao): void
    {
        $this->executar(
            'UPDATE produto SET categoria_id = ?, nome = ?, descricao = ? WHERE id = ?',
            [$categoriaId, $nome, $descricao, $id]
        );
    }

    /** Ativa/desativa. */
    public function alternar(int $id): void
    {
        $this->executar('UPDATE produto SET ativo = 1 - ativo WHERE id = ?', [$id]);
    }

    /** Lança PDOException se o produto tiver variações. */
    public function excluir(int $id): void
    {
        $this->executar('DELETE FROM produto WHERE id = ?', [$id]);
    }

    /** Nome do arquivo da foto enviada (coluna imagem) ou null. */
    public function imagemAtual(int $id): ?string
    {
        return $this->um('SELECT imagem FROM produto WHERE id = ?', [$id])['imagem'] ?? null;
    }

    public function definirImagem(int $id, ?string $arquivo): void
    {
        $this->executar('UPDATE produto SET imagem = ? WHERE id = ?', [$arquivo, $id]);
    }
}
