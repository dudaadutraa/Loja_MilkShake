<?php
require_once __DIR__ . '/_layout.php';
layout_topo('Nova entrada', 'entrada');
?>
<p><a href="<?= h(sn_url(['controller' => 'entrada'])) ?>">&larr; Voltar para entradas</a></p>

<?php if (!$fornecedores || !$variacoes): ?>
    <div class="card vazio">
        Para registrar uma entrada é preciso ter
        <?php if (!$fornecedores): ?><a href="<?= h(sn_url(['controller' => 'fornecedor'])) ?>">um fornecedor cadastrado</a><?php endif; ?>
        <?php if (!$fornecedores && !$variacoes): ?> e <?php endif; ?>
        <?php if (!$variacoes): ?><a href="<?= h(sn_url(['controller' => 'variacao'])) ?>">produtos com tamanho e preço</a><?php endif; ?>.
    </div>
<?php else: ?>
<form method="post" action="<?= h(sn_url(['controller' => 'entrada', 'action' => 'salvar'])) ?>" class="card">
    <input type="hidden" name="csrf" value="<?= h($csrf) ?>">

    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:0 16px">
        <div>
            <label for="fornecedor_id">Fornecedor</label>
            <select class="input" id="fornecedor_id" name="fornecedor_id" required>
                <option value="">Selecione...</option>
                <?php foreach ($fornecedores as $f): ?>
                    <option value="<?= (int) $f['id'] ?>"><?= h($f['nome']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label for="data">Data</label>
            <input class="input" type="date" id="data" name="data" value="<?= date('Y-m-d') ?>" required>
        </div>
        <div>
            <label for="nf_numero">Nº da nota (opcional)</label>
            <input class="input" id="nf_numero" name="nf_numero" maxlength="10">
        </div>
        <div>
            <label for="nf_serie">Série</label>
            <input class="input" id="nf_serie" name="nf_serie" maxlength="5" placeholder="1">
        </div>
        <div style="grid-column:1/-1">
            <label for="nf_chave">Chave de acesso (44 dígitos, opcional)</label>
            <input class="input" id="nf_chave" name="nf_chave" maxlength="54">
        </div>
    </div>

    <h2 style="margin-top:24px">Itens recebidos</h2>
    <?php $comCusto = true; require __DIR__ . '/_itens.php'; ?>

    <div class="acoes">
        <button class="btn btn-primario" type="submit">Confirmar entrada</button>
    </div>
    <p class="muted">Ao confirmar, as quantidades são somadas ao estoque.</p>
</form>
<?php endif; ?>
<?php layout_rodape(); ?>
