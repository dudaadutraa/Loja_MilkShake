<?php

require_once __DIR__ . '/LojaBaseController.php';

/**
 * Conta do cliente no site.
 *   conta&action=entrar     -> login
 *   conta&action=cadastro   -> criar conta
 *   conta&action=sair       -> sair (POST)
 *   conta                   -> meus pedidos
 *   conta&action=pedido&id  -> detalhe de um pedido
 *   conta&action=dados      -> meus dados e troca de senha
 */
class ContaController extends LojaBaseController
{
    /* ===================== LOGIN ===================== */

    public function entrar(): void
    {
        if ($this->cliente()) {
            $this->depoisDoLogin();
        }

        $email = '';
        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
            $this->exigirPost(['controller' => 'conta', 'action' => 'entrar']);

            $email = mb_strtolower(trim((string) ($_POST['email'] ?? '')));
            $senha = (string) ($_POST['senha'] ?? '');

            $c = $email !== ''
                ? $this->clientes->buscarParaLogin($email)
                : null;

            if ($c && password_verify($senha, $c['senha'])) {
                session_regenerate_id(true);
                $_SESSION['cliente_id'] = (int) $c['id'];
                sn_flash('ok', 'Olá, ' . explode(' ', trim($c['nome']))[0] . '!');
                $this->depoisDoLogin();
            }

            usleep(300000);   // atrasa tentativas seguidas
            sn_flash('erro', 'E-mail ou senha incorretos.');
        }

        $this->tela('entrar', ['email' => $email]);
    }

    public function cadastro(): void
    {
        if ($this->cliente()) {
            $this->depoisDoLogin();
        }

        $f = ['nome' => '', 'email' => '', 'telefone' => '', 'cpf' => '', 'endereco' => ''];

        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
            $this->exigirPost(['controller' => 'conta', 'action' => 'cadastro']);

            foreach ($f as $k => $_) {
                $f[$k] = trim((string) ($_POST[$k] ?? ''));
            }
            $f['email'] = mb_strtolower($f['email']);
            $cpf   = preg_replace('/\D/', '', $f['cpf']);
            $senha = (string) ($_POST['senha'] ?? '');
            $conf  = (string) ($_POST['senha2'] ?? '');

            $erro = null;
            if (mb_strlen($f['nome']) < 3) {
                $erro = 'Informe seu nome completo.';
            } elseif (!filter_var($f['email'], FILTER_VALIDATE_EMAIL)) {
                $erro = 'Informe um e-mail válido.';
            } elseif (strlen(preg_replace('/\D/', '', $f['telefone'])) < 10) {
                $erro = 'Informe um telefone com DDD.';
            } elseif (!self::cpfValido($cpf)) {
                $erro = 'CPF inválido.';
            } elseif (strlen($senha) < 6) {
                $erro = 'A senha precisa ter pelo menos 6 caracteres.';
            } elseif ($senha !== $conf) {
                $erro = 'As senhas não conferem.';
            } elseif ($this->clientes->existeContaComEmail($f['email'])) {
                $erro = 'Já existe uma conta com este e-mail. Entre com a sua senha.';
            } elseif ($this->clientes->existeContaComCpf($cpf)) {
                $erro = 'Já existe uma conta com este CPF.';
            }

            if ($erro) {
                sn_flash('erro', $erro);
            } else {
                // Se a loja já cadastrou esse CPF no balcão, a conta usa o mesmo cadastro (ver ClienteSite::criarConta)
                $id = $this->clientes->criarConta(
                    $f['nome'], $f['telefone'], $f['email'], $f['endereco'], $cpf,
                    password_hash($senha, PASSWORD_DEFAULT)
                );

                session_regenerate_id(true);
                $_SESSION['cliente_id'] = $id;
                sn_flash('ok', 'Conta criada! Bem-vindo(a), ' . explode(' ', $f['nome'])[0] . '.');
                $this->depoisDoLogin();
            }
        }

        $this->tela('cadastro', ['f' => $f]);
    }

    public function sair(): void
    {
        $this->exigirPost(['controller' => 'loja']);
        unset($_SESSION['cliente_id'], $_SESSION['loja_voltar'], $_SESSION['checkout']);
        sn_flash('ok', 'Você saiu da sua conta.');
        sn_irpara(['controller' => 'loja']);
    }

    /* ===================== ÁREA DO CLIENTE ===================== */

    /** Meus pedidos. */
    public function index(): void
    {
        $c = $this->exigirCliente(['controller' => 'conta']);

        $this->tela('conta_pedidos', [
            'pedidos' => $this->clientes->pedidos((int) $c['id']),
        ]);
    }

    public function pedido(): void
    {
        $c = $this->exigirCliente(['controller' => 'conta']);

        $pedido = $this->clientes->pedido((int) ($_GET['id'] ?? 0), (int) $c['id']);
        if (!$pedido) {
            sn_flash('erro', 'Pedido não encontrado.');
            sn_irpara(['controller' => 'conta']);
        }

        $this->tela('conta_pedido', [
            'pedido' => $pedido,
            'itens'  => $this->clientes->itensDoPedido((int) $pedido['id']),
        ]);
    }

    public function dados(): void
    {
        $c = $this->exigirCliente(['controller' => 'conta', 'action' => 'dados']);

        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
            $this->exigirPost(['controller' => 'conta', 'action' => 'dados']);

            if (($_POST['acao'] ?? '') === 'senha') {
                $atual = (string) ($_POST['senha_atual'] ?? '');
                $nova  = (string) ($_POST['senha_nova'] ?? '');

                if (!$this->clientes->senhaConfere((int) $c['id'], $atual)) {
                    sn_flash('erro', 'A senha atual está incorreta.');
                } elseif (strlen($nova) < 6) {
                    sn_flash('erro', 'A nova senha precisa ter pelo menos 6 caracteres.');
                } else {
                    $this->clientes->alterarSenha((int) $c['id'], password_hash($nova, PASSWORD_DEFAULT));
                    sn_flash('ok', 'Senha alterada.');
                }
            } else {
                $nome     = trim((string) ($_POST['nome'] ?? ''));
                $telefone = trim((string) ($_POST['telefone'] ?? ''));
                $endereco = trim((string) ($_POST['endereco'] ?? ''));

                if (mb_strlen($nome) < 3 || strlen(preg_replace('/\D/', '', $telefone)) < 10) {
                    sn_flash('erro', 'Informe nome e telefone com DDD.');
                } else {
                    $this->clientes->atualizarDados((int) $c['id'], $nome, $telefone, $endereco);
                    sn_flash('ok', 'Dados atualizados.');
                }
            }
            sn_irpara(['controller' => 'conta', 'action' => 'dados']);
        }

        $this->tela('conta_dados', []);
    }

    /* ===================== APOIO ===================== */

    private function depoisDoLogin(): void
    {
        $voltar = $_SESSION['loja_voltar'] ?? null;
        unset($_SESSION['loja_voltar']);
        sn_irpara(is_array($voltar) ? $voltar : ['controller' => 'loja']);
    }

    private static function cpfValido(string $cpf): bool
    {
        if (strlen($cpf) !== 11 || preg_match('/^(\d)\1{10}$/', $cpf)) {
            return false;
        }
        for ($t = 9; $t < 11; $t++) {
            $soma = 0;
            for ($i = 0; $i < $t; $i++) {
                $soma += (int) $cpf[$i] * (($t + 1) - $i);
            }
            $dig = ((10 * $soma) % 11) % 10;
            if ((int) $cpf[$t] !== $dig) {
                return false;
            }
        }
        return true;
    }
}
