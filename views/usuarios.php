<?php
require_once __DIR__ . '/_layout.php';
layout_topo('Usuários', 'usuario');
?>
<div class="grade">
    <div class="card">
        <h2><?= $editar ? 'Editar usuário #' . (int) $editar['id'] : 'Novo usuário' ?></h2>
        <form method="post" action="<?= h(sn_url(['controller' => 'usuario', 'action' => 'salvar'])) ?>" autocomplete="off">
            <input type="hidden" name="csrf" value="<?= h($csrf) ?>">
            <input type="hidden" name="id" value="<?= (int) ($editar['id'] ?? 0) ?>">

            <label for="nome">Nome</label>
            <input class="input" id="nome" name="nome" required maxlength="100" value="<?= h($editar['nome'] ?? '') ?>">

            <label for="email">E-mail (login)</label>
            <input class="input" id="email" name="email" type="email" required maxlength="100" value="<?= h($editar['email'] ?? '') ?>">

            <label for="senha"><?= $editar ? 'Nova senha (deixe em branco para manter)' : 'Senha' ?></label>
            <input class="input" id="senha" name="senha" type="password" minlength="6" autocomplete="new-password" <?= $editar ? '' : 'required' ?>>

            <label for="perfil">Perfil</label>
            <select class="input" id="perfil" name="perfil">
                <option value="vendedor" <?= ($editar['perfil'] ?? '') === 'vendedor' ? 'selected' : '' ?>>Vendedor</option>
                <option value="admin" <?= ($editar['perfil'] ?? '') === 'admin' ? 'selected' : '' ?>>Administrador</option>
            </select>

            <div class="acoes">
                <button class="btn btn-primario" type="submit">Salvar</button>
                <?php if ($editar): ?>
                    <a class="btn" href="<?= h(sn_url(['controller' => 'usuario'])) ?>">Cancelar</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <div class="card">
        <h2>Usuários cadastrados</h2>
        <table>
            <thead><tr><th>Nome</th><th>E-mail</th><th>Perfil</th><th>Situação</th><th>Ações</th></tr></thead>
            <tbody>
            <?php foreach ($usuarios as $u): ?>
                <tr>
                    <td><strong><?= h($u['nome']) ?></strong></td>
                    <td><?= h($u['email']) ?></td>
                    <td><?= $u['perfil'] === 'admin' ? 'Administrador' : 'Vendedor' ?></td>
                    <td><span class="tag <?= $u['ativo'] ? 'ok' : 'off' ?>"><?= $u['ativo'] ? 'Ativo' : 'Inativo' ?></span></td>
                    <td>
                        <a class="btn" href="<?= h(sn_url(['controller' => 'usuario', 'id' => $u['id']])) ?>">Editar</a>
                        <?php if ((int) $u['id'] !== (int) $_SESSION['usuario_id']): ?>
                        <form class="inline" method="post" action="<?= h(sn_url(['controller' => 'usuario', 'action' => 'alternar'])) ?>">
                            <input type="hidden" name="csrf" value="<?= h($csrf) ?>">
                            <input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
                            <button class="btn" type="submit"><?= $u['ativo'] ? 'Desativar' : 'Ativar' ?></button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php layout_rodape(); ?>
