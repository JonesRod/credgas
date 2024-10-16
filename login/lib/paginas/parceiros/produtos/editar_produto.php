<?php
// Inclui a conexão com o banco de dados
include('../../../conexao.php');

// Inicia a sessão
session_start();

// Verifica se o ID do produto foi passado via GET
if (isset($_GET['id'])) {
    // Obtém o ID do produto da URL e faz o tratamento adequado para evitar injeção SQL
    $id_produto = intval($_GET['id']);

    // Consulta para obter os dados do produto
    $sql_produto = "SELECT * FROM produtos WHERE id_produto = ?";
    $stmt = $mysqli->prepare($sql_produto); // Prepara a consulta
    if ($stmt) {
        $stmt->bind_param("i", $id_produto); // Liga o parâmetro ID ao SQL
        $stmt->execute(); // Executa a consulta
        $result = $stmt->get_result(); // Obtém o resultado da consulta

        // Verifica se o produto foi encontrado
        if ($result->num_rows > 0) {
            // Armazena os dados do produto
            $produto = $result->fetch_assoc();
        } else {
            echo "Produto não encontrado.";
            exit;
        }

        // Libera o resultado e fecha a declaração
        $stmt->close();
    } else {
        echo "Erro na preparação da consulta: " . $mysqli->error;
        exit;
    }
} else {
    echo "ID do produto não fornecido.";
    exit;
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="adicionar_produtos.css">
    <title>Editar Produto</title>
</head>
<body>
    <form action="atualizar_produto.php" method="POST" enctype="multipart/form-data">
        <h1>Editar Produto</h1>

        <input type="hidden" id="id_parceiro" name="id_parceiro" value="<?php echo $produto['id_parceiro']; ?>">

        <!-- Nome do Produto -->
        <div class="form-group">
            <label for="nome_produto">Nome do Produto:</label>
            <input type="text" id="nome_produto" name="nome_produto" value="<?php echo htmlspecialchars($produto['nome_produto']); ?>" required>
        </div>

        <!-- Descrição do Produto -->
        <div class="form-group">
            <label for="descricao_produto">Descrição do Produto:</label>
            <textarea id="descricao_produto" name="descricao_produto" rows="4" required><?php echo htmlspecialchars($produto['descricao_produto']); ?></textarea>
        </div>

        <!-- Valor do Produto -->
        <div class="form-group">
            <label for="valor_produto">Valor do Produto (R$):</label>
            <input type="text" id="valor_produto" name="valor_produto" step="0.01" value="<?php echo htmlspecialchars($produto['valor_produto']); ?>" required oninput="formatarValor(this)" >
        </div>

        <div class="form-group">
            <label for="valor_produto_taxa">Valor do Produto + taxa (10%) da plataforma (R$):</label>
            <input type="text" id="valor_produto_taxa" name="valor_produto_taxa" step="0.01" value="" required readonly>
        </div>

        <!-- Opção de Frete Grátis -->
        <div class="form-group">
            <label>Frete Grátis:</label>
            <select id="frete_gratis" name="frete_gratis">
                <option value="nao" <?php echo ($produto['frete_gratis'] == 'nao') ? 'selected' : ''; ?>>Não</option>
                <option value="sim" <?php echo ($produto['frete_gratis'] == 'sim') ? 'selected' : ''; ?>>Sim</option>
            </select>
        </div>

        <div class="frete-group" id="frete-group">
            <label for="valor_frete">Valor do Frete (R$):</label>
            <input type="text" id="valor_frete" name="valor_frete" step="0.01" value="<?php echo htmlspecialchars($produto['valor_frete']); ?>" oninput="formatarValorFrete(this)">
        </div>

        <!-- Upload de Imagens (até 6) -->
        <div class="form-group">
            <div id="preview">
                <?php
                // Converte a string de imagens em um array
                $imagens = isset($produto['imagens']) ? explode(separator: ',', string: $produto['imagens']) : [];
                
                // Itera sobre as imagens
                foreach ($imagens as $index => $imagem):
                    $imagem = trim($imagem); // Remove espaços em branco ao redor da string da imagem
                    if (!empty($imagem)): // Verifica se há uma imagem válida
                ?>
                    <div>
                        <img src="<?php echo htmlspecialchars(string: $imagem); ?>" alt="Imagem do produto" style="width: 100px; height: 100px;">
                        <button type="button" class="remove-btn" onclick="removerImagem(<?php echo $index; ?>)">
                            <i class="fas fa-trash"></i> <!-- Ícone de lixeira -->
                        </button>
                    </div>
                <?php endif; endforeach; ?>
            </div>
            <label for="produtoImagens">Atualizar Imagens (até 6):</label>
            <input type="file" id="produtoImagens" name="produtoImagens[]" accept="image/*" multiple>
        </div>

        <!-- Botões -->
        <div class="form-group">
            <button type="button" class="btn btn-secondary" onclick="window.history.back();">Voltar</button>
            <button type="submit" class="btn btn-primary">Salvar Alterações</button>
        </div>
        
        <script src="adicionar_produto.js"></script>
        <script>
                window.onload = function() {
                    formatarValor(document.getElementById('valor_produto'));
                };
        </script>
    </form>
    
</body>
</html>
