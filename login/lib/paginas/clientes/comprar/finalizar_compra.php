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
</head>
<body>
    <h2>Finalizar Compra</h2>

    <?php if (!empty($limite_cred) && $limite_cred > 0): ?>
        <h3>Saldo disponível no crediário: R$ <?php echo number_format($limite_cred, 2, ',', '.'); ?></h3>
    <?php endif; ?>

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
            <label>Escolha a 1ª forma de pagamento:</label>
            <select name="forma_pagamento" id="forma_pagamento" onchange="formasPagamento()">
                <option value="selecionar">Selecionar</option>
                <option value="pix">PIX</option>
                <?php if ($cartao_credito_ativo): ?>
                    <option value="cartaoCred">Cartão de Crédito</option>
                <?php endif; ?>

                <?php if ($cartao_debito_ativo): ?>
                    <option value="cartaoDeb">Cartão de Débito</option>
                <?php endif; ?>

                <?php if (!empty($limite_cred) && $limite_cred > 0): ?>
                    <option value="crediario">Crediario</option>
                <?php endif; ?>

                <option value="boleto">Boleto Bancário</option>

                <?php if ($outros): ?>
                    <option value="outros">Outros</option>
                <?php endif; ?>
            </select>
            
            <div id="entrada" style="display: none; margin-top: 10px;">
                <h3>1ª forma de pagamento ou Entrada</h3>
                <label for="entradaInput">Valor da entrada: </label>
                <input type="text" id="entradaInput" name="entradaInput">
                <br>
                <label>Escolha a forma de pagamento:</label>
                <select name="forma_pagamento_entrada" id="forma_pagamento_entrada" onchange="formasPagamentoEntrada()">
                    <option value="pix">PIX</option>
                    <?php if ($cartao_credito_ativo): ?>
                        <option value="cartaoCred">Cartão de Crédito</option>
                    <?php endif; ?>

                    <?php if ($cartao_debito_ativo): ?>
                        <option value="cartaoDeb">Cartão de Débito</option>
                    <?php endif; ?>

                </select>  
                <p>Restante: <span id="restante">R$ 0,00</span></p> 
            </div>

            <!-- Áreas para exibir os cartões aceitos -->
            <div id="cartoesCredAceitos" style="display: none; margin-top: 10px;">
                <p>Cartões de Crédito aceitos: <?php echo htmlspecialchars($parceiro['cartao_credito']); ?></p>
            </div>

            <div id="cartoesDebAceitos" style="display: none; margin-top: 10px;">
                <p>Cartões de Débito aceitos: <?php echo htmlspecialchars($parceiro['cartao_debito']); ?></p>
            </div>

            <div id="outros" style="display: none; margin-top: 10px;">
                <p>Outras formas de pagamento disponíveis: <?php echo htmlspecialchars($parceiro['outras_formas']); ?></p>
            </div>

            <!-- Opção de Crediário -->
            <div id="crediarioOpcoes" style="display: none; margin-top: 10px;">
                <h3>Escolha em quantas parcelas você prefere.</h3>
                <label>Dividir em 
                    <select name="parcelas" id="parcelas">
                        <option value="">Selecione</option>
                    </select>
                    parcelas.
                </label>
            </div>
            <br>
            <a href="javascript:history.back()" class="voltar">Voltar</a>
            <button type="submit">Finalizar Compra</button>
        </form>
    <?php else: ?>
        <p>Erro: Nenhum produto encontrado.</p>
    <?php endif; ?>

    <script>

        let maiorFrete = parseFloat("<?php echo $maiorFrete; ?>");
        let totalGeral = parseFloat("<?php echo $totalGeral; ?>");
        let enderecoCadastrado = "<?php echo $endereco_cadastrado ?? ''; ?>";
        let valorTaxaCrediario = "<?php echo $valorTaxaCrediario ?? ''; ?>";
        let taxaCred = document.getElementById('taxaCred');
        let totalAtual = parseFloat("<?php echo $totalGeral; ?>");

        function verificarEndereco() {
            if (enderecoCadastrado.trim() !== "") {
                document.getElementById("enderecoCadastrado").style.display = "block";
                document.getElementById("enderecoParceiro").style.display = "none";
            } else {
                usarEnderecoCadastrado();
                document.getElementById("enderecoParceiro").style.display = "none";
            }
            atualizarTotal(true);
            formasPagamento();
            atualizarRestante();
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
            atualizarTotal(false);
            formasPagamento();
            atualizarRestante();
        }
        
        function formasPagamento() {
            let formaPagamento = document.getElementById("forma_pagamento").value;
            let pix = document.getElementById("pix");
            let cartoesCredAceitos = document.getElementById("cartoesCredAceitos");
            let cartoesDebAceitos = document.getElementById("cartoesDebAceitos");
            let crediarioOpcoes = document.getElementById("crediarioOpcoes");
            let parcelasSelect = document.getElementById("parcelas");
            let outros = document.getElementById("outros");
            let taxaCred = document.getElementById("taxaCred");
            let entrada = document.getElementById("entrada");

            // Esconde todos os elementos antes de exibir o correto
            if (pix) pix.style.display = "none";
            if (cartoesCredAceitos) cartoesCredAceitos.style.display = "none";
            if (cartoesDebAceitos) cartoesDebAceitos.style.display = "none";
            if (crediarioOpcoes) crediarioOpcoes.style.display = "none";
            if (outros) outros.style.display = "none";
            if (taxaCred) taxaCred.style.display = "none";
            if (entrada) entrada.style.display = "none";

            if (formaPagamento === "selecionar") {
                if (entrada) entrada.style.display = "none";
            } else {
                if (entrada) {
                    entrada.style.display = "block";
                    atualizarRestante(); // Calcular o restante quando o campo de entrada for exibido
                }
            }

            if (formaPagamento === "pix") {
                if (pix) pix.style.display = "block";
            } else if (formaPagamento === "cartaoCred") {
                if (cartoesCredAceitos) cartoesCredAceitos.style.display = "block";
            } else if (formaPagamento === "cartaoDeb") {
                if (cartoesDebAceitos) cartoesDebAceitos.style.display = "block";
            } else if (formaPagamento === "crediario") {
                if (crediarioOpcoes) crediarioOpcoes.style.display = "block";
                parcelasSelect.innerHTML = '<option value="">Selecione</option>';

                let maxParcelas = document.getElementById("qt_parcelas").value;

                if (entrada) entrada.style.display = "block";

                if (maxParcelas > 0) {
                    for (let i = 1; i <= maxParcelas; i++) {
                        let valorParcela;
                        let labelJuros = ""; // Texto para indicar se há juros

                        if (i > 3) {
                            // Aplicar juros compostos para parcelas acima de 3x
                            let taxaJuros = 0.0299; // 2.99% ao mês
                            valorParcela = (totalAtual * Math.pow(1 + taxaJuros, i)) / i;
                            labelJuros = " 2,99% a.m.";
                        } else {
                            // Parcelas sem juros
                            valorParcela = totalAtual / i;
                            labelJuros = " sem juros";
                        }

                        let option = document.createElement("option");
                        option.value = i + "x";
                        option.textContent = `${i}x de R$ ${valorParcela.toFixed(2).replace('.', ',')}${labelJuros}`;
                        parcelasSelect.appendChild(option);
                    }
                } else {
                    console.error("Erro: qt_parcelas inválido.");
                }

                atualizarTabelaCrediario(); // Atualizar a tabela de produtos com a taxa do crediário
            } else if (formaPagamento === "outros") {
                if (outros) outros.style.display = "block";
            }

            atualizarTotal(document.querySelector('input[name="entrega"]:checked').value === "entregar");
            atualizarRestante(); // Recalcular o restante toda vez que a forma de pagamento for alterada
        }

        function atualizarTabelaCrediario() {
            let produtos = <?php echo json_encode($produtos); ?>;
            let taxaCrediario = parseFloat("<?php echo $valorTaxaCrediario; ?>") / 100;
            let tabela = document.querySelector("table");
            let linhas = tabela.querySelectorAll("tr");

            // Remove todas as linhas de produtos existentes
            linhas.forEach((linha, index) => {
                if (index > 0) linha.remove();
            });

            let totalGeral = 0;
            let maiorFrete = 0;
            
            produtos.forEach(produto => {
                let valorProComTaxa = produto.valor_produto * (1 + taxaCrediario);
                let total = valorProComTaxa * produto.qt;
                totalGeral += total;

                // Verifica o maior frete no carrinho
                if (produto.frete > maiorFrete) {
                    maiorFrete = produto.frete;
                }

                let linha = document.createElement("tr");
                linha.innerHTML = `
                    <td>${produto.nome_produto}</td>
                    <td style="text-align: center;">${produto.qt}</td>
                    <td>R$ ${valorProComTaxa.toFixed(2).replace('.', ',')}</td>
                    <td>R$ ${total.toFixed(2).replace('.', ',')}</td>
                `;
                tabela.appendChild(linha);
            });

            let totalComFrete = parseFloat(totalGeral) + parseFloat(maiorFrete);
            document.getElementById('Total').innerText = 'R$ ' + totalGeral.toFixed(2).replace('.', ',');
            let freteComTaxa = maiorFrete + ((maiorFrete * parseFloat("<?php echo $valorTaxaCrediario; ?>")) / 100);
            document.getElementById('frete').innerText = (maiorFrete > 0) ? 'R$ ' + freteComTaxa.toFixed(2).replace('.', ',') : 'Entrega Grátis';
            document.getElementById('ValorTotal').innerText = 'R$ ' + (totalGeral + (document.querySelector('input[name="entrega"]:checked').value === "entregar" ? freteComTaxa : maiorFrete)).toFixed(2).replace('.', ',');
        }

        document.getElementById('entradaInput').addEventListener('input', function() {
            let entrada = this.value.replace(/\D/g, ''); // Remove todos os caracteres não numéricos
            entrada = (entrada / 100).toFixed(2); // Divide por 100 e fixa duas casas decimais
            this.value = formatarValor(this.value); // Formata o valor enquanto o usuário digita

            atualizarRestante();
        });

        document.getElementById('entradaInput').addEventListener('input', function() {
            let entrada = parseFloat(this.value.replace(',', '.')) || 0;
            let totalBase = parseFloat(document.getElementById('ValorTotal').innerText.replace('R$', '').replace(',', '.'));
            let restante = totalBase - entrada;
            document.getElementById('restante').innerText = 'R$ ' + restante.toFixed(2).replace('.', ',');
        });

        function atualizarTotal(cobrarFrete) {
            // Garantir que o cálculo do total sempre seja reiniciado corretamente
            let totalBase = totalGeral;

            // Atualizar o frete na tela
            let selectPagamento = document.getElementById("forma_pagamento");
            let opcaoSelecionada = selectPagamento.value;
            let freteComTaxa = maiorFrete;

            if (opcaoSelecionada === "crediario" && cobrarFrete) {
                freteComTaxa += (maiorFrete * parseFloat("<?php echo $valorTaxaCrediario; ?>")) / 100;
            }

            let freteTexto = (cobrarFrete && maiorFrete > 0) ? 'R$ ' + freteComTaxa.toFixed(2).replace('.', ',') : 'Entrega Grátis';
            document.getElementById('frete').innerText = freteTexto;

            // Atualizar o valor total antes de aplicar a taxa
            document.getElementById('ValorTotal').innerText = 'R$ ' + (totalBase + (cobrarFrete ? freteComTaxa : 0)).toFixed(2).replace('.', ',');

            if (opcaoSelecionada === "crediario") {
                let taxaCrediarioValor = (totalBase * valorTaxaCrediario) / 100;
                document.getElementById('taxaCrediario').innerText = taxaCrediarioValor.toFixed(2).replace('.', ',');

                // Atualizar o total corretamente após aplicar a taxa
                totalBase += taxaCrediarioValor;
                document.getElementById('ValorTotal').innerText = 'R$ ' + (totalBase + (cobrarFrete ? freteComTaxa : maiorFrete)).toFixed(2).replace('.', ',');

                //console.log("Taxa crediário aplicada com frete:", cobrarFrete ? "Sim" : "Não");
            } else {
                //console.log("Nenhuma taxa de crediário aplicada.");
            }

            atualizarRestante(); // Recalcular o restante após atualizar o total
        }

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
            }else if (value.length > 1) {
                value = value.replace(/(\d{1})/, '($1');
            }
            input.value = value;
        }
        
        function verificaCelular(){
            var celular =document.getElementById('contato').value;
            //console.log(celular.length);
            if(celular.length < 15 ){
                
                document.querySelector('#msgAlerta').textContent = "Preencha o campo Celular corretamente!";
                document.getElementById('contato').focus();
            }else{
                document.querySelector('#msgAlerta').textContent = "";
            }
        }

        function atualizarRestante() {
            let entrada = parseFloat(document.getElementById('entradaInput').value.replace(',', '.')) || 0;
            let totalBase = parseFloat(document.getElementById('ValorTotal').innerText.replace('R$', '').replace(',', '.'));
            let restante = totalBase - entrada;
            document.getElementById('restante').innerText = 'R$ ' + restante.toFixed(2).replace('.', ',');
        }

        document.querySelectorAll('input[name="entrega"]').forEach(radio => {
            radio.addEventListener('change', function() {
                atualizarTotal(this.value === "entregar");
            });
        });

        document.getElementById('forma_pagamento').addEventListener('change', function() {
            formasPagamento();
        });

    </script>

</body>
</html>
