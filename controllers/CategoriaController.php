<?php

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/Categoria.php';

/** Cadastro de categorias. */
class CategoriaController extends BaseController
{
    protected string $rota = 'categoria';
    private Categoria $categorias;

    public function __construct()
    {
        parent::__construct();
        $this->categorias = new Categoria();
    }

    public function index(): void
    {
        $this->tela('categorias', [
            'editar'     => $this->idGet() > 0 ? $this->categorias->buscar($this->idGet()) : null,
            'categorias' => $this->categorias->listarComQtdProdutos(),
        ]);
    }

    public function salvar(): void
    {
        $this->exigirPost();

        $id   = $this->idPost();
        $nome = $this->texto('nome');

        if ($nome === '') {
            sn_flash('erro', 'Informe o nome da categoria.');
            $this->voltar($id ? ['id' => $id] : []);
        }

        if ($id > 0) {
            $this->categorias->atualizar($id, $nome);
            sn_flash('ok', 'Categoria atualizada.');
        } else {
            $this->categorias->inserir($nome);
            sn_flash('ok', 'Categoria cadastrada.');
        }

        $this->voltar();
    }

    public function alternar(): void
    {
        $this->exigirPost();
        $this->categorias->alternar($this->idPost());
        sn_flash('ok', 'Situação da categoria alterada.');
        $this->voltar();
    }

    public function excluir(): void
    {
        $this->exigirPost();

        try {
            $this->categorias->excluir($this->idPost());
            sn_flash('ok', 'Categoria excluída.');
        } catch (PDOException $e) {
            sn_flash('erro', $this->emUso($e)
                ? 'Esta categoria tem produtos. Desative-a em vez de excluir.'
                : 'Não foi possível excluir a categoria.');
        }

        $this->voltar();
    }
}
