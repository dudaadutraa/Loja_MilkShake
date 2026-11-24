<?php

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/Estoque.php';

/** Posição de estoque por tamanho + histórico de movimentos. */
class EstoqueController extends BaseController
{
    protected string $rota = 'estoque';
    protected array $acoesAdmin = ['minimo'];   // o vendedor só consulta o estoque
    private Estoque $estoque;

    public function __construct()
    {
        parent::__construct();
        $this->estoque = new Estoque();
    }

    public function index(): void
    {
        $filtro = (string) ($_GET['filtro'] ?? '');

        $this->tela('estoque', [
            'itens'      => $this->estoque->posicao($filtro),
            'filtro'     => $filtro,
            'movimentos' => $this->estoque->movimentosRecentes(30),
        ]);
    }

    /** Altera o estoque mínimo de um tamanho. */
    public function minimo(): void
    {
        $this->exigirPost();

        $this->estoque->definirMinimo($this->idPost(), max(0, (int) ($_POST['minimo'] ?? 0)));

        sn_flash('ok', 'Estoque mínimo atualizado.');
        $this->voltar();
    }
}
