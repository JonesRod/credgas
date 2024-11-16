<?php
include('../../conexao.php');

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
    header("Location: ../../../../index.php");
    exit();
}

// Pega o ID da notificação apenas se ele existir
$id = isset($_GET['id']) ? $_GET['id'] : null;

$session_id = $_GET['session_id'] ?? null;

// Consulta para buscar produtos em análise
$sql_query = "SELECT * FROM contador_notificacoes_parceiro WHERE not_novo_produto = '1'";
$result = $mysqli->query($sql_query) or die($mysqli->error);

?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>Produto em Análise</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
        }

        h1, h2 {
            text-align: center;
            color: #343a40;
            margin: 20px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px auto;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
            font-size: 16px;
        }

        th {
            background-color: #007bff;
            color: white;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        td img {
            width: 100px;
            height: auto;
            border-radius: 5px;
        }

        td a {
            color: #007bff;
            text-decoration: none;
            font-weight: bold;
        }

        td a:hover {
            text-decoration: underline;
        }

        .hide-id-column {
            display: none;
        }

        .center {
            text-align: center;
        }

        .back-link {
            text-decoration: none;
            color: #007bff;
            font-weight: bold;
            padding: 10px;
            border: 1px solid #007bff;
            border-radius: 5px;
        }

        .back-link:hover {
            background-color: #007bff;
            color: white;
        }

        .fa-trash {
            cursor: pointer;
            color: red;
            transition: color 0.3s ease;
        }

        .fa-trash:hover {
            color: darkred;
        }

        /* Responsividade para telas menores */
        @media (max-width: 768px) {
            table {
                width: 100%;
                font-size: 14px;
            }

            td, th {
                padding: 8px;
            }

            h1, h2 {
                font-size: 1.5em;
            }

            td img {
                width: 80px;
            }

            .back-link {
                font-size: 14px;
                padding: 8px;
            }
        }

        @media (max-width: 480px) {
            table {
                width: 100%;
                font-size: 12px;
            }

            td, th {
                padding: 6px;
            }

            td img {
                width: 60px;
            }

            .back-link {
                font-size: 12px;
                padding: 6px;
            }
        }
    </style>

</head>
<body>
    <h1>Notificação</h1>
    <h2>Novo Produtos</h2>

    <?php if ($result->num_rows > 0): ?>
        <table>
            <tr>
                <th class="hide-id-column">id</th>
                <th>Data</th>
                <th>Imagem</th>
                <th>Nome Produto</th>
                <th>Status</th>
                <th>Mensagem</th>
                <th>Detalhes</th>
                <th>Ações</th>
            </tr>

            <?php while ($parceiro = $result->fetch_assoc()): ?>
                <?php
                // Dados do produto
                $id_produto = $parceiro['id_produto'];
                $sql_produto = "SELECT * FROM produtos WHERE id_produto = $id_produto";
                $result_prod_dados = $mysqli->query($sql_produto) or die($mysqli->error);
                $dados_produto = $result_prod_dados->fetch_assoc();

                // Preparar os dados para exibição
                $id = htmlspecialchars($parceiro['id']);
                $data = date("d/m/Y", strtotime($parceiro['data']));
                $imagens = explode(',', $dados_produto['imagens'] ?? '');
                $nomeProduto = htmlspecialchars($dados_produto['nome_produto'] ?? 'Produto não disponível');                
                $imagem_principal = htmlspecialchars($imagens[0] ?? 'default.jpg');
                $status = htmlspecialchars($parceiro['analize'] ?? '');
                $msg = htmlspecialchars($parceiro['msg'] ?? '');
                ?>

                <tr>
                    <td class="hide-id-column"><?php echo htmlspecialchars($id); ?></td>
                    <td><?php echo htmlspecialchars($data); ?></td>
                    <td><img src="../parceiros/produtos/img_produtos/<?php echo $imagem_principal; ?>" alt="Imagem do Produto" style="width: 100px; height: auto;"></td>
                    <td><?php echo $nomeProduto; ?></td>
                    <td><?php echo $status; ?></td>
                    <td><?php echo $msg; ?></td>
                    <td>
                        <a href="produtos/editar_produto.php?id=<?php echo htmlspecialchars($parceiro['id']); ?>&id_parceiro=<?php echo htmlspecialchars($parceiro['id_parceiro']); ?>&id_produto=<?php echo htmlspecialchars($parceiro['id_produto']); ?>">Ver Detalhes</a>
                    </td>
                    <td>
                        <i class="fas fa-trash" onclick="confirmarExclusao(this)" title="Excluir notificação"></i>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <div class="center"><p>Nenhum produto em análise.</p></div>
    <?php endif; ?>

    <div class="center">
        <a href="parceiro_home.php" class="back-link">Voltar</a>
    </div>

</body>
<script>
// Função para excluir a notificação
function confirmarExclusao(btn) {
    var idNotificacao = btn.closest('tr').querySelector('td.hide-id-column').textContent;
    if (confirm("Tem certeza de que deseja excluir esta notificação?")) {
        // Redireciona para a página de exclusão
        window.location.href = `excluir_notificacao.php?id_notificacao=${idNotificacao}`;
    }
}
</script>
</html>
