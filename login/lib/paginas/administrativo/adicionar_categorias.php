<?php
include('../../conexao.php');

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
    header("Location: ../../../index.php");
    exit();
}

// Processa a adição de uma nova categoria
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['nova_categoria'])) {
        $novaCategoria = trim(mysqli_real_escape_string($mysqli, $_POST['nova_categoria']));

        if (!empty($novaCategoria)) {
            $checkExist = $mysqli->query("SELECT * FROM categorias WHERE categorias = '$novaCategoria'");
            if ($checkExist->num_rows > 0) {
                $mensagem = "Categoria já existe!";
            } else {
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

    // Processa a exclusão de uma categoria
    if (isset($_POST['excluir_categoria'])) {
        $categoriaExcluir = intval($_POST['excluir_categoria']);
        $queryDelete = "DELETE FROM categorias WHERE id = $categoriaExcluir";
        if ($mysqli->query($queryDelete)) {
            $mensagem = "Categoria excluída com sucesso!";
        } else {
            $mensagem = "Erro ao excluir categoria: " . $mysqli->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Categorias</title>
    <style>
        body {
            /*width: 60%;*/
            font-family: Arial, sans-serif;
            margin: 20px;
            padding: 0;
            box-sizing: border-box;
        }
        .conteiner{
            max-width: 600px;
            margin: auto;
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 20px;
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
    <div class="conteiner">
        <h1>Gerenciar Categorias</h1>

        <!-- Formulário para adicionar nova categoria -->
        <form method="POST" action="">
            <div class="form-group">
                <label for="nova_categoria">Nova Categoria:</label>
                <input type="text" id="nova_categoria" name="nova_categoria" placeholder="Digite o nome da nova categoria" required>
            </div>
            <button type="submit">Adicionar</button>
        </form>

        <!-- Exibição de mensagem -->
        <?php if (isset($mensagem)): ?>
            <p class="mensagem"><?php echo $mensagem; ?></p>
        <?php endif; ?>

        <!-- Lista de categorias já salvas -->
        <h2>Categorias Salvas</h2>
        <ul>
            <?php
            $resultadoCategorias = $mysqli->query("SELECT * FROM categorias ORDER BY categorias ASC");
            if ($resultadoCategorias->num_rows > 0):
                while ($categoria = $resultadoCategorias->fetch_assoc()):
            ?>
                <li>
                    <?php echo htmlspecialchars($categoria['categorias']); ?>
                    <!-- Botão para excluir categoria -->
                    <form method="POST" action="" style="display: inline;">
                        <input type="hidden" name="excluir_categoria" value="<?php echo $categoria['id']; ?>">
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
        <p><a href="configuracoes.php?id_admin=<?php echo $idParceiro; ?>">Voltar</a></p>
    </div>
</body>
</html>
