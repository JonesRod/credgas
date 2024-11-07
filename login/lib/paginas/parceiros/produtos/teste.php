<!-- Consulta para buscar produtos ocultos do catálogo -->
<?php
$produtos_ocultos = $mysqli->query("SELECT * FROM produtos WHERE id_parceiro = '$id' AND oculto = 'sim'") or die($mysqli->error);
?>

<div id="conteudo-produtos_ocultos" class="conteudo-aba" style="display: none;">
    <div class="container">
        <?php 
            // Verifica se há produtos ocultos
            if ($produtos_ocultos->num_rows > 0): 
        ?>
            <input id="inputPesquisaProdutosOcultos" class="input" type="text" placeholder="Pesquisar Produto.">
        </div> 

        <!-- Lista de produtos ocultos aqui -->
        <div class="lista-produtos_ocultos">
            <?php while ($produto = $produtos_ocultos->fetch_assoc()): ?>
                <div class="produto-item">
                    <?php
                        // Exibe a imagem do produto, caso haja uma
                        $imagensArray = explode(',', $produto['imagens']);
                        $primeiraImagem = !empty($imagensArray[0]) ? $imagensArray[0] : 'default_image.jpg';
                    ?>
                    <?php 
                        // Exibe o ícone de oculto, se o produto estiver oculto
                        if ($produto['oculto'] === 'sim'): 
                    ?>
                        <span class="icone-oculto" title="Produto oculto">👁️‍🗨️</span>
                    <?php endif; ?>
                    <img src="produtos/img_produtos/<?php echo $primeiraImagem; ?>" alt="Imagem do Produto" class="produto-imagem">

                    <div class="produto-detalhes">
                        <h3 class="produto-nome">
                            <?php 
                                // Exibe o ícone de frete grátis, se o produto tiver frete grátis
                                if ($produto['frete_gratis'] === 'sim' || ($produto['promocao'] === 'sim' && $produto['frete_gratis_promocao'] === 'sim')): 
                            ?>
                                <span class="icone-frete-gratis" title="Frete grátis">🚚</span>
                            <?php 
                                endif;

                                // Exibe o ícone de promoção, se o produto estiver em promoção
                                if ($produto['promocao'] === 'sim'): 
                            ?>
                                <span class="icone-promocao" title="Produto em promoção">🔥</span>
                            <?php 
                                endif; 
                            ?>
                            <?php echo $produto['nome_produto']; ?>
                        </h3>

                        <p class="produto-descricao"><?php echo $produto['descricao_produto']; ?></p>

                        <?php
                            // Formatação do valor promocional
                            $valor_produto_promocao = floatval(str_replace(',', '.', $produto['valor_produto_taxa']));
                        ?>
                        <p class="produto-preco">R$ <?php echo number_format($valor_produto_promocao, 2, ',', '.'); ?></p>
                        <a href="produtos/editar_produto.php?id_produto=<?php echo $produto['id_produto']; ?>" class="button-editar">Editar</a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>

        <!-- Mensagem de produto não encontrado -->
        <p id="mensagemNaoEncontrado" style="display: none;">Produto não encontrado.</p>
        
        <?php else: ?>
            <p>Nenhum Produto Oculto.</p>
        <?php endif; ?>
</div>
