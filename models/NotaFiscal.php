<?php

require_once __DIR__ . '/Model.php';

/**
 * Notas fiscais (simuladas, para fins didáticos):
 *   nota_fiscal_venda   -> emitida depois da venda, número sequencial por modelo/série
 *   nota_fiscal_entrada -> registrada junto com a entrada, chave de acesso única
 *
 * A venda/entrada (o fato comercial) e a nota (o documento fiscal) ficam em tabelas
 * separadas: a venda acontece primeiro e a nota pode ser emitida depois.
 */
class NotaFiscal extends Model
{
    public const MODELO_VENDA = 'NFCe';
    public const SERIE_VENDA  = '1';

    /* ---------------- Nota de VENDA ---------------- */

    public function buscarPorVenda(int $vendaId): ?array
    {
        return $this->um('SELECT * FROM nota_fiscal_venda WHERE venda_id = ?', [$vendaId]);
    }

    /**
     * Emite a nota de uma venda finalizada e devolve o número.
     * Lança RuntimeException se a venda já tiver nota ou ainda não estiver finalizada.
     */
    public function emitirParaVenda(int $vendaId): string
    {
        return $this->emTransacao(function () use ($vendaId) {
            $venda = $this->um('SELECT id, status, valor_total FROM venda WHERE id = ? FOR UPDATE', [$vendaId]);
            if (!$venda) {
                throw new RuntimeException('Venda não encontrada.');
            }
            if ($venda['status'] !== 'finalizada') {
                throw new RuntimeException('A nota só pode ser emitida depois que o pedido for entregue/finalizado.');
            }

            $jaEmitida = $this->buscarPorVenda($vendaId);
            if ($jaEmitida) {
                throw new RuntimeException('Esta venda já tem a nota fiscal nº ' . $jaEmitida['numero'] . '.');
            }

            $numero = $this->proximoNumero(self::MODELO_VENDA, self::SERIE_VENDA);
            $this->executar(
                'INSERT INTO nota_fiscal_venda (venda_id, modelo, serie, numero, data_emissao, valor_total)
                 VALUES (?, ?, ?, ?, CURDATE(), ?)',
                [$vendaId, self::MODELO_VENDA, self::SERIE_VENDA, $numero, $venda['valor_total']]
            );
            return $numero;
        });
    }

    /** Próximo número da nota de venda (o maior número já usado no modelo/série + 1). */
    private function proximoNumero(string $modelo, string $serie): string
    {
        $r = $this->um(
            'SELECT MAX(CAST(numero AS UNSIGNED)) AS ultimo FROM nota_fiscal_venda WHERE modelo = ? AND serie = ? FOR UPDATE',
            [$modelo, $serie]
        );
        return (string) ((int) ($r['ultimo'] ?? 0) + 1);
    }

    /* ---------------- Nota de ENTRADA ---------------- */

    public function buscarPorEntrada(int $entradaId): ?array
    {
        return $this->um('SELECT * FROM nota_fiscal_entrada WHERE entrada_id = ?', [$entradaId]);
    }

    /**
     * Registra a nota de uma entrada (chamado dentro da transação da entrada).
     * Sem chave de acesso, grava "SEM-CHAVE-<id da entrada>".
     * Lança PDOException (1062) se a chave de acesso já existir.
     */
    public function registrarParaEntrada(int $entradaId, string $numero, string $modelo, string $serie, string $chave, string $data, float $total): void
    {
        $this->executar(
            'INSERT INTO nota_fiscal_entrada (entrada_id, modelo, serie, numero, chave_acesso, data_emissao, valor_total)
             VALUES (?, ?, ?, ?, ?, ?, ?)',
            [
                $entradaId,
                mb_substr($modelo ?: '55', 0, 5),
                mb_substr($serie ?: '1', 0, 5),
                mb_substr($numero, 0, 10),
                mb_substr(preg_replace('/\D/', '', $chave) ?: 'SEM-CHAVE-' . $entradaId, 0, 44),
                $data,
                round($total, 2),
            ]
        );
    }
}
