<?php

require_once __DIR__ . '/../models/Usuario.php';

/** Login e logout dos funcionários (painel). */
class AuthController
{
    private Usuario $usuarios;

    public function __construct()
    {
        $this->usuarios = new Usuario();
    }

    /** index.php?controller=auth&action=form (padrão do sistema). */
    public function form(): void
    {
        $this->login();
    }

    /** Tela de login + processamento do formulário. */
    public function login(): void
    {
        if (sn_logado()) {
            sn_irpara(['controller' => 'dashboard', 'action' => 'index']);
        }

        $erro  = '';
        $email = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim((string) ($_POST['email'] ?? ''));
            $senha = (string) ($_POST['senha'] ?? '');

            if (!sn_csrf_ok()) {
                $erro = 'Sessão expirada. Tente novamente.';
            } elseif (($_SESSION['bloqueio_ate'] ?? 0) > time()) {
                $erro = 'Muitas tentativas. Aguarde um minuto.';
            } elseif ($email === '' || $senha === '') {
                $erro = 'Preencha o e-mail e a senha.';
            } else {
                $user = $this->usuarios->buscarPorEmail($email);

                if ($user && (int) $user['ativo'] === 1 && password_verify($senha, $user['senha'])) {
                    session_regenerate_id(true);

                    $_SESSION['usuario_id'] = (int) $user['id'];
                    $_SESSION['nome']       = $user['nome'];
                    $_SESSION['perfil']     = $user['perfil'];
                    $_SESSION['csrf']       = bin2hex(random_bytes(32));
                    unset($_SESSION['tentativas'], $_SESSION['bloqueio_ate']);

                    sn_irpara(['controller' => 'dashboard', 'action' => 'index']);
                }

                $_SESSION['tentativas'] = ($_SESSION['tentativas'] ?? 0) + 1;

                if ($_SESSION['tentativas'] >= 5) {
                    $_SESSION['bloqueio_ate'] = time() + 60;
                    $_SESSION['tentativas'] = 0;
                }

                $erro = ($user && (int) $user['ativo'] !== 1)
                    ? 'Este usuário está inativo.'
                    : 'E-mail ou senha incorretos.';
            }
        }

        sn_view('login', [
            'erro'  => $erro,
            'email' => $email,
            'csrf'  => sn_csrf(),
        ]);
    }

    public function logout(): void
    {
        sn_sair();
    }
}
