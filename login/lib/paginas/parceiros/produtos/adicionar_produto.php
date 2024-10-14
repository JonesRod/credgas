<?php
// Verifica se o ID do parceiro foi enviado via POST
if (isset($_POST['id_parceiro'])) {
    $id_parceiro = $_POST['id_parceiro'];
    // Agora você pode usar $id_parceiro
    echo "ID do Parceiro: " . $id_parceiro;
} else {
    session_unset();
    session_destroy(); 
    header("Location: ../../../../../index.php");
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
            <input type="number" id="valor_produto" name="valor_produto" step="0.01" required>
        </div>

        <!-- Upload de Imagens (até 10) -->
        <div class="form-group">
            <label for="produtoImagens">Selecione até 10 imagens do produto:</label>
            <input type="file" id="produtoImagens" name="produtoImagens[]" accept="image/*" multiple>
            <!-- Div para pré-visualização das imagens -->
            <div id="preview"></div>
        </div>

        <!-- Botões adicionais -->
        <div class="form-group">
            <button type="submit" name="acao" value="excluir" class="btn btn-danger">Excluir Produto</button>
            <button type="submit" name="acao" value="ocultar" class="btn btn-warning">Ocultar Produto</button>
            <button type="submit" name="acao" value="promocao" class="btn btn-success">Colocar em Promoção</button>
        </div>

        <!-- Botão de Salvar Produto -->
        <div class="form-group">
            <button type="submit" class="btn btn-primary">Salvar Produto</button>
        </div>
    </form>

    <script>
        document.getElementById('produtoImagens').addEventListener('change', function(event) {
            const previewDiv = document.getElementById('preview');
            previewDiv.innerHTML = ''; // Limpa a pré-visualização anterior
            const files = event.target.files; // Arquivos selecionados

            if (files.length > 0) {
                for (let i = 0; i < files.length; i++) {
                    const file = files[i];
                    const fileReader = new FileReader();

                    fileReader.onload = function(e) {
                        const img = document.createElement('img');
                        img.src = e.target.result;

                        // Cria o botão de exclusão
                        const deleteButton = document.createElement('button');
                        deleteButton.innerHTML = '<i class="fas fa-trash-alt"></i>'; // Ícone de lixeira
                        deleteButton.type = 'button';
                        deleteButton.classList.add('delete-button');
                        deleteButton.onclick = function() {
                            previewDiv.removeChild(imgContainer);
                        };

                        const imgContainer = document.createElement('div');
                        imgContainer.style.position = 'relative'; // Para posicionar o botão de exclusão
                        imgContainer.appendChild(img);
                        imgContainer.appendChild(deleteButton);
                        previewDiv.appendChild(imgContainer);
                    };

                    fileReader.readAsDataURL(file); // Converte o arquivo em URL base64 para exibir no <img>
                }
            }
        });
    </script>
</body>
</html>
