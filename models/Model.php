<?php

require_once __DIR__ . '/../config/db.php';

/**
 * Base de todos os Models.
 * Guarda a conexão com o banco e as funções de consulta usadas por todos.
 * Todos os Models usam a MESMA conexão (Database::getConnection), por isso
 * uma transação aberta num Model vale também para os outros.
 */
abstract class Model
{
    protected PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::getConnection();
    }

    /** Várias linhas. */
    protected function todos(string $sql, array $params = []): array
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** Uma linha (ou null). */
    protected function um(string $sql, array $params = []): ?array
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $r = $stmt->fetch();
        return $r ?: null;
    }

    /** INSERT / UPDATE / DELETE. Devolve quantas linhas foram afetadas. */
    protected function executar(string $sql, array $params = []): int
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    protected function ultimoId(): int
    {
        return (int) $this->db->lastInsertId();
    }

    /**
     * Executa $passos dentro de uma transação: confirma se der tudo certo,
     * desfaz e repassa o erro se algo falhar.
     */
    protected function emTransacao(callable $passos)
    {
        $this->db->beginTransaction();
        try {
            $resultado = $passos();
            $this->db->commit();
            return $resultado;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }
}
