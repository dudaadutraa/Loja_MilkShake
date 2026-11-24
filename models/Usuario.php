<?php

require_once __DIR__ . '/Model.php';

/** Usuários do painel (tabela usuario): administradores e vendedores. */
class Usuario extends Model
{
    public function buscarPorEmail(string $email): ?array
    {
        return $this->um('SELECT * FROM usuario WHERE email = ? LIMIT 1', [$email]);
    }

    /** Dados para o formulário de edição (sem a senha). */
    public function buscar(int $id): ?array
    {
        return $this->um('SELECT id, nome, email, perfil, ativo FROM usuario WHERE id = ?', [$id]);
    }

    public function listar(): array
    {
        return $this->todos('SELECT id, nome, email, perfil, ativo FROM usuario ORDER BY nome');
    }

    public function cadastrar(string $nome, string $email, string $senhaHash, string $perfil = 'vendedor', int $ativo = 1): bool
    {
        return $this->executar(
            'INSERT INTO usuario (nome, email, senha, perfil, ativo) VALUES (?, ?, ?, ?, ?)',
            [$nome, $email, $senhaHash, $perfil, $ativo]
        ) > 0;
    }

    public function atualizar(int $id, string $nome, string $email, string $perfil): void
    {
        $this->executar('UPDATE usuario SET nome = ?, email = ?, perfil = ? WHERE id = ?', [$nome, $email, $perfil, $id]);
    }

    public function alterarSenha(int $id, string $senhaHash): void
    {
        $this->executar('UPDATE usuario SET senha = ? WHERE id = ?', [$senhaHash, $id]);
    }

    /** Ativa/desativa. */
    public function alternar(int $id): void
    {
        $this->executar('UPDATE usuario SET ativo = 1 - COALESCE(ativo, 1) WHERE id = ?', [$id]);
    }
}
