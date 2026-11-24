<?php
require_once __DIR__ . '/_layout.php';
layout_topo('Produtos', 'produto');
?>
<div class="grade">
    <div class="card">
        <h2><?= $editar ? 'Editar produto #' . (int) $editar['id'] : 'Novo produto' ?></h2>
        <?php if (!$categorias): ?>
            <p>Cadastre uma <a href="<?= h(sn_url(['controller' => 'categoria'])) ?>">categoria</a> antes.</p>
        <?php else: ?>
        <form method="post" enctype="multipart/form-data" action="<?= h(sn_url(['controller' => 'produto', 'action' => 'salvar'])) ?>">
            <input type="hidden" name="csrf" value="<?= h($csrf) ?>">
            <input type="hidden" name="id" value="<?= (int) ($editar['id'] ?? 0) ?>">

            <label for="categoria_id">Categoria</label>
            <select class="input" id="categoria_id" name="categoria_id" required>
                <option value="">Selecione...</option>
                <?php foreach ($categorias as $c): ?>
                    <option value="<?= (int) $c['id'] ?>" <?= (int) ($editar['categoria_id'] ?? 0) === (int) $c['id'] ? 'selected' : '' ?>>
                        <?= h(trim($c['nome'])) ?><?= $c['ativo'] ? '' : ' (inativa)' ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="nome">Nome</label>
            <input class="input" id="nome" name="nome" required maxlength="100" value="<?= h($editar['nome'] ?? '') ?>">

            <label for="descricao">Descrição / ingredientes</label>
            <textarea class="input" id="descricao" name="descricao" rows="3"><?= h($editar['descricao'] ?? '') ?></textarea>

            <label for="foto">Foto do produto (opcional)</label>
            <?php if ($editar): ?>
                <div style="display:flex;gap:12px;align-items:center;margin-bottom:8px">
                    <img src="<?= h(sn_produto_img_url($editar)) ?>" alt="" style="width:84px;height:84px;border-radius:14px;object-fit:cover;background:#fff">
                    <span class="muted"><?= strpos(sn_produto_img_url($editar), 'uploads/') !== false ? 'Usando foto.' : 'Sem foto: usando a ilustração gerada pelo sabor e ingredientes.' ?></span>
                </div>
            <?php endif; ?>
            <input class="input" type="file" id="foto" name="foto" accept="image/jpeg,image/png,image/webp">
            <div class="muted" style="margin:-6px 0 10px">JPG, PNG ou WEBP até 3 MB, de preferência quadrada. Sem foto, o sistema desenha o milkshake pelo nome e pela descrição.</div>
            <?php if (!empty($editar['imagem'])): ?>
                <label style="display:flex;gap:8px;align-items:center;font-weight:400"><input type="checkbox" name="remover_foto" value="1"> Remover a foto e voltar para a ilustração</label>
            <?php endif; ?>

            <div class="acoes">
                <button class="btn btn-primario" type="submit">Salvar</button>
                <?php if ($editar): ?>
                    <a class="btn" href="<?= h(sn_url(['controller' => 'produto'])) ?>">Cancelar</a>
                <?php endif; ?>
            </div>
        </form>
        <?php endif; ?>
    </div>

    <div class="card">
        <form method="get" action="<?= h(BASE_URL . 'index.php') ?>" class="acoes" style="margin:0 0 16px">
            <input type="hidden" name="controller" value="produto">
            <input class="input" name="q" placeholder="Buscar produto ou categoria" value="<?= h($q) ?>" style="flex:1">
            <button class="btn" type="submit">Buscar</button>
        </form>

        <?php if (!$produtos): ?>
            <div class="vazio">Nenhum produto encontrado.</div>
        <?php else: ?>
        <table>
            <thead><tr><th style="width:64px"></th><th>Produto</th><th>Categoria</th><th>Preço</th><th>Variações</th><th>Estoque</th><th>Situação</th><th>Ações</th></tr></thead>
            <tbody>
            <?php foreach ($produtos as $p): ?>
                <tr>
                    <td><img class="thumb" src="<?= h(sn_produto_img_url($p)) ?>" alt="" loading="lazy" style="width:56px;height:56px"></td>
                    <td><strong><?= h($p['nome']) ?></strong><div class="muted"><?= h(trim((string) $p['descricao'])) ?></div></td>
                    <td><?= h(trim($p['categoria_nome'])) ?></td>
                    <td>
                        <?php if ((int) $p['qtd_variacoes'] === 0): ?>
                            <span class="tag alerta">Sem preço</span>
                        <?php elseif ($p['preco_min'] == $p['preco_max']): ?>
                            <?= moeda($p['preco_min']) ?>
                        <?php else: ?>
                            <?= moeda($p['preco_min']) ?> a <?= moeda($p['preco_max']) ?>
                        <?php endif; ?>
                    </td>
                    <td><?= (int) $p['qtd_variacoes'] ?></td>
                    <td><?= (int) $p['estoque_total'] ?></td>
                    <td><span class="tag <?= $p['ativo'] ? 'ok' : 'off' ?>"><?= $p['ativo'] ? 'Ativo' : 'Inativo' ?></span></td>
                    <td>
                        <a class="btn btn-ok" href="<?= h(sn_url(['controller' => 'variacao', 'produto' => $p['id']])) ?>">Variações</a>
                        <a class="btn" href="<?= h(sn_url(['controller' => 'produto', 'id' => $p['id']])) ?>">Editar</a>
                        <form class="inline" method="post" action="<?= h(sn_url(['controller' => 'produto', 'action' => 'alternar'])) ?>">
                            <input type="hidden" name="csrf" value="<?= h($csrf) ?>">
                            <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                            <button class="btn" type="submit"><?= $p['ativo'] ? 'Desativar' : 'Ativar' ?></button>
                        </form>
                        <?php if ((int) $p['qtd_variacoes'] === 0): ?>
                        <form class="inline" method="post" action="<?= h(sn_url(['controller' => 'produto', 'action' => 'excluir'])) ?>" onsubmit="return confirm('Excluir este produto?')">
                            <input type="hidden" name="csrf" value="<?= h($csrf) ?>">
                            <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                            <button class="btn btn-perigo" type="submit">Excluir</button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>
<?php layout_rodape(); ?>
