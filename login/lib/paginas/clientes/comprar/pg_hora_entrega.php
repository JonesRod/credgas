<?php
session_start();
include('../../../conexao.php'); // Conexão com o banco

// Verificação de sessão
if (!isset($_SESSION['id'])) {
    header("Location: ../../../../index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Captura os valores do array $_POST em variáveis
    $id_cliente = isset($_POST['id_cliente']) ? $_POST['id_cliente'] : null;
    $id_parceiro = isset($_POST['id_parceiro']) ? $_POST['id_parceiro'] : null;
    $valor_frete = isset($_POST['valor_frete']) ? $_POST['valor_frete'] : '0.00';
    $valor_total = isset($_POST['valor_total']) ? $_POST['valor_total'] : '0.00';
    $entrada_saldo = isset($_POST['entrada_saldo']) ? $_POST['entrada_saldo'] : '0.00';
    $detalhes_produtos = isset($_POST['detalhes_produtos']) ? $_POST['detalhes_produtos'] : '';
    $entrega = isset($_POST['entrega']) ? $_POST['entrega'] : 'entregar';
    $rua = isset($_POST['rua']) ? $_POST['rua'] : '';
    $bairro = isset($_POST['bairro']) ? $_POST['bairro'] : '';
    $numero = isset($_POST['numero']) ? $_POST['numero'] : '';
    $contato = isset($_POST['contato']) ? $_POST['contato'] : '';
    $comentario = isset($_POST['comentario']) ? $_POST['comentario'] : '';
    $bandeiras_outros_aceitos = isset($_POST['bandeiras_outros_aceitos']) ? $_POST['bandeiras_outros_aceitos'] : '';

    $valor_total = number_format($valor_total, 2, ',', '.');
    $valor_frete = number_format($valor_frete, 2, ',', '.');
    $entrada_saldo = number_format($entrada_saldo, 2, ',', '.');
    // Debug para verificar os valores capturados
    //var_dump($_POST);

    //echo 'hora entrega';
}

?>
<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmar pedido</title>
</head>

<body>
    <h3>Você escolheu em pagar na hora da entrega ou retirada.</h3>
    <p>Valor total da compra: R$ <?php echo $valor_total; ?> .</p>
    <p <?php if (isset($entrada_saldo) && floatval($entrada_saldo) > 0): ?> style="display: block;" <?php else: ?>
            style="display: none;" <?php endif; ?>>
        Saldo utilizado na compra: R$ <?php echo $entrada_saldo; ?> .
    </p>
    <p
        style="<?php echo (isset($valor_frete) && floatval($valor_frete) == 0) ? 'color: darkgreen;' : 'color: black;'; ?>">
        <?php
        if (isset($valor_frete) && floatval($valor_frete) == 0) {
            echo "Entrega Grátis";
        } else {
            echo "Valor do Frete: R$ " . number_format($valor_frete, 2, ',', '.');
        }
        ?>
    </p>

    <p <?php if (isset($entrada_saldo) && floatval($entrada_saldo) >= 0): ?> style="display: block;" <?php else: ?>
            style="display: none;" <?php endif; ?>>
        Valor á pagar: R$
        <?php echo number_format(floatval($valor_total) + floatval($valor_frete) - floatval($entrada_saldo), 2, ',', '.'); ?>
        .
    </p>

    <p>Formas de pagamentos escolhidas:</p>
    <ul>
        <?php
        // Expressão regular para separar itens sem dividir o conteúdo entre parênteses
        preg_match_all('/(?:[^,(]+|\([^)]*\))+/i', $bandeiras_outros_aceitos, $matches);
        $formas_pagamento = $matches[0];
        foreach ($formas_pagamento as $forma): ?>
            <li><?php echo trim($forma); ?>.</li>
        <?php endforeach; ?>
    </ul>

    <p>Confira os tipos de pagamentos e bandeiras aceitas pela loja.</p>

    <p <?php if (isset($entrega) && $entrega == 'entregar'): ?> style="display: block;" <?php else: ?>
            style="display: none;" <?php endif; ?>>
        <strong>O seu pedido ao ser aprovado, será entregue no endereço do usúario cadastrado.</strong>
    </p>

    <div <?php if (isset($entrega) && $entrega == 'entregar' && isset($rua) && $rua != ''): ?> style="display: block;"
        <?php else: ?> style="display: none;" <?php endif; ?>>
        <strong>O seu pedido ao ser aprovado, será entregue na:
            <p for="">Rua/Av: <?php echo $rua; ?>.</p>
            <p for="">Número: <?php echo $numero; ?>.</p>
            <p for="">Bairro: <?php echo $bairro; ?>.</p>
            <p for="">Contato: <?php echo $contato; ?>.</p>
            <p for="">Comentário: <?php echo $comentario; ?>.</p>
    </div>

    <p <?php if (isset($entrega) && $entrega == 'buscar'): ?> style="display: block;" <?php else: ?>
            style="display: none;" <?php endif; ?>>
        <strong>O seu pedido ao ser aprovado, pode ser retirado no endereço da loja.</strong>
    </p>

    <input type="hidden" id="id_cliente" name="id_cliente" value="<?php echo $id_cliente; ?>">
    <input type="hidden" id="id_parceiro" name="id_parceiro" value="<?php echo $id_parceiro; ?>">
    <input type="hidden" id="valor_frete" name="valor_frete" value="<?php echo $valor_frete; ?>">
    <input type="hidden" id="valor_total" name="valor_total" value="<?php echo $valor_total; ?>">
    <input type="hidden" id="entrada_saldo" name="entrada_saldo" value="<?php echo $entrada_saldo; ?>">
    <input type="hidden" id="detalhes_produtos" name="detalhes_produtos" value="<?php echo $detalhes_produtos; ?>">
    <input type="hidden" id="entrega" name="entrega" value="<?php echo $entrega; ?>">
    <input type="hidden" id="rua" name="rua" value="<?php echo $rua; ?>">
    <input type="hidden" id="bairro" name="bairro" value="<?php echo $bairro; ?>">
    <input type="hidden" id="numero" name="numero" value="<?php echo $numero; ?>">
    <input type="hidden" id="contato" name="contato" value="<?php echo $contato; ?>">
    <input type="hidden" id="comentario" name="comentario" value="<?php echo $comentario; ?>">
    <input type="hidden" id="bandeiras_outros_aceitos" name="bandeiras_outros_aceitos"
        value="<?php echo $bandeiras_outros_aceitos; ?>">
    <input type="hidden" id="data_hora" name="data_hora" value="">

    <div>
        <a href="forma_entrega.php?id_cliente=<?php echo $id_cliente; ?>&id_parceiro=<?php echo $id_parceiro; ?>">
            <button type="button">Voltar</button>
        </a>
        <button type="button" id="btn_continuar_pg">Confirmar</button>
    </div>

    <div id="popup-senha" class="popup-content" style="display: none;">
        <h3>Finalizar Compra</h3>
        <p>Senha do Cliente:
            <input type="password" id="senha_cliente" name="senha_cliente">
            <span id="toggle_senha" style="cursor: pointer;">👁️</span>
        </p>
        <p id="msg_erro" style="color: red; display: none;"></p>
        <p id="msg_sucesso" style="color: green; display: none;"></p>
        <p>Digite sua senha para continuar com o pedido.</p>
        <button type="button" id="btn_cancelar">Cancelar</button>
        <button type="button" id="btn_finalizar">Finalizar</button>
    </div>

    <script>
        function obterHorarioLocal() {
            const agora = new Date();

            // Obtém os componentes da data e hora
            const ano = agora.getFullYear();
            const mes = String(agora.getMonth() + 1).padStart(2, '0'); // Mês começa do 0, então +1
            const dia = String(agora.getDate()).padStart(2, '0');
            const hora = String(agora.getHours()).padStart(2, '0');
            const minuto = String(agora.getMinutes()).padStart(2, '0');
            const segundo = String(agora.getSeconds()).padStart(2, '0');

            // Formata a data e hora como YYYY-MM-DD HH:MM:SS
            const dataFormatada = `${ano}-${mes}-${dia} ${hora}:${minuto}:${segundo}`;

            //console.log("Horário do dispositivo:", dataFormatada);
            document.getElementById('data_hora').value = dataFormatada;
        }

        document.addEventListener("DOMContentLoaded", function () {
            const popupSenha = document.getElementById("popup-senha");
            const btnContinuarPg = document.getElementById("btn_continuar_pg");
            const btnCancelar = document.getElementById("btn_cancelar");
            const senhaInput = document.getElementById("senha_cliente");
            const toggleSenha = document.getElementById("toggle_senha");

            let senhaVisivelTimeout;

            obterHorarioLocal();

            // Criar o overlay escuro
            const overlay = document.createElement("div");
            overlay.id = "overlay";
            overlay.style.position = "fixed";
            overlay.style.top = "0";
            overlay.style.left = "0";
            overlay.style.width = "100%";
            overlay.style.height = "100%";
            overlay.style.backgroundColor = "rgba(0, 0, 0, 0.5)";
            overlay.style.zIndex = "999";
            overlay.style.display = "none";
            document.body.appendChild(overlay);

            // Criar o popup de sucesso
            const popupSucesso = document.createElement("div");
            popupSucesso.id = "popup-sucesso";
            popupSucesso.style.display = "none";
            popupSucesso.style.position = "fixed";
            popupSucesso.style.top = "50%";
            popupSucesso.style.left = "50%";
            popupSucesso.style.transform = "translate(-50%, -50%)";
            popupSucesso.style.zIndex = "1000";
            popupSucesso.style.backgroundColor = "#fff";
            popupSucesso.style.padding = "20px";
            popupSucesso.style.borderRadius = "8px";
            popupSucesso.style.boxShadow = "0 4px 8px rgba(0, 0, 0, 0.2)";
            popupSucesso.innerHTML = `
                <h3>Compra Finalizada</h3>
                <p>Sua compra foi finalizada com sucesso!</p>
                <p>Você será redirecionado em <span id="contador">5</span> segundos...</p>
            `;
            document.body.appendChild(popupSucesso);

            // Mostrar o popup de sucesso e o overlay
            function mostrarPopupSucesso() {
                overlay.style.display = "block";
                popupSucesso.style.display = "block";
                document.body.style.overflow = "hidden"; // Travar a rolagem da tela
            }

            // Ocultar o popup de sucesso e o overlay
            function ocultarPopupSucesso() {
                overlay.style.display = "none";
                popupSucesso.style.display = "none";
                document.body.style.overflow = "auto"; // Liberar a rolagem da tela
            }

            // Função para abrir o popup de senha e exibir o overlay ao clicar em "Confirmar"
            btnContinuarPg.onclick = function () {
                overlay.style.display = "block"; // Mostrar o overlay
                popupSenha.style.display = "block";
                popupSenha.style.position = "fixed";
                popupSenha.style.top = "50%";
                popupSenha.style.left = "50%";
                popupSenha.style.transform = "translate(-50%, -50%)";
                popupSenha.style.zIndex = "1000";
                popupSenha.style.backgroundColor = "#fff";
                popupSenha.style.padding = "20px";
                popupSenha.style.boxShadow = "0 4px 8px rgba(0, 0, 0, 0.2)";
            };

            // Função para fechar o popup de senha e ocultar o overlay
            btnCancelar.addEventListener("click", function () {
                popupSenha.style.display = "none";
                overlay.style.display = "none"; // Ocultar o overlay
            });

            // Função para alternar visibilidade da senha
            toggleSenha.addEventListener("click", function () {
                if (senhaInput.type === "password") {
                    senhaInput.type = "text";
                    toggleSenha.textContent = "🙈"; // Ícone para ocultar
                    clearTimeout(senhaVisivelTimeout);
                    senhaVisivelTimeout = setTimeout(() => {
                        senhaInput.type = "password";
                        toggleSenha.textContent = "👁️"; // Ícone para visualizar
                    }, 5000);
                } else {
                    senhaInput.type = "password";
                    toggleSenha.textContent = "👁️"; // Ícone para visualizar
                    clearTimeout(senhaVisivelTimeout);
                }
            });

            // Função para finalizar a compra
            document.getElementById("btn_finalizar").addEventListener("click", function () {
                // Atualizar o campo data_hora antes de enviar os dados
                obterHorarioLocal();

                const dataHora = document.getElementById("data_hora").value;
                if (!dataHora) {
                    document.getElementById("msg_erro").textContent = "Erro: A data e hora não foram definidas.";
                    document.getElementById("msg_erro").style.display = "block";
                    return;
                }

                const formData = {
                    id_cliente: document.getElementById("id_cliente").value,
                    id_parceiro: document.getElementById("id_parceiro").value,
                    valor_frete: parseFloat(document.getElementById("valor_frete").value) || 0,
                    valor_total: parseFloat(document.getElementById("valor_total").value) || 0,
                    entrada_saldo: parseFloat(document.getElementById("entrada_saldo").value) || 0,
                    detalhes_produtos: document.getElementById("detalhes_produtos").value,
                    entrega: document.getElementById("entrega").value,
                    rua: document.getElementById("rua").value,
                    bairro: document.getElementById("bairro").value,
                    numero: document.getElementById("numero").value,
                    contato: document.getElementById("contato").value,
                    comentario: document.getElementById("comentario").value,
                    senha_cliente: document.getElementById("senha_cliente").value,
                    data_hora: dataHora,
                    bandeiras_outros_aceitos: document.getElementById("bandeiras_outros_aceitos").value
                };

                fetch("finalizar_pg_hora_entrega.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify(formData)
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            popupSenha.style.display = "none"; // Ocultar o popup de senha
                            overlay.style.display = "none"; // Ocultar o overlay
                            mostrarPopupSucesso(); // Mostrar o popup de sucesso e o overlay
                            let contador = 5;
                            const intervalo = setInterval(() => {
                                contador--;
                                document.getElementById("contador").textContent = contador;
                                if (contador === 0) {
                                    clearInterval(intervalo);
                                    ocultarPopupSucesso(); // Ocultar o popup e o overlay antes de redirecionar
                                    window.location.href = "meus_pedidos.php"; // Redirecionar após 5 segundos
                                }
                            }, 1000);
                        } else {
                            document.getElementById("msg_erro").textContent = data.message || "Erro ao finalizar a compra.";
                            document.getElementById("msg_erro").style.display = "block";
                        }
                    })
                    .catch(error => {
                        console.error("Erro:", error);
                        document.getElementById("msg_erro").textContent = "Erro ao processar a solicitação.";
                        document.getElementById("msg_erro").style.display = "block";
                    });
            });
        });
    </script>
</body>

</html>