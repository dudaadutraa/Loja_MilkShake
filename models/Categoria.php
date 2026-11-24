<?php

require_once __DIR__ . '/Model.php';

/** Tabela categoria. */
class Categoria extends Model
{
    public function buscar(int $id): ?array
    {
        return $this->um('SELECT * FROM categoria WHERE id = ?', [$id]);
    }

    /** Todas as categorias com a quantidade de produtos de cada uma (tela Categorias). */
    public function listarComQtdProdutos(): array
    {
        return $this->todos(
            'SELECT c.*, (SELECT COUNT(*) FROM produto p WHERE p.categoria_id = c.id) AS qtd_produtos
               FROM categoria c
              ORDER BY c.nome'
        );
    }

    /** Lista simples para o campo "Categoria" do cadastro de produto. */
    public function listarParaSelect(): array
    {
        return $this->todos('SELECT id, nome, ativo FROM categoria ORDER BY nome');
    }

    public function inserir(string $nome): void
    {
        $this->executar('INSERT INTO categoria (nome, ativo) VALUES (?, 1)', [$nome]);
    }

    public function atualizar(int $id, string $nome): void
    {
        $this->executar('UPDATE categoria SET nome = ? WHERE id = ?', [$nome, $id]);
    }

    /** Ativa/desativa. */
    public function alternar(int $id): void
    {
        $this->executar('UPDATE categoria SET ativo = 1 - ativo WHERE id = ?', [$id]);
    }

    /** Lança PDOException se a categoria tiver produtos. */
    public function excluir(int $id): void
    {
        $this->executar('DELETE FROM categoria WHERE id = ?', [$id]);
    }
}
