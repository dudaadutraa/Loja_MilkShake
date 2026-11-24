<?php

require_once __DIR__ . '/PessoaController.php';

/** Cadastro de fornecedores (tabela fornecedor). A lógica fica no PessoaController. */
class FornecedorController extends PessoaController
{
    protected string $rota = 'fornecedor';
    protected string $tipo = 'fornecedor';
    protected array $textos = [
        'titulo'   => 'Fornecedores',
        'singular' => 'fornecedor',
        'doc'      => 'CNPJ',
        'docObrig' => true,
        'emUso'    => 'Este fornecedor tem entradas registradas e não pode ser apagado. Use Desativar: ele sai das novas entradas e o histórico continua.',
    ];
}
