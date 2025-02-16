<?php
    session_start();
    include('../../../conexao.php'); // Conexão com o banco

    $id_cliente = isset($_GET['id_cliente']) ? intval($_GET['id_cliente']) : 0;
    $id_parceiro = isset($_GET['id_parceiro']) ? intval($_GET['id_parceiro']) : 0;

    // Buscar os produtos do carrinho
    $stmt = $mysqli->prepare("SELECT c.*, p.nome_produto, p.valor_produto, c.frete FROM carrinho c 
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

    $cidade_parceiro = !empty($parceiro['cidade']) ? $parceiro['cidade'] : '';
    $uf_parceiro = !empty($parceiro['estado']) ? $parceiro['estado'] : '';
    $endereco_parceiro = !empty($parceiro['endereco']) ? $parceiro['endereco'] : '';
    $numero_parceiro = !empty($parceiro['numero']) ? $parceiro['numero'] : '';
    $bairro_parceiro = !empty($parceiro['bairro']) ? $parceiro['bairro'] : '';

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
            $totalGeral = 0;
            $maiorFrete = 0;

            foreach ($produtos as $produto): 
                $total = $produto['valor_produto'] * $produto['qt'];
                $totalGeral += $total;

                // Verifica o maior frete no carrinho
                if ($produto['frete'] > $maiorFrete) {
                    $maiorFrete = $produto['frete'];
                }
            ?>
            <tr>
                <td><?php echo htmlspecialchars($produto['nome_produto']); ?></td>
                <td><?php echo $produto['qt']; ?></td>
                <td>R$ <?php echo number_format($produto['valor_produto'], 2, ',', '.'); ?></td>
                <td>R$ <?php echo number_format($total, 2, ',', '.'); ?></td>
            </tr>
            <?php endforeach; 
            
            // Soma o maior frete ao total da compra
            $totalComFrete = $totalGeral + $maiorFrete;
            $totalCrediario = $limite_cred - $totalComFrete;
            ?>
        </table>

        <form action="processar_pagamento.php" method="post">
            <h3>Total: <span><?php echo 'R$ ' . number_format($totalGeral, 2, ',', '.'); ?></span></h3>
            <h3>Frete: <span id="frete"><?php echo ($maiorFrete > 0) ? 'R$ ' . number_format($maiorFrete, 2, ',', '.') : 'Entrega Grátis'; ?></span></h3>
            <h3>Valor Total: <span id="total"><?php echo 'R$ ' . number_format($totalComFrete, 2, ',', '.'); ?></span></h3>
        
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
                    <button type="button" onclick="usarEnderecoCadastrado()">Usar este endereço</button>
                    <button type="button" onclick="mostrarCamposEndereco()">Outro endereço</button>
                </div>
            <?php endif; ?>

            <div id="novoEndereco" style="display: none; margin-top: 10px;">
                <h3>Endereço da entrega</h3>
                <label>Rua/Av.:</label>
                <input type="text" name="rua" placeholder="Digite a rua/avenida"><br>

                <label>Bairro:</label>
                <input type="text" name="bairro" placeholder="Digite o bairro"><br>

                <label>Número:</label>
                <input type="text" name="numero" placeholder="Digite o número"><br>

                <button type="button" onclick="usarEnderecoCadastrado()">Usar meu endereço</button>
            </div>

            <?php if (!empty($endereco_parceiro)): ?>
                <div id="enderecoParceiro" style="display: none;">
                    <h3>Endereço da loja</h3>
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
                </div>
            <?php endif; ?>

            <input type="hidden" name="id_parceiro" value="<?php echo $id_parceiro; ?>">
            <input type="hidden" id="inputTotal" name="total" value="<?php echo $totalComFrete; ?>">
            <br>
            <label>Escolha a forma de pagamento:</label>
            <select name="forma_pagamento" id="forma_pagamento" onchange="mostrarCartoes()">
                <option value="pix">PIX</option>
                <?php if ($cartao_credito_ativo): ?>
                    <option value="cartaoCred">Cartão de Crédito</option>
                <?php endif; ?>

                <?php if ($cartao_debito_ativo): ?>
                    <option value="cartaoDeb">Cartão de Débito</option>
                <?php endif; ?>

                <option value="boleto">Boleto Bancário</option>

                <?php if ($outros): ?>
                    <option value="outros">Outros</option>
                <?php endif; ?>
            </select>

<!-- Áreas para exibir os cartões aceitos -->
<div id="cartoesCredAceitos" style="display: none;">
    <p>Cartões de Crédito aceitos: <?php echo htmlspecialchars($parceiro['cartao_credito']); ?></p>
</div>

<div id="cartoesDebAceitos" style="display: none;">
    <p>Cartões de Débito aceitos: <?php echo htmlspecialchars($parceiro['cartao_debito']); ?></p>
</div>

<div id="outros" style="display: none;">
    <p>Outras formas de pagamento disponíveis: <?php echo htmlspecialchars($parceiro['outras_formas']); ?></p>
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

        function verificarEndereco() {
            atualizarTotal(true);
            
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

        function atualizarTotal(cobrarFrete) {
            let total = cobrarFrete ? totalGeral + maiorFrete : totalGeral;
            
            // Se o frete for maior que zero, exibe o valor do frete; se não, exibe "Entrega Grátis"
            let freteTexto = (cobrarFrete && maiorFrete > 0) ? 'R$ ' + maiorFrete.toFixed(2).replace('.', ',') : 'Entrega Grátis';

            document.getElementById('frete').innerText = freteTexto;
            document.getElementById('total').innerText = 'R$ ' + total.toFixed(2).replace('.', ',');
            document.getElementById('inputTotal').value = total;
        }


        function mostrarCartoes() {
    let formaPagamento = document.getElementById("forma_pagamento").value;
    
    let cartoesCredAceitos = document.getElementById("cartoesCredAceitos");
    let cartoesDebAceitos = document.getElementById("cartoesDebAceitos");
    let outros = document.getElementById("outros");

    // Esconde todos os elementos antes de exibir o correto
    cartoesCredAceitos.style.display = "none";
    cartoesDebAceitos.style.display = "none";
    outros.style.display = "none";

    if (formaPagamento === "cartaoCred") {
        cartoesCredAceitos.style.display = "block";
    } else if (formaPagamento === "cartaoDeb") {
        cartoesDebAceitos.style.display = "block";
    } else if (formaPagamento === "outros") {
        outros.style.display = "block";
    }
}


</script>

</body>
</html>
