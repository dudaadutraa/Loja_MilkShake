<?php

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/Produto.php';
require_once __DIR__ . '/../models/Categoria.php';

/**
 * Produtos (tabela produto).
 * As variações (tamanhos e preços) ficam no VariacaoController.
 */
class ProdutoController extends BaseController
{
    protected string $rota = 'produto';
    private Produto $produtos;

    public function __construct()
    {
        parent::__construct();
        $this->produtos = new Produto();
    }

    public function index(): void
    {
        $q = trim((string) ($_GET['q'] ?? ''));

        $this->tela('produtos', [
            'editar'     => $this->idGet() > 0 ? $this->produtos->buscar($this->idGet()) : null,
            'produtos'   => $this->produtos->listarComResumo($q),
            'categorias' => (new Categoria())->listarParaSelect(),
            'q'          => $q,
        ]);
    }

    public function salvar(): void
    {
        $this->exigirPost();

        $id          = $this->idPost();
        $categoriaId = (int) ($_POST['categoria_id'] ?? 0);
        $nome        = $this->texto('nome');
        $descricao   = $this->texto('descricao') ?: null;

        if ($nome === '' || $categoriaId <= 0) {
            sn_flash('erro', 'Informe o nome e a categoria do produto.');
            $this->voltar($id ? ['id' => $id] : []);
        }

        if ($id > 0) {
            $this->produtos->atualizar($id, $categoriaId, $nome, $descricao);
            $msgFoto = $this->tratarFoto($id);
            sn_flash($msgFoto && $msgFoto[0] === 'erro' ? 'erro' : 'ok', 'Produto atualizado.' . ($msgFoto ? ' ' . $msgFoto[1] : ''));
            $this->voltar();
        }

        $novoId = $this->produtos->inserir($categoriaId, $nome, $descricao);
        $this->tratarFoto($novoId);

        sn_flash('ok', 'Produto cadastrado. Agora cadastre as variações (tamanhos e preços).');
        sn_irpara(['controller' => 'variacao', 'action' => 'index', 'produto' => $novoId]);
    }

    public function alternar(): void
    {
        $this->exigirPost();
        $this->produtos->alternar($this->idPost());
        sn_flash('ok', 'Situação do produto alterada.');
        $this->voltar();
    }

    public function excluir(): void
    {
        $this->exigirPost();

        try {
            $this->produtos->excluir($this->idPost());
            sn_flash('ok', 'Produto excluído.');
        } catch (PDOException $e) {
            sn_flash('erro', $this->emUso($e)
                ? 'Este produto tem variações cadastradas. Exclua as variações antes ou desative o produto.'
                : 'Não foi possível excluir o produto.');
        }

        $this->voltar();
    }

    /**
     * Foto do produto: salva a foto enviada ou remove a atual (volta para a ilustração).
     * Devolve null se nada mudou, ou [tipo, mensagem].
     */
    private function tratarFoto(int $id): ?array
    {
        $atual = $this->produtos->imagemAtual($id);
        $pasta = RAIZ . '/public/uploads/produtos';

        $apagarAtual = function () use ($atual, $pasta): void {
            if ($atual && is_file($pasta . '/' . basename($atual))) {
                @unlink($pasta . '/' . basename($atual));
            }
        };

        $arq = $_FILES['foto'] ?? null;
        if ($arq && ($arq['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            if ($arq['error'] !== UPLOAD_ERR_OK || $arq['size'] > 3 * 1024 * 1024) {
                return ['erro', 'A foto não foi salva: envie uma imagem de até 3 MB.'];
            }
            $info = @getimagesize($arq['tmp_name']);
            $tipos = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp'];
            if (!$info || !isset($tipos[$info[2]])) {
                return ['erro', 'A foto não foi salva: use JPG, PNG ou WEBP.'];
            }
            if (!is_dir($pasta) && !@mkdir($pasta, 0755, true)) {
                return ['erro', 'A foto não foi salva: não foi possível criar a pasta public/uploads/produtos.'];
            }
            $nomeArq = 'produto-' . $id . '-' . bin2hex(random_bytes(4)) . '.' . $tipos[$info[2]];
            if (!move_uploaded_file($arq['tmp_name'], $pasta . '/' . $nomeArq)) {
                return ['erro', 'A foto não foi salva. Tente novamente.'];
            }
            $apagarAtual();
            $this->produtos->definirImagem($id, $nomeArq);
            return ['ok', 'Foto salva.'];
        }

        if (!empty($_POST['remover_foto']) && $atual) {
            $apagarAtual();
            $this->produtos->definirImagem($id, null);
            return ['ok', 'Foto removida: o produto volta a usar a ilustração.'];
        }
        return null;
    }

    /** Endereço antigo: as variações agora ficam em controller=variacao. */
    public function variacoes(): void
    {
        sn_irpara(['controller' => 'variacao', 'action' => 'index', 'produto' => $this->idGet()]);
    }
}
