<?php

require_once __DIR__ . '/Model.php';

/**
 * Cadastro de pessoas: CLIENTES (tabela cliente) e FORNECEDORES (tabela fornecedor).
 * As duas tabelas têm os mesmos campos; só muda o nome da coluna do documento
 * (cpf_cnpj no cliente, cnpj no fornecedor). Por isso um Model atende as duas.
 * Exclusão lógica: em vez de apagar, o cadastro pode ser desativado (coluna ativo).
 * Uso:
 *   new Pessoa('cliente')   ou   new Pessoa('fornecedor')
 */
class Pessoa extends Model
{
    private const TIPOS = [
        'cliente'    => 'cpf_cnpj',
        'fornecedor' => 'cnpj',
    ];

    private string $tabela;
    private string $campoDoc;

    public function __construct(string $tipo, ?PDO $db = null)
    {
        if (!isset(self::TIPOS[$tipo])) {
            throw new InvalidArgumentException("Tipo de pessoa inválido: {$tipo}");
        }
        parent::__construct($db);
        $this->tabela   = $tipo;
        $this->campoDoc = self::TIPOS[$tipo];
    }

    /** Nome da coluna do documento (usado pela view compartilhada). */
    public function campoDoc(): string
    {
        return $this->campoDoc;
    }

    public function buscar(int $id): ?array
    {
        return $this->um("SELECT * FROM {$this->tabela} WHERE id = ?", [$id]);
    }

    /** Lista com busca por nome, documento ou e-mail. */
    public function listar(string $busca = ''): array
    {
        $sql = "SELECT * FROM {$this->tabela}";
        $params = [];
        if ($busca !== '') {
            $sql .= " WHERE nome LIKE ? OR {$this->campoDoc} LIKE ? OR email LIKE ?";
            $params = ["%{$busca}%", "%{$busca}%", "%{$busca}%"];
        }
        $sql .= ' ORDER BY nome';

        return $this->todos($sql, $params);
    }

    /** Lista simples (id e nome) dos ATIVOS, para os campos de seleção de venda e entrada. */
    public function listarParaSelect(): array
    {
        return $this->todos("SELECT id, nome FROM {$this->tabela} WHERE ativo = 1 ORDER BY nome");
    }

    /** $d = [nome, doc, telefone, email, cep, endereco] */
    public function inserir(array $d): void
    {
        $this->executar(
            "INSERT INTO {$this->tabela} (nome, {$this->campoDoc}, telefone, email, cep, endereco) VALUES (?, ?, ?, ?, ?, ?)",
            [$d['nome'], $d['doc'], $d['telefone'], $d['email'], $d['cep'], $d['endereco']]
        );
    }

    public function atualizar(int $id, array $d): void
    {
        $this->executar(
            "UPDATE {$this->tabela} SET nome = ?, {$this->campoDoc} = ?, telefone = ?, email = ?, cep = ?, endereco = ? WHERE id = ?",
            [$d['nome'], $d['doc'], $d['telefone'], $d['email'], $d['cep'], $d['endereco'], $id]
        );
    }

    /** Exclusão lógica: ativa/desativa o cadastro (o histórico de entradas e vendas continua). */
    public function alternar(int $id): void
    {
        $this->executar("UPDATE {$this->tabela} SET ativo = 1 - ativo WHERE id = ?", [$id]);
    }

    /** Lança PDOException se a pessoa estiver em uso (ex.: fornecedor com entradas). */
    /**
     * Exclusão definitiva. Fornecedor com entradas é barrado pelo próprio banco (chave estrangeira).
     * Cliente com vendas também é barrado aqui: no banco a venda ficaria sem cliente
     * (ON DELETE SET NULL) e o histórico perderia o nome dele. Nesses casos use Desativar.
     */
    public function excluir(int $id): void
    {
        if ($this->tabela === 'cliente' && $this->um('SELECT 1 AS r FROM venda WHERE cliente_id = ? LIMIT 1', [$id])) {
            throw new PDOException('Cliente com vendas', 23000);
        }
        $this->executar("DELETE FROM {$this->tabela} WHERE id = ?", [$id]);
    }
}
