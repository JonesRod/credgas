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
$sql_query = "SELECT * FROM contador_notificacoes_admin WHERE not_atualizar_produto = '1'";
$result = $mysqli->query($sql_query) or die($mysqli->error);

?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produto em Análise</title>
    <link rel="stylesheet" href="not_detalhes_edicao_produtos.css">
</head>
<body>
    <h1>Notificação</h1>
    <h2>Produtos Editados</h2>

    <?php if ($result->num_rows > 0): ?>
        <table>
            <tr>
                <th>Data de Cadastro</th>
                <th>Logo</th>
                <th>Nome Fantasia</th>
                <th>Img Produto</th>
                <th>Produto</th>
                <th>Detalhes</th>
            </tr>

            <?php while ($parceiro = $result->fetch_assoc()): ?>
                <?php
                // Dados do parceiro
                $id_parceiro = $parceiro['id_parceiro'];
                //echo ('oi').$id_parceiro;
                $sql_parceiro = "SELECT * FROM meus_parceiros WHERE id = $id_parceiro";
                $result_par_dados = $mysqli->query($sql_parceiro) or die($mysqli->error);
                $dados_parceiro = $result_par_dados->fetch_assoc();

                // Dados do produto
                $id_produto = $parceiro['id_produto'];
                $sql_produto = "SELECT * FROM produtos WHERE id_produto = $id_produto";
                $result_prod_dados = $mysqli->query($sql_produto) or die($mysqli->error);
                $dados_produto = $result_prod_dados->fetch_assoc();

                // Preparar os dados para exibição
                $data = date("d/m/Y", strtotime($parceiro['data']));
                $logo = htmlspecialchars( ($dados_parceiro['logo'] ?? 'default.jpg'));
                $nomeFantasia = htmlspecialchars($dados_parceiro['nomeFantasia'] ?? 'Nome não disponível');
                $imagens = explode(',', $dados_produto['imagens'] ?? '');
                $imagem_principal = htmlspecialchars($imagens[0] ?? 'default.jpg');
                $nomeProduto = htmlspecialchars($dados_produto['nome_produto'] ?? 'Produto não disponível');
                //echo $logo;
                ?>

                <tr>
                    <td data-label="Data"><?php echo htmlspecialchars($data); ?></td>
                    <td data-label="Logo"><img src="../parceiros/arquivos/<?php echo $logo; ?>" alt="Logo" style="width: 100px; height: auto;"></td>
                    <td data-label="Nome Fantasia"><?php echo $nomeFantasia; ?></td>
                    <td data-label="Imagem"><img src="../parceiros/produtos/img_produtos/<?php echo $imagem_principal; ?>" alt="Imagem do Produto" style="width: 100px; height: auto;"></td>
                    <td data-label="Produto"><?php echo $nomeProduto; ?></td>
                    <td data-label="Detalhes">
                        <a href="detalhes_produto.php?id_parceiro=<?php echo htmlspecialchars($parceiro['id_parceiro']); ?>&id_produto=<?php echo htmlspecialchars($parceiro['id_produto']); ?>">
                            Ver Detalhes
                        </a>
                    </td>

                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <div class="center"><p>Nenhum produto em análise.</p></div>
    <?php endif; ?>

    <!-- Link para voltar -->
    <div class="center">
        <a href="admin_home.php" class="back-link">Voltar</a>
    </div>

</body>
</html>
