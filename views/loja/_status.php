<?php
/** Rótulos de situação do pedido e do pagamento (usados na área do cliente). */
if (!function_exists('loja_status_pedido')) {
    function loja_status_pedido(array $p): string
    {
        if ($p['status'] === 'cancelada') {
            return '<span class="etiqueta off">Cancelado</span>';
        }
        if ($p['status'] === 'finalizada') {
            return '<span class="etiqueta ok">' . (($p['entrega'] ?? '') === 'entrega' ? 'Entregue' : 'Retirado') . '</span>';
        }
        return '<span class="etiqueta alerta">Em preparo</span>';
    }

    function loja_status_pagamento(?string $metodo, ?string $status): string
    {
        $nomes = ['pix' => 'Pix', 'cartao' => 'Cartão', 'dinheiro' => 'Dinheiro'];
        $nome = $nomes[$metodo] ?? 'Na loja';
        $rot = [
            'aprovado'   => ['ok', 'pago'],
            'pendente'   => ['alerta', 'aguardando'],
            'na_entrega' => ['off', 'pagar no recebimento'],
        ][$status] ?? ['off', ''];
        return h($nome) . ($rot[1] !== '' ? ' <span class="etiqueta ' . $rot[0] . '">' . $rot[1] . '</span>' : '');
    }
}
