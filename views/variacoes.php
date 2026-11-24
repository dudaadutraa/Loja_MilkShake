<?php
require_once __DIR__ . '/_layout.php';
layout_topo($produto ? 'Variações — ' . $produto['nome'] : 'Variações', 'variacao');

$filtro      = (int) ($produto['id'] ?? 0);
$produtoForm = (int) ($editar['produto_id'] ?? $filtro);
$urlLista    = sn_url(['controller' => 'variacao'] + ($filtro ? ['produto' => $filtro] : []));
?>
<?php if ($produto): ?>
<p><a href="<?= h(sn_url(['controller' => 'produto'])) ?>">&larr; Voltar para produtos</a>
   &nbsp;·&nbsp; <a href="<?= h(sn_url(['controller' => 'variacao'])) ?>">Ver variações de todos os produtos</a></p>

<div class="card">
    <h2><?= h($produto['nome']) ?> <span class="muted">— <?= h(trim($produto['categoria_nome'])) ?></span></h2>
    <div class="muted"><?= h(trim((string) $produto['descricao'])) ?></div>
</div>
<?php endif; ?>

<div class="grade">
    <div class="card">
        <h2><?= $editar ? 'Editar variação' : 'Nova variação' ?></h2>
        <?php if (!$produtos): ?>
            <p>Cadastre um <a href="<?= h(sn_url(['controller' => 'produto'])) ?>">produto</a> antes.</p>
        <?php else: ?>
        <form method="post" action="<?= h(sn_url(['controller' => 'variacao', 'action' => 'salvar'])) ?>">
            <input type="hidden" name="csrf" value="<?= h($csrf) ?>">
            <input type="hidden" name="id" value="<?= (int) ($editar['id'] ?? 0) ?>">
            <input type="hidden" name="filtro" value="<?= $filtro ?>">

            <label for="produto_id">Produto</label>
            <select class="input" id="produto_id" name="produto_id" required>
                <option value="">Selecione...</option>
                <?php foreach ($produtos as $p): ?>
                    <option value="<?= (int) $p['id'] ?>" <?= $produtoForm === (int) $p['id'] ? 'selected' : '' ?>>
                        <?= h($p['nome']) ?><?= $p['ativo'] ? '' : ' (inativo)' ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="tamanho">Tamanho</label>
            <input class="input" id="tamanho" name="tamanho" required maxlength="10" placeholder="Ex.: 300ml, 500ml, P, M, G" value="<?= h($editar['tamanho'] ?? '') ?>">

            <label for="preco">Preço de venda</label>
            <input class="input" id="preco" name="preco" required inputmode="decimal" placeholder="15,90" value="<?= isset($editar['preco']) ? h(number_format((float) $editar['preco'], 2, ',', '')) : '' ?>">

            <label for="minimo">Estoque mínimo (alerta)</label>
            <input class="input" id="minimo" name="minimo" type="number" min="0" value="<?= (int) ($editar['minimo'] ?? 5) ?>">

            <label for="sku">Código / SKU (opcional)</label>
            <input class="input" id="sku" name="sku" maxlength="50" placeholder="Gerado automaticamente" value="<?= h($editar['sku'] ?? '') ?>">

            <div class="acoes">
                <button class="btn btn-primario" type="submit">Salvar</button>
                <?php if ($editar): ?>
                    <a class="btn" href="<?= h($urlLista) ?>">Cancelar</a>
                <?php endif; ?>
            </div>
        </form>
        <?php endif; ?>
    </div>

    <div class="card">
        <h2><?= $produto ? 'Tamanhos deste produto' : 'Todas as variações' ?></h2>

        <?php if (!$produto && $produtos): ?>
        <form method="get" action="<?= h(BASE_URL . 'index.php') ?>" class="acoes" style="margin:0 0 16px">
            <input type="hidden" name="controller" value="variacao">
            <select class="input" name="produto" style="flex:1">
                <option value="">Filtrar por produto...</option>
                <?php foreach ($produtos as $p): ?>
                    <option value="<?= (int) $p['id'] ?>"><?= h($p['nome']) ?></option>
                <?php endforeach; ?>
            </select>
            <button class="btn" type="submit">Filtrar</button>
        </form>
        <?php endif; ?>

        <?php if (!$variacoes): ?>
            <div class="vazio">Nenhuma variação cadastrada. Cadastre ao menos um tamanho para poder vender o produto.</div>
        <?php else: ?>
        <table>
            <thead><tr><?php if (!$produto): ?><th>Produto</th><?php endif; ?><th>Tamanho</th><th>SKU</th><th>Preço</th><th>Estoque</th><th>Ações</th></tr></thead>
            <tbody>
            <?php foreach ($variacoes as $v): ?>
                <tr>
                    <?php if (!$produto): ?>
                        <td><a href="<?= h(sn_url(['controller' => 'variacao', 'produto' => $v['produto_id']])) ?>"><?= h($v['produto_nome']) ?></a><?= $v['produto_ativo'] ? '' : ' <span class="tag off">Inativo</span>' ?></td>
                    <?php endif; ?>
                    <td><strong><?= h($v['tamanho']) ?></strong></td>
                    <td class="muted"><?= h($v['sku']) ?></td>
                    <td><?= moeda($v['preco']) ?></td>
                    <td>
                        <span class="tag <?= (int) $v['quantidade'] <= (int) $v['minimo'] ? 'alerta' : 'ok' ?>"><?= (int) $v['quantidade'] ?></span>
                        <div class="muted">mín. <?= (int) $v['minimo'] ?></div>
                    </td>
                    <td>
                        <a class="btn" href="<?= h(sn_url(['controller' => 'variacao', 'id' => $v['id']] + ($filtro ? ['produto' => $filtro] : []))) ?>">Editar</a>
                        <form class="inline" method="post" action="<?= h(sn_url(['controller' => 'variacao', 'action' => 'excluir'])) ?>" onsubmit="return confirm('Excluir esta variação?')">
                            <input type="hidden" name="csrf" value="<?= h($csrf) ?>">
                            <input type="hidden" name="filtro" value="<?= $filtro ?>">
                            <input type="hidden" name="id" value="<?= (int) $v['id'] ?>">
                            <button class="btn btn-perigo" type="submit">Excluir</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>
<?php layout_rodape(); ?>
