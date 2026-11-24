<?php

require_once __DIR__ . '/Model.php';

/**
 * Carrinho da loja virtual.
 * Fica guardado na sessão do visitante: $_SESSION['carrinho'] = [variacao_id => quantidade].
 * Nome, preço e estoque são sempre lidos do banco (nunca vêm do navegador).
 */
class Carrinho extends Model
{
    /** Conteúdo bruto: [variacao_id => quantidade]. */
    public function conteudo(): array
    {
        $c = $_SESSION['carrinho'] ?? [];
        return is_array($c) ? $c : [];
    }

    /** Grava o carrinho (itens com quantidade 0 saem). */
    public function salvar(array $c): void
    {
        $_SESSION['carrinho'] = array_filter($c, fn ($q) => $q > 0);
    }

    /** Total de unidades (número que aparece no ícone do carrinho). */
    public function quantidadeTotal(): int
    {
        return (int) array_sum($this->conteudo());
    }

    public function esvaziar(): void
    {
        unset($_SESSION['carrinho']);
    }

    /** Variação de produto ativo com nome e estoque (para conferir antes de adicionar). */
    public function variacaoDisponivel(int $variacaoId): ?array
    {
        return $this->um(
            'SELECT v.id, v.tamanho, p.nome, COALESCE(e.quantidade, 0) AS estoque
               FROM variacao v
               JOIN produto p ON p.id = v.produto_id AND p.ativo = 1
               JOIN categoria c ON c.id = p.categoria_id AND c.ativo = 1
          LEFT JOIN estoque e ON e.variacao_id = v.id
              WHERE v.id = ?',
            [$variacaoId]
        );
    }

    /**
     * Itens do carrinho com nome, tamanho, preço, estoque e subtotal vindos do banco.
     * Remove do carrinho o que não existe mais (ou foi desativado).
     */
    public function itens(): array
    {
        $c = $this->conteudo();
        if (!$c) {
            return [];
        }

        $ids = array_map('intval', array_keys($c));
        $marcas = implode(',', array_fill(0, count($ids), '?'));
        $linhas = $this->todos(
            "SELECT v.id, v.tamanho, v.preco, p.id AS produto_id, p.nome AS produto, p.descricao, p.imagem,
                    COALESCE(e.quantidade, 0) AS estoque
               FROM variacao v
               JOIN produto p ON p.id = v.produto_id AND p.ativo = 1
               JOIN categoria c ON c.id = p.categoria_id AND c.ativo = 1
          LEFT JOIN estoque e ON e.variacao_id = v.id
              WHERE v.id IN ($marcas)
              ORDER BY p.nome, v.preco",
            $ids
        );

        $itens = [];
        $limpo = [];
        foreach ($linhas as $l) {
            $qtd = (int) $c[$l['id']];
            $l['quantidade'] = $qtd;
            $l['subtotal'] = $qtd * (float) $l['preco'];
            $itens[] = $l;
            $limpo[(int) $l['id']] = $qtd;
        }
        $this->salvar($limpo);

        return $itens;
    }

    /** Soma dos subtotais. */
    public static function total(array $itens): float
    {
        return (float) array_sum(array_column($itens, 'subtotal'));
    }
}
