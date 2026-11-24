<?php

require_once __DIR__ . '/Model.php';

/**
 * Ajustes de estrutura do banco feitos durante o projeto (ALTER TABLE / CREATE TABLE).
 * Se o script SQL de ajustes não foi rodado no phpMyAdmin, este Model cria sozinho
 * o que falta. Nunca apaga nem altera dados existentes.
 * Só verifica uma vez por sessão (chamado pelo BaseController e pelo LojaBaseController).
 */
class EstruturaBanco extends Model
{
    public function atualizar(): void
    {
        if (!empty($_SESSION['estrutura_banco_v2'])) {
            return;
        }

        // Loja online: login do cliente
        $this->adicionarColuna('cliente', 'senha', 'VARCHAR(255) NULL DEFAULT NULL AFTER email');
        $this->adicionarColuna('cliente', 'criado_em', 'DATETIME NULL DEFAULT CURRENT_TIMESTAMP');

        // CEP (API ViaCEP) e exclusão lógica (ativo) nos cadastros de pessoas
        $this->adicionarColuna('cliente', 'cep', 'VARCHAR(9) NULL DEFAULT NULL AFTER endereco');
        $this->adicionarColuna('cliente', 'ativo', 'TINYINT(1) NOT NULL DEFAULT 1');
        $this->adicionarColuna('fornecedor', 'cep', 'VARCHAR(9) NULL DEFAULT NULL AFTER endereco');
        $this->adicionarColuna('fornecedor', 'ativo', 'TINYINT(1) NOT NULL DEFAULT 1');

        // Cancelamento de vendas: a venda fica no histórico com status "cancelada" em vez de ser apagada
        $tipo = $this->um(
            "SELECT COLUMN_TYPE AS t FROM information_schema.COLUMNS
              WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'venda' AND COLUMN_NAME = 'status'"
        );
        if ($tipo && strpos($tipo['t'], 'cancelada') === false) {
            $this->db->exec("ALTER TABLE venda MODIFY status ENUM('aberta','finalizada','cancelada') NOT NULL DEFAULT 'aberta'");
        }

        // Foto do produto (sem foto o site usa a ilustração gerada)
        $this->adicionarColuna('produto', 'imagem', 'VARCHAR(120) NULL DEFAULT NULL AFTER descricao');

        // Entrega e pagamento dos pedidos feitos no site
        $this->db->exec(
            "CREATE TABLE IF NOT EXISTS pedido_online (
                venda_id         INT(11)      NOT NULL,
                entrega          ENUM('retirada','entrega') NOT NULL DEFAULT 'retirada',
                endereco         VARCHAR(255) DEFAULT NULL,
                pagamento        ENUM('pix','cartao','dinheiro') NOT NULL,
                pagamento_status ENUM('pendente','aprovado','na_entrega') NOT NULL DEFAULT 'pendente',
                cartao_final     CHAR(4)      DEFAULT NULL,
                observacao       VARCHAR(255) DEFAULT NULL,
                criado_em        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (venda_id),
                CONSTRAINT fk_pedido_online_venda FOREIGN KEY (venda_id) REFERENCES venda (id) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"
        );

        $_SESSION['estrutura_banco_v2'] = true;
    }

    /** ALTER TABLE ... ADD COLUMN, só se a coluna ainda não existir. */
    private function adicionarColuna(string $tabela, string $coluna, string $definicao): void
    {
        $existe = $this->um(
            'SELECT 1 AS ok FROM information_schema.COLUMNS
              WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?',
            [$tabela, $coluna]
        );
        if (!$existe) {
            $this->db->exec("ALTER TABLE `{$tabela}` ADD COLUMN `{$coluna}` {$definicao}");
        }
    }
}
