<?php
$nomeCategoria = '';
foreach ($categoriasMenu as $c) {
    if ((int) $c['id'] === $categoria) { $nomeCategoria = trim($c['nome']); }
}
$tituloPagina = $q !== '' ? 'Busca: ' . $q : ($nomeCategoria ?: 'Cardápio');
require __DIR__ . '/_topo.php';
require_once __DIR__ . '/_icone.php';

$filtrando = $q !== '' || $categoria > 0;

// produtos agrupados por categoria (o SQL já vem ordenado por categoria)
$grupos = [];
foreach ($produtos as $p) {
    $grupos[(int) $p['categoria_id']]['nome'] = trim($p['categoria']);
    $grupos[(int) $p['categoria_id']]['itens'][] = $p;
}

// frase curta de cada grupo, pelo nome da categoria
$frase = function (string $nome): string {
    $n = sn_sem_acento($nome);
    if (strpos($n, 'fit') !== false)     { return 'Com whey e sem exagero no açúcar, para antes ou depois do treino.'; }
    if (strpos($n, 'natura') !== false)  { return 'Fruta, leite e mel. O básico bem feito.'; }
    if (strpos($n, 'mix') !== false)     { return 'Frutas combinadas e batidas na hora.'; }
    if (strpos($n, 'especia') !== false) { return 'Sorvete, calda e cobertura caprichada.'; }
    return '';
};

// até 3 fotos para o topo (uma de cada categoria, só produtos com foto)
$destaques = [];
foreach ($grupos as $g) {
    foreach ($g['itens'] as $p) {
        if ((int) $p['estoque'] > 0 && strpos(sn_produto_img_url($p), 'uploads/') !== false) { $destaques[] = $p; break; }
    }
    if (count($destaques) === 3) { break; }
}
?>

<?php if (!$filtrando): ?>
<section class="abre">
  <div class="wrap abre-grade">
    <div class="abre-texto">
      <p class="sobre">Shake Nature</p>
      <h1>Shake gelado,<br>do jeito que você gosta.</h1>
      <p><?= count($produtos) ?> sabores entre <?= h(implode(', ', array_map(fn ($g) => mb_strtolower($g['nome']), array_slice(array_values($grupos), 0, -1)))) ?><?= count($grupos) > 1 ? ' e ' . h(mb_strtolower(end($grupos)['nome'])) : '' ?>. Peça aqui e retire na loja ou receba em casa.</p>
      <div class="abre-acoes">
        <a class="btn" href="#cardapio">Ver o cardápio</a>
        <a class="btn ghost" href="#como-funciona">Como funciona</a>
      </div>
    </div>
    <?php if ($destaques): ?>
    <div class="abre-fotos n<?= count($destaques) ?>" aria-hidden="true">
      <?php foreach ($destaques as $k => $d): ?>
        <img class="f<?= $k ?>" src="<?= h(sn_produto_img_url($d)) ?>" alt="">
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</section>
<?php endif; ?>

<div class="barra" id="cardapio">
  <div class="wrap barra-in">
    <nav class="chips" aria-label="Categorias">
      <?php if ($filtrando): ?>
        <a href="<?= h(sn_url(['controller' => 'loja'])) ?>#cardapio">← Todo o cardápio</a>
      <?php endif; ?>
      <?php foreach ($categoriasMenu as $c): ?>
        <a href="<?= $filtrando ? h(sn_url(['controller' => 'loja', 'categoria' => $c['id']])) : '#cat-' . (int) $c['id'] ?>"
           class="<?= $categoria === (int) $c['id'] ? 'ativo' : '' ?>"><?= h(trim($c['nome'])) ?></a>
      <?php endforeach; ?>
    </nav>
    <form class="procura" method="get" action="<?= h(BASE_URL . 'index.php') ?>" role="search">
      <input type="hidden" name="controller" value="loja">
      <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M20 20l-4-4"/></svg>
      <label class="sr" for="q">Buscar no cardápio</label>
      <input id="q" type="search" name="q" maxlength="80" placeholder="Buscar sabor ou ingrediente" value="<?= h($q) ?>" autocomplete="off">
    </form>
  </div>
</div>

<div class="wrap">
  <?php if ($q !== ''): ?>
    <p class="resultado"><?= count($produtos) ?> resultado<?= count($produtos) === 1 ? '' : 's' ?> para “<?= h($q) ?>”</p>
  <?php endif; ?>

  <?php if (!$produtos): ?>
    <div class="nada">
      <p><strong>Nenhum shake encontrado.</strong></p>
      <p class="muted">Tente outro sabor ou ingrediente.</p>
      <a class="btn" href="<?= h(sn_url(['controller' => 'loja'])) ?>">Ver todo o cardápio</a>
    </div>
  <?php endif; ?>

  <?php foreach ($grupos as $cid => $g): ?>
  <section class="grupo" id="cat-<?= (int) $cid ?>">
    <header class="grupo-topo">
      <h2><?= h($g['nome']) ?></h2>
      <?php if ($frase($g['nome']) !== ''): ?><p><?= h($frase($g['nome'])) ?></p><?php endif; ?>
    </header>
    <div class="shakes">
      <?php foreach ($g['itens'] as $p): $sem = (int) $p['estoque'] <= 0; ?>
        <a class="shake <?= $sem ? 'sem' : '' ?>" href="<?= h(sn_url(['controller' => 'loja', 'action' => 'produto', 'id' => $p['id']])) ?>">
          <?= loja_foto($p, $sem) ?>
          <div class="shake-info">
            <h3><?= h($p['nome']) ?></h3>
            <?php if (trim((string) $p['descricao']) !== ''): ?><p class="ingr"><?= h($p['descricao']) ?></p><?php endif; ?>
            <div class="shake-pe">
              <span class="valor">
                <?php if ($p['preco_min'] != $p['preco_max']): ?><small>a partir de</small><?php endif; ?>
                <?= moeda($p['preco_min']) ?>
              </span>
              <span class="mais" aria-hidden="true"><?= $sem ? '–' : '+' ?></span>
            </div>
            <span class="tams"><?= h($p['tamanhos']) ?></span>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endforeach; ?>
</div>

<?php if (!$filtrando): ?>
<section class="como" id="como-funciona">
  <div class="wrap">
    <h2>Como funciona</h2>
    <ol>
      <li><b>1</b><div><strong>Escolha o shake e o tamanho</strong><span>Monte o pedido com quantos quiser.</span></div></li>
      <li><b>2</b><div><strong>Pague pelo site</strong><span>Pix, cartão ou dinheiro na hora de receber.</span></div></li>
      <li><b>3</b><div><strong>Retire ou receba em casa</strong><span>A gente avisa pelo WhatsApp quando estiver pronto.</span></div></li>
    </ol>
  </div>
</section>
<?php endif; ?>
<?php require __DIR__ . '/_rodape.php'; ?>
