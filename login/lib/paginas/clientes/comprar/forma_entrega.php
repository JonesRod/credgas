<?php
    session_start();
    include('../../../conexao.php'); // Conexão com o banco

    $id_cliente = isset($_GET['id_cliente']) ? intval($_GET['id_cliente']) : 0;
    $id_parceiro = isset($_GET['id_parceiro']) ? intval($_GET['id_parceiro']) : 0;

    // Buscar os produtos do carrinho
    $stmt = $mysqli->prepare("SELECT c.*, p.nome_produto, p.valor_produto, p.qt_parcelas, c.frete FROM carrinho c 
                            JOIN produtos p ON c.id_produto = p.id_produto 
                            WHERE c.id_cliente = ? AND p.id_parceiro = ?");

    $stmt->bind_param("ii", $id_cliente, $id_parceiro);
    $stmt->execute();
    $result = $stmt->get_result();
    $produtos = $result->fetch_all(MYSQLI_ASSOC);

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

    // Prepara e executa a consulta
    $taxa = $mysqli->prepare("SELECT data_alteracao, taxa_padrao, taxa_crediario FROM config_admin WHERE taxa_crediario <> '' ORDER BY data_alteracao DESC LIMIT 1");

    if ($taxa) {
        $taxa->execute();
        $taxa->bind_result($dataRegistro,$taxa_padrao, $valorTaxaCrediario);
        $taxa->fetch();
        $taxa->close();
    }

    if (!empty($dataRegistro) && !empty($valorTaxaCrediario)) {
        // Formata a taxa para exibir vírgula decimal e ponto como separador de milhar
        //$valorTaxaCrediario = number_format($valorTaxaCrediario, 2, ',', '.');
        
        //echo "Última alteração: " . $dataRegistro . " - Taxa: " . $valorTaxaCrediario . "%";
    } else {
        //echo "Nenhum registro encontrado.";
    }
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
}

table {
    width: 100%;
    border-collapse: collapse;
    margin: 20px 0;
}

table, th, td {
    border: 1px solid #ddd;
}

th, td {
    padding: 8px;
    text-align: left;
}

th {
    background-color: #f2f2f2;
}

form {
    max-width: 600px;
    margin: 0 auto;
    padding: 20px;
    background-color: #fff;
    border: 1px solid #ddd;
    border-radius: 5px;
}

label {
    display: block;
    margin: 10px 0 5px;
}

input[type="text"], select {
    width: 100%;
    padding: 8px;
    margin: 5px 0 10px;
    border: 1px solid #ccc;
    border-radius: 4px;
}

button {
    width: 100%;
    padding: 10px;
    background-color: #4CAF50;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

button:hover {
    background-color: #45a049;
}

.voltar {
    display: block;
    text-align: center;
    margin: 20px 0;
    color: #333;
    text-decoration: none;
}

.voltar:hover {
    text-decoration: underline;
}

@media (max-width: 600px) {
    table, th, td {
        display: block;
        width: 100%;
    }

    th, td {
        text-align: right;
        padding-left: 50%;
        position: relative;
    }

    th::before, td::before {
        content: attr(data-label);
        position: absolute;
        left: 0;
        width: 50%;
        padding-left: 10px;
        font-weight: bold;
        text-align: left;
    }

    th::before {
        background-color: #f2f2f2;
    }
}

    </style>
</head>
<body>
    <h2>Minha compra</h2>

    <?php if (!empty($produtos)): ?>
        <table border="1">
            <tr>
                <th>Produto</th>
                <th>Quantidade</th>
                <th>Valor Unitário</th>
                <th>Total</th>
            </tr>
            <?php 
                if (!empty($produtos) && is_array($produtos)) {
                    $totalGeral = 0; // MOVIDO PARA FORA DO LOOP
                    $maiorFrete = 0;
                    $maiorQtPar = 0;

                    foreach ($produtos as $produto): 
                        $qtParcelas = $produto['qt_parcelas'];

                        $valorProComTaxa = ($produto['valor_produto'] * $taxa_padrao) / 100 + $produto['valor_produto'];
                        $total = $valorProComTaxa * $produto['qt'];
                        
                        $totalGeral += $total; // Agora ele soma corretamente a cada loop

                        // Verifica o maior frete no carrinho
                        if ($produto['frete'] > $maiorFrete) {
                            $maiorFrete = $produto['frete'];
                        }

                        // Verifica o maior quantidade de parcelas
                        if ($qtParcelas > $maiorQtPar) {
                            $maiorQtPar = $qtParcelas;
                        }
            ?>
            <tr>
                <td><?php echo htmlspecialchars($produto['nome_produto']); ?></td>
                <td style="text-align: center;"><?php echo $produto['qt']; ?></td>
                <td>R$ <?php echo number_format($valorProComTaxa, 2, ',', '.'); ?></td>
                <td>R$ <?php echo number_format($total, 2, ',', '.'); ?></td>
            </tr>
            <?php endforeach; 
                
                // Soma o maior frete ao total da compra
                $totalComFrete = $totalGeral + $maiorFrete;
                $totalCrediario = $limite_cred - $totalComFrete;
            } else {
                echo '<tr><td colspan="4">Nenhum produto no carrinho.</td></tr>';
            }
            ?>
        </table>

        <form action="processar_pagamento.php" method="post">
            <h3>Total: <span id="Total"><?php echo 'R$ ' . number_format($totalGeral, 2, ',', '.'); ?></span></h3>
            <h3>Frete: <span id="frete"><?php echo ($maiorFrete > 0) ? 'R$ ' . number_format($maiorFrete, 2, ',', '.') : 'Entrega Grátis'; ?></span></h3>
            <input type="hidden" name="valorTaxaCrediario" value="<?php echo $valorTaxaCrediario; ?>">
            <h3 id="taxaCred" style="display: none; margin-top: 10px;">Taxa Crediário: R$  
                <span id="taxaCrediario">
                    <?php
                        $total = ($totalComFrete * $valorTaxaCrediario) / 100;
                        echo number_format($total, 2, ',', '.'); 
                    ?>
                </span>
            </h3>
            
            <h3>Valor Total: <span id="ValorTotal"><?php echo 'R$ ' . number_format($totalComFrete, 2, ',', '.'); ?></span></h3>
            <h2>Escolha a forma de entrega!</h2>
            <label>
                <input type="radio" name="entrega" value="entregar" checked onclick="verificarEndereco()"> Entregar
            </label>
            <label>
                <input type="radio" name="entrega" value="buscar" onclick="atualizarTotal(false); esconderCamposEndereco(); mostrarEnderecoLoja()"> Retirar no local
            </label>

            <?php if (!empty($endereco_cadastrado)): ?>
                <div id="enderecoCadastrado">
                    <h3>Meu endereço</h3>
                    <p><strong>Cidade/UF:</strong> 
                        <span id="cidade_uf"><?php echo $cidade.'/'.$uf; ?></span>
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
                <br>
                <br>
                <label>Rua/Av.:</label>
                <input type="text" name="rua" placeholder="Digite a rua/avenida"><br>

                <label>Bairro:</label>
                <input type="text" name="bairro" placeholder="Digite o bairro"><br>

                <label>Número:</label>
                <input type="text" name="numero" placeholder="Digite o número"><br>

                <label>Contato/WhatsApp:</label>
                <input type="text" name="contato" id="contato" placeholder="Digite o número" oninput="formatarCelular(this)" onblur="verificaCelular()"><br>

                <button type="button" onclick="usarEnderecoCadastrado()">Usar meu endereço</button>
            </div>

            <?php if (!empty($endereco_parceiro)): ?>
                <div id="enderecoParceiro" style="display: none;">
                    <p>
                        <strong>
                            Loja: <span id="nomeFantasia"><?php echo $nomeFantasia; ?></span>
                        </strong> 
                    </p>
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

            <input type="hidden" name="id_parceiro" value="<?php echo $id_parceiro; ?>">
            
            <input type="hidden" id="qt_parcelas" value="<?php echo $maiorQtPar; ?>">
            <br>
            <a href="javascript:history.back()" class="voltar">Voltar</a>
            <button type="submit">Continua</button>
        </form>

    <?php else: ?>
        <p>Erro: Nenhum produto encontrado.</p>
    <?php endif; ?>

    <script>

        let maiorFrete = parseFloat("<?php echo $maiorFrete; ?>");
        let totalGeral = parseFloat("<?php echo $totalGeral; ?>");
        let enderecoCadastrado = "<?php echo $endereco_cadastrado ?? ''; ?>";

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

        function mostrarEnderecoLoja(){
            document.getElementById("enderecoParceiro").style.display = "block";
        }
        

        document.querySelectorAll('input[name="entrega"]').forEach(radio => {
            radio.addEventListener('change', function() {
                atualizarTotal(this.value === "entregar");
            });
        });

        function atualizarTotal(cobrarFrete) {
            // Garantir que o cálculo do total sempre seja reiniciado corretamente
            let totalBase = totalGeral;

            // Atualizar o frete na tela
            let freteComTaxa = maiorFrete;
            let freteTexto = (cobrarFrete && maiorFrete > 0) ? 'R$ ' + freteComTaxa.toFixed(2).replace('.', ',') : 'Entrega Grátis';
            document.getElementById('frete').innerText = freteTexto;

            // Atualizar o valor total
            let totalComFrete = totalBase + (cobrarFrete ? freteComTaxa : 0);
            document.getElementById('ValorTotal').innerText = 'R$ ' + totalComFrete.toFixed(2).replace('.', ',');

        }

    </script>

</body>
</html>
