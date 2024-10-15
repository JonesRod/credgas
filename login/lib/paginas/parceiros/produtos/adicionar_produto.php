<?php
    include('../../../conexao.php');

    // Inicia a sessão
    if (!isset($_SESSION)) {
        session_start(); 
    }
    // Verifica se o ID do parceiro foi enviado via POST
    if (isset($_POST['id_parceiro'])) {
        $id_parceiro = $_POST['id_parceiro'];
        // Agora você pode usar $id_parceiro
        echo "ID do Parceiro: " . $id_parceiro;
    } else {
        session_unset();
        session_destroy(); 
        header(header: "Location: ../../../../../index.php");
        exit();
    }
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="adicionar_produtos.css">
    <title>Adicionar Produtos</title>
</head>
<body>
    <form action="salvar_produto.php" method="POST" enctype="multipart/form-data">
        <h1>Produto</h1>

        <input type="hidden" id="id_parceiro" name="id_parceiro" value="<?php echo $id_parceiro;?>">

        <!-- Nome do Produto -->
        <div class="form-group">
            <label for="nome_produto">Nome do Produto:</label>
            <input type="text" id="nome_produto" name="nome_produto" required>
        </div>

        <!-- Descrição do Produto -->
        <div class="form-group">
            <label for="descricao_produto">Descrição do Produto:</label>
            <textarea id="descricao_produto" name="descricao_produto" rows="4" required></textarea>
        </div>

        <!-- Valor do Produto -->
        <div class="form-group">
            <label for="valor_produto">Valor do Produto (R$):</label>
            <input type="text" id="valor_produto" name="valor_produto" step="0.01" required 
            oninput="formatarValor(this)">
        </div>

        <div class="form-group">
            <label for="valor_produto_taxa">Valor do Produto + taxa (10%) da plataforma (R$):</label>
            <input type="text" id="valor_produto_taxa" name="valor_produto_taxa" step="0.01" required readonly>
        </div>

        <!-- Opção de Frete Grátis -->
        <div class="form-group">
            <label>Frete Grátis:</label>
            <select id="frete_gratis" name="frete_gratis">
                <option value="nao">Não</option>
                <option value="sim">Sim</option>
            </select>
        </div>

        <div class="frete-group" id="frete-group">
            <label for="valor_frete">Valor do Frete (R$):</label>
            <input type="text" id="valor_frete" name="valor_frete" step="0.01" oninput="formatarValorFrete(this)" >
        </div>

        <!-- Upload de Imagens (até 10) -->
        <div class="form-group">
            <div id="preview"></div>
            <label for="produtoImagens">Selecione no máximo 6 imagens do produto:</label>
            <input type="file" id="produtoImagens" name="produtoImagens[]" accept="image/*" multiple >
        </div>

        <!-- Botões -->
        <div class="form-group">
            <button type="button" class="btn btn-secondary" onclick="window.history.back();">Voltar</button>
            <button type="submit" class="btn btn-primary">Salvar Produto</button>
        </div>
        <script src="adicionar_produto.js"></script>        
    </form>
    

</body>
</html>
