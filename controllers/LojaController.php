<?php

require_once __DIR__ . '/LojaBaseController.php';

/**
 * Vitrine da loja virtual (aberta ao público, sem login).
 *   index.php?controller=loja                       -> página inicial / cardápio
 *   index.php?controller=loja&categoria=ID          -> filtra por categoria
 *   index.php?controller=loja&q=texto               -> busca
 *   index.php?controller=loja&action=produto&id=ID  -> detalhes do produto
 */
class LojaController extends LojaBaseController
{
    private Vitrine $vitrine;

    public function __construct()
    {
        parent::__construct();
        $this->vitrine = new Vitrine();
    }

    public function index(): void
    {
        $categoria = max(0, (int) ($_GET['categoria'] ?? 0));
        $q = trim((string) ($_GET['q'] ?? ''));

        $this->tela('inicio', [
            'produtos'  => $this->vitrine->produtos($categoria, $q),
            'categoria' => $categoria,
            'q'         => $q,
        ]);
    }

    public function produto(): void
    {
        $id = max(0, (int) ($_GET['id'] ?? 0));
        $produto = $this->vitrine->produto($id);

        if (!$produto) {
            sn_flash('erro', 'Produto não encontrado.');
            sn_irpara(['controller' => 'loja']);
        }

        $this->tela('produto', [
            'produto'   => $produto,
            'variacoes' => $this->vitrine->tamanhos($id),
            'outros'    => $this->vitrine->outrosDaCategoria((int) $produto['categoria_id'], $id),
        ]);
    }

    /** Ilustração do produto (SVG gerado pelo sabor e ingredientes). Pública, com cache no navegador. */
    public function imagem(): void
    {
        $p = $this->vitrine->dadosParaIlustracao(max(0, (int) ($_GET['id'] ?? 0)));
        $svg = sn_milkshake_svg($p ?: ['id' => 0, 'nome' => 'Milkshake', 'descricao' => '']);

        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        header('Content-Type: image/svg+xml; charset=utf-8');
        header_remove('Pragma');
        header('Cache-Control: public, max-age=604800');   // 7 dias (o endereço muda quando o produto muda)
        header('X-Content-Type-Options: nosniff');
        echo $svg;
        exit;
    }
}
