<?php

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/Usuario.php';

/** Usuários do sistema (somente administradores). */
class UsuarioController extends BaseController
{
    protected string $rota = 'usuario';
    private Usuario $usuarios;

    public function __construct()
    {
        parent::__construct();
        $this->usuarios = new Usuario();   // área só de administradores (ver sn_so_admin)
    }

    public function index(): void
    {
        $this->tela('usuarios', [
            'editar'   => $this->idGet() > 0 ? $this->usuarios->buscar($this->idGet()) : null,
            'usuarios' => $this->usuarios->listar(),
        ]);
    }

    public function salvar(): void
    {
        $this->exigirPost();

        $id     = $this->idPost();
        $nome   = $this->texto('nome');
        $email  = strtolower($this->texto('email'));
        $senha  = (string) ($_POST['senha'] ?? '');
        $perfil = ($_POST['perfil'] ?? '') === 'admin' ? 'admin' : 'vendedor';
        $volta  = $id ? ['id' => $id] : [];

        if ($nome === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            sn_flash('erro', 'Informe nome e um e-mail válido.');
            $this->voltar($volta);
        }
        if (($id === 0 || $senha !== '') && strlen($senha) < 6) {
            sn_flash('erro', 'A senha precisa ter pelo menos 6 caracteres.');
            $this->voltar($volta);
        }
        if ($id === (int) $_SESSION['usuario_id'] && $perfil !== 'admin') {
            sn_flash('erro', 'Você não pode tirar o seu próprio acesso de administrador.');
            $this->voltar($volta);
        }

        try {
            if ($id > 0) {
                $this->usuarios->atualizar($id, $nome, $email, $perfil);
                if ($senha !== '') {
                    $this->usuarios->alterarSenha($id, password_hash($senha, PASSWORD_DEFAULT));
                }
                if ($id === (int) $_SESSION['usuario_id']) {
                    $_SESSION['nome'] = $nome;
                }
                sn_flash('ok', 'Usuário atualizado.');
            } else {
                $this->usuarios->cadastrar($nome, $email, password_hash($senha, PASSWORD_DEFAULT), $perfil, 1);
                sn_flash('ok', 'Usuário cadastrado.');
            }
        } catch (PDOException $e) {
            sn_flash('erro', ($e->errorInfo[1] ?? 0) == 1062 ? 'Já existe um usuário com esse e-mail.' : 'Não foi possível salvar.');
            $this->voltar($volta);
        }

        $this->voltar();
    }

    public function alternar(): void
    {
        $this->exigirPost();

        if ($this->idPost() === (int) $_SESSION['usuario_id']) {
            sn_flash('erro', 'Você não pode desativar a sua própria conta.');
            $this->voltar();
        }

        $this->usuarios->alternar($this->idPost());
        sn_flash('ok', 'Situação do usuário alterada.');
        $this->voltar();
    }
}
