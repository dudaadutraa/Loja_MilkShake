<?php

require_once __DIR__ . '/../models/Cep.php';

/**
 * API interna de CEP (responde em JSON).
 *   index.php?controller=cep&action=buscar&cep=26210000
 *
 * Funciona como "atravessador" (proxy): o JavaScript das telas chama este endereço,
 * o servidor consulta o ViaCEP (Model Cep) e devolve só os campos que o site usa.
 * Usado no cadastro de clientes e fornecedores (painel) e no cadastro e entrega da loja.
 */
class CepController
{
    public function buscar(): void
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: public, max-age=86400');

        try {
            $endereco = (new Cep())->buscar((string) ($_GET['cep'] ?? ''));
            echo json_encode($endereco, JSON_UNESCAPED_UNICODE);
        } catch (InvalidArgumentException $e) {
            $this->erro(400, $e->getMessage());
        } catch (DomainException $e) {
            $this->erro(404, $e->getMessage());
        } catch (RuntimeException $e) {
            $this->erro(502, $e->getMessage());
        }
        exit;
    }

    private function erro(int $codigo, string $mensagem): void
    {
        http_response_code($codigo);
        header('Cache-Control: no-store');
        echo json_encode(['erro' => $mensagem], JSON_UNESCAPED_UNICODE);
    }
}
