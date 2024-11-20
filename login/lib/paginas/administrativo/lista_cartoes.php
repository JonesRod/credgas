<?php
    include('../../conexao.php');

    // Inicia a sessão
    if (!isset($_SESSION)) {
        session_start(); 
    }

    // Verifica se o usuário está logado
    if (isset($_SESSION['id'])) {
        $id = $_SESSION['id'];

        // Consulta para buscar o parceiro
        $sql_query = $mysqli->query("SELECT * FROM config_admin WHERE id_cliente = '$id'") or die($mysqli->error);
        $admin = $sql_query->fetch_assoc();

    } else {
        session_unset();
        session_destroy(); 
        header("Location: ../../../../index.php");
        exit();
    }

    // Obtém a lista de cartões do banco de dados
    $sql_cartoes = "SELECT * FROM cartoes";
    $result_cartoes = $mysqli->query($sql_cartoes);
    
    // Verifica se a consulta foi bem-sucedida
    if (!$result_cartoes) {
        die("Erro na consulta SQL: " . $mysqli->error);
    }
    
    // Processa os resultados
    $lista_cartoes = [];
    if ($result_cartoes->num_rows > 0) {
        while ($cartao = $result_cartoes->fetch_assoc()) {
            $lista_cartoes[] = $cartao;
        }
        // Libera os resultados após o processamento
        $result_cartoes->free();
    }

    // Se a requisição for para alterar um cartão
    if (isset($_GET['editar'])) {
        $id_cartao = $_GET['editar'];
        $sql_edit = "SELECT * FROM cartoes WHERE id = $id_cartao";
        $result_edit = $mysqli->query($sql_edit);
        $cartao_editar = $result_edit->fetch_assoc();
    }
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Cartões</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f8f8;
        }

        h2 {
            text-align: center;
            margin-top: 20px;
            color: #333;
        }

        form {
            width: 80%;
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        label {
            display: block;
            margin: 10px 0 5px;
            font-weight: bold;
        }

        input[type="text"] {
            width: 100%;
            padding: 8px;
            margin: 5px 0 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        button {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background-color: #0056b3;
        }

        /* Estilo para a tabela de cartões */
        table {
            width: 80%;
            max-width: 900px;
            margin: 30px auto;
            border-collapse: collapse;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 6px;
            text-align: center;
        }

        th {
            background-color: #f4f4f4;
        }

        td {
            background-color: #fafafa;
        }

        /* Estilo para os botões de Excluir e Editar */
        .excluir, .editar {
            background-color: #f44336;
            color: white;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            margin: 5px;
        }

        .editar {
            background-color: #4CAF50;
        }

        .excluir:hover, .editar:hover {
            background-color: #d32f2f;
        }

        /* Alinhamento e estilo dos botões */
        .botoes-container {
            display: flex;
            justify-content: flex-start;  /* Alinha os botões à esquerda */
            gap: 15px;                    /* Espaço entre os botões */
            margin-top: 20px;             /* Espaço acima dos botões */
        }

        /* Estilos gerais dos botões */
        button {
            font-size: 16px;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.2s;
            display: inline-block;
        }

        .voltar:active {
            background-color: #e96a2e;  /* Cor ao clicar */
        }


        /* Estilo para centralizar o botão */
        .centralizar-botao {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 10vh; /* Faz o botão ficar centralizado verticalmente */
        }

        .voltar {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
        }

        .voltar:hover {
            background-color: #218838;
        }


        /* Estilo específico para o botão Alterar */
        button[type="submit"] {
            background-color: #5bc0de;  /* Cor de fundo azul claro */
            color: white;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        button[type="submit"]:hover {
            background-color: #31b0d5;  /* Cor de fundo mais escura no hover */
            transform: scale(1.05);      /* Leve aumento de tamanho no hover */
        }

        button[type="submit"]:active {
            background-color: #269abc;  /* Cor ao clicar */
        }

    </style>
</head>
<body>

    <h2>Gerenciar Cartões</h2>

    <!-- Formulário de adição de cartões -->
    <form id="formCartoes" action="salvar_cartoes.php?editar=<?php echo isset($id_cartao) ? $id_cartao : 'null'; ?>" method="POST">
        <input type="hidden" name="id_admin" value="<?php echo $id; ?>">

        <label for="novoCartao">Adicionar ou Alterar Cartão:</label>
        <input type="text" id="novoCartao" name="novoCartao" required placeholder="Nome do Cartão" 
            value="<?php echo isset($cartao_editar) ? htmlspecialchars($cartao_editar['nome']) : ''; ?>">

        <!-- Parcelas -->
        <?php for ($i = 1; $i <= 12; $i++) : ?>
            <label for="<?php echo $i . 'x'; ?>"><?php echo $i; ?>x %</label>
            <input type="text" id="<?php echo $i . 'x'; ?>" name="<?php echo $i . 'x'; ?>" required 
                value="<?php echo isset($cartao_editar) ? htmlspecialchars($cartao_editar[$i . 'x']) : ''; ?>" placeholder="Porcentagem para <?php echo $i; ?>x">
        <?php endfor; ?>

        <!-- Contêiner para os botões -->
        <div class="botoes-container">
            <?php if (isset($cartao_editar)): ?>
                <!-- Botão para retornar (cancelar a edição) -->
                <button type="button" class="voltar" onclick="window.location.href = 'lista_cartoes.php';">Retornar</button>

                <!-- Botão para alterar -->
                <button type="submit" name="alterar" value="<?php echo $cartao_editar['id']; ?>">Alterar</button>

            <?php else: ?>
                <!-- Aqui o botão "Adicionar" será removido da div botoes-container -->
            <?php endif; ?>
        </div>

        <!-- Botão Adicionar (fora do contêiner) -->
        <?php if (!isset($cartao_editar)): ?>
            <button type="submit">Adicionar</button>
        <?php endif; ?>


    </form>

    <!-- Tabela de cartões -->
    <table>
        <thead>
            <tr>
                <th>Nome do Cartão</th>
                <?php for ($i = 1; $i <= 12; $i++) : ?>
                    <th><?php echo $i . 'x %'; ?></th>
                <?php endfor; ?>
                <th>Ação</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($lista_cartoes)) : ?>
                <?php foreach ($lista_cartoes as $cartao) : ?>
                    <tr>
                        <td><?php echo htmlspecialchars($cartao['nome']); ?></td>
                        <?php for ($i = 1; $i <= 12; $i++) : ?>
                            <td><?php echo htmlspecialchars($cartao[$i . 'x']); ?>%</td>
                        <?php endfor; ?>
                        <td>
                            <a href="?editar=<?php echo $cartao['id']; ?>"><button type="button" class="editar">Alterar</button></a>
                            <button type="button" class="excluir" onclick="excluirCartao('<?php echo $cartao['id']; ?>')">Excluir</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="14">Nenhum cartão encontrado.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Contêiner para centralizar o botão -->
    <div class="centralizar-botao">
        <!-- Botão voltar -->
        <button type="button" class="voltar" onclick="window.location.href='configuracoes.php?id_admin=<?php echo $id;?>';">Voltar</button>
    </div>


    <script>
        function excluirCartao(cartaoId) {
            if (confirm('Tem certeza que deseja excluir este cartão?')) {
                // Envia o ID do cartão a ser excluído via AJAX
                fetch('excluir_cartao.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ cartao: cartaoId }) // Envia o ID como JSON
                })
                .then(response => response.json())
                .then(data => {
                    if (data.sucesso) {
                        alert('Cartão excluído com sucesso!');
                        // Atualiza a lista de cartões
                        location.reload();
                    } else {
                        alert('Erro ao excluir o cartão.');
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    alert('Erro ao excluir o cartão.');
                });
            }
        }
    </script>

</body>
</html>
