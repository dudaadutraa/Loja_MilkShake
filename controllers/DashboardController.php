<?php

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/Dashboard.php';

/** index.php?controller=dashboard&action=index */
class DashboardController extends BaseController
{
    protected string $rota = 'dashboard';

    public function index(): void
    {
        $model = new Dashboard();

        $grafico = $model->getGrafico();
        $valores = array_column($grafico, 'valor');
        $melhor  = max($valores) > 0
            ? $grafico[array_search(max($valores), $valores)]['label']
            : '—';

        $this->tela('dashboard', [
            'usuarioNome' => $_SESSION['nome'] ?? '',
            'numeros'    => $model->getNumeros(),
            'grafico'    => $grafico,
            'melhor'     => $melhor,
            'ultimas'    => $model->getUltimasVendas(5),
            'baixoLista' => $model->getEstoqueBaixo(5),
            'falhas'     => $model->getErros(),
        ]);
    }
}
