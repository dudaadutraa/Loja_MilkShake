/*
 * Busca de endereço pelo CEP (usada no painel e na loja).
 *
 * Como usar no HTML:
 *   <input data-cep data-endereco="#idDoCampoEndereco" data-status="#idDoAviso">
 *
 * Quando o campo perde o foco, o navegador chama o NOSSO servidor
 * (index.php?controller=cep&action=buscar&cep=...), que consulta o ViaCEP
 * e devolve um JSON. O navegador nunca fala direto com o ViaCEP.
 */
(function () {
    function iniciar(campo) {
        var endereco = document.querySelector(campo.dataset.endereco);
        var status = document.querySelector(campo.dataset.status);
        var url = campo.dataset.url || 'index.php?controller=cep&action=buscar&cep=';

        function aviso(texto) {
            if (status) { status.textContent = texto; }
        }

        // máscara 00000-000
        campo.addEventListener('input', function () {
            var n = campo.value.replace(/\D/g, '').slice(0, 8);
            campo.value = n.length > 5 ? n.slice(0, 5) + '-' + n.slice(5) : n;
        });

        campo.addEventListener('blur', function () {
            var cep = campo.value.replace(/\D/g, '');
            if (cep.length !== 8) {
                aviso(cep.length ? 'O CEP precisa ter 8 números.' : '');
                return;
            }

            aviso('Buscando endereço...');

            fetch(url + cep)
                .then(function (resposta) { return resposta.json(); })
                .then(function (dados) {
                    if (dados.erro) {
                        aviso(dados.erro);
                        return;
                    }
                    if (endereco) {
                        endereco.value = dados.logradouro + ', ' + dados.bairro + ' - ' + dados.cidade + '/' + dados.estado;
                        endereco.focus();   // o usuário completa com o número
                    }
                    aviso('Endereço preenchido. Complete com o número.');
                })
                .catch(function () {
                    aviso('Não foi possível consultar o CEP agora. Digite o endereço.');
                });
        });
    }

    document.querySelectorAll('input[data-cep]').forEach(iniciar);
})();
