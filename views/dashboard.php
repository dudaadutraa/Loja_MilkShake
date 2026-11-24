<?php
/**
 * SHAKE NATURE - DASHBOARD
 * Variáveis recebidas do DashboardController:
 *   $usuarioNome, $numeros, $grafico, $melhor, $ultimas, $baixoLista, $falhas
 */

$num = function ($n) {
    return number_format((float) $n, 0, ',', '.');
};

$dados        = $numeros;
$primeiroNome = trim(explode(' ', trim((string) $usuarioNome))[0] ?? '');
$variacao     = $dados['variacao'];
$flash        = sn_pegar_flash();
$pagina       = 'dashboard';
?><!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Shake Nature · Dashboard</title>
<link rel="icon" type="image/png" href="<?= h(sn_asset('assets/img/logo.png')) ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&family=Pacifico&display=swap" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<style>
:root{
  --bg:#fdf3ea; --card:#fff9f4; --line:#f6e4d4; --ink:#3a2a20; --muted:#8a7565;
  --orange:#f28c28; --orange-deep:#e8762b; --peach:#fde3c3; --salmon:#fbd0c0; --coral:#f06a42;
  --sidebar:#fff7f0;
}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'DM Sans',system-ui,sans-serif;background:var(--bg);color:var(--ink);display:flex;min-height:100vh}
a{color:inherit;text-decoration:none}

/* Sidebar */
.sidebar{width:260px;flex-shrink:0;background:var(--sidebar);border-right:1px solid var(--line);
  display:flex;flex-direction:column;padding:28px 16px 0;position:sticky;top:0;height:100vh;overflow-y:auto}
.brand{text-align:center;margin-bottom:28px}
.brand img{display:block;width:84px;height:84px;min-width:84px;object-fit:contain;background:#fff;margin:0 auto 10px;border-radius:50%;box-shadow:0 6px 18px rgba(214,170,110,.25)}
.brand b{display:block;font-size:34px;font-weight:700;color:var(--orange);letter-spacing:.5px;line-height:1}
.brand i{display:block;font-family:'Pacifico',cursive;font-style:normal;font-size:22px;color:var(--orange-deep);margin-top:-2px}
.nav a{display:flex;align-items:center;gap:16px;padding:12px 16px;margin-bottom:4px;border-radius:14px;color:#5d4a3d;font-weight:500;font-size:16px;transition:background .15s}
.nav a:hover{background:var(--peach)}
.nav a.ativo{background:var(--peach);color:var(--orange-deep)}
.nav svg,.sair svg{width:22px;height:22px;stroke:currentColor;fill:none;stroke-width:1.7;stroke-linecap:round;stroke-linejoin:round;flex-shrink:0}
.sair{margin-top:18px;padding-top:18px;border-top:1px solid var(--line)}
.sair a{display:flex;align-items:center;gap:14px;padding:10px 16px;color:#5d4a3d;font-weight:500}
.onda{margin-top:auto;margin-left:-16px;margin-right:-16px;height:90px;flex-shrink:0;
  background:radial-gradient(120% 100% at 30% 100%,#fcd9ae 0 45%,transparent 46%),radial-gradient(100% 100% at 90% 110%,#fde7cc 0 50%,transparent 51%)}

/* Conteúdo */
main{flex:1;min-width:0;padding:32px 24px 40px}
.topo{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:28px;gap:16px}
.topo h1{font-size:30px;font-weight:700}
.topo p{color:var(--muted);margin-top:4px}
.acoes{display:flex;align-items:center;gap:22px}
.sino{position:relative;width:28px;height:28px;color:var(--ink)}
.sino svg{width:28px;height:28px;stroke:currentColor;fill:none;stroke-width:1.6;stroke-linecap:round;stroke-linejoin:round}
.sino.alerta::after{content:"";position:absolute;top:-2px;right:0;width:10px;height:10px;border-radius:50%;background:var(--coral)}
.avatar{width:60px;height:60px;border-radius:50%;background:var(--peach);display:grid;place-items:center;color:var(--orange-deep);font-size:24px;font-weight:700}
.avatar svg{width:30px;height:30px;stroke:currentColor;fill:none;stroke-width:1.7;stroke-linecap:round}

.cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(230px,1fr));gap:16px;margin-bottom:24px}
.card{background:var(--card);border:1px solid var(--line);border-radius:22px;padding:18px 20px;display:flex;align-items:center;gap:18px;min-width:0}
.icone{width:96px;height:96px;border-radius:50%;display:grid;place-items:center;flex-shrink:0}
.icone svg{width:52px;height:52px;stroke:currentColor;fill:none;stroke-width:1.6;stroke-linecap:round;stroke-linejoin:round}
.i-amarelo{background:var(--peach);color:var(--orange)}
.i-salmao{background:var(--salmon);color:var(--coral)}
.card small{display:block;color:#5d4a3d;font-size:17px}
.card strong{display:block;font-size:32px;font-weight:700;margin-top:4px;white-space:nowrap}
.card strong.menor{font-size:26px}

.painel{background:var(--card);border:1px solid var(--line);border-radius:22px;padding:22px 24px}
.painel-topo{display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;gap:12px}
.painel-topo h2{display:flex;align-items:center;gap:14px;font-size:22px;font-weight:500}
.painel-topo h2 svg{width:40px;height:40px;stroke:var(--orange);fill:none;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round}
select{font:inherit;color:#5d4a3d;background:var(--card);border:1px solid var(--line);border-radius:999px;padding:10px 18px;cursor:pointer}
.grafico{position:relative;height:340px}
.aviso{background:#fde8e0;border:1px solid #f6c7b6;color:#9a3d1f;padding:12px 16px;border-radius:14px;margin-bottom:20px}
.aviso.ok{background:#e6f4e4;border-color:#c5e3c0;color:#2e6b2a}

.sub{display:block;margin-top:6px;font-size:13px;color:var(--muted)}
.sub.up{color:#3f8f4f}.sub.down{color:var(--coral)}.sub.warn{color:var(--coral)}
.resumo{display:flex;flex-wrap:wrap;gap:12px;margin-bottom:14px}
.resumo div{background:var(--bg);border:1px solid var(--line);border-radius:14px;padding:10px 16px;min-width:150px}
.resumo small{display:block;color:var(--muted);font-size:13px}
.resumo b{font-size:18px}
.duas{display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:16px;margin-top:24px}
.painel h3{font-size:18px;font-weight:500;margin-bottom:12px;display:flex;justify-content:space-between;align-items:center}
.painel h3 a{font-size:14px;color:var(--orange-deep)}
.lista{list-style:none}
.lista li{display:flex;justify-content:space-between;align-items:center;gap:12px;padding:11px 0;border-bottom:1px solid var(--line)}
.lista li:last-child{border-bottom:0}
.lista small{color:var(--muted);display:block}
.lista .val{font-weight:700;white-space:nowrap}
.selo{background:var(--salmon);color:var(--coral);border-radius:999px;padding:3px 12px;font-weight:700;font-size:14px;white-space:nowrap}
.vazio{color:var(--muted);padding:14px 0}
.debug{background:#fff;border:1px dashed var(--orange);border-radius:14px;padding:14px 18px;margin-bottom:20px;font-size:14px;overflow-x:auto}
.debug code{background:var(--peach);padding:1px 6px;border-radius:6px}

@media (max-width:860px){
  body{flex-direction:column}
  .sidebar{width:100%;height:auto;position:static;padding:16px}
  .brand{margin-bottom:12px}.onda{display:none}
  .nav{display:flex;flex-wrap:wrap;gap:4px}.nav a{padding:10px 14px;margin:0}
  .sair{margin-top:8px;padding-top:8px}
  .icone{width:72px;height:72px}.icone svg{width:38px;height:38px}
  .topo h1{font-size:24px}
}
</style>
</head>
<body>

<aside class="sidebar">
  <a class="brand" href="<?= h(sn_url_dashboard()) ?>">
    <img src="<?= h(sn_asset('assets/img/logo.png')) ?>" alt="Shake Nature">
    <b>SHAKE</b><i>nature</i>
  </a>

  <nav class="nav">
    <?php foreach (sn_menu() as $chave => [$rotulo, $link, $icone]): ?>
      <a href="<?= h($link) ?>" class="<?= $chave === $pagina ? 'ativo' : '' ?>"<?= $chave === 'site' ? ' target="_blank" rel="noopener"' : '' ?>>
        <svg viewBox="0 0 24 24"><?= $icone ?></svg><?= h($rotulo) ?>
      </a>
    <?php endforeach; ?>
  </nav>

  <div class="sair">
    <a href="<?= h(sn_url_sair()) ?>">
      <svg viewBox="0 0 24 24"><path d="M9 4H5a1 1 0 0 0-1 1v14a1 1 0 0 0 1 1h4M16 8l4 4-4 4M20 12H9"/></svg>Sair
    </a>
  </div>
  <div class="onda"></div>
</aside>

<main>
  <header class="topo">
    <div>
      <h1>Olá, <?= $primeiroNome !== '' ? h($primeiroNome) : 'seja bem-vindo(a)' ?>!</h1>
      <p>Aqui está um resumo do seu negócio.</p>
    </div>
    <div class="acoes">
      <a class="sino<?= $dados['baixo'] ? ' alerta' : '' ?>"
         href="<?= h(sn_url(['controller' => 'estoque', 'action' => 'index', 'filtro' => 'baixo'])) ?>"
         title="<?= $dados['baixo'] ? h($num($dados['baixo']) . ' itens com estoque baixo') : 'Nenhum alerta' ?>">
        <svg viewBox="0 0 24 24"><path d="M6 16V11a6 6 0 0 1 12 0v5l2 2H4zM10 20a2 2 0 0 0 4 0"/></svg>
      </a>
      <span class="avatar" title="<?= h($usuarioNome) ?>">
        <?php if ($primeiroNome !== ''): ?>
          <?= h(mb_strtoupper(mb_substr($primeiroNome, 0, 1))) ?>
        <?php else: ?>
          <svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4.4 3.6-7 8-7s8 2.6 8 7z"/></svg>
        <?php endif; ?>
      </span>
    </div>
  </header>

  <?php if ($flash): ?>
    <div class="aviso <?= $flash['tipo'] === 'ok' ? 'ok' : '' ?>"><?= h($flash['msg']) ?></div>
  <?php endif; ?>

  <?php if ($falhas): ?>
    <div class="aviso">
      Algumas informações não puderam ser carregadas (<?= h(implode(', ', array_keys($falhas))) ?>).
      <?php if (!DEBUG): ?>Abra <a href="<?= h(sn_url(['controller' => 'dashboard', 'action' => 'index', 'debug' => 1])) ?>"><u>o modo debug</u></a> para ver os detalhes.<?php endif; ?>
    </div>
    <?php if (DEBUG): ?>
      <div class="debug">
        <b>Erros das consultas</b>
        <ul><?php foreach ($falhas as $k => $m): ?><li><code><?= h($k) ?></code>: <?= h($m) ?></li><?php endforeach; ?></ul>
      </div>
    <?php endif; ?>
  <?php endif; ?>

  <section class="cards">
    <?php
    // Cartão com link só se o usuário pode abrir a área (o vendedor não abre Produtos, Entradas e Fornecedores)
    $linkCard = function (string $controller): string {
        return (sn_eh_admin() || !in_array($controller, sn_so_admin(), true))
            ? ' href="' . h(sn_url(['controller' => $controller, 'action' => 'index'])) . '"'
            : '';
    };
    ?>
    <a class="card"<?= $linkCard('produto') ?>>
      <div class="icone i-amarelo"><svg viewBox="0 0 24 24"><path d="M12 3 3 7.5v9L12 21l9-4.5v-9z"/><path d="M3 7.5 12 12l9-4.5M12 12v9"/></svg></div>
      <div><small>Produtos</small><strong><?= $num($dados['produtos']) ?></strong>
        <span class="sub <?= $dados['baixo'] ? 'warn' : '' ?>"><?= isset($falhas['estoque baixo']) ? '—' : ($dados['baixo'] ? $num($dados['baixo']) . ' com estoque baixo' : 'Estoque em dia') ?></span></div>
    </a>
    <a class="card"<?= $linkCard('venda') ?>>
      <div class="icone i-salmao"><svg viewBox="0 0 24 24"><path d="M5 8h14l-1 12H6z"/><path d="M9 8V6a3 3 0 0 1 6 0v2"/></svg></div>
      <div><small>Vendas</small><strong class="menor"><?= moeda($dados['vendas']) ?></strong>
        <span class="sub">Hoje: <?= moeda($dados['hoje']) ?></span></div>
    </a>
    <a class="card"<?= $linkCard('entrada') ?>>
      <div class="icone i-amarelo"><svg viewBox="0 0 24 24"><path d="M12 3v12M7 10l5 5 5-5"/><path d="M4 17v3h16v-3"/></svg></div>
      <div><small>Entradas</small><strong><?= $num($dados['entradas']) ?></strong>
        <span class="sub">Este mês: <?= moeda($dados['entradas_mes']) ?></span></div>
    </a>
    <a class="card"<?= $linkCard('fornecedor') ?>>
      <div class="icone i-salmao"><svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="3"/><circle cx="5.5" cy="10" r="2.2"/><circle cx="18.5" cy="10" r="2.2"/><path d="M6.5 20c0-3.3 2.5-5.5 5.5-5.5s5.5 2.2 5.5 5.5M1.5 18c0-2 1.5-3.5 4-3.5M22.5 18c0-2-1.5-3.5-4-3.5"/></svg></div>
      <div><small>Fornecedores</small><strong><?= $num($dados['fornecedores']) ?></strong>
        <span class="sub">Cadastrados</span></div>
    </a>
  </section>

  <section class="painel">
    <div class="painel-topo">
      <h2>
        <svg viewBox="0 0 40 40"><path d="M4 36v-8h6v8M15 36V22h6v14M26 36V16h6v20M6 18l10-8 7 5 14-11M30 4h7v7"/></svg>
        Vendas nos últimos 7 dias
      </h2>
      <select id="periodo" aria-label="Período"><option value="7">7 dias</option></select>
    </div>
    <div class="resumo">
      <div><small>Total no período</small><b><?= moeda($dados['sem_atual']) ?></b>
        <?php if ($variacao !== null): ?><span class="sub <?= $variacao >= 0 ? 'up' : 'down' ?>"><?= ($variacao >= 0 ? '+' : '') . number_format($variacao, 1, ',', '.') ?>% vs. 7 dias anteriores</span><?php endif; ?></div>
      <div><small>Pedidos</small><b><?= $num($dados['qtd7']) ?></b></div>
      <div><small>Ticket médio</small><b><?= moeda($dados['qtd7'] ? $dados['sem_atual'] / $dados['qtd7'] : 0) ?></b></div>
      <div><small>Melhor dia</small><b><?= h($melhor) ?></b></div>
    </div>
    <div class="grafico"><canvas id="grafico"></canvas></div>
  </section>

  <section class="duas">
    <div class="painel">
      <h3>Últimas vendas <a href="<?= h(sn_url(['controller' => 'venda', 'action' => 'index'])) ?>">Ver todas</a></h3>
      <?php if ($ultimas): ?>
        <ul class="lista">
          <?php foreach ($ultimas as $u): ?>
            <li>
              <a href="<?= h(sn_url(['controller' => 'venda', 'action' => 'ver', 'id' => $u['id']])) ?>">
                Venda #<?= (int) $u['id'] ?> · <?= h($u['cliente']) ?><small><?= h(data_br($u['data'])) ?></small>
              </a>
              <span class="val"><?= moeda($u['total']) ?></span>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php else: ?><p class="vazio">Nenhuma venda registrada ainda.</p><?php endif; ?>
    </div>
    <div class="painel">
      <h3>Estoque baixo <a href="<?= h(sn_url(['controller' => 'estoque', 'action' => 'index', 'filtro' => 'baixo'])) ?>">Ver estoque</a></h3>
      <?php if ($baixoLista): ?>
        <ul class="lista">
          <?php foreach ($baixoLista as $b): ?>
            <li><div><?= h($b['nome']) ?><small>Mínimo: <?= $num($b['minimo']) ?> · repor em breve</small></div><span class="selo"><?= $num($b['qtd']) ?> un.</span></li>
          <?php endforeach; ?>
        </ul>
      <?php else: ?><p class="vazio">Nenhum produto com estoque baixo.</p><?php endif; ?>
    </div>
  </section>
</main>

<script>
(function () {
  if (typeof Chart === 'undefined') { return; }   // sem internet para o Chart.js: o resto da página funciona

  const dados = <?= json_encode($grafico, JSON_UNESCAPED_UNICODE) ?>;
  const ctx = document.getElementById('grafico').getContext('2d');

  const gradiente = ctx.createLinearGradient(0, 0, 0, 340);
  gradiente.addColorStop(0, 'rgba(242,140,40,.28)');
  gradiente.addColorStop(1, 'rgba(242,140,40,.03)');

  new Chart(ctx, {
    type: 'line',
    data: {
      labels: dados.map(d => d.label),
      datasets: [{
        data: dados.map(d => d.valor),
        borderColor: '#f9a826',
        backgroundColor: gradiente,
        fill: true,
        tension: 0,
        borderWidth: 2.5,
        pointRadius: 5,
        pointBackgroundColor: '#f9a826',
        pointBorderColor: '#f9a826'
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            label: c => 'R$ ' + c.parsed.y.toLocaleString('pt-BR', { minimumFractionDigits: 2 })
          }
        }
      },
      scales: {
        x: { grid: { color: '#f3e6da' }, ticks: { color: '#3a2a20', font: { size: 14 } } },
        y: {
          beginAtZero: true,
          grid: { color: '#f3e6da' },
          ticks: { color: '#3a2a20', font: { size: 14 }, callback: v => v.toLocaleString('pt-BR') }
        }
      }
    }
  });
})();
</script>
</body>
</html>
