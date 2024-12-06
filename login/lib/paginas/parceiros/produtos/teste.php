<?php
include('../../../conexao.php');

// Inicia a sessão
if (!isset($_SESSION)) {
    session_start();
}

// Verifica se o ID do parceiro foi enviado via GET
if (isset($_GET['id'])) {
    $idParceiro = intval($_GET['id']);
} else {
    session_unset();
    session_destroy();
    header("Location: ../../../../index.php");
    exit();
}

// Processa a adição de uma nova categoria
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nova_categoria'])) {
    $novaCategoria = trim(mysqli_real_escape_string($mysqli, $_POST['nova_categoria']));

    if (!empty($novaCategoria)) {
        // Verifica se a categoria já existe
        $checkExist = $mysqli->query("SELECT * FROM categorias WHERE categorias = '$novaCategoria'");
        if ($checkExist->num_rows > 0) {
            $mensagem = "Categoria já existe!";
        } else {
            // Insere a nova categoria no banco de dados
            $queryInsert = "INSERT INTO categorias (categorias) VALUES ('$novaCategoria')";
            if ($mysqli->query($queryInsert)) {
                $mensagem = "Categoria adicionada com sucesso!";
            } else {
                $mensagem = "Erro ao adicionar categoria: " . $mysqli->error;
            }
        }
    } else {
        $mensagem = "O campo da nova categoria não pode estar vazio.";
    }
}

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Nova Categoria</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            padding: 0;
            box-sizing: border-box;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
        }
        input[type="text"] {
            width: 100%;
            padding: 10px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            padding: 10px 15px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        button:hover {
            background-color: #0056b3;
        }
        .botao-excluir {
            padding: 5px 10px;
            background-color: #FF0000;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .botao-excluir:hover {
            background-color: #CC0000;
        }
        .mensagem {
            margin-top: 20px;
            color: green;
        }
        .erro {
            color: red;
        }
        ul {
            list-style-type: none;
            padding: 0;
        }
        li {
            background-color: #f9f9f9;
            padding: 10px;
            margin: 5px 0;
            border-radius: 5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: 1px solid #ccc;
        }
        li span {
            flex-grow: 1;
        }
        @media (max-width: 600px) {
            body {
                margin: 10px;
            }
            button {
                width: 100%;
                margin-top: 10px;
            }
            li {
                flex-direction: column;
                align-items: flex-start;
            }
            li button {
                margin-top: 5px;
            }
        }
    </style>
</head>
<body>
    <h1>Adicionar Nova Categoria</h1>

    <!-- Formulário para adicionar nova categoria -->
    <form method="POST" action="">
        <div class="form-group">
            <label for="nova_categoria">Nova Categoria:</label>
            <input type="text" id="nova_categoria" name="nova_categoria" placeholder="Digite o nome da nova categoria" required>
        </div>
        <button type="submit">Adicionar</button>
    </form>

    <!-- Lista de categorias já salvas -->
    <h2>Categorias Salvas</h2>
    <ul>
        <?php
        $resultadoCategorias = $mysqli->query("SELECT * FROM categorias ORDER BY categorias ASC");
        if ($resultadoCategorias && $resultadoCategorias->num_rows > 0):
            while ($categoria = $resultadoCategorias->fetch_assoc()):
        ?>
                <li>
                    <span><?php echo htmlspecialchars($categoria['categorias']); ?></span>
                    <form method="POST" action="" style="display:inline;">
                        <input type="hidden" name="id_categoria" value="<?php echo $categoria['id']; ?>">
                        <button type="submit" class="botao-excluir">Excluir</button>
                    </form>
                </li>
        <?php
            endwhile;
        else:
        ?>
            <li>Nenhuma categoria cadastrada.</li>
        <?php endif; ?>
    </ul>

    <!-- Link para voltar -->
    <p><a href="configuracoes.php?id=<?php echo $idParceiro; ?>">Voltar</a></p>
</body>
</html>
