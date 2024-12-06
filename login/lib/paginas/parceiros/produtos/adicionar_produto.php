<?php
    include('../../../conexao.php');

    // Inicia a sessão
    if (!isset($_SESSION)) {
        session_start(); 
    }

    // Verifica se o ID do parceiro foi enviado via POST
    if (isset($_POST['id_parceiro'])) {
        $id_parceiro = $_POST['id_parceiro'];
    } else {
        session_unset();
        session_destroy(); 
        header("Location: ../../../../../index.php");
        exit();
    }


    // Consulta para buscar as categorias
    $sql = $mysqli->query("SELECT * FROM categorias where id > '0'") or die($mysqli->error);
    $categorias = $sql->fetch_assoc();
 

    if ($result->num_rows > 0) {
        echo '<div class="form-group">';
        echo '<label for="descricao_produto">Categoria:</label>';
        echo '<select required name="categoria" id="categoria">';
        echo '<option value="">Escolha</option>';
        
        // Gerando as opções dinamicamente
        while ($row = $result->fetch_assoc()) {
            echo '<option value="' . htmlspecialchars($row['id']) . '">' . htmlspecialchars($row['categorias']) . '</option>';
        }

        echo '</select>';
        echo '</div>';
    } else {
        echo '<p>Nenhuma categoria encontrada.</p>';
    }

    $conn->close();

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
        <h1>Adicionar Produto</h1>

        <!-- ID do parceiro (campo escondido) -->
        <input type="hidden" id="id_parceiro" name="id_parceiro" value="<?php echo $id_parceiro;?>">

        <!-- Nome do produto -->
        <div class="form-group">
            <label for="nome_produto">Nome do Produto:</label>
            <input type="text" id="nome_produto" name="nome_produto" required>
        </div>

        <!-- Descrição do produto -->
        <div class="form-group">
            <label for="descricao_produto">Descrição do Produto:</label>
            <textarea id="descricao_produto" name="descricao_produto" rows="4" required></textarea>
        </div>

        <!-- Categoria-->
        <div class="form-group">
            <label for="descricao_produto">Categoria:</label>
            <select required name="categoria" id="categoria">
                <option value="Escolha"></option>
                <option value="Alimenticios">Alimenticios</option>
                <option value="Utilitarios">Utilitarios</option>
                <option value="Limpeza">Limpeza</option>
                <option value="Bebidas">Bebidas</option>
            </select>
        </div>

        <!-- Valor do produto -->
        <div class="form-group">
            <label for="valor_produto">Valor do Produto (R$):</label>
            <input type="text" id="valor_produto" name="valor_produto" required oninput="formatarValor(this)">
        </div>

        <!-- Valor do produto com taxa da plataforma -->
        <div class="form-group">
            <label for="valor_produto_taxa">Valor do Produto + taxa (10%) da plataforma (R$):</label>
            <input type="text" id="valor_produto_taxa" name="valor_produto_taxa" readonly>
        </div>

        <!-- Frete grátis (sim ou não) -->
        <div class="form-group">
            <label>Frete Grátis:</label>
            <select id="frete_gratis" name="frete_gratis">
                <option value="nao">Não</option>
                <option value="sim">Sim</option>
            </select>
        </div>

        <!-- Valor do frete (se frete grátis for "não") -->
        <div class="frete-group" id="frete-group">
            <label for="valor_frete">Valor do Frete (R$):</label>
            <input type="text" id="valor_frete" name="valor_frete" oninput="formatarValorFrete(this)">
        </div>

        <!-- Upload de imagens (máximo de 6) -->
        <div class="form-img">
            <label for="produtoImagens">Selecione até 6 imagens do produto:</label>
            
            <!-- Aqui está o input de arquivo para selecionar imagens -->
            <input type="file" id="produtoImagens" name="produtoImagens[]" required multiple accept="image/*">

            <!-- Container de visualização das imagens selecionadas -->
            <div id="image-container" style="display: flex; gap: 10px; flex-wrap: wrap; margin-top: 10px;">
                <!-- As imagens selecionadas aparecerão aqui -->
            </div>
        </div>

        <!-- Botões para voltar e salvar o produto -->
        <div class="form-buton">
            <button type="button" class="btn btn-secondary" onclick="window.history.back();">Voltar</button>
            <button type="submit" class="btn btn-primary">Salvar Produto</button>
        </div>

    </form>

    <script src="adicionar_produto.js"></script>

    <!-- Script para manipulação de imagens -->
    <script>
        // Array para armazenar as imagens carregadas
        let imagens = [];
        const maxFileSize = 5 * 1024 * 1024; // 6 MB em bytes

        document.getElementById('produtoImagens').addEventListener('change', function() {
            const imageContainer = document.getElementById('image-container');
            const files = Array.from(this.files); // Converte FileList para array
            const validTypes = ['image/jpg', 'image/jpeg', 'image/png', 'image/gif'];

            // Verificar se excedeu o limite de 6 imagens (considerando as já carregadas)
            if (imagens.length + files.length > maxFileSize) {
                alert("Você só pode selecionar até 6 imagens.");
                this.value = ''; // Limpa a seleção
                imageContainer.innerHTML = ''; // Limpa todas as imagens do container
                return;
 
            }

            files.forEach((file, index) => {
                if (validTypes.includes(file.type)) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const imageBox = document.createElement('div');
                        imageBox.classList.add('image-upload-box');
                        
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        imageBox.appendChild(img);

                        const deleteButton = document.createElement('button');
                        deleteButton.classList.add('delete-button');
                        deleteButton.innerHTML = '<i class="fas fa-trash"></i>';
                        
                        deleteButton.onclick = function() {
                            // Remover a imagem do DOM
                            imageBox.remove();

                            // Remover a imagem do array de arquivos
                            imagens = imagens.filter((_, i) => i !== index);
                            atualizarInputFiles(imagens);
                        };

                        imageBox.appendChild(deleteButton);
                        document.getElementById('image-container').appendChild(imageBox);

                        imagens.push(file); // Adiciona ao array de imagens
                        atualizarInputFiles(imagens);
                    };
                    reader.readAsDataURL(file);
                } else {
                    alert("Por favor, selecione uma imagem válida (jpg, jpeg, png, gif).");
                    this.value = ''; // Limpa a seleção
                }
            });

        });

        // Função para atualizar o input file após inclusão e exclusão de arquivos
        function atualizarInputFiles(imagens) {
            const dataTransfer = new DataTransfer();
            imagens.forEach(image => dataTransfer.items.add(image));
            document.getElementById('produtoImagens').files = dataTransfer.files;
        }
    </script>
</body>
</html>

