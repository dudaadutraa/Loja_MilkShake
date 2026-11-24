<?php

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/Entrada.php';
require_once __DIR__ . '/../models/Pessoa.php';
require_once __DIR__ . '/../models/Variacao.php';

/** Entrada de mercadoria (compras de fornecedor) — soma no estoque. */
class EntradaController extends BaseController
{
    protected string $rota = 'entrada';
    protected array $acoesAdmin = ['excluir'];   // o vendedor registra e consulta entradas; só o administrador exclui
    private Entrada $entradas;

    public function __construct()
    {
        parent::__construct();
        $this->entradas = new Entrada();
    }

    public function index(): void
    {
        $this->tela('entradas', [
            'entradas' => $this->entradas->listar(),
        ]);
    }

    public function nova(): void
    {
        $this->tela('entrada_form', [
            'fornecedores' => (new Pessoa('fornecedor'))->listarParaSelect(),
            'variacoes'    => (new Variacao())->listarParaSelect(),
        ]);
    }

    public function salvar(): void
    {
        $this->exigirPost();

        $fornecedorId = (int) ($_POST['fornecedor_id'] ?? 0);
        $data = (string) ($_POST['data'] ?? '');
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data)) {
            $data = date('Y-m-d');
        }

        try {
            $fornecedor = $fornecedorId > 0 ? (new Pessoa('fornecedor'))->buscar($fornecedorId) : null;
            if (!$fornecedor || (int) ($fornecedor['ativo'] ?? 1) !== 1) {
                throw new InvalidArgumentException('Escolha um fornecedor ativo.');
            }
            if ($data > date('Y-m-d')) {
                throw new InvalidArgumentException('A data da entrada não pode ser no futuro.');
            }

            $itens = $this->itensDoPost(true);

            // Nota fiscal (opcional): só é gravada se o número for informado
            $nfNumero = $this->texto('nf_numero');
            $nota = $nfNumero === '' ? null : [
                'numero' => $nfNumero,
                'modelo' => $this->texto('nf_modelo'),
                'serie'  => $this->texto('nf_serie'),
                'chave'  => $this->texto('nf_chave'),
            ];

            $entrada = $this->entradas->registrar($fornecedorId, $data, $itens, $nota);

            sn_flash('ok', 'Entrada #' . $entrada['id'] . ' registrada. Estoque atualizado.');
            $this->voltar();
        } catch (InvalidArgumentException | RuntimeException | PDOException $e) {
            sn_flash('erro', $e instanceof PDOException
                ? (($e->errorInfo[1] ?? 0) == 1062 ? 'Já existe uma nota com essa chave de acesso.' : 'Não foi possível salvar a entrada.')
                : $e->getMessage());
            sn_irpara(['controller' => 'entrada', 'action' => 'nova']);
        }
    }

    public function ver(): void
    {
        $entrada = $this->entradas->buscar($this->idGet());

        if (!$entrada) {
            sn_flash('erro', 'Entrada não encontrada.');
            $this->voltar();
        }

        $this->tela('entrada_ver', [
            'entrada' => $entrada,
            'itens'   => $this->entradas->itens((int) $entrada['id']),
        ]);
    }

    /**
     * Exclui uma entrada (ex.: lançada por engano) e tira do estoque o que ela colocou.
     * Só administrador. Se parte das unidades já foi vendida, não deixa excluir.
     */
    public function excluir(): void
    {
        $this->exigirPost();

        $id = $this->idPost();

        try {
            $this->entradas->excluir($id);
            sn_flash('ok', 'Entrada #' . $id . ' excluída e itens retirados do estoque.');
        } catch (RuntimeException | PDOException $e) {
            sn_flash('erro', $e instanceof RuntimeException ? $e->getMessage() : 'Não foi possível excluir a entrada.');
            sn_irpara(['controller' => 'entrada', 'action' => 'ver', 'id' => $id]);
        }

        $this->voltar();
    }
}
