<?php
/**
 * Imagem do produto no site: a foto enviada pelo painel ou, sem foto,
 * a ilustração do milkshake gerada pelo sabor e ingredientes (helpers/ilustracao_produto.php).
 * Uso: echo loja_foto($produto, $esgotado);   // $produto com id, nome, descricao, imagem
 */
if (!function_exists('loja_foto')) {
    function loja_foto(array $p, bool $esgotado = false): string
    {
        return '<div class="foto">'
            . ($esgotado ? '<span class="esg">Esgotado</span>' : '')
            . '<img src="' . h(sn_produto_img_url($p)) . '" alt="' . h($p['nome'] ?? '') . '" loading="lazy" width="400" height="400">'
            . '</div>';
    }
}
