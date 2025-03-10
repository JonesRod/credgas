<?php
include('../../../conexao.php');
session_start();

if (isset($_SESSION['id']) && isset($_GET['id_cliente'])) {
    $id_cliente = intval($_GET['id_cliente']);

    // Obtém a data de hoje menos 1 dia
    $data_limite = date('Y-m-d', strtotime('-1 days'));

    // Exclui produtos do carrinho do cliente adicionados há mais de 1 dia
    $sql_delete = "DELETE FROM carrinho WHERE id_cliente = ? AND DATE(data) < ?";
    $stmt_delete = $mysqli->prepare($sql_delete);
    $stmt_delete->bind_param("is", $id_cliente, $data_limite);
    $stmt_delete->execute();
    $stmt_delete->close();

    $sql_produtos = $mysqli->query("SELECT c.*, p.nome_produto, p.valor_produto, p.taxa_padrao, pa.nomeFantasia 
                                    FROM carrinho c
                                    INNER JOIN produtos p ON c.id_produto = p.id_produto
                                    INNER JOIN meus_parceiros pa ON c.id_parceiro = pa.id
                                    WHERE c.id_cliente = $id_cliente
                                    ORDER BY c.id_parceiro") or die($mysqli->error);

    $carrinho = [];
    while ($produto = $sql_produtos->fetch_assoc()) {
        $id_parceiro = $produto['id_parceiro'];
        
        if (!isset($carrinho[$id_parceiro])) {
            $carrinho[$id_parceiro] = [
                'nomeFantasia' => $produto['nomeFantasia'],
                'produtos' => [],
                'total' => 0
            ];
        }

        $carrinho[$id_parceiro]['produtos'][] = $produto;
        $carrinho[$id_parceiro]['total'] += (($produto['valor_produto']*$produto['taxa_padrao'])/100 + $produto['valor_produto']) * $produto['qt'];
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>Meu Carrinho</title>
    
    <style>
        .carrinho-container {
            width: 90%;
            margin: 20px auto;
            font-family: Arial, sans-serif;
        }
        .parceiro {
            margin-bottom: 20px;
            padding: 10px;
            border-radius: 8px;
            background: #f9f9f9;
        }
        .parceiro h3 {
            margin: 0 0 10px 0;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            border-radius: 20px;
        }
        thead {
            background-color: #007bff;
            color: white;
        }
        #direita{
            border-radius: 5px 0 0 0;
        }
        #esquerda{
            border-radius: 0 5px 0 0;
        }
        th, td {
            padding: 3px;
            text-align: left;
        }
        tbody tr:hover {
            background-color:rgb(140, 232, 19);
        }
        .total {
            text-align: center;
            font-weight: bold;
            font-size: 16px;
            background-color: chartreuse;
            padding: 5px;
            border-radius: 0 0 5px 5px;
        }
        .comprar {
            width: 100px;
            text-align: center;
            padding: 5px;
            background: rgb(4, 90, 1);
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            font-size: 16px;
            margin-left: 15px;
            border: none; /* Remove a borda */
            cursor: pointer;
            transition: transform 0.2s, color 0.2s;
        }

        .comprar:hover {
            background:rgb(116, 179, 0);
            transform: scale(1.2);
        }
        .voltar {
            display: block;
            width: 200px;
            text-align: center;
            margin: 20px auto;
            padding: 10px;
            background: #007bff;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;

        }
        .voltar:hover {
            background: #0056b3;
        }
        .lixeira {
            color: red;
            font-size: 18px;
            cursor: pointer;
            transition: transform 0.2s, color 0.2s;
        }
        .lixeira:hover {
            transform: scale(1.2);
            color: darkred;
        }
    </style>
</head>
<body>

<div class="carrinho-container">
    <h2>Meu Carrinho</h2>

    <?php if (!empty($carrinho)): ?>
        <?php foreach ($carrinho as $id_parceiro => $dados): ?>
            <div class="parceiro" data-id-parceiro="<?php echo $id_parceiro; ?>">
                <h3>Loja: <?php echo htmlspecialchars($dados['nomeFantasia']); ?></h3>
                <table>
                    <thead>
                        <tr>
                            <th id="direita">Produto</th>
                            <th>Valor uni.</th>
                            <th>Qt</th>
                            <th>Total</th>
                            <th id="esquerda">Ação</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($dados['produtos'] as $produto): ?>
                            <tr title="Detalhes" class="produto" data-id-produto="<?php echo $produto['id_produto']; ?>" data-id-cliente="<?php echo $id_cliente; ?>">
                                <td><?php echo htmlspecialchars($produto['nome_produto']); ?></td>
                                <td class="preco-produto" data-valor="
                                    <?php $preco=(($produto['valor_produto']*$produto['taxa_padrao'])/100 + $produto['valor_produto']);
                                        echo $preco;
                                    ?>">R$ <?php echo number_format($preco, 2, ',', '.'); ?></td>
                                <td>
                                    <input type="number" 
                                        style="width: 40px; border: none;" 
                                        class="quantidade" 
                                        data-id="<?php echo $produto['id_produto']; ?>" 
                                        data-id-cliente="<?php echo $id_cliente; ?>"
                                        value="<?php echo $produto['qt']; ?>" 
                                        min="1"
                                        onchange="atualizarQuantidade(this)">
                                </td>

                                <td class="total-produto" id="total-produto-<?php echo $produto['id_produto']; ?>">
                                    R$ <?php $precoTotal=(($produto['valor_produto']*$produto['taxa_padrao'])/100 + $produto['valor_produto']) * $produto['qt'];
                                        echo number_format($precoTotal, 2, ',', '.'); ?>
                                </td>
                                <td>
                                    <span class="lixeira">
                                        <i data-id="<?php echo $produto['id']; ?>" data-id-cliente="<?php echo $id_cliente; ?>" 
                                        class="lixeira fas fa-trash-alt"></i>
                                    </span>
                                </td>

                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div class="total">Total: R$ 
                    <?php 
                        echo number_format($dados['total'], 2, ',','.');
                    ?><button class="comprar" data-id-cliente="<?php echo $id_cliente; ?>">Comprar</button>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Seu carrinho está vazio.</p>
    <?php endif; ?>

    <a href="javascript:history.back()" class="voltar">Voltar</a>
</div>
<script>

    document.addEventListener("DOMContentLoaded", function () {
        let inputsQuantidade = document.querySelectorAll(".quantidade");

        inputsQuantidade.forEach(input => {
            input.addEventListener("input", function () {
                let produtoRow = this.closest(".produto"); // Linha do produto
                let idProduto = this.getAttribute("data-id"); // ID do produto
                let quantidade = parseInt(this.value); // Quantidade inserida

                // Garante que a quantidade seja válida
                if (isNaN(quantidade) || quantidade < 1) {
                    quantidade = 1;
                    this.value = 1;
                }

                let precoUnitario = parseFloat(produtoRow.querySelector(".preco-produto").getAttribute("data-valor")); // Preço do produto
                let totalProduto = precoUnitario * quantidade; // Cálculo do total do produto

                // Atualiza o valor do total do produto
                document.getElementById("total-produto-" + idProduto).innerText = "R$ " + totalProduto.toLocaleString("pt-BR", { minimumFractionDigits: 2 });

                // Atualiza o total do parceiro
                atualizarTotalParceiro(produtoRow.closest(".parceiro"));

                // Atualiza o total geral
                atualizarTotalGeral();
            });
        });
    });

    document.addEventListener("DOMContentLoaded", function () {
        document.querySelectorAll(".lixeira").forEach(lixeira => {
            lixeira.addEventListener("click", function (event) {
                event.stopPropagation();  // Evita que o clique no produto seja disparado
                let id = this.getAttribute("data-id");
                let idCliente = this.getAttribute("data-id-cliente");

                if (!id || !idCliente) {
                    return;  // Previne a execução caso algum valor esteja faltando
                }

                let url = `remover_produto.php?id=${id}&id_cliente=${idCliente}`;

                if (confirm("Tem certeza que deseja remover este item do carrinho?")) {
                    fetch(url)
                        .then(response => response.text())
                        .then(data => {
                            if (data.trim() === "sucesso") {
                                let produto = this.closest(".produto");
                                let parceiroDiv = produto.closest(".parceiro");

                                // Remove o produto da interface
                                produto.remove();

                                // Recalcula os totais após a remoção
                                atualizarTotalParceiro(parceiroDiv);
                                atualizarTotalGeral();
                            } else {
                                alert("Erro ao remover o produto.");
                            }
                        });
                }
            });
        });
    });

    // Função para atualizar o total do parceiro
    function atualizarTotalParceiro(parceiroDiv) {
        let totalParceiro = 0;
        let totaisProdutos = parceiroDiv.querySelectorAll(".total-produto");

        totaisProdutos.forEach(span => {
            totalParceiro += parseFloat(span.innerText.replace("R$ ", "").replace(".", "").replace(",", "."));
        });

        // Atualiza o total do parceiro
        const totalDiv = parceiroDiv.querySelector(".total");
        if (totalDiv) {
            totalDiv.firstChild.textContent = "Total: R$ " + totalParceiro.toLocaleString("pt-BR", { minimumFractionDigits: 2 });
        }
    }

    // Função para atualizar o total geral do carrinho
    function atualizarTotalGeral() {
        let totalGeral = 0;
        document.querySelectorAll(".parceiro").forEach(parceiro => {
            let totalParceiro = parseFloat(parceiro.querySelector(".total").innerText.replace("Total: R$ ", "").replace(".", "").replace(",", "."));
            totalGeral += totalParceiro;
        });

        // Atualiza o total geral
        const totalCarrinho = document.querySelector(".total");
        if (totalCarrinho) {
            totalCarrinho.firstChild.textContent = "Total: R$ " + totalGeral.toLocaleString("pt-BR", { minimumFractionDigits: 2 });
        }
    }

    // Clique na linha do produto (mas não no input ou ícone de lixeira)
    document.querySelectorAll(".produto").forEach(produto => {
        produto.addEventListener("click", function (event) {
            let target = event.target;
            if (!target.classList.contains("quantidade") && !target.classList.contains("lixeira")) {
                let idProduto = this.getAttribute("data-id-produto");
                let idCliente = this.getAttribute("data-id-cliente");
                window.location.href = `../detalhes_produto.php?id_produto=${idProduto}&id_cliente=${idCliente}`;
            }
        });
    });

    function atualizarQuantidade(elemento) {
        let id_produto = elemento.getAttribute("data-id");
        let id_cliente = elemento.getAttribute("data-id-cliente");
        let nova_quantidade = elemento.value;

        // Enviar os dados via AJAX
        fetch("atualizar_quantidade.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                id_cliente: id_cliente,
                id_produto: id_produto,
                quantidade: nova_quantidade
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === "sucesso") {
                console.log("Quantidade atualizada!");
            } else {
                console.error("Erro ao atualizar:", data.mensagem);
            }
        })
        .catch(error => console.error("Erro na requisição:", error));
    }

    document.addEventListener("DOMContentLoaded", function () {
        document.querySelectorAll(".comprar").forEach(botao => {
            botao.addEventListener("click", function () {
                let parceiroDiv = this.closest(".parceiro");
                let idParceiro = parceiroDiv.getAttribute("data-id-parceiro"); // ID do parceiro
                let idCliente = this.getAttribute("data-id-cliente"); // ID do cliente
                let produtos = [];

                // ✅ Log para depuração
                console.log("ID Parceiro:", idParceiro);
                console.log("ID Cliente:", idCliente);

                // 🚨 Verificação se os IDs estão corretos
                if (!idCliente || !idParceiro) {
                    alert("Erro: Cliente ou Parceiro não identificado.");
                    return;
                }

                parceiroDiv.querySelectorAll(".produto").forEach(produto => {
                    let idProduto = produto.getAttribute("data-id-produto");
                    let quantidade = parseInt(produto.querySelector(".quantidade").value) || 0;

                    // ✅ Apenas adiciona produtos com quantidade > 0
                    if (idProduto && quantidade > 0) {
                        produtos.push({
                            id_produto: idProduto,
                            quantidade: quantidade
                        });
                    }
                });

                // 🚨 Se não houver produtos válidos, impedir a requisição
                if (produtos.length === 0) {
                    alert("Nenhum produto selecionado!");
                    return;
                }
                console.log("Enviando para o servidor:", JSON.stringify({
                    id_cliente: idCliente,
                    id_parceiro: idParceiro,
                    produtos: produtos
                }));
                fetch("atualizar_carrinho.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({
                        id_cliente: idCliente,
                        id_parceiro: idParceiro,
                        produtos: produtos,
                    })
                })
                .then(response => response.text()) // 👈 Primeiro, pega como texto
                .then(text => {
                    //console.log("Resposta bruta do servidor:", text); // 👀 Log da resposta
                    return JSON.parse(text); // Depois, tenta converter para JSON
                })
                .then(data => {
                    console.log("Resposta JSON processada:", data);
                    if (data.status === "sucesso") {
                        window.location.href = "forma_entrega.php?id_parceiro=" + idParceiro + "&id_cliente=" + idCliente;
                    } else {
                        alert("Erro ao atualizar o carrinho: " + data.mensagem);
                    }
                })
                .catch(error => {
                    //console.error("Erro ao processar resposta:", error);
                    alert("Erro ao conectar ao servidor.");
                });

            });
        });
    });

</script>
</body>
</html>
