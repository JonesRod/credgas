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
$sql_query = "SELECT * FROM contador_notificacoes_parceiro WHERE not_adicao_produto = '1'";
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
        .hide-id-column, td:nth-child(0) {
            display: none;
        }

</style>
</head>
<body>
    <h1>Notificação</h1>
    <h2>Produtos Editados</h2>

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
                //echo $logo;
                ?>

                <tr>
                    <td data-label="id"><?php echo htmlspecialchars($id); ?></td>
                    <td data-label="Data"><?php echo htmlspecialchars($data); ?></td>
                    <td data-label="Imagem"><img src="../parceiros/produtos/img_produtos/<?php echo $imagem_principal; ?>" alt="Imagem do Produto" style="width: 100px; height: auto;"></td>
                    <td data-label="Produto"><?php echo $nomeProduto; ?></td>
                    <td data-label="status"><?php echo $status; ?></td>
                    <td data-label="Nome Fantasia"><?php echo $msg; ?></td>
                    <td data-label="Detalhes">
                        <a href="produtos/editar_produto.php?id_parceiro=<?php echo htmlspecialchars($parceiro['id_parceiro']); ?>&id_produto=<?php echo htmlspecialchars($parceiro['id_produto']); ?>">
                            Ver Detalhes
                        </a>
                    </td>
                    <!-- Ícone de lixeira para exclusão -->
                    <td>
                        <i class="fas fa-trash" onclick="confirmarExclusao(this)" style="cursor: pointer; color: red;" title="Excluir notificação"></i>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <div class="center"><p>Nenhum produto em análise.</p></div>
    <?php endif; ?>

    <!-- Link para voltar -->
    <div class="center">
        <a href="parceiro_home.php" class="back-link">Voltar</a>
    </div>

</body>
<script>
// Função para excluir a notificação, pegando o ID da célula oculta
function confirmarExclusao(btn) {
    // Pega o ID da célula oculta que está na primeira coluna (índice 0)
    var idNotificacao = btn.closest('tr').querySelector('td:first-child').textContent;

    if (confirm("Tem certeza de que deseja excluir esta notificação?")) {
        // Exibe o ID no console para verificação
        console.log('id_notificacao=' + idNotificacao);
        
        // Redireciona para a página de exclusão
        window.location.href = `excluir_notificacao.php?id_notificacao=${idNotificacao}`;
    }
}
</script>
</html>
