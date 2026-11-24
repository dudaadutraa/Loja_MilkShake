<?php
/* Tela compartilhada pelos cadastros de Fornecedores e Clientes (PessoaController). */
require_once __DIR__ . '/_layout.php';
layout_topo($textos['titulo'], $rota);
?>
<div class="grade">
    <div class="card">
        <h2><?= $editar ? 'Editar ' . h($textos['singular']) . ' #' . (int) $editar['id'] : 'Novo ' . h($textos['singular']) ?></h2>
        <form method="post" action="<?= h(sn_url(['controller' => $rota, 'action' => 'salvar'])) ?>">
            <input type="hidden" name="csrf" value="<?= h($csrf) ?>">
            <input type="hidden" name="id" value="<?= (int) ($editar['id'] ?? 0) ?>">

            <label for="nome">Nome</label>
            <input class="input" id="nome" name="nome" required maxlength="100" value="<?= h($editar['nome'] ?? '') ?>">

            <label for="doc"><?= h($textos['doc']) ?></label>
            <input class="input" id="doc" name="doc" maxlength="20" <?= $textos['docObrig'] ? 'required' : '' ?> value="<?= h($editar[$campoDoc] ?? '') ?>">

            <label for="telefone">Telefone</label>
            <input class="input" id="telefone" name="telefone" maxlength="20" value="<?= h($editar['telefone'] ?? '') ?>">

            <label for="email">E-mail</label>
            <input class="input" id="email" name="email" type="email" maxlength="100" value="<?= h($editar['email'] ?? '') ?>">

            <label for="cep">CEP</label>
            <input class="input" id="cep" name="cep" maxlength="9" inputmode="numeric" placeholder="00000-000"
                   data-cep data-endereco="#endereco" data-status="#cepStatus"
                   data-url="<?= h(sn_url(['controller' => 'cep', 'action' => 'buscar'])) ?>&cep=" value="<?= h($editar['cep'] ?? '') ?>">
            <div class="muted" id="cepStatus" style="margin:-6px 0 10px" aria-live="polite">Digite o CEP para preencher o endereço.</div>

            <label for="endereco">Endereço</label>
            <input class="input" id="endereco" name="endereco" maxlength="255" value="<?= h($editar['endereco'] ?? '') ?>">

            <div class="acoes">
                <button class="btn btn-primario" type="submit">Salvar</button>
                <?php if ($editar): ?>
                    <a class="btn" href="<?= h(sn_url(['controller' => $rota])) ?>">Cancelar</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <div class="card">
        <form method="get" action="<?= h(BASE_URL . 'index.php') ?>" class="acoes" style="margin:0 0 16px">
            <input type="hidden" name="controller" value="<?= h($rota) ?>">
            <input class="input" name="q" placeholder="Buscar por nome, <?= h($textos['doc']) ?> ou e-mail" value="<?= h($q) ?>" style="flex:1">
            <button class="btn" type="submit">Buscar</button>
        </form>

        <?php if (!$lista): ?>
            <div class="vazio">Nenhum <?= h($textos['singular']) ?> encontrado.</div>
        <?php else: ?>
        <table>
            <thead><tr><th>Nome</th><th><?= h($textos['doc']) ?></th><th>Contato</th><th>Situação</th><th>Ações</th></tr></thead>
            <tbody>
            <?php foreach ($lista as $p): ?>
                <tr>
                    <td><strong><?= h($p['nome']) ?></strong><div class="muted"><?= h(trim(($p['endereco'] ?? '') . (!empty($p['cep']) ? ' · CEP ' . $p['cep'] : ''), ' ·')) ?></div></td>
                    <td><?= h($p[$campoDoc] ?: '-') ?></td>
                    <td><?= h($p['telefone'] ?? '') ?><div class="muted"><?= h($p['email'] ?? '') ?></div></td>
                    <td><span class="tag <?= (int) $p['ativo'] === 1 ? 'ok' : 'off' ?>"><?= (int) $p['ativo'] === 1 ? 'Ativo' : 'Inativo' ?></span></td>
                    <td>
                        <a class="btn" href="<?= h(sn_url(['controller' => $rota, 'id' => $p['id']])) ?>">Editar</a>
                        <?php if (sn_eh_admin()): ?>
                        <form class="inline" method="post" action="<?= h(sn_url(['controller' => $rota, 'action' => 'alternar'])) ?>">
                            <input type="hidden" name="csrf" value="<?= h($csrf) ?>">
                            <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                            <button class="btn" type="submit"><?= (int) $p['ativo'] === 1 ? 'Desativar' : 'Ativar' ?></button>
                        </form>
                        <form class="inline" method="post" action="<?= h(sn_url(['controller' => $rota, 'action' => 'excluir'])) ?>" onsubmit="return confirm('Excluir este registro?')">
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
<script src="<?= h(sn_asset('assets/js/cep.js')) ?>"></script>
<?php layout_rodape(); ?>
