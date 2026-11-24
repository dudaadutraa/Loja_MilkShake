<?php

require_once __DIR__ . '/Model.php';

/**
 * Conta do cliente na LOJA VIRTUAL (login, cadastro, dados e pedidos).
 * Usa a mesma tabela cliente do painel: um cliente tem conta no site
 * quando a coluna senha está preenchida.
 */
class ClienteSite extends Model
{
    /** Dados do cliente logado (só se ele tiver conta no site). */
    public function buscarComConta(int $id): ?array
    {
        return $this->um(
            'SELECT id, nome, cpf_cnpj, telefone, email, endereco FROM cliente WHERE id = ? AND senha IS NOT NULL',
            [$id]
        );
    }

    /** Conta para o login (inclui o hash da senha). */
    public function buscarParaLogin(string $email): ?array
    {
        return $this->um('SELECT id, nome, senha FROM cliente WHERE email = ? AND senha IS NOT NULL ORDER BY id LIMIT 1', [$email]);
    }

    public function existeContaComEmail(string $email): bool
    {
        return $this->um('SELECT id FROM cliente WHERE email = ? AND senha IS NOT NULL', [$email]) !== null;
    }

    public function existeContaComCpf(string $cpf): bool
    {
        return $this->um('SELECT id FROM cliente WHERE cpf_cnpj = ? AND senha IS NOT NULL', [$cpf]) !== null;
    }

    /**
     * Cria a conta. Se a loja já tinha cadastrado esse CPF no balcão (sem senha),
     * a conta usa o mesmo cadastro. Devolve o id do cliente.
     */
    public function criarConta(string $nome, string $telefone, string $email, string $endereco, string $cpf, string $senhaHash): int
    {
        $dados = [mb_substr($nome, 0, 100), mb_substr($telefone, 0, 20), mb_substr($email, 0, 100), mb_substr($endereco, 0, 255) ?: null, $senhaHash];

        $existente = $this->um('SELECT id FROM cliente WHERE cpf_cnpj = ? AND senha IS NULL ORDER BY id LIMIT 1', [$cpf]);
        if ($existente) {
            $this->executar(
                'UPDATE cliente SET nome = ?, telefone = ?, email = ?, endereco = COALESCE(?, endereco), senha = ? WHERE id = ?',
                array_merge($dados, [$existente['id']])
            );
            return (int) $existente['id'];
        }

        $this->executar(
            'INSERT INTO cliente (nome, telefone, email, endereco, senha, cpf_cnpj) VALUES (?, ?, ?, ?, ?, ?)',
            array_merge($dados, [$cpf])
        );
        return $this->ultimoId();
    }

    public function atualizarDados(int $id, string $nome, string $telefone, string $endereco): void
    {
        $this->executar(
            'UPDATE cliente SET nome = ?, telefone = ?, endereco = ? WHERE id = ?',
            [mb_substr($nome, 0, 100), mb_substr($telefone, 0, 20), mb_substr($endereco, 0, 255) ?: null, $id]
        );
    }

    public function senhaConfere(int $id, string $senha): bool
    {
        $hash = $this->um('SELECT senha FROM cliente WHERE id = ?', [$id])['senha'] ?? '';
        return password_verify($senha, (string) $hash);
    }

    public function alterarSenha(int $id, string $senhaHash): void
    {
        $this->executar('UPDATE cliente SET senha = ? WHERE id = ?', [$senhaHash, $id]);
    }

    /** Pedidos do cliente (Minha conta > Meus pedidos). */
    public function pedidos(int $clienteId): array
    {
        return $this->todos(
            "SELECT v.id, v.data, v.status, v.valor_total,
                    po.entrega, po.pagamento, po.pagamento_status,
                    (SELECT COALESCE(SUM(quantidade), 0) FROM venda_item vi WHERE vi.venda_id = v.id) AS itens
               FROM venda v
          LEFT JOIN pedido_online po ON po.venda_id = v.id
              WHERE v.cliente_id = ?
              ORDER BY v.id DESC",
            [$clienteId]
        );
    }

    /** Um pedido do cliente (só se for dele). */
    public function pedido(int $pedidoId, int $clienteId): ?array
    {
        return $this->um(
            'SELECT v.*, po.entrega, po.endereco AS endereco_entrega, po.pagamento, po.pagamento_status, po.cartao_final
               FROM venda v LEFT JOIN pedido_online po ON po.venda_id = v.id
              WHERE v.id = ? AND v.cliente_id = ?',
            [$pedidoId, $clienteId]
        );
    }

    public function itensDoPedido(int $pedidoId): array
    {
        return $this->todos(
            'SELECT vi.quantidade, vi.preco_unitario, va.tamanho, p.id AS produto_id, p.nome AS produto, p.descricao, p.imagem
               FROM venda_item vi
               JOIN variacao va ON va.id = vi.variacao_id
               JOIN produto p ON p.id = va.produto_id
              WHERE vi.venda_id = ?',
            [$pedidoId]
        );
    }
}
