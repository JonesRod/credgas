<?php
    // Inclui a conexão com o banco de dados
    include('../../../conexao.php');

    // Inicia a sessão
    session_start();

    if (isset($_GET['id'])) {
        //echo ('oi');
    
        $id = $_GET['id'];
    
        // Consulta para buscar a notificação com o ID fornecido
        $sql_not = "SELECT * FROM contador_notificacoes_parceiro WHERE id = $id";
        $result = $mysqli->query($sql_not) or die($mysqli->error);
    
        if (isset($_GET['id'])) {
            //echo ('oi');
        
            $id = $_GET['id'];
        
            // Consulta para buscar a notificação com o ID fornecido
            $sql_not = "SELECT * FROM contador_notificacoes_parceiro WHERE id = $id";
            $result = $mysqli->query($sql_not) or die($mysqli->error);
        
            // Verifica se a notificação foi encontrada
            if ($result->num_rows > 0) {
                // Exclui a notificação da tabela
                $sql_delete = "DELETE FROM contador_notificacoes_parceiro WHERE id = $id";
                if ($mysqli->query($sql_delete)) {
                    //echo "Notificação excluída com sucesso.";
                } else {
                    //echo "Erro ao excluir a notificação: " . $mysqli->error;
                }
            } else {
                //echo "Notificação não encontrada.";
            }
        }
        
    }

    // Verifica se o ID do produto foi passado via GET
    if (isset($_GET['id_produto'])) {
        // Obtém o ID do produto da URL e faz o tratamento adequado para evitar injeção SQL
        $id_produto = intval(value: $_GET['id_produto']);

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
            //$stmt->close();
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
        <input type="hidden" id="imagens_removidas" name="imagens_removidas" value="">

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

        <?php
            // Consulta para buscar as categorias
            $sql = $mysqli->query("SELECT * FROM categorias WHERE id > '0'") or die($mysqli->error);

            // Verifica se a consulta retornou resultados
            if ($sql->num_rows > 0) {
                echo '<div class="form-group">';
                echo '<label for="descricao_produto">Categoria:</label>';
                echo '<select required name="categoria" id="categoria">';

                // A primeira opção será a "Escolha", com valor vazio
                echo '<option value="">Escolha</option>';

                // Gerando as opções dinamicamente
                while ($row = $sql->fetch_assoc()) {
                    // Verifica se a categoria é a que foi salva anteriormente
                    $selected = ($row['categorias'] == $produto['categoria']) ? 'selected' : '';

                    // Exibe a opção com a verificação de seleção
                    echo '<option value="' . htmlspecialchars($row['categorias']) . '" ' . $selected . '>' . htmlspecialchars($row['categorias']) . '</option>';
                }

                echo '</select>';
                echo '</div>';
            } else {
                echo '<p>Nenhuma categoria encontrada.</p>';
            }

            // Fecha a conexão com o banco
            //$mysqli->close();
        ?>

        <!-- Valor do Produto -->
        <div class="form-group">
            <!-- Converte o valor do produto para float e formata -->
            <?php
                $valor_produto = str_replace(',', '.', $produto['valor_produto']);
                $valor_produto = floatval($valor_produto);
            ?>
            <label for="valor_produto">Valor do Produto (R$):</label>
            <input type="text" id="valor_produto" name="valor_produto" 
            value="<?php echo number_format($valor_produto, 2, ',', '.'); ?>" 
            required oninput="formatarValor(this)">
        </div>

        <div class="form-group">
            <label for="valor_produto_taxa">Valor do Produto + Taxa da Plataforma (%): R$ </label>
            <input type="hidden" id="taxa" name="taxa" value="<?php echo $produto['taxa_padrao'];?>">           
            <input type="text" id="valor_produto_taxa" name="valor_produto_taxa" 
            value="<?php echo htmlspecialchars($produto['taxa_padrao']); ?>" readonly required>
        </div>

        <!-- Frete Grátis -->
        <div class="form-group">
            <label for="frete_gratis">Frete Grátis:</label>
            <select id="frete_gratis" name="frete_gratis" ><!--onchange="toggleFreteValor(this)"-->
                <option value="0" <?php echo ($produto['frete_gratis'] == 0) ? 'selected' : ''; ?>>Não</option>
                <option value="1" <?php echo ($produto['frete_gratis'] == 1) ? 'selected' : ''; ?>>Sim</option>
            </select>
        </div>

        <!-- Valor do Frete -->
        <div class="form-group" id="frete-group" style="<?php echo ($produto['frete_gratis'] == 1) ? 'display:none;' : 'display:block;'; ?>">
            <?php
                $valor_frete = str_replace(',', '.', $produto['valor_frete']);
                $valor_frete = floatval($valor_frete);
            ?>            
            <label for="valor_frete">Valor do Frete (R$):</label>
            <input type="text" id="valor_frete" name="valor_frete" 
            value="<?php echo number_format($valor_frete, 2, ',', '.'); ?>" 
            oninput="formatarValorFrete(this)">
        </div>

        <!-- Imagens Salvas (até 6) -->
        <div class="form-group">
            <div id="preview">
                <?php
                $imagens = isset($produto['imagens']) ? explode(separator: ',', string: $produto['imagens']) : [];
                if (count($imagens) > 0) {
                    foreach ($imagens as $index => $imagem):
                        $imagem = trim($imagem);
                        if (!empty($imagem)):
                ?>
                            <div class="preview-img" id="imagem-<?php echo $index; ?>">
                                <img src="img_produtos/<?php echo htmlspecialchars($imagem); ?>" alt="Imagem do produto" style="width: 100px; height: 100px;" required>
                                <button type="button" class="remove-btn" onclick="removerImagem('<?php echo htmlspecialchars($imagem); ?>', <?php echo $index; ?>)">
                                    <i class="fas fa-trash"></i> <!-- Ícone de lixeira -->
                                </button>
                            </div>
                <?php
                        endif;
                    endforeach;
                } else {
                    echo "<p class='alert alert-warning'>É necessário adicionar pelo menos uma imagem do produto.</p>";
                }
                ?>
            </div>

            <label for="produtoImagens">Atualizar Imagens (até 6):</label>
            <input type="file" id="produtoImagens" name="produtoImagens[]" accept="image/*" multiple>
        </div>

        <!-- Ocultar -->
        <div class="form-group">
            <label for="ocultar">Ocultar:</label>
            <select id="ocultar" name="ocultar" >
                <option value="0" <?php echo ($produto['oculto'] == 0) ? 'selected' : ''; ?>>Não</option>
                <option value="1" <?php echo ($produto['oculto'] == 1) ? 'selected' : ''; ?>>Sim</option>
            </select>
        </div>

        <!-- Configurações de Promoção -->
        <h2>Promoção</h2>

        <!-- Ativar Promoção -->
        <div class="form-group">
            <label for="promocao">Colocar em Promoção:</label>
            <select id="promocao" name="promocao" onchange="togglePromocaoFields()">
                <option value="0" <?php echo ($produto['promocao'] == 0) ? 'selected' : ''; ?>>Não</option>
                <option value="1" <?php echo ($produto['promocao'] == 1) ? 'selected' : ''; ?>>Sim</option>
            </select>
        </div>

        <!-- Valor do Produto na Promoção -->
        <div class="form-group promocao-field" style="<?php echo ($produto['promocao'] == 1) ? 'display:block;' : 'display:none;'; ?>">
            <label for="valor_promocao">Valor do Produto na Promoção (R$):</label>
            <input type="text" id="valor_promocao" name="valor_promocao" 
            value="<?php echo number_format($produto['valor_promocao'], 2, ',', '.'); ?>" 
            oninput="formatarValorPromocao(this)">
        </div>

        <!-- Valor com Taxa na Promoção -->
        <div class="form-group promocao-field" style="<?php echo ($produto['promocao'] == 1) ? 'display:block;' : 'display:none;'; ?>">
            <label for="valor_promocao_taxa">Valor com Taxa (R$):</label>
            <input type="text" id="valor_promocao_taxa" name="valor_promocao_taxa" 
            value="<?php echo number_format($produto['taxa_padrao'], 2, ',', '.'); ?>" readonly>
        </div>

        <!-- Frete Grátis na Promoção -->
        <div class="form-group promocao-field" style="<?php echo ($produto['promocao'] == 1) ? 'display:block;' : 'display:none;'; ?>">
            <label for="frete_gratis_promocao">Frete Grátis na Promoção:</label>
            <select id="frete_gratis_promocao" name="frete_gratis_promocao">
                <option value="0" <?php echo ($produto['frete_gratis_promocao'] == 0) ? 'selected' : ''; ?>>Não</option>
                <option value="1" <?php echo ($produto['frete_gratis_promocao'] == 1) ? 'selected' : ''; ?>>Sim</option>
            </select>
        </div>

        <!-- Valor do Frete -->
        <div class="form-group" id="frete-gratis-group" style="<?php echo ($produto['frete_gratis_promocao'] == 1) ? 'display:none;' : 'display:block;'; ?>">
            <?php
                $valor_frete_promocao = str_replace(',', '.', $produto['valor_frete_promocao']);
                $valor_frete_promocao = floatval($valor_frete_promocao);
            ?>            
            <label for="valor_frete_promocao">Valor do Frete (R$):</label>
            <input type="text" id="valor_frete_promocao" name="valor_frete_promocao" 
            value="<?php echo number_format($valor_frete_promocao, 2, ',', '.'); ?>" 
            oninput="formatarValorFretePromocao(this)">
        </div>

        <!-- Campo Data de Início da Promoção -->
        <div class="form-group promocao-field">
            <label for="ini_promocao">Data de Início da Promoção:</label>
            <input type="date" id="ini_promocao" name="ini_promocao" required
                value="<?php echo !empty($produto['ini_promocao']) ? $produto['ini_promocao'] : date('Y-m-d'); ?>">
        </div>

        <!-- Data Final da Promoção -->
        <div class="form-group promocao-field" style="<?php echo ($produto['promocao'] == 1) ? 'display:block;' : 'display:none;'; ?>">
            <label for="fim_promocao">Data Final da Promoção:</label>
            <input type="date" id="fim_promocao" name="fim_promocao" required
            value="<?php echo !empty($produto['fim_promocao']) ? $produto['fim_promocao'] : date('Y-m-d'); ?>">
        </div>

        <!-- Botões -->
        <div class="form-group">
            <button type="button" class="btn btn-secondary" onclick="window.location.href='../parceiro_home.php'">Voltar</button>
            <button type="submit" class="btn btn-primary" onclick="checkPromocao()">Salvar Alterações</button>
        </div>

        <script>
            // Função para remover imagem
            function removerImagem(imagem, index) {
                if (confirm("Tem certeza que deseja remover esta imagem?")) {
                    const imagensRemovidas = document.getElementById('imagens_removidas').value.split(',');
                    imagensRemovidas.push(imagem);
                    document.getElementById('imagens_removidas').value = imagensRemovidas.join(',');

                    document.getElementById('imagem-' + index).remove();
                }
            }

            // Função de pré-visualização de imagens
            const inputImagens = document.getElementById('produtoImagens');
            const preview = document.getElementById('preview');

            inputImagens.addEventListener('change', function() {
                const imagensAtuais = preview.querySelectorAll('.preview-img');
                const totalImagensSalvas = imagensAtuais.length;

                const files = inputImagens.files;

                if (files.length + totalImagensSalvas > 6) {
                    alert('Você pode carregar até 6 imagens no total (incluindo as já existentes).');
                    inputImagens.value = '';
                    return;
                }

                for (let i = 0; i < files.length; i++) {
                    const file = files[i];
                    const reader = new FileReader();

                    reader.onload = function(e) {
                        const div = document.createElement('div');
                        div.classList.add('preview-img');
                        div.id = 'nova-imagem-' + (totalImagensSalvas + i); // Novo ID para a nova imagem

                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.style.width = '100px';
                        img.style.height = '100px';

                        const removeBtn = document.createElement('button');
                        removeBtn.type = 'button';
                        removeBtn.classList.add('remove-btn');
                        removeBtn.innerHTML = '<i class="fas fa-trash"></i>';
                        removeBtn.onclick = function() {
                            div.remove();
                            const dataTransfer = new DataTransfer();
                            for (let j = 0; j < inputImagens.files.length; j++) {
                                if (j !== i) {
                                    dataTransfer.items.add(inputImagens.files[j]);
                                }
                            }
                            inputImagens.files = dataTransfer.files; // Atualiza o campo de arquivo
                        };

                        div.appendChild(img);
                        div.appendChild(removeBtn);
                        preview.appendChild(div);
                    };

                    reader.readAsDataURL(file);
                }
            });

            // Validação do formulário antes de enviar
            document.querySelector('form').addEventListener('submit', function(event) {
                const previewImages = document.querySelectorAll('.preview-img');
                const freteGratisPromocao = document.getElementById('frete_gratis_promocao').value;
                const valorFretePromocao = document.getElementById('valor_frete_promocao').value.replace(',', '.');

                // Verificação de imagem
                if (previewImages.length == 0) {
                    event.preventDefault(); // Impede o envio do formulário
                    alert('É necessário adicionar pelo menos uma imagem do produto.');
                    return;
                }

                // Verificação de valor de frete quando o frete grátis não está ativado
                if (freteGratisPromocao == 0 && (valorFretePromocao == '' || parseFloat(valorFretePromocao) == 0)) {
                    event.preventDefault(); // Impede o envio do formulário
                    alert('O valor do frete promocional deve ser maior que 0.');
                    return;
                }
            });

            // Exibe ou oculta os campos da promoção com base na seleção
            function togglePromocaoFields() {
                const promocaoSelect = document.getElementById('promocao').value;
                const promocaoFields = document.querySelectorAll('.promocao-field');
                
                promocaoFields.forEach(field => {
                    field.style.display = promocaoSelect == 1 ? 'block' : 'none';
                });
                
                // Calcula o valor com a taxa da promoção
                if (promocaoSelect == 1) formatarValorPromocao();
            }

            // Carrega os campos corretamente ao carregar a página
            window.onload = function() {
                togglePromocaoFields();
                // Calcula o valor com a taxa ao carregar a página
                formatarValor();

                const freteGratisSelect = document.getElementById('frete_gratis_promocao');
                const freteGroup = document.getElementById('frete-gratis-group');
                const valorFrete = document.getElementById('valor_frete_promocao');
                
                // Exibir ou ocultar o campo de frete com base no valor de frete grátis ao carregar a página
                if (freteGratisSelect && freteGroup && valorFrete) {
                    // Verifica o valor do campo frete grátis na promoção e ajusta a exibição
                    if (freteGratisSelect.value == 1) {
                        freteGroup.style.display = 'none'; // Oculta o campo de frete
                        valorFrete.value = '0,00'; // Define o valor do frete como 0,00
                        console.log(freteGratisSelect.value);
                    } else {
                        freteGroup.style.display = 'block'; // Exibe o campo de frete
                    }

                    // Adiciona evento de mudança para atualizar conforme o usuário altera o frete grátis na promoção
                    freteGratisSelect.addEventListener('change', function() {
                        if (this.value == 1) {
                            freteGroup.style.display = 'none'; // Oculta o campo de frete quando for frete grátis
                            valorFrete.value = '0,00'; // Define o valor do frete como 0,00 se for frete grátis
                        } else {
                            freteGroup.style.display = 'block'; // Mostra o campo de frete quando não for frete grátis
                            console.log('Página carregada com sucesso2');
                        }
                    });
                }
            };

            function checkPromocao() {
                const promocao = document.getElementById('promocao').value;
                if (promocao == 0) {
                    document.getElementById('valor_promocao').value = '';
                    document.getElementById('valor_promocao_taxa').value = '';
                    document.getElementById('frete_gratis_promocao').value = 1;
                    document.getElementById('ini_promocao').value = new Date().toISOString().split('T')[0];
                    document.getElementById('fim_promocao').value = new Date().toISOString().split('T')[0];
                    //console.log('Promoção desativada');
                }
            }

        </script>
    </form>
</body>
</html>
