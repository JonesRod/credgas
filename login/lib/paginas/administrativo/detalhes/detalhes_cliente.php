<?php
include('../../../conexao.php');

if (!isset($_SESSION)) {
    session_start();
}

if (isset($_SESSION['id'])) {
    $id = $_SESSION['id'];
    $sql_query = $mysqli->query("SELECT * FROM meus_clientes WHERE id = '$id'") or die($mysqli->error);
    $usuario = $sql_query->fetch_assoc();
} else {
    // Se não houver uma sessão de usuário, redirecione para a página de login
    session_unset();
    session_destroy();
    header("Location: ../../../../../index.php");
    exit();
}

// Pega o ID do cliente da URL
$id_cliente = isset($_GET['id']) ? $_GET['id'] : null;

if ($id_cliente) {
    // Carrega os dados do cliente
    $sql_cliente = "SELECT * FROM meus_clientes WHERE id = '$id_cliente'";
    $result_cliente = $mysqli->query($sql_cliente) or die($mysqli->error);
    $cliente = $result_cliente->fetch_assoc();

    // Excluir a notificação apenas se not_novo_cliente for igual a 1
    $sql_delete_notificacao = "DELETE FROM contador_notificacoes_admin WHERE id_cliente = '$id_cliente' AND not_novo_cliente = 1";
    $mysqli->query($sql_delete_notificacao) or die($mysqli->error);

} else {
    // Caso não tenha ID de cliente na URL, redireciona de volta
    header("Location: lista_notificacoes.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes do Cliente</title>
    <style>
        /* Estilo geral */
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
            color: #333;
        }

        h1, h2 {
            text-align: center;
            color: #0056b3;
            margin-top: 20px;
        }
        .img{
            text-align: center;
        }
img{
    width: 300px;
    height: 300px;
    border-radius: 50%;
}
        /* Container principal */
        .container {
            width: 80%;
            max-width: 800px;
            margin: 20px auto;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        /* Informações do cliente */
        .info {
            margin: 15px 0;
            font-size: 16px;
            line-height: 1.8;
        }

        .info strong {
            color: #333;
        }

        /* Botão voltar */
        .back-link {
            display: inline-block;
            margin-top: 20px;
            text-align: center;
            text-decoration: none;
            background-color: #0056b3;
            color: #fff;
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 14px;
            transition: background-color 0.3s ease;
        }

        .back-link:hover {
            background-color: #003f8a;
        }

        /* Responsividade */
        @media (max-width: 768px) {
            .container {
                width: 90%;
                padding: 15px;
            }

            .info {
                font-size: 14px;
            }

            .back-link {
                font-size: 12px;
                padding: 8px 15px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if ($cliente): ?>
        <h2>Informações do Cliente</h2>
        <div class="img">
            <img src="<?php echo '../../clientes/arquivos/'.htmlspecialchars($cliente['imagem']); ?>" alt="sem imagem">
        </div>       
        <div class="info"><strong>Data de Cadastro:</strong> <?php echo date("d/m/Y", strtotime($cliente['data_cadastro'])); ?></div>
        <div class="info"><strong>Nome:</strong> <?php echo htmlspecialchars($cliente['nome_completo']); ?></div>
        <div class="info"><strong>CPF:</strong> <?php echo htmlspecialchars($cliente['cpf']); ?></div>
        <div class="info"><strong>Data de Nascimento:</strong> <?php echo date("d/m/Y", strtotime($cliente['nascimento'])); ?></div>
        <div class="info"><strong>Idade:</strong> 
            <?php
                $data_nascimento = new DateTime($cliente['nascimento']);
                $data_atual = new DateTime();
                echo $data_nascimento->diff($data_atual)->y; // Calcula a idade
            ?>
        </div>
        <div class="info"><strong>Telefone:</strong> <?php echo htmlspecialchars($cliente['celular1']); ?></div>
        <div class="info"><strong>Telefone(Opcional):</strong> <?php echo htmlspecialchars($cliente['celular2']); ?></div>
        <div class="info"><strong>E-mail:</strong> <?php echo htmlspecialchars($cliente['email']); ?></div>
        <div class="info"><strong>Estado:</strong> <?php echo htmlspecialchars($cliente['uf']); ?></div>
        <div class="info"><strong>Cidade:</strong> <?php echo htmlspecialchars($cliente['cidade']); ?></div>
        <div class="info"><strong>CEP:</strong> <?php echo htmlspecialchars($cliente['cep']); ?></div>
        <div class="info"><strong>Rua/Av:</strong> <?php echo htmlspecialchars($cliente['endereco']); ?></div>    
        <div class="info"><strong>Numero:</strong> <?php echo htmlspecialchars($cliente['numero']); ?></div>   
        <div class="info"><strong>Bairro:</strong> <?php echo htmlspecialchars($cliente['bairro']); ?></div>

        <hr>
        <h3>Histórico de compras</h3>

        <div id="conteudo-produtos" class="conteudo-aba" style="display: block;">
            <div class="filtros-compras">
                <!-- Filtro por intervalo de datas -->
                <label for="data_inicio">Data Início:</label>
                <input type="date" id="data_inicio" name="data_inicio">

                <label for="data_fim">Data Fim:</label>
                <input type="date" id="data_fim" name="data_fim">

                <!-- Filtro por formas de pagamento carregadas do banco -->
                <label for="forma_pagamento">Forma de Pagamento:</label>
                <select name="forma_pagamento" id="forma_pagamento">
                    <option value="">Todas as Formas</option>
                    <?php
                    // Consulta para buscar as formas de pagamento disponíveis no banco
                    $queryFormasPagamento = "SELECT id, nome FROM formas_pagamento ORDER BY nome ASC";
                    $resultFormasPagamento = $mysqli->query($queryFormasPagamento);

                    if ($resultFormasPagamento->num_rows > 0) {
                        while ($forma = $resultFormasPagamento->fetch_assoc()) {
                            echo "<option value='" . htmlspecialchars($forma['id']) . "'>" . htmlspecialchars($forma['nome']) . "</option>";
                        }
                    } else {
                        echo "<option value=''>Nenhuma forma de pagamento encontrada</option>";
                    }
                    ?>
                </select>

                <button id="filtrar" onclick="filtrarCompras()">Filtrar</button>
                <?php
                    include('../../../conexao.php');

                    // Consulta SQL para carregar os produtos
                    $sql = "SELECT * FROM vendas WHERE id_cliente = $id_cliente";
                    $result = $mysqli->query($sql);


                    // Conta o número total de produtos carregados
                    $totalCompras = $result->num_rows;

                ?>
                <span id="total-compras" style="margin-left: 10px; margin-top: 10px; font-weight: bold;">Total de compras: <?php echo $totalCompras; ?></span>
            </div>

            <table class="tabela-compras">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Nº Pedido</th>
                        <th>Produto</th>
                        <th>Valor</th>
                        <th>Detalhes</th>
                    </tr>
                </thead>

                <tbody id="compras-tabela">
                    <?php
                    include('../../../conexao.php');

                    // Consulta SQL para carregar os produtos
                    $sql = "SELECT id, data, nu_pedido, id_cliente, id_parceiro, produtos, valor_produtos FROM vendas WHERE id_cliente = $id_cliente ORDER BY data DESC";
                    $result = $mysqli->query($sql);

                    if ($result->num_rows > 0) {
                        while ($compras = $result->fetch_assoc()) {

                            echo "<tr>";
                            echo "<td>" . date('d/m/Y', strtotime($compras['data'])) . "</td>";
                            echo "<td>" . htmlspecialchars($compras['nu_pedido']) . "</td>";
                            echo "<td>" . htmlspecialchars($compras['produtos']) . "</td>";
                            echo "<td>" . htmlspecialchars($compras['valor_produtos']) . "</td>";
                            echo "<td><a href='detalhes_compras.php?id=" . $compras['id_cliente'] . "' class='detalhes-link'>Ver Detalhes</a></td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5'>Nenhum produto encontrado.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>

        <div class="info">
            <a href="../admin_home.php" class="back-link">Voltar</a>
        </div>
        <?php else: ?>
            <p>Cliente não encontrado.</p>

            <div class="info">
                <a href="../admin_home.php" class="back-link">Voltar</a>
            </div>
        <?php endif; ?>

    </div>
</body>
    <script>
        function filtrarCompras() {
            // Obtém os valores dos filtros
            const dataInicio = document.getElementById('data_inicio').value;
            const dataFim = document.getElementById('data_fim').value;
            const formaPagamento = document.getElementById('forma_pagamento').value;

            // Cria uma requisição AJAX
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'filtrar_compras.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

            // Quando a requisição for concluída, atualiza a tabela
            xhr.onload = function () {
                if (xhr.status === 200) {
                    document.getElementById('compras-tabela').innerHTML = xhr.responseText;

                    // Conta o número de produtos carregados
                    const linhasCompras = document.querySelectorAll('#compras-tabela tr');
                    const totalComprar = linhasCompras.length;
                    document.getElementById('total-compras').textContent = `Total de compras: ${totalProdutos}`;
                }
            };

            // Envia os dados dos filtros para o servidor
            xhr.send(`data_inicio=${dataInicio}&data_fim=${dataFim}&forma_pagamento=${formaPagamento}`);
        }

    </script>
</html>
