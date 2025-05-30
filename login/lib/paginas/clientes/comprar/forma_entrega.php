<?php
session_start();
include('../../../conexao.php'); // Conexão com o banco

$id_cliente = isset($_GET['id_cliente']) ? intval($_GET['id_cliente']) : 0;
$id_parceiro = isset($_GET['id_parceiro']) ? intval($_GET['id_parceiro']) : 0;

// Obtém a data de hoje menos 1 dia
$data_limite = date('Y-m-d', strtotime('-1 days'));

// Exclui produtos do carrinho do cliente adicionados há mais de 1 dia
$sql_delete = "DELETE FROM carrinho WHERE id_cliente = ? AND DATE(data) < ?";
$stmt_delete = $mysqli->prepare($sql_delete);
$stmt_delete->bind_param("is", $id_cliente, $data_limite);
$stmt_delete->execute();
$stmt_delete->close();

// Buscar os produtos do carrinho
$stmt = $mysqli->prepare("SELECT c.*, p.nome_produto, c.valor_produto, p.taxa_padrao, p.vende_crediario, p.qt_parcelas, c.frete 
                              FROM carrinho c 
                              JOIN produtos p ON c.id_produto = p.id_produto 
                              WHERE c.id_cliente = ? AND p.id_parceiro = ?");

$stmt->bind_param("ii", $id_cliente, $id_parceiro);
$stmt->execute();
$result = $stmt->get_result();
$produtos = $result->fetch_all(MYSQLI_ASSOC);

$taxa_padrao = !empty($produtos) ? $produtos[0]['taxa_padrao'] : 0;
$vendeCrediario = !empty($produtos) ? $produtos[0]['vende_crediario'] : 0;

// Buscar se o parceiro aceita cartão de crédito
$stmt = $mysqli->prepare("SELECT * FROM meus_parceiros WHERE id = ?");
$stmt->bind_param("i", $id_parceiro);
$stmt->execute();
$result = $stmt->get_result();
$parceiro = $result->fetch_assoc();

$cartao_debito_ativo = !empty($parceiro['cartao_debito']);
$cartao_credito_ativo = !empty($parceiro['cartao_credito']); // Se estiver vazio, será falso
$outros = !empty($parceiro['outras_formas']);

$nomeFantasia = !empty($parceiro['nomeFantasia']) ? $parceiro['nomeFantasia'] : '';
$cidade_parceiro = !empty($parceiro['cidade']) ? $parceiro['cidade'] : '';
$uf_parceiro = !empty($parceiro['estado']) ? $parceiro['estado'] : '';
$endereco_parceiro = !empty($parceiro['endereco']) ? $parceiro['endereco'] : '';
$numero_parceiro = !empty($parceiro['numero']) ? $parceiro['numero'] : '';
$bairro_parceiro = !empty($parceiro['bairro']) ? $parceiro['bairro'] : '';
$telefoneComercial = !empty($parceiro['telefoneComercial']) ? $parceiro['telefoneComercial'] : '';
$valor_min_entrega_gratis = !empty($parceiro['valor_min_entrega_gratis']) ? $parceiro['valor_min_entrega_gratis'] : 0;

// Buscar se o parceiro aceita cartão de crédito
$stmt = $mysqli->prepare("SELECT * FROM meus_clientes WHERE id = ?");
$stmt->bind_param("i", $id_cliente);
$stmt->execute();
$result = $stmt->get_result();
$cliente = $result->fetch_assoc();

$limite_cred = !empty($cliente['limite_cred']) ? $cliente['limite_cred'] : 0;

$cidade = !empty($cliente['cidade']) ? $cliente['cidade'] : '';
$uf = !empty($cliente['uf']) ? $cliente['uf'] : '';
$endereco_cadastrado = !empty($cliente['endereco']) ? $cliente['endereco'] : '';
$numero = !empty($cliente['numero']) ? $cliente['numero'] : '';
$bairro = !empty($cliente['bairro']) ? $cliente['bairro'] : '';
$celular1 = !empty($cliente['celular1']) ? $cliente['celular1'] : '';

$saldo = !empty($cliente['saldo']) ? $cliente['saldo'] : 0;

// Definir a variável $valorTaxaCrediario
$valorTaxaCrediario = 5; // Exemplo: 5% de taxa
//var_dump($produtos);
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finalizar Compra</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        h2 {
            text-align: center;
            margin-top: 20px;
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 1px 1px 1px 1px;
            /* Centraliza a tabela */
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        table,
        th,
        td {
            border: 1px solid #ddd;

        }

        th,
        td {
            padding: 5px;
            text-align: left;

        }

        th {
            background-color: #f2f2f2;
            color: #333;

        }

        form {
            max-width: 50%;
            margin: 0 auto;
            padding: 5px;
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .entrega {
            background-color: cornflowerblue;
            margin-top: 10px;
            border: 1px solid #ddd;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border-radius: 5px;
            padding: 10px;
        }

        .entrega h3 {
            margin-top: 5px;
            text-align: center;
        }

        .input-radio {
            display: flex;
            justify-content: center;
            /* Centraliza os elementos */
            align-items: center;
            /* Alinha os itens verticalmente */
            flex-wrap: wrap;
            /* Permite que os elementos quebrem para a linha de baixo se necessário */
            gap: 10px;
            /* Espaçamento entre os elementos */
            text-align: center;
            margin-top: 10px;
            margin-bottom: 20px;
            color: #333;
        }

        .input-radio label {
            display: flex;
            align-items: center;
            /* Mantém o rádio alinhado ao texto */
        }

        .input-radio label input[type="radio"] {
            margin-right: 5px;
            /* Espaço entre o rádio e o texto */
            transform: translateY(-2px);
            /* Move o rádio um pouco para cima */
            vertical-align: middle;
            /* Alinha melhor com o texto */
        }

        .entrega p {
            margin-left: 10px;
        }

        input[type="text"],
        select {
            width: 100%;
            padding: 10px;
            margin: 5px 0 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        .valores {
            margin-top: 10px;
            border: 1px solid #ddd;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: left;
            /* Garante que o texto fique alinhado à esquerda */
            padding: 10px;
            /* Adiciona um espaço interno para não colar no canto */
        }

        .valores h3 {
            font-size: 15px;
            margin: 10px;
            color: #333;
        }

        button {
            width: 100%;
            padding: 12px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background-color: rgb(32, 105, 36);
        }

        #enderecoCadastrado button[onclick="mostrarCamposEndereco()"] {
            background-color: blue;
            color: white;
        }

        #enderecoCadastrado button[onclick="mostrarCamposEndereco()"]:hover {
            background-color: rgb(70, 70, 238)
        }

        #novoEndereco button[onclick="usarEnderecoCadastrado()"] {
            background-color: blue;
            color: white;
        }

        #novoEndereco button[onclick="usarEnderecoCadastrado()"]:hover {
            background-color: rgb(70, 70, 238)
        }

        .voltar {
            display: block;
            text-align: center;
            margin: 20px 0;
            color: #333;
            text-decoration: none;
            font-size: 16px;
        }

        .voltar:hover {
            text-decoration: underline;
        }

        #div_saldo p {
            text-align: center
        }

        #div_valores {
            background-color:rgb(203, 169, 106);
            margin-top: 10px;
            border: 1px solid #ddd;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border-radius: 5px;
            padding: 10px;
        }

        .btn-voltar {
            background-color: #007bff;
            /* Azul */
            color: white;
            border: none;
            padding: 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            width: 93.5%;
            margin-bottom: 10px;
        }

        .btn-voltar:hover {
            background-color: rgb(9, 71, 137);
            /* Azul mais escuro */
        }

        th:nth-child(1),
        td:nth-child(1),
        /* Oculta a coluna ID Produto */
        th:nth-child(6),
        td:nth-child(6) {
            /* Oculta a coluna Vende Crediário */
            display: none;
        }

        .highlight-saldo {
            background-color: #d4edda;
            /* Verde claro */
            border: 2px solid #28a745;
            /* Verde escuro */
            animation: pulse 1.5s infinite;
            /* Animação de movimento */
        }

        @media screen and (max-width: 768px) {
            table {
                width: 100%;
                font-size: 14px;
                /* Reduz o tamanho da fonte para melhor ajuste */
            }

            th,
            td {
                padding: 8px;
                /* Ajusta o espaçamento */
            }

            form {
                margin-left: 1px;
                margin-right: 1px;
                max-width: 100%;
                /* Aumenta a largura do formulário em telas pequenas */
                padding: 10px;

            }

            .input-radio {
                /*flex-direction: column; /* Empilha os itens quando o espaço for pequeno */
                /*align-items: flex-start; /* Alinha os itens à esquerda */
                gap: 5px;
                /* Reduz o espaçamento */
            }

            .input-radio label {
                justify-content: flex-start;
                /* Mantém o alinhamento dos elementos */
            }

            .valores {
                padding: 8px;
                /* Reduz o espaçamento interno */
            }

            .valores h3 {
                font-size: 14px;
                margin: 8px;
            }

            button {
                font-size: 14px;
                /* Reduz o tamanho do botão */
                padding: 10px;
            }

            .voltar {
                font-size: 14px;
                margin: 15px 0;
            }
        }

        @media screen and (max-width: 480px) {
            h2 {
                font-size: 18px;
                /* Reduz o tamanho do título */
            }

            table {
                font-size: 12px;
                /* Ajusta a fonte da tabela */
            }

            th,
            td {
                padding: 6px;
                /* Reduz o padding */
            }

            form {
                max-width: 95%;
                padding: 8px;
            }

            .entrega h3 {
                font-size: 14px;
            }

            .input-radio {
                flex-direction: column;
                /* Empilha os itens quando o espaço for pequeno */
                align-items: flex-start;
                /* Alinha os itens à esquerda */
                gap: 5px;
                /* Reduz o espaçamento */
            }

            .input-radio label {
                font-size: 14px;
            }

            button {
                font-size: 12px;
                padding: 8px;
            }

            .voltar {
                font-size: 12px;
                margin: 10px 0;
            }
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }

            100% {
                transform: scale(1);
            }
        }
    </style>
</head>

<body>
    <h2>Minha compra</h2>
    <?php if (!empty($produtos)): ?>
        <form action="pagamento.php" method="post">
            <table border="1">
                <tr>
                    <th>ID Produto</th>
                    <th>Produto</th>
                    <th>Quantidade</th>
                    <th>Valor Unitário</th>
                    <th>Total</th>
                    <th>Vende Crediário</th>
                </tr>
                <?php
                if (!empty($produtos) && is_array($produtos)) {
                    $totalGeral = 0;
                    $maiorFrete = 0;
                    $maiorQtPar = 0;

                    foreach ($produtos as $produto):
                        $qtParcelas = $produto['qt_parcelas'];

                        $valorProComTaxa = $produto['valor_produto'];
                        $total = $valorProComTaxa * $produto['qt'];

                        $totalGeral += $total;

                        // Verifica o maior frete no carrinho
                        if ($produto['frete'] > $maiorFrete) {
                            $maiorFrete = $produto['frete'];
                        }
                        if ($totalGeral > $valor_min_entrega_gratis) {
                            $maiorFrete = 0; // Se o frete for menor que o mínimo, não cobra frete
                        }
                        // Verifica o maior quantidade de parcelas
                        if ($qtParcelas > $maiorQtPar) {
                            $maiorQtPar = $qtParcelas;
                        }
                        ?>
                        <tr>
                            <td><?php echo $produto['id_produto']; ?></td>
                            <td><?php echo htmlspecialchars($produto['nome_produto']); ?></td>
                            <td style="text-align: center;"><?php echo $produto['qt']; ?></td>
                            <td>R$ <?php echo number_format($valorProComTaxa, 2, ',', '.'); ?></td>
                            <td>R$ <?php echo number_format($total, 2, ',', '.'); ?></td>
                            <td><?php echo $produto['vende_crediario'] ? '1' : '0'; ?></td>
                        </tr>
                    <?php endforeach;

                    // Soma o maior frete ao total da compra
                    $totalComFrete = $totalGeral + $maiorFrete;
                    $totalCrediario = $limite_cred - $totalComFrete;
                } else {
                    echo '<tr><td colspan="6">Nenhum produto no carrinho.</td></tr>';
                }
                ?>
            </table>

            <input type="hidden" name="produtos" id="produtos" value="">
            <script>
                // Preenche o campo oculto com os dados dos produtos
                const produtos = <?php echo json_encode($produtos); ?>;
                document.getElementById('produtos').value = JSON.stringify(produtos);
            </script>

            <div class="entrega">
                <h3>Escolha a forma de entrega.</h3>
                <div class="input-radio">
                    <label>
                        <input type="radio" name="entrega" value="entregar" checked onclick="verificarEndereco()"> Entregar
                    </label>
                    <label>
                        <input type="radio" name="entrega" value="buscar"
                            onclick="atualizarTotal(false); esconderCamposEndereco(); mostrarEnderecoLoja()"> Retirar no
                        local
                    </label>
                </div>

                <div class="valores" id="div_valores">
                    <h3>Total: <span id="Total"><?php echo 'R$ ' . number_format($totalGeral, 2, ',', '.'); ?></span></h3>
                    <h3>Frete: <span
                            id="frete"><?php echo ($maiorFrete > 0) ? 'R$ ' . number_format($maiorFrete, 2, ',', '.') : 'Entrega Grátis'; ?></span>
                    </h3>
                    <?php if (isset($saldo) && $saldo > 0): ?>
                        <div id="div_saldo" class="valores highlight-saldo">
                            <h3>Saldo disponível: <span
                                    id="saldo"><?php echo 'R$ ' . number_format($saldo, 2, ',', '.'); ?></span></h3>
                            <p><input type="checkbox" name="checkbox_saldo" id="checkbox_saldo" onchange="toggleCampoSaldo()">
                                <label for="checkbox_saldo">Usar saldo</label>
                            </p>
                            <p id="p_saldo" style="display: none;">Digite o valor que você deseja usar: R$ <input type="text"
                                    oninput="formatarSaldo(this)" name="input_saldo" id="input_saldo" value="0,00"></p>
                        </div>
                    <?php endif; ?>

                    <h3 id="taxaCred" style="display: none; margin-top: 10px;">Taxa Crediário: R$
                        <span id="taxaCrediario">
                            <?php
                            $total = ($totalComFrete * $valorTaxaCrediario) / 100;
                            echo number_format($total, 2, ',', '.');
                            ?>
                        </span>
                    </h3>
                    <h3>Valor Total á pagar: <span
                            id="ValorTotal"><?php echo 'R$ ' . number_format($totalComFrete, 2, ',', '.'); ?></span></h3>
                </div>

                <?php if (!empty($endereco_cadastrado)): ?>
                    <div id="enderecoCadastrado">
                        <h3>Meu endereço</h3>
                        <p><strong>Cidade/UF:</strong>
                            <span id="cidade_uf"><?php echo $cidade . '/' . $uf; ?></span>
                        </p>
                        <p><strong>Rua/AV.:</strong>
                            <span id="enderecoAtual"><?php echo htmlspecialchars($endereco_cadastrado); ?></span>
                        </p>
                        <p><strong>Número:</strong>
                            <span id="enderecoAtual"><?php echo htmlspecialchars($numero); ?></span>
                        </p>
                        <p><strong>Bairro</strong>:</strong>
                            <span id="enderecoAtual"><?php echo htmlspecialchars($bairro); ?></span>
                        </p>
                        <p><strong>WhatsApp:</strong>
                            <span id="celular1"><?php echo htmlspecialchars($celular1); ?></span>
                        </p>
                        <button type="button" onclick="mostrarCamposEndereco()">Outro endereço</button>
                    </div>
                <?php endif; ?>

                <div id="novoEndereco" style="display: none; margin-top: 10px;">
                    <h3>Endereço da entrega</h3>
                    <span id="msgAlerta"></span>

                    <label>Rua/Av.:</label>
                    <input type="text" name="rua" placeholder="Digite a rua/avenida"><br>

                    <label>Bairro:</label>
                    <input type="text" name="bairro" placeholder="Digite o bairro"><br>

                    <label>Número:</label>
                    <input type="text" name="numero" placeholder="Digite o número"><br>

                    <label>Contato/WhatsApp:</label>
                    <input type="text" name="contato" id="contato" placeholder="Digite o número"
                        oninput="formatarCelular(this)" onblur="verificaCelular()"><br>

                    <button type="button" onclick="usarEnderecoCadastrado()">Usar meu endereço</button>
                </div>

                <?php if (!empty($endereco_parceiro)): ?>
                    <div id="enderecoParceiro" style="display: none;">
                        <h3>
                            <strong>
                                Loja: <span id="nomeFantasia"><?php echo $nomeFantasia; ?></span>
                            </strong>
                        </h3>
                        <p><strong>Cidade/UF:</strong>
                            <span id="cidade_uf"><?php echo $cidade_parceiro . '/' . $uf_parceiro; ?></span>
                        </p>
                        <p><strong>Rua/AV.:</strong>
                            <span id="ruaParceiro"><?php echo htmlspecialchars($endereco_parceiro); ?></span>
                        </p>
                        <p><strong>Número:</strong>
                            <span id="numeroParceiro"><?php echo htmlspecialchars($numero_parceiro); ?></span>
                        </p>
                        <p><strong>Bairro:</strong>
                            <span id="bairroParceiro"><?php echo htmlspecialchars($bairro_parceiro); ?></span>
                        </p>
                        <p><strong>WhatsApp:</strong>
                            <span id="telefone"><?php echo htmlspecialchars($telefoneComercial); ?></span>
                        </p>
                    </div>
                <?php endif; ?>
            </div>

            <input type="hidden" name="id_parceiro" value="<?php echo $id_parceiro; ?>">
            <input type="hidden" name="id_cliente" value="<?php echo $id_cliente; ?>">
            <input type="hidden" name="valor_total" value="<?php echo $totalComFrete; ?>">
            <input type="hidden" name="valor_frete" id="valor_frete"
                value="<?php echo ($maiorFrete > 0) ? $maiorFrete : 0; ?>">

            <input type="hidden" id="qt_parcelas" value="<?php echo $maiorQtPar; ?>">
            <input type="hidden" name="detalhes_produtos" id="detalhes_produtos">
            <br>
            <a href="meu_carrinho.php?id_cliente=<?php echo $id_cliente; ?>" class="btn-voltar">
                Voltar
            </a>
            <button type="submit">Continuar</button>
        </form>

    <?php else: ?>
        <p style="text-align: center;">Nenhum produto no carrinho.</p>
        <a href="../cliente_home.php" class="btn-voltar">Voltar</a>
    <?php endif; ?>

    <script>
        let maiorFrete = parseFloat("<?php echo $maiorFrete; ?>");
        let totalGeral = parseFloat("<?php echo $totalGeral; ?>");
        let enderecoCadastrado = "<?php echo $endereco_cadastrado ?? ''; ?>";

        function calcularDescontoSaldo() {
            const inputSaldo = document.getElementById('input_saldo');
            const checkboxSaldo = document.getElementById('checkbox_saldo');
            const valorTotalElement = parseFloat("<?php echo $totalComFrete; ?>");

            if (checkboxSaldo.checked) {
                const valorSaldo = parseFloat(inputSaldo.value.replace(/\./g, '').replace(',', '.')) || 0;
                if (valorSaldo > totalGeral) {
                    alert('O valor do saldo não pode ser maior que o total da compra.');
                    inputSaldo.value = '0,00';
                    document.getElementById('ValorTotal').textContent = 'R$ ' + valorTotalElement.toFixed(2).replace('.', ',');
                    return;
                }
                const novoTotal = totalGeral - valorSaldo + maiorFrete;
                document.getElementById('ValorTotal').textContent = 'R$ ' + novoTotal.toFixed(2).replace('.', ',');
            } else {
                document.getElementById('ValorTotal').textContent = 'R$ ' + totalGeral.toFixed(2).replace('.', ',');
            }
        }

        function toggleCampoSaldo() {
            const checkbox = document.getElementById('checkbox_saldo');
            const campo = document.getElementById('p_saldo');
            const input = document.getElementById('input_saldo');
            const valorTotalElement = parseFloat("<?php echo $totalComFrete; ?>");
            let inputsaldo = parseFloat("<?php echo $saldo; ?>") || 0;

            if (checkbox.checked) {
                campo.style.display = 'block';

                const novoTotal = valorTotalElement - inputsaldo;
                input.value = inputsaldo.toFixed(2).replace('.', ',');
                document.getElementById('ValorTotal').textContent = novoTotal.toFixed(2).replace('.', ',');

            } else {
                campo.style.display = 'none';
                input.value = '0,00';
                document.getElementById('ValorTotal').textContent = valorTotalElement.toFixed(2).replace('.', ',');
            }
        }

        function formatarSaldo(input) {
            let valor_saldo = input.value.replace(/[^\d]/g, ''); // Remove tudo que não for número
            if (valor_saldo) {
                valor_saldo = (parseInt(valor_saldo) / 100).toFixed(2); // Divide por 100 para ajustar os centavos
                input.value = valor_saldo.replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.'); // Formata com ponto de milhar e vírgula para centavos

            } else {
                input.value = '0'
            }
            calcularDescontoSaldo();
        }

        function verificarEndereco() {
            if (enderecoCadastrado.trim() !== "") {
                document.getElementById("enderecoCadastrado").style.display = "block";
                document.getElementById("enderecoParceiro").style.display = "none";
            } else {
                usarEnderecoCadastrado();
                document.getElementById("enderecoParceiro").style.display = "none";
            }
        }

        function mostrarCamposEndereco() {
            document.getElementById("enderecoCadastrado").style.display = "none";
            document.getElementById("novoEndereco").style.display = "block";
        }

        function usarEnderecoCadastrado() {
            document.getElementById("enderecoCadastrado").style.display = "block";
            document.getElementById("novoEndereco").style.display = "none";
        }

        function esconderCamposEndereco() {
            document.getElementById("enderecoCadastrado").style.display = "none";
            document.getElementById("novoEndereco").style.display = "none";
        }

        function mostrarEnderecoLoja() {
            document.getElementById("enderecoParceiro").style.display = "block";
        }

        document.querySelectorAll('input[name="entrega"]').forEach(radio => {
            radio.addEventListener('change', function () {
                atualizarTotal(this.value === "entregar");
            });
        });

        function atualizarTotal(cobrarFrete) {
            // Garantir que o cálculo do total sempre seja reiniciado corretamente
            let totalBase = totalGeral;

            // Atualizar o frete na tela
            let freteComTaxa = maiorFrete;
            let freteTexto = (cobrarFrete && maiorFrete > 0) ? 'R$ ' + freteComTaxa.toFixed(2).replace('.', ',') : 'Entrega Grátis';
            let freteElement = document.getElementById('frete');
            freteElement.innerText = freteTexto;

            // Atualizar o valor do frete no campo oculto
            document.getElementById('valor_frete').value = (cobrarFrete && maiorFrete > 0) ? freteComTaxa : 0;

            // Alterar a cor do texto "Entrega Grátis" para verde
            if (!cobrarFrete || maiorFrete === 0) {
                freteElement.style.color = 'green';
            } else {
                freteElement.style.color = 'black';
            }
            // Atualizar o valor total
            let totalComFrete = totalBase + (cobrarFrete ? freteComTaxa : 0);
            document.getElementById('ValorTotal').innerText = 'R$ ' + totalComFrete.toFixed(2).replace('.', ',');
        }
        // Chamar a função para definir a cor inicial do frete
        atualizarTotal(true);

        function enviarFormulario() {
            const inputSaldo = document.getElementById('input_saldo');
            const valorSaldo = parseFloat(inputSaldo.value.replace(/\./g, '').replace(',', '.')) || 0;
            const totalComFrete = parseFloat("<?php echo $totalComFrete; ?>");
            const checkbox = document.getElementById('checkbox_saldo');

            if (checkbox.checked && valorSaldo <= 0 || valorSaldo > totalComFrete) {
                alert('O valor do saldo deve ser maior que 0 e menor ou igual ao valor total da compra.');
                inputSaldo.value = '0,00';
                inputSaldo.focus(); // Foca no campo para correção
                return false; // Impede o envio do formulário
            }
            //return true; // Permite o envio do formulário

            const form = document.querySelector('form');
            const enderecoSelecionado = document.querySelector('input[name="entrega"]:checked').value;
            const novoEndereco = document.getElementById("novoEndereco");

            // Coletar detalhes dos produtos
            const produtos = <?php echo json_encode($produtos); ?>;
            const detalhesProdutos = produtos.map(produto => {
                return `${produto.nome_produto}/${produto.qt}/${produto.valor_produto}/${produto.qt * produto.valor_produto}`;
            }).join('|');

            document.getElementById('detalhes_produtos').value = detalhesProdutos;

            if (enderecoSelecionado === 'entregar' && novoEndereco.style.display === "block") {
                const rua = document.querySelector('input[name="rua"]').value;
                const bairro = document.querySelector('input[name="bairro"]').value;
                const numero = document.querySelector('input[name="numero"]').value;
                const contato = document.querySelector('input[name="contato"]').value;

                if (!rua || !bairro || !numero || !contato) {
                    alert('Por favor, preencha todos os campos do endereço.');
                    return false;
                }
            }

            form.submit();
        }

        document.querySelector('button[type="submit"]').addEventListener('click', function (event) {
            event.preventDefault();
            enviarFormulario();

        });

        function formatarCelular(input) {
            let value = input.value.replace(/\D/g, ''); // Remove todos os caracteres não numéricos
            if (value.length > 11) value = value.substr(0, 11);
            if (value.length > 10) {
                value = value.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
            } else if (value.length > 6) {
                value = value.replace(/(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3');
            } else if (value.length > 2) {
                value = value.replace(/(\d{2})(\d{0,4})/, '($1) $2');
            } else {
                value = value.replace(/(\d{0,2})/, '($1');
            }
            input.value = value;
        }

        function verificaCelular() {
            const input = document.getElementById('contato');
            const value = input.value.replace(/\D/g, ''); // Remove caracteres não numéricos

            if (value.length < 10 || value.length > 11) {
                alert('Por favor, insira um número de celular válido com DDD.');
                input.focus();
            }
        }
    </script>
</body>

</html>