<?php

require_once __DIR__ . '/LojaBaseController.php';

/** Carrinho de compras da loja virtual (guardado na sessão do visitante). */
class CarrinhoController extends LojaBaseController
{
    public function index(): void
    {
        $itens = $this->carrinho->itens();

        $this->tela('carrinho', [
            'itens' => $itens,
            'total' => Carrinho::total($itens),
        ]);
    }

    public function adicionar(): void
    {
        $vid = (int) ($_POST['variacao_id'] ?? 0);
        $qtd = max(1, min(99, (int) ($_POST['quantidade'] ?? 1)));
        $produtoId = (int) ($_POST['produto_id'] ?? 0);
        $voltar = $produtoId > 0 ? ['controller' => 'loja', 'action' => 'produto', 'id' => $produtoId] : ['controller' => 'loja'];

        $this->exigirPost($voltar);

        $v = $this->carrinho->variacaoDisponivel($vid);

        if (!$v) {
            sn_flash('erro', 'Escolha um tamanho.');
            sn_irpara($voltar);
        }

        $c = $this->carrinho->conteudo();
        $nova = ($c[$vid] ?? 0) + $qtd;

        if ($nova > (int) $v['estoque']) {
            sn_flash('erro', (int) $v['estoque'] > 0
                ? 'Temos só ' . (int) $v['estoque'] . ' unidade(s) de ' . $v['nome'] . ' ' . $v['tamanho'] . '.'
                : $v['nome'] . ' ' . $v['tamanho'] . ' está esgotado no momento.');
            sn_irpara($voltar);
        }

        $c[$vid] = $nova;
        $this->carrinho->salvar($c);

        sn_flash('ok', $v['nome'] . ' ' . $v['tamanho'] . ' adicionado ao carrinho.');
        sn_irpara(['controller' => 'carrinho']);
    }

    /** Atualiza as quantidades (quantidade 0 remove o item). */
    public function atualizar(): void
    {
        $this->exigirPost(['controller' => 'carrinho']);

        $c = $this->carrinho->conteudo();
        foreach ((array) ($_POST['qtd'] ?? []) as $vid => $qtd) {
            $vid = (int) $vid;
            if (isset($c[$vid])) {
                $c[$vid] = max(0, min(99, (int) $qtd));
            }
        }
        $this->carrinho->salvar($c);

        // Respeita o estoque disponível
        foreach ($this->carrinho->itens() as $i) {
            if ($i['quantidade'] > (int) $i['estoque']) {
                $c[(int) $i['id']] = max(0, (int) $i['estoque']);
                sn_flash('erro', 'Ajustamos ' . $i['produto'] . ' ' . $i['tamanho'] . ' para o estoque disponível.');
            }
        }
        $this->carrinho->salvar($c);

        sn_irpara(['controller' => 'carrinho']);
    }

    public function remover(): void
    {
        $this->exigirPost(['controller' => 'carrinho']);

        $c = $this->carrinho->conteudo();
        unset($c[(int) ($_POST['variacao_id'] ?? 0)]);
        $this->carrinho->salvar($c);

        sn_irpara(['controller' => 'carrinho']);
    }
}
