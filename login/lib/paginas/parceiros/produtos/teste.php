<?php
// Inclui a conexão com o banco de dados
include('../../../conexao.php');

// Inicia a sessão
session_start();

// Verifica se o ID do produto foi passado via GET
if (isset($_GET['id'])) {
    // Obtém o ID do produto da URL e faz o tratamento adequado para evitar injeção SQL
    $id_produto = intval(value: $_GET['id']);

    // Consulta para obter os dados do produto
    $sql_produto = "SELECT * FROM produtos WHERE id_produto = ?";
    $stmt = $mysqli->prepare(query: $sql_produto); // Prepara a consulta
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
    <link rel="stylesheet" href="editar_produtos.css">
    <script src="editar_produto.js"></script>
    <title>Editar Produto</title>
</head>
<body>
    <form action="atualizar_produto.php" method="POST" enctype="multipart/form-data">
        <h1>Editar Produto</h1>

        <input type="hidden" id="id_parceiro" name="id_parceiro" value="<?php echo $produto['id_parceiro']; ?>">
        <input type="hidden" name="id_produto" value="<?php echo htmlspecialchars($produto['id_produto']); ?>">
        <input type="hidden" id="imagens_salvas" name="imagens_salvas" value="<?php echo htmlspecialchars($produto['imagens']); ?>">

        <!-- Nome do Produto -->
        <div class="form-group">
            <label for="nome_produto">Nome do Produto:</label>
            <input type="text" id="nome_produto" name="nome_produto" value="<?php echo htmlspecialchars($produto['nome_produto']); ?>" required>
        </div>

        <!-- Descrição do Produto -->
        <div class="form-group">
            <label for="descricao_produto">Descrição do Produto:</label>
            <textarea id="descricao_produto" name="descricao_produto" rows="4" required><?php echo htmlspecialchars($produto['descricao_produto']); ?>
            </textarea>
        </div>

        <!-- Valor do Produto -->
        <div class="form-group">
            <!-- Converte o valor do produto para float e formata -->
            <?php
                $valor_produto = str_replace(search: ',', replace: '.', subject: $produto['valor_produto']);
                $valor_produto = floatval(value: $valor_produto);
            ?>
            <label for="valor_produto">Valor do Produto (R$):</label>
            <input type="text" id="valor_produto" name="valor_produto" 
            value="<?php echo number_format(num: $valor_produto, decimals: 2, decimal_separator: ',', thousands_separator: '.'); ?>" 
            required
            oninput="formatarValor(this)">
        </div>

        <!-- Valor do Produto + Taxa -->
        <div class="form-group">
            <label for="valor_produto_taxa">Valor do Produto + Taxa (10%) da Plataforma (R$):</label>
            <input type="text" id="valor_produto_taxa" name="valor_produto_taxa" 
            value="<?php echo htmlspecialchars($produto['valor_produto_taxa']); ?>" readonly required>
        </div>

        <!-- Frete Grátis -->
        <div class="form-group">
            <label for="frete_gratis">Frete Grátis:</label>
            <select id="frete_gratis" name="frete_gratis" onchange="toggleFreteValor()">
                <option value="nao" <?php echo ($produto['frete_gratis'] == 'nao') ? 'selected' : ''; ?>>Não</option>
                <option value="sim" <?php echo ($produto['frete_gratis'] == 'sim') ? 'selected' : ''; ?>>Sim</option>
            </select>
        </div>

        <!-- Valor do Frete -->
        <div class="form-group" id="frete-group" style="<?php echo ($produto['valor_frete'] == 'sim') ? 'display:none;' : 'display:block;'; ?>">
            <!-- Converte o valor do frete para float e formata -->
            <?php
                $valor_frete = str_replace(search: ',', replace: '.', subject: $produto['valor_frete']);
                $valor_frete = floatval(value: $valor_frete);
            ?>            
            <label for="valor_frete">Valor do Frete (R$):</label>
            <input type="text" id="valor_frete" name="valor_frete" 
            value="<?php echo number_format(num: $valor_frete, decimals: 2, decimal_separator: ',', thousands_separator: '.'); ?>" 
            oninput="formatarValorFrete(this)">
        </div>

        <!-- Imagens Salvas (até 6) -->
        <div class="form-group">
            <div id="preview">
                <?php
                // Converte a string de imagens em um array
                $imagens = isset($produto['imagens']) ? explode(',', $produto['imagens']) : [];

                // Itera sobre as imagens
                foreach ($imagens as $index => $imagem):
                    $imagem = trim($imagem);
                    if (!empty($imagem)):
                ?>
                    <div class="preview-img">
                        <img src="img_produtos/<?php echo htmlspecialchars($imagem); ?>" alt="Imagem do produto" style="width: 100px; height: 100px;">
                        <button type="button" class="remove-btn" onclick="removerImagem('<?php echo htmlspecialchars($imagem); ?>')">
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
        <script src="editar_produto.js"></script>
        <script>

            // Função para calcular o valor com a taxa
            function calcularValorComTaxa() {
                const valorProduto = parseFloat(document.getElementById('valor_produto').value.replace(',', '.')) || 0;
                const taxa = 0.10;
                const valorComTaxa = valorProduto + (valorProduto * taxa);
                document.getElementById('valor_produto_taxa').value = valorComTaxa.toFixed(2).replace('.', ',');
            }

            // Valor original do frete vindo do BD
            var originalFreteValue = "<?php echo number_format(num: $valor_frete, decimals: 2, decimal_separator: ',', thousands_separator: '.'); ?>";

            function toggleFreteValor() {
                var select = document.getElementById("frete_gratis");
                var freteGroup = document.getElementById("frete-group");
                var valorFreteInput = document.getElementById("valor_frete");

                if (select.value === "sim") {
                    // Esconde o campo e zera o valor
                    freteGroup.style.display = "none";
                    valorFreteInput.value = "0.00";
                } else {
                    // Mostra o campo e restaura o valor do BD
                    freteGroup.style.display = "block";
                    setTimeout(function() {
                        valorFreteInput.value = originalFreteValue;
                    }, 10);
                }
                formatarValorFrete();
            }

            // Função para remover imagem
            function removerImagem(imagem) {
                if (confirm("Tem certeza que deseja remover esta imagem?")) {
                    const imagensSalvas = document.getElementById('imagens_salvas').value.split(',');
                    const novaListaImagens = imagensSalvas.filter(img => img.trim() !== imagem.trim());
                    document.getElementById('imagens_salvas').value = novaListaImagens.join(',');
                    location.reload(); // Atualiza a página
                }
            }

            // Função de pré-visualização de imagens
            const inputImagens = document.getElementById('produtoImagens');
            const preview = document.getElementById('preview');

            inputImagens.addEventListener('change', function() {
                preview.innerHTML = ''; // Limpa o preview
                const files = inputImagens.files;

                if (files.length > 6) {
                    alert('Você pode carregar até 6 imagens.');
                    inputImagens.value = ''; // Limpa o campo de arquivo se exceder
                    return;
                }

                for (let i = 0; i < files.length; i++) {
                    const file = files[i];
                    const reader = new FileReader();

                    reader.onload = function(e) {
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.style.width = '100px';
                        img.style.height = '100px';
                        preview.appendChild(img);
                    };

                    reader.readAsDataURL(file); // Lê o arquivo de imagem
                }
            });
            // Função para formatar o valor digitado no campo "valor_produto"
            /*function formatarValorFrete() {
                let valor = document.getElementById('valor_frete').value.replace(/\D/g, '');  // Remove todos os caracteres não numéricos
                valor = (valor / 100).toFixed(2);           // Divide por 100 para ajustar para formato de decimal (0.00)

                valor = valor.replace('.', ',');            // Substitui o ponto pela vírgula
                valor = valor.replace(/\B(?=(\d{3})+(?!\d))/g, ".");  // Adiciona os pontos para separar os milhares

                document.getElementById('valor_frete').value = valor;                        // Atualiza o valor no campo
                //console.log('oii');
            }*/

            // Executa a função para ajustar o campo de frete ao carregar a página
            window.onload = function() {
                //toggleFreteValor();
                calcularValorComTaxa(); // Calcula o valor com a taxa ao carregar a página
                formatarValorFrete();
                //calcularTaxa()
            };

        </script>
    </form>
</body>
</html>
