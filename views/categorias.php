<?php
require_once __DIR__ . '/_layout.php';
layout_topo('Categorias', 'categoria');
?>
<div class="grade">
    <div class="card">
        <h2><?= $editar ? 'Editar categoria #' . (int) $editar['id'] : 'Nova categoria' ?></h2>
        <form method="post" action="<?= h(sn_url(['controller' => 'categoria', 'action' => 'salvar'])) ?>">
            <input type="hidden" name="csrf" value="<?= h($csrf) ?>">
            <input type="hidden" name="id" value="<?= (int) ($editar['id'] ?? 0) ?>">
            <label for="nome">Nome</label>
            <input class="input" id="nome" name="nome" required maxlength="100" value="<?= h($editar['nome'] ?? '') ?>">
            <div class="acoes">
                <button class="btn btn-primario" type="submit">Salvar</button>
                <?php if ($editar): ?>
                    <a class="btn" href="<?= h(sn_url(['controller' => 'categoria'])) ?>">Cancelar</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <div class="card">
        <h2>Categorias cadastradas</h2>
        <?php if (!$categorias): ?>
            <div class="vazio">Nenhuma categoria cadastrada.</div>
        <?php else: ?>
        <table>
            <thead><tr><th>#</th><th>Nome</th><th>Produtos</th><th>Situação</th><th>Ações</th></tr></thead>
            <tbody>
            <?php foreach ($categorias as $c): ?>
                <tr>
                    <td><?= (int) $c['id'] ?></td>
                    <td><?= h(trim($c['nome'])) ?></td>
                    <td><?= (int) $c['qtd_produtos'] ?></td>
                    <td><span class="tag <?= $c['ativo'] ? 'ok' : 'off' ?>"><?= $c['ativo'] ? 'Ativa' : 'Inativa' ?></span></td>
                    <td>
                        <a class="btn" href="<?= h(sn_url(['controller' => 'categoria', 'id' => $c['id']])) ?>">Editar</a>
                        <form class="inline" method="post" action="<?= h(sn_url(['controller' => 'categoria', 'action' => 'alternar'])) ?>">
                            <input type="hidden" name="csrf" value="<?= h($csrf) ?>">
                            <input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
                            <button class="btn" type="submit"><?= $c['ativo'] ? 'Desativar' : 'Ativar' ?></button>
                        </form>
                        <?php if ((int) $c['qtd_produtos'] === 0): ?>
                        <form class="inline" method="post" action="<?= h(sn_url(['controller' => 'categoria', 'action' => 'excluir'])) ?>" onsubmit="return confirm('Excluir esta categoria?')">
                            <input type="hidden" name="csrf" value="<?= h($csrf) ?>">
                            <input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
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
