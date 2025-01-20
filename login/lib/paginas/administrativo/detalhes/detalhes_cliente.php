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

            #conteudo-compras{
                background-color: #fff;
            }

            /* Estilização da tabela de parceiros e produtos */
            .tabela-compras {
                width: 100%;
                border-collapse: collapse;
                border-radius: 8px;
                background-color: #fff;
                margin: 0; /* Remove as margens */
                padding: 0; /* Remove qualquer padding interno */
            }
            /* Ajuste para as células da tabela */
            .tabela-compras th,
            .tabela-compras td {
                padding: 5px;
                text-align: left;
                border-bottom: 1px solid #ddd;
            }
            
            .tabela-compras th {
                background-color: #f4f4f4;
                font-weight: bold;
                border-radius: 0px;
            }

            .tabela-compras .detalhes-link {
                color: #007bff;
                text-decoration: none;
                font-weight: bold;
            }

            .tabela-compras .detalhes-link:hover {
                text-decoration: underline;
            }
            .imagem {
                width: 80px;
                height: 80px;
                object-fit: cover;
                border-radius: 10px;
                border: 1px solid #ddd;
            }
            /* Estilo dos filtros de produtos */
/* Estilo dos filtros de produtos */
.filtros-compras {
    margin-bottom: 20px;
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
}

.filtros-compras label {
    display: flex;
    align-items: center;
    font-size: 14px;
    cursor: pointer;
}

.filtros-compras input[type="checkbox"] {
    margin-right: 5px;
}
/* Caixa de seleção estilizada */
.filtros-compras select {
    padding: 8px;
    border-radius: 5px;
    border: 1px solid #ccc;
    font-size: 14px;
    background-color: #f9f9f9;
    width: 200px;
}

.filtrar {
    background-color: #007bff; /* Cor de fundo azul */
    color: #fff; /* Cor do texto */
    border: none; /* Sem borda */
    border-radius: 8px; /* Bordas arredondadas */
    padding: 5px 20px; /* Espaçamento interno */
    font-size: 15px; /* Tamanho da fonte */
    cursor: pointer; /* Cursor de ponteiro */
    transition: background-color 0.3s ease; /* Transição suave para o hover */
}

.filtrar:hover {
    background-color: #0056b3; /* Cor de fundo mais escura no hover */
}

.filtrar:active {
    background-color: #003f7f; /* Cor mais escura quando pressionado */
}

@media (max-width: 768px) {
    /*.filtros-produtos*/ .filtrar {
        width: 100%;
        font-size: 14px;
        padding: 12px;
    }
}
    </style>
</head>
<body>
    <div class="container">
        <?php if ($cliente): ?>
        <h2>Informações do Cliente</h2>
        <input type="hidden" id="id" value="<?php echo htmlspecialchars($cliente['id']); ?>">
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
                            echo "<option value='" . htmlspecialchars($forma['nome']) . "'>" . htmlspecialchars($forma['nome']) . "</option>";
                        }
                    } else {
                        echo "<option value=''>Nenhuma forma de pagamento encontrada</option>";
                    }
                    ?>
                </select>

                <button class="filtrar" id="filtrar" onclick="filtrarCompras()">Filtrar</button>
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
                            echo "<td>" . htmlspecialchars(str_pad($compras['nu_pedido'], 4, '0', STR_PAD_LEFT)) . "</td>";
                            echo "<td>" . htmlspecialchars($compras['produtos']) . "</td>";
                            echo "<td>R$ " . htmlspecialchars(number_format($compras['valor_produtos'], 2, ',', '.')) . "</td>";
                            echo "<td><a href='detalhes_compras.php?id=" . htmlspecialchars($compras['id']) . "&id_cliente=" . htmlspecialchars($compras['id_cliente']) . "' class='detalhes-link'>Ver Detalhes</a></td>";
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
    const id = document.getElementById('id').value;
    const dataInicio = document.getElementById('data_inicio').value;
    const dataFim = document.getElementById('data_fim').value;
    const formaPagamento = document.querySelector('#forma_pagamento').value;

    // Valida os campos obrigatórios
    if (!dataInicio || !dataFim) {
        alert("Por favor, preencha as datas de início e fim.");
        return;
    }

    // Cria uma requisição AJAX
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'filtrar_compras.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

    // Dados enviados na requisição
    const params = `id_cliente=${id}&data_inicio=${dataInicio}&data_fim=${dataFim}&forma_pagamento=${formaPagamento}`;
    console.log("Enviando dados:", params); // Debug

    // Quando a requisição for concluída, atualiza a tabela
    xhr.onload = function () {
        if (xhr.status === 200) {
            document.getElementById('compras-tabela').innerHTML = xhr.responseText;

            // Conta o número de compras carregadas
            const linhasCompras = document.querySelectorAll('#compras-tabela tr');
            const totalComprar = linhasCompras.length;
            document.getElementById('total-compras').textContent = `Total de compras: ${totalComprar}`;
        } else {
            console.error("Erro ao carregar dados:", xhr.statusText);
        }
    };

    // Envia os dados dos filtros para o servidor
    xhr.send(params);
}


    </script>
</html>
