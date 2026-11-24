<?php

/**
 * Consulta de endereço na API pública ViaCEP (https://viacep.com.br).
 * Não usa o nosso banco: a "fonte de dados" deste Model é uma API externa,
 * que responde em JSON. Ex.: https://viacep.com.br/ws/26210000/json/
 */
class Cep
{
    private const URL = 'https://viacep.com.br/ws/%s/json/';

    /**
     * Devolve ['cep', 'logradouro', 'bairro', 'cidade', 'estado'].
     * Lança InvalidArgumentException (CEP mal digitado), DomainException (CEP não existe)
     * ou RuntimeException (API fora do ar / sem internet).
     */
    public function buscar(string $cep): array
    {
        $cep = preg_replace('/\D/', '', $cep);
        if (strlen($cep) !== 8) {
            throw new InvalidArgumentException('CEP inválido. Informe 8 números.');
        }

        $resposta = $this->baixar(sprintf(self::URL, $cep));
        $dados = json_decode((string) $resposta, true);

        if (!is_array($dados)) {
            throw new RuntimeException('Não foi possível consultar o serviço de CEP.');
        }
        if (isset($dados['erro'])) {   // o ViaCEP responde {"erro": true} quando o CEP não existe
            throw new DomainException('CEP não encontrado.');
        }

        return [
            'cep'        => $dados['cep'] ?? '',
            'logradouro' => $dados['logradouro'] ?? '',
            'bairro'     => $dados['bairro'] ?? '',
            'cidade'     => $dados['localidade'] ?? '',
            'estado'     => $dados['uf'] ?? '',
        ];
    }

    /** Faz a requisição GET com tempo limite (cURL; se não houver, file_get_contents). */
    private function baixar(string $url): string
    {
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
            $resposta = curl_exec($ch);
            $erro = curl_error($ch);
            curl_close($ch);

            if ($erro || $resposta === false) {
                throw new RuntimeException('Não foi possível consultar o serviço de CEP.');
            }
            return $resposta;
        }

        $contexto = stream_context_create(['http' => ['timeout' => 5]]);
        $resposta = @file_get_contents($url, false, $contexto);
        if ($resposta === false) {
            throw new RuntimeException('Não foi possível consultar o serviço de CEP.');
        }
        return $resposta;
    }
}
