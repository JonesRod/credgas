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
    $stmt = $mysqli->prepare("SELECT c.*, p.nome_produto, p.valor_produto, p.taxa_padrao, p.qt_parcelas, c.frete FROM carrinho c 
                            JOIN produtos p ON c.id_produto = p.id_produto 
                            WHERE c.id_cliente = ? AND p.id_parceiro = ?");

    $stmt->bind_param("ii", $id_cliente, $id_parceiro);
    $stmt->execute();
    $result = $stmt->get_result();
    $produtos = $result->fetch_all(MYSQLI_ASSOC);

    $taxa_padrao = !empty($produtos) ? $produtos[0]['taxa_padrao'] : 0;

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
            margin: 1px 1px 1px 1px; /* Centraliza a tabela */
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        table, th, td {
            border: 1px solid #ddd;

        }

        th, td {
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
        .entrega{
            margin-top: 10px;
            border: 1px solid #ddd;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border-radius: 5px;
            padding: 10px;
        }
        .entrega h3{
            margin-top: 5px;
            text-align: center;
        }
        .input-radio {
            display: flex;
            justify-content: center; /* Centraliza os elementos */
            align-items: center; /* Alinha os itens verticalmente */
            flex-wrap: wrap; /* Permite que os elementos quebrem para a linha de baixo se necessário */
            gap: 10px; /* Espaçamento entre os elementos */
            text-align: center;
            margin-top: 10px;
            margin-bottom: 20px;
            color: #333;
        }

        .input-radio label {
            display: flex;
            align-items: center; /* Mantém o rádio alinhado ao texto */
        }

        .input-radio label input[type="radio"] {
            margin-right: 5px; /* Espaço entre o rádio e o texto */
            transform: translateY(-2px); /* Move o rádio um pouco para cima */
            vertical-align: middle; /* Alinha melhor com o texto */
        }

        .entrega p{
            margin-left: 10px;
        }

        input[type="text"], select {
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
            text-align: left; /* Garante que o texto fique alinhado à esquerda */
            padding: 10px; /* Adiciona um espaço interno para não colar no canto */
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
            background-color: #45a049;
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

        @media screen and (max-width: 768px) {
            table {
                width: 100%;
                font-size: 14px; /* Reduz o tamanho da fonte para melhor ajuste */
            }

            th, td {
                padding: 8px; /* Ajusta o espaçamento */
            }

            form {
                margin-left: 25px;
                max-width: 95%; /* Aumenta a largura do formulário em telas pequenas */
                padding: 10px;

            }

            .input-radio {
                /*flex-direction: column; /* Empilha os itens quando o espaço for pequeno */
                /*align-items: flex-start; /* Alinha os itens à esquerda */
                gap: 5px; /* Reduz o espaçamento */
            }

            .input-radio label {
                justify-content: flex-start; /* Mantém o alinhamento dos elementos */
            }

            .valores {
                padding: 8px; /* Reduz o espaçamento interno */
            }

            .valores h3 {
                font-size: 14px;
                margin: 8px;
            }

            button {
                font-size: 14px; /* Reduz o tamanho do botão */
                padding: 10px;
            }

            .voltar {
                font-size: 14px;
                margin: 15px 0;
            }
        }

@media screen and (max-width: 480px) {
    h2 {
        font-size: 18px; /* Reduz o tamanho do título */
    }

    table {
        font-size: 12px; /* Ajusta a fonte da tabela */
    }

    th, td {
        padding: 6px; /* Reduz o padding */
    }

    form {
        max-width: 95%;
        padding: 8px;
    }

    .entrega h3 {
        font-size: 14px;
    }

    .input-radio {
        flex-direction: column; /* Empilha os itens quando o espaço for pequeno */
        align-items: flex-start; /* Alinha os itens à esquerda */
        gap: 5px; /* Reduz o espaçamento */
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

    </style>
</head>
<body>
    <h2>Minha compra</h2>

    <?php if (!empty($produtos)): ?>
        <form action="pagamento.php" method="post">        
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

            <div class="entrega">
                <h3>Escolha a forma de entrega.</h3>
                <div class="input-radio">
                    <label>
                        <input type="radio" name="entrega" value="entregar" checked onclick="verificarEndereco()"> Entregar
                    </label>
                    <label>
                        <input type="radio" name="entrega" value="buscar" onclick="atualizarTotal(false); esconderCamposEndereco(); mostrarEnderecoLoja()"> Retirar no local
                    </label>                    
                </div>

                <div class="valores">
                    <h3>Total: <span id="Total"><?php echo 'R$ ' . number_format($totalGeral, 2, ',', '.'); ?></span></h3>
                    <h3>Frete: <span id="frete"><?php echo ($maiorFrete > 0) ? 'R$ ' . number_format($maiorFrete, 2, ',', '.') : 'Entrega Grátis'; ?></span></h3>
                    <h3 id="taxaCred" style="display: none; margin-top: 10px;">Taxa Crediário: R$  
                        <span id="taxaCrediario">
                            <?php
                                $total = ($totalComFrete * $valorTaxaCrediario) / 100;
                                echo number_format($total, 2, ',', '.'); 
                            ?>
                        </span>
                    </h3>
                    <h3>Valor Total: <span id="ValorTotal"><?php echo 'R$ ' . number_format($totalComFrete, 2, ',', '.'); ?></span></h3>
                </div>  

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
            let freteElement = document.getElementById('frete');
            freteElement.innerText = freteTexto;

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
            const form = document.querySelector('form');
            const enderecoSelecionado = document.querySelector('input[name="entrega"]:checked').value;
            const novoEndereco = document.getElementById("novoEndereco");

            if (enderecoSelecionado === 'entregar' && novoEndereco.style.display === "block") {
                const rua = document.querySelector('input[name="rua"]').value;
                const bairro = document.querySelector('input[name="bairro"]').value;
                const numero = document.querySelector('input[name="numero"]').value;
                const contato = document.querySelector('input[name="contato"]').value;

                if (rua && bairro && numero && contato) {
                    form.submit();
                } else {
                    alert('Por favor, preencha todos os campos do endereço.');
                }
            } else {
                form.submit();
            }
        }

        document.querySelector('button[type="submit"]').addEventListener('click', function(event) {
            event.preventDefault();
            enviarFormulario();
        });

        function formatarCelular(input) {
            let value = input.value.replace(/\D/g, ''); // Remove todos os caracteres não numéricos
            if (value.length > 11) {
                value = value.substr(0, 11);
            }
            if (value.length > 10) {
                value = value.replace(/(\d{1})(\d{1})(\d{5})/, '($1$2) $3-');
            } else if (value.length > 6) {
                value = value.replace(/(\d{1})(\d{1})(\d{4})/, '($1$2) $3-');
            } else if (value.length > 2) {
                value = value.replace(/(\d{1})(\d{1})/, '($1$2) ');
            }else if (value.length > 2) {
                value = value.replace(/(\d{1})(\d{1})/, '($1$2) ');
            }else if (value.length = 1) {
                value = value.replace(/(\d{1})/, '($1');
            }
            input.value = value;
        }

    </script>

</body>
</html>
