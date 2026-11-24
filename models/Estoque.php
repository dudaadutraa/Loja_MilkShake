<?php

require_once __DIR__ . '/Model.php';

/** Tabelas estoque e movimento_estoque. */
class Estoque extends Model
{
    /** Posição de estoque de cada variação (tela Estoque). $filtro = 'baixo' mostra só o que está no mínimo. */
    public function posicao(string $filtro = ''): array
    {
        $sql = "SELECT v.id, v.tamanho, v.sku, v.preco, p.nome AS produto, p.ativo,
                       COALESCE(e.quantidade, 0) AS quantidade, COALESCE(e.minimo, 0) AS minimo
                  FROM variacao v
                  JOIN produto p ON p.id = v.produto_id
             LEFT JOIN estoque e ON e.variacao_id = v.id";
        if ($filtro === 'baixo') {
            $sql .= ' WHERE p.ativo = 1 AND COALESCE(e.quantidade, 0) <= COALESCE(e.minimo, 0)';
        }
        $sql .= ' ORDER BY p.nome, v.preco';

        return $this->todos($sql);
    }

    /** Últimos movimentos (entradas e saídas). */
    public function movimentosRecentes(int $limite = 30): array
    {
        $limite = max(1, min(200, $limite));
        return $this->todos(
            "SELECT m.*, p.nome AS produto, v.tamanho
               FROM movimento_estoque m
               JOIN variacao v ON v.id = m.variacao_id
               JOIN produto p ON p.id = v.produto_id
              ORDER BY m.data DESC, m.id DESC
              LIMIT $limite"
        );
    }

    /** Cria a linha de estoque da variação (se não existir) e grava o estoque mínimo. */
    public function definirMinimo(int $variacaoId, int $minimo): void
    {
        $this->executar(
            'INSERT INTO estoque (variacao_id, quantidade, minimo) VALUES (?, 0, ?)
             ON DUPLICATE KEY UPDATE minimo = VALUES(minimo)',
            [$variacaoId, $minimo]
        );
    }

    /**
     * Único lugar que mexe na quantidade em estoque: soma ($tipo = 'entrada') ou subtrai ($tipo = 'saida')
     * e grava o movimento no histórico. Deve ser chamado dentro de uma transação.
     *
     * A linha do estoque é travada com SELECT ... FOR UPDATE: se duas pessoas tentam vender
     * a última unidade ao mesmo tempo, a segunda espera a primeira terminar e então recebe
     * "Estoque insuficiente" (o estoque nunca fica negativo).
     *
     * $origem: 'venda' ou 'entrada' (de onde veio o movimento) e $origemId o número dela.
     */
    public function movimentar(int $variacaoId, int $quantidade, string $tipo, string $origem, int $origemId): void
    {
        $atual = $this->travar($variacaoId);
        $nova  = $atual + ($tipo === 'saida' ? -$quantidade : $quantidade);

        if ($nova < 0) {
            throw new RuntimeException('Estoque insuficiente para ' . $this->nome($variacaoId) . ' (disponível: ' . $atual . ').');
        }

        $this->executar('UPDATE estoque SET quantidade = ? WHERE variacao_id = ?', [$nova, $variacaoId]);
        $this->executar(
            'INSERT INTO movimento_estoque (variacao_id, tipo, quantidade, origem, origem_id, data) VALUES (?, ?, ?, ?, ?, NOW())',
            [$variacaoId, $tipo === 'saida' ? 'saida' : 'entrada', $quantidade, $origem, $origemId]
        );
    }

    /** Trava a linha de estoque da variação (criando-a se faltar) e devolve a quantidade atual. */
    public function travar(int $variacaoId): int
    {
        $this->executar('INSERT IGNORE INTO estoque (variacao_id, quantidade, minimo) VALUES (?, 0, 0)', [$variacaoId]);
        $linha = $this->um('SELECT quantidade FROM estoque WHERE variacao_id = ? FOR UPDATE', [$variacaoId]);
        return (int) ($linha['quantidade'] ?? 0);
    }

    /** "Produto 300ml" (para as mensagens). */
    public function nome(int $variacaoId): string
    {
        $n = $this->um(
            'SELECT CONCAT(p.nome, " ", v.tamanho) AS n FROM variacao v JOIN produto p ON p.id = v.produto_id WHERE v.id = ?',
            [$variacaoId]
        );
        return $n['n'] ?? 'o item';
    }

    /**
     * Ordena os itens pelo tamanho (variacao_id) antes de travar o estoque.
     * Com todas as operações travando na mesma ordem, duas vendas simultâneas não se bloqueiam (deadlock).
     */
    public static function emOrdem(array $itens, string $campo = 'variacao_id'): array
    {
        usort($itens, function ($a, $b) use ($campo) {
            return (int) $a[$campo] <=> (int) $b[$campo];
        });
        return $itens;
    }
}
