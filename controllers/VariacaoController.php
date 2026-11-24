<?php

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/Variacao.php';
require_once __DIR__ . '/../models/Produto.php';

/**
 * Variações dos produtos (tabela variacao): cada uma é um TAMANHO com preço,
 * SKU e uma linha na tabela estoque.
 *
 *   index.php?controller=variacao                    -> todas as variações
 *   index.php?controller=variacao&produto=ID         -> só as de um produto
 *   index.php?controller=variacao&produto=ID&id=VID  -> editar uma variação
 */
class VariacaoController extends BaseController
{
    protected string $rota = 'variacao';
    private Variacao $variacoes;
    private Produto $produtos;

    public function __construct()
    {
        parent::__construct();
        $this->variacoes = new Variacao();
        $this->produtos  = new Produto();
    }

    public function index(): void
    {
        $produtoId = max(0, (int) ($_GET['produto'] ?? 0));

        $produto = null;
        if ($produtoId > 0) {
            $produto = $this->produtos->buscarComCategoria($produtoId);
            if (!$produto) {
                sn_flash('erro', 'Produto não encontrado.');
                $this->voltar();
            }
        }

        $this->tela('variacoes', [
            'produto'   => $produto,
            'editar'    => $this->idGet() > 0 ? $this->variacoes->buscarComMinimo($this->idGet()) : null,
            'variacoes' => $this->variacoes->listar($produtoId),
            'produtos'  => $this->produtos->listarParaSelect(),
        ]);
    }

    public function salvar(): void
    {
        $this->exigirPost();

        $produtoId = (int) ($_POST['produto_id'] ?? 0);
        $filtro    = (int) ($_POST['filtro'] ?? 0);   // produto que estava filtrado na tela
        $vid       = $this->idPost();
        $tamanho   = $this->texto('tamanho');
        $sku       = strtoupper($this->texto('sku'));
        $preco     = $this->dinheiro($this->texto('preco'));
        $minimo    = max(0, (int) ($_POST['minimo'] ?? 0));
        $voltar    = $filtro > 0 ? ['produto' => $filtro] : [];

        if ($produtoId <= 0 || $tamanho === '' || $preco === null || $preco <= 0) {
            sn_flash('erro', 'Escolha o produto e informe o tamanho e um preço maior que zero (ex.: 15,90).');
            $this->voltar($vid ? $voltar + ['id' => $vid] : $voltar);
        }

        // Cada tamanho aparece uma vez por produto (300ml e 500ml têm preço e estoque próprios)
        if ($this->variacoes->tamanhoRepetido($produtoId, $tamanho, $vid)) {
            sn_flash('erro', 'Este produto já tem o tamanho ' . $tamanho . '. Edite a variação existente.');
            $this->voltar($vid ? $voltar + ['id' => $vid] : $voltar);
        }

        if ($sku === '') {
            $sku = 'P' . $produtoId . '-' . preg_replace('/[^A-Z0-9]/', '', strtoupper($tamanho)) . '-' . substr(bin2hex(random_bytes(2)), 0, 4);
        }

        try {
            $this->variacoes->salvar($vid, $produtoId, $tamanho, $sku, $preco, $minimo);
            sn_flash('ok', 'Variação salva.');
        } catch (PDOException $e) {
            sn_flash('erro', ($e->errorInfo[1] ?? 0) == 1062
                ? 'Já existe uma variação com esse SKU.'
                : 'Não foi possível salvar a variação.');
        }

        $this->voltar($voltar);
    }

    public function excluir(): void
    {
        $this->exigirPost();

        $filtro = (int) ($_POST['filtro'] ?? 0);
        $vid    = $this->idPost();

        try {
            $this->variacoes->excluir($vid);
            sn_flash('ok', 'Variação excluída.');
        } catch (PDOException $e) {
            sn_flash('erro', 'Esta variação já tem vendas, entradas ou estoque e não pode ser excluída.');
        }

        $this->voltar($filtro > 0 ? ['produto' => $filtro] : []);
    }
}
