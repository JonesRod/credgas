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

    $sql_produtos = $mysqli->query("SELECT c.*, p.nome_produto, p.valor_produto, p.taxa_padrao, p.promocao, p.valor_promocao, pa.nomeFantasia, pa.valor_minimo_pedido, c.frete 
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
                'frete' => $produto['frete'],
                'valor_minimo_pedido' => $produto['valor_minimo_pedido'], // Adiciona o valor mínimo do pedido
                'produtos' => [],
                'total' => 0
            ];
        }

        // Verifica se o produto está em promoção
        $valor_produto = $produto['promocao'] ? $produto['valor_promocao'] : $produto['valor_produto'];
        $preco_com_taxa = (($valor_produto * $produto['taxa_padrao']) / 100) + $valor_produto;

        $carrinho[$id_parceiro]['produtos'][] = $produto;
        $carrinho[$id_parceiro]['total'] += $preco_com_taxa * $produto['qt'];
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
            text-align: center;
            /* Centraliza o conteúdo */
        }

        .parceiro {
            margin-bottom: 20px;
            padding: 10px;
            border-radius: 8px;
            background: #f9f9f9;
            text-align: left;
            /* Alinha o texto à esquerda dentro dos parceiros */
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

        #direita {
            border-radius: 5px 0 0 0;
        }

        #esquerda {
            border-radius: 0 5px 0 0;
        }

        th,
        td {
            padding: 3px;
            text-align: left;
        }

        tbody tr:hover {
            background-color: rgb(140, 232, 19);
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
            border: none;
            /* Remove a borda */
            cursor: pointer;
            transition: transform 0.2s, color 0.2s;
        }

        .comprar:hover {
            background: rgb(116, 179, 0);
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

        /* Responsividade */
        @media (max-width: 768px) {
            .carrinho-container {
                width: 100%;
                padding: 10px;
            }

            table,
            thead,
            tbody,
            th,
            td,
            tr {
                display: block;
            }

            thead tr {
                position: absolute;
                top: -9999px;
                left: -9999px;
            }

            tr {
                margin: 0 0 1rem 0;
            }

            tr:nth-child(odd) {
                background: #f9f9f9;
            }

            td {
                border: none;
                position: relative;
                padding-left: 50%;
                text-align: right;
            }

            td:before {
                position: absolute;
                top: 0;
                left: 6px;
                width: 45%;
                padding-right: 10px;
                white-space: nowrap;
                text-align: left;
                font-weight: bold;
            }

            td:nth-of-type(1):before {
                content: "Produto";
            }

            td:nth-of-type(2):before {
                content: "Valor uni.";
            }

            td:nth-of-type(3):before {
                content: "Qt";
            }

            td:nth-of-type(4):before {
                content: "Total";
            }

            td:nth-of-type(5):before {
                content: "Ação";
            }
        }

        /* Centralizar mensagem de carrinho vazio */
        .empty-cart-message {
            text-align: center;
            font-size: 18px;
            color: #555;
            margin-top: 20px;
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
                    <input type="hidden" class="valor-minimo-pedido" value="<?php echo $dados['valor_minimo_pedido']; ?>">
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
                                <tr title="Detalhes" class="produto" data-id-produto="<?php echo $produto['id_produto']; ?>"
                                    data-id-cliente="<?php echo $id_cliente; ?>">
                                    <td><?php echo htmlspecialchars($produto['nome_produto']); ?></td>
                                    <td class="preco-produto" data-valor="
                                    <?php $preco = $produto['promocao'] ? $produto['valor_promocao'] : $produto['valor_produto'];
                                    $preco_com_taxa = (($preco * $produto['taxa_padrao']) / 100) + $preco;
                                    echo $preco_com_taxa;
                                    ?>">R$ <?php echo number_format($preco_com_taxa, 2, ',', '.'); ?></td>
                                    <td>
                                        <input type="number" style="width: 40px; border: none;" class="quantidade"
                                            data-id="<?php echo $produto['id_produto']; ?>"
                                            data-id-cliente="<?php echo $id_cliente; ?>" value="<?php echo $produto['qt']; ?>"
                                            min="1" onchange="atualizarQuantidade(this)">
                                    </td>

                                    <td class="total-produto" id="total-produto-<?php echo $produto['id_produto']; ?>">
                                        R$ <?php $precoTotal = $preco_com_taxa * $produto['qt'];
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
                        $total = $dados['total'];
                        echo number_format($total, 2, ',', '.');
                        ?><button class="comprar" data-id-cliente="<?php echo $id_cliente; ?>">Comprar</button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="empty-cart-message">Seu carrinho está vazio.</p>
        <?php endif; ?>

        <a href="../cliente_home.php" class="voltar">Voltar</a>
    </div>
    <script>
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

            // Se não houver mais produtos, remove o parceiro da interface
            if (totaisProdutos.length === 0) {
                parceiroDiv.remove();
            }
        }

        // Clique na lixeira para remover um produto
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

                                    // Recalcula os totais apenas para o parceiro correspondente
                                    atualizarTotalParceiro(parceiroDiv);
                                } else {
                                    alert("Erro ao remover o produto.");
                                }
                            });
                    }
                });
            });
        });

        // Atualizar a quantidade de produtos no carrinho
        function atualizarQuantidade(elemento) {
            let id_produto = elemento.getAttribute("data-id");
            let id_cliente = elemento.getAttribute("data-id-cliente");
            let nova_quantidade = elemento.value;

            // Garante que a quantidade seja válida
            if (isNaN(nova_quantidade) || nova_quantidade < 1) {
                nova_quantidade = 1;
                elemento.value = 1;
            }

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

            // Atualiza os cálculos na interface
            let produtoRow = elemento.closest(".produto");
            let precoUnitario = parseFloat(produtoRow.querySelector(".preco-produto").getAttribute("data-valor"));
            let totalProduto = precoUnitario * nova_quantidade;

            // Atualiza o valor do total do produto
            document.getElementById("total-produto-" + id_produto).innerText = "R$ " + totalProduto.toLocaleString("pt-BR", { minimumFractionDigits: 2 });

            // Atualiza o total do parceiro
            atualizarTotalParceiro(produtoRow.closest(".parceiro"));
        }

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
                    //atualizarTotalGeral();
                });
            });
        });

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

        document.addEventListener("DOMContentLoaded", function () {
            document.querySelectorAll(".comprar").forEach(botao => {
                botao.addEventListener("click", function () {
                    let parceiroDiv = this.closest(".parceiro");
                    let idParceiro = parceiroDiv.getAttribute("data-id-parceiro");
                    let idCliente = this.getAttribute("data-id-cliente");
                    let produtos = [];

                    // Calcula o total do pedido (produtos + frete)
                    let totalPedido = 0;
                    parceiroDiv.querySelectorAll(".produto").forEach(produto => {
                        let quantidade = parseInt(produto.querySelector(".quantidade").value) || 0;
                        let precoUnitario = parseFloat(produto.querySelector(".preco-produto").getAttribute("data-valor")) || 0;
                        totalPedido += precoUnitario * quantidade;
                    });


                    // Pega o valor mínimo do pedido do input oculto
                    let valorMinimoInput = parceiroDiv.querySelector("input.valor-minimo-pedido");
                    let valorMinimo = valorMinimoInput ? parseFloat(valorMinimoInput.value) || 0 : 0;

                    if (totalPedido < valorMinimo) {
                        mostrarPopupMinimoPedido("O valor mínimo para pedidos nesta loja é R$ " + valorMinimo.toLocaleString("pt-BR", { minimumFractionDigits: 2 }) + ". Seu pedido está em R$ " + totalPedido.toLocaleString("pt-BR", { minimumFractionDigits: 2 }) + ".");
                        return;
                    }

                    parceiroDiv.querySelectorAll(".produto").forEach(produto => {
                        let idProduto = produto.getAttribute("data-id-produto");
                        let quantidade = parseInt(produto.querySelector(".quantidade").value) || 0;
                        if (idProduto && quantidade > 0) {
                            produtos.push({
                                id_produto: idProduto,
                                quantidade: quantidade
                            });
                        }
                    });

                    if (produtos.length === 0) {
                        alert("Nenhum produto selecionado!");
                        return;
                    }

                    fetch("atualizar_carrinho.php", {
                        method: "POST",
                        headers: { "Content-Type": "application/json" },
                        body: JSON.stringify({
                            id_cliente: idCliente,
                            id_parceiro: idParceiro,
                            produtos: produtos,
                        })
                    })
                        .then(response => response.text())
                        .then(text => {
                            return JSON.parse(text);
                        })
                        .then(data => {
                            if (data.status === "sucesso") {
                                window.location.href = "forma_entrega.php?id_parceiro=" + idParceiro + "&id_cliente=" + idCliente;
                            } else {
                                alert("Erro ao atualizar o carrinho: " + data.mensagem);
                            }
                        })
                        .catch(error => {
                            alert("Erro ao conectar ao servidor.");
                        });
                });
            });
        });

        // Função para mostrar popup de valor mínimo
        function mostrarPopupMinimoPedido(msg) {
            let popup = document.createElement("div");
            popup.style.position = "fixed";
            popup.style.top = "30%";
            popup.style.left = "50%";
            popup.style.transform = "translate(-50%, -50%)";
            popup.style.background = "#fff";
            popup.style.color = "#222";
            popup.style.padding = "30px 40px";
            popup.style.borderRadius = "10px";
            popup.style.boxShadow = "0 2px 10px rgba(0,0,0,0.2)";
            popup.style.zIndex = "9999";
            popup.style.fontSize = "18px";
            popup.style.textAlign = "center";

            // Conteúdo do popup com título e botão centralizado
            popup.innerHTML = `
                <strong style="display:block; font-size:22px; margin-bottom:15px;">Atenção</strong>
                ${msg}
                <div style="margin-top: 20px;">
                    <button 
                        style="padding: 8px 20px; background: #007bff; color: #fff; border: none; border-radius: 5px; cursor: pointer;"
                        onclick="this.closest('div').parentNode.remove()"
                    >OK</button>
                </div>
            `;

            document.body.appendChild(popup);
        }

    </script>
</body>

</html>