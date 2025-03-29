        <!-- Conteúdos correspondentes às abas -->
        <div id="conteudo-promocoes" class="conteudo-aba" style="display: none;">
            <h2>Nossos Parceiros</h2>

            <!-- Pesquisa de Parceiros -->
            <input id="inputPesquisaParceiroPromocao" class="input" type="text" placeholder="Pesquisar Parceiro.">

            <!-- Carrossel de Parceiros -->
            <div class="parceiros-carousel owl-carousel">
                <?php 
                    // Consulta para buscar parceiros que têm produtos em promoção, visíveis e aprovados
                    $sql_parceiros = "
                        SELECT DISTINCT mp.* 
                        FROM meus_parceiros mp
                        JOIN produtos p ON mp.id = p.id_parceiro
                        WHERE mp.status = '1'";
        
                    $result_parceiros = $mysqli->query($sql_parceiros) or die($mysqli->error);
                    // Variável para rastrear se algum parceiro será exibido
                   $parceiro_exibido = false;

                    if ($result_parceiros->num_rows > 0): 
                        while ($parceiro = $result_parceiros->fetch_assoc()): 
                            $id_parceiro = (int)$parceiro['id'];
                            
                            // Consulta para verificar se o parceiro possui produtos em promoção
                            $sql_produtos = "
                                SELECT COUNT(*) AS total 
                                FROM produtos 
                                WHERE id_parceiro = $id_parceiro 
                                    AND oculto != '1' 
                                    AND produto_aprovado = '1' 
                                    AND promocao = '1'
                            ";
                            $result_produtos = $mysqli->query($sql_produtos) or die($mysqli->error);
                            $produto_data = $result_produtos->fetch_assoc();
                            echo $result_produtos->num_rows;
                            // Se o parceiro tiver ao menos um produto em promoção
                            if ($produto_data['total'] > 0): 
                                $parceiro_exibido = true; // Marca que pelo menos um parceiro foi exibido
                                $logoParceiro = !empty($parceiro['logo']) ? htmlspecialchars($parceiro['logo']) : 'placeholder.jpg';
                                ?>
                                <div class="parceiro-card" onclick="window.location.href='login/lib/paginas/loja_parceiro/loja_parceiro.php?id=<?php echo $id_parceiro; ?>'">
                                    <img src="login/lib/paginas/parceiros/arquivos/<?php echo $logoParceiro; ?>" 
                                        alt="Loja não encontrada">
                                    <h3>
                                        <?php
                                            $nomeFantasia = htmlspecialchars($parceiro['nomeFantasia'] ?? '');
                                            echo mb_strimwidth($nomeFantasia, 0, 18, '...'); // Limita a 18 caracteres com "..."
                                        ?>
                                    </h3>
                                    <p><?php echo htmlspecialchars($parceiro['categoria'] ?? 'Categoria não informada'); ?></p>
                                </div>
                            <?php endif; ?>
                        <?php endwhile; ?>

                        <?php 
                        // Caso nenhum parceiro tenha produtos em promoção
                        if (!$parceiro_exibido): ?>
                            <p>Não há Lojas com promoção no momento.</p>
                        <?php endif; ?>

                    <?php else: ?>
                        <p>Nenhum parceiro ativo no momento.</p>
                    <?php endif; ?>
            </div>


            <!-- Mensagem de Parceiro Não Encontrado -->
            <p id="mensagemParNaoEncontradoPromocao" style="display: none;">Parceiro não encontrado.</p> 

            <!-- Produtos -->
            <h2>Produtos</h2>
            <div class="container">

                <!-- Pesquisa de Produtos -->
                <input id="inputPesquisaPromocao" class="input" type="text" placeholder="Pesquisar Produto."></div>

                <div class="products">
                    <?php if (isset($result_produtos) && $result_produtos->num_rows > 0): ?>
                        <?php while ($produto = $result_produtos->fetch_assoc()): ?>
                            <div class="product-card">
                                <?php
                                    // Supondo que a coluna 'imagens' contém os nomes das imagens separados por vírgulas
                                    $imagens = !empty($produto['imagens']) ? explode(',', $produto['imagens']) : [];
                                    $primeira_imagem = $imagens[0] ?? 'placeholder.jpg'; // Usa uma imagem padrão se não houver imagens
                                ?>

                                <img src="login/lib/paginas/parceiros/produtos/img_produtos/<?php echo htmlspecialchars($primeira_imagem); ?>" alt="<?php echo htmlspecialchars($produto['nome_produto']); ?>">
                                <?php 
                                    // Exibe o ícone de frete grátis, se o produto tiver frete grátis
                                    if ($produto['frete_gratis'] == '1' || ($produto['promocao'] == '1' && $produto['frete_gratis_promocao'] == '1')): 
                                ?>
                                    <span class="icone-frete-gratis" title="Frete grátis">🚚</span>
                                <?php 
                                    endif;

                                    // Exibe o ícone de promoção, se o produto estiver em promoção
                                    if ($produto['promocao'] == '1'): 
                                ?>
                                    <span class="icone-promocao" title="Produto em promoção">🔥</span>
                                <?php 
                                    endif; 

                                    $dataCadastro = new DateTime($produto['data']); // Data do produto
                                    $dataAtual = new DateTime(); // Data atual
                                    $intervalo = $dataCadastro->diff($dataAtual); // Calcula a diferença entre as datas
                                    $diasDesdeCadastro = $intervalo->days; // Número de dias de diferença
                                
                                    if ($diasDesdeCadastro <= 30):
                                ?>
                                        <span class="icone-novidades" title="Novidades">🆕</span>
                                <?php
                                    endif;
                                ?>                      
                                
                                <h3><?php echo htmlspecialchars($produto['nome_produto']); ?></h3>
                                <p class="moeda">R$ <?php echo number_format($produto['valor_venda_vista'], 2, ',', '.'); ?></p>
                                <a href="login/lib/detalhes_produto.php?id_produto=<?php echo $produto['id_produto']; ?>" class="btn">Detalhes</a>

                                <!-- Verifica se o usuário está logado para permitir a compra -->
                                <?php if (isset($usuarioLogado) && $usuarioLogado): ?>
                                    <a href="#" class="btn">Comprar</a>
                                <?php else: ?>
                                    <a href="login/lib/login.php" class="btn">Faça login para comprar</a>
                                <?php endif; ?>
                            </div>
                        <?php endwhile; ?>
                    
                        <p>Não há produtos na promoção no momento.</p>
                    <?php endif; ?>
                    <!-- Mensagem de produto não encontrado -->
                    <p id="mensagemNaoEncontradoPromocao" style="display: none;">Produto não encontrado.</p>
                </div>
            </div>
        </div>