<?php

require_once __DIR__ . '/PessoaController.php';

/** Cadastro de clientes (tabela cliente). A lógica fica no PessoaController. */
class ClienteController extends PessoaController
{
    protected string $rota = 'cliente';
    protected string $tipo = 'cliente';
    protected array $acoesAdmin = ['alternar', 'excluir'];   // o vendedor cadastra e edita, mas não desativa nem apaga
    protected array $textos = [
        'titulo'   => 'Clientes',
        'singular' => 'cliente',
        'doc'      => 'CPF/CNPJ',
        'docObrig' => false,
        'emUso'    => 'Este cliente tem vendas registradas e não pode ser apagado. Use Desativar: ele sai das novas vendas e o histórico continua.',
    ];
}
