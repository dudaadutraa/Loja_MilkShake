<?php

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/Pessoa.php';

/**
 * Base dos cadastros de PESSOAS: clientes e fornecedores.
 * Os dois cadastros têm os mesmos campos e a mesma tela (views/cadastro_pessoas.php);
 * cada controller filho só informa o tipo e os textos:
 *   ClienteController    -> tabela cliente
 *   FornecedorController -> tabela fornecedor
 */
abstract class PessoaController extends BaseController
{
    /** 'cliente' ou 'fornecedor' (definido no controller filho). */
    protected string $tipo = '';

    /** Textos da tela: titulo, singular, doc, docObrig, emUso. */
    protected array $textos = [];

    protected Pessoa $pessoas;

    public function __construct()
    {
        parent::__construct();
        $this->pessoas = new Pessoa($this->tipo);
    }

    public function index(): void
    {
        $q = trim((string) ($_GET['q'] ?? ''));

        $this->tela('cadastro_pessoas', [
            'rota'     => $this->rota,
            'campoDoc' => $this->pessoas->campoDoc(),
            'textos'   => $this->textos,
            'editar'   => $this->idGet() > 0 ? $this->pessoas->buscar($this->idGet()) : null,
            'lista'    => $this->pessoas->listar($q),
            'q'        => $q,
        ]);
    }

    public function salvar(): void
    {
        $this->exigirPost();

        $id = $this->idPost();
        $d = [
            'nome'     => $this->texto('nome'),
            'doc'      => $this->texto('doc'),
            'telefone' => $this->texto('telefone') ?: null,
            'email'    => $this->texto('email') ?: null,
            'cep'      => $this->cep($this->texto('cep')),
            'endereco' => $this->texto('endereco') ?: null,
        ];

        if ($d['nome'] === '' || ($this->textos['docObrig'] && $d['doc'] === '')) {
            sn_flash('erro', 'Preencha o nome e o ' . $this->textos['doc'] . '.');
            $this->voltar($id ? ['id' => $id] : []);
        }

        if ($d['email'] !== null && !filter_var($d['email'], FILTER_VALIDATE_EMAIL)) {
            sn_flash('erro', 'E-mail inválido.');
            $this->voltar($id ? ['id' => $id] : []);
        }

        if ($id > 0) {
            $this->pessoas->atualizar($id, $d);
            sn_flash('ok', ucfirst($this->textos['singular']) . ' atualizado.');
        } else {
            $this->pessoas->inserir($d);
            sn_flash('ok', ucfirst($this->textos['singular']) . ' cadastrado.');
        }

        $this->voltar();
    }

    /** Exclusão lógica: desativa (ou reativa) o cadastro sem apagar o histórico. */
    public function alternar(): void
    {
        $this->exigirPost();
        $this->pessoas->alternar($this->idPost());
        sn_flash('ok', 'Situação do ' . $this->textos['singular'] . ' alterada.');
        $this->voltar();
    }

    /** Exclusão definitiva: só funciona se não houver entradas/vendas ligadas ao cadastro. */
    public function excluir(): void
    {
        $this->exigirPost();

        try {
            $this->pessoas->excluir($this->idPost());
            sn_flash('ok', ucfirst($this->textos['singular']) . ' excluído.');
        } catch (PDOException $e) {
            sn_flash('erro', $this->emUso($e) ? $this->textos['emUso'] : 'Não foi possível excluir.');
        }

        $this->voltar();
    }

    /** Deixa o CEP no formato 00000-000 (ou null se não tiver 8 dígitos). */
    private function cep(string $valor): ?string
    {
        $num = preg_replace('/\D/', '', $valor);
        return strlen($num) === 8 ? substr($num, 0, 5) . '-' . substr($num, 5) : null;
    }
}
