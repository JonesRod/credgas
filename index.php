<?php
include('login/lib/conexao.php');

if (!isset($_SESSION)) {
    session_start();
}

// Verifica se o usuário está logado
$usuarioLogado = isset($_SESSION['id']);
//$id_conf = '1';
//echo $usuarioLogado;

$dados = $mysqli->query("SELECT * FROM config_admin WHERE logo != '' ORDER BY data_alteracao DESC LIMIT 1") or die($mysqli->error);
$dadosEscolhido = $dados->fetch_assoc();
$nomeFantasia = $dadosEscolhido['nomeFantasia'];

// Carrega a logo
if (isset($dadosEscolhido['logo'])) {
    $logo = $dadosEscolhido['logo'];
    if ($logo == '') {
        $logo = 'login/lib/paginas/arquivos_fixos/imagem_credgas.jpg';
    } else {
        $logo = 'login/lib/paginas/administrativo/arquivos/' . $logo;
    }
}

$taxa_padrao = $mysqli->query("SELECT * FROM config_admin WHERE taxa_padrao != '' ORDER BY data_alteracao DESC LIMIT 1") or die($mysqli->error);
$taxa = $taxa_padrao->fetch_assoc();

//$sql_usuario = $mysqli->query("SELECT * FROM meus_clientes WHERE id= $usuarioLogado") or die($mysqli->error);
//$usuario = $sql_usuario->fetch_assoc();
//echo $usuario['nome_completo'];
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $nomeFantasia; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css">
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>

    <!--<script src="cadastro_inicial/localizador.js" defer></script>-->
    <link rel="stylesheet" href="login/style/index.css">

</head>

<body>

    <!-- Header -->
    <header>
        <div class="container">
            <div class="logo">
                <img src="<?php if (isset($logo))
                    echo $logo; ?>" alt="Logo" class="logo-img">
                <h1 class="nome-fantasia">
                    <?php
                    if (!empty($nomeFantasia)) {
                        echo htmlspecialchars($nomeFantasia);
                    } else {
                        echo "Nome Fantasia Indisponível";
                    }
                    ?>
                </h1>
            </div>
            <div class="user-area">
                <span>Seja bem-vindo!</span>
                <a href="login/lib/login.php" class="btn-login">Entrar</a>
            </div>
        </div>
    </header>

    <!-- Conteúdo principal -->
    <main id="main-content">
        <!-- Conteúdo -->
        <div class="opcoes">
            <!-- Conteúdo -->
            <div class="tab active" onclick="mostrarConteudo('catalogo',this)">
                <span>Catálogo</span>
            </div>

            <div class="tab" onclick="mostrarConteudo('promocoes',this)">
                <span class="icone-promocao" title="Produto em promoção">🔥</span><span>Promoções</span>
            </div>

            <div class="tab" onclick="mostrarConteudo('frete_gratis',this)">
                <span class="icone-frete-gratis" title="Frete grátis">🚚</span><span>Frete Grátis</span>
            </div>

            <div class="tab" onclick="mostrarConteudo('novidades',this)">
                <span class="icone-novidades" title="Novidades">🆕</span><span>Novidades</span>
            </div>

        </div>

        <!-- Conteúdos correspondentes às abas -->
        <div id="conteudo-catalogo" class="conteudo-aba" style="display: none;">
            <h2>Nossos Parceiros</h2>
            <?php
            // Consulta para buscar parceiros ativos
            $sql_parceiros = "SELECT * FROM meus_parceiros WHERE status = '1'";
            $result_parceiros = $mysqli->query($sql_parceiros) or die($mysqli->error);

            // Consulta para buscar produtos visíveis e aprovados que não estão ocultos
            $sql_produtos = "SELECT * FROM produtos WHERE oculto != '1' AND produto_aprovado = '1'";
            $result_produtos = $mysqli->query($sql_produtos) or die($mysqli->error);

            if ($result_parceiros->num_rows > 0 && $result_produtos->num_rows > 0): ?>
                <!-- Pesquisa de Parceiros -->
                <input id="inputPesquisaParceiroCatalogo" class="input" type="text" placeholder="Pesquisar Parceiro.">
                <!-- Carrossel de Parceiros -->
                <div class="parceiros-carousel owl-carousel">
                    <?php while ($parceiro = $result_parceiros->fetch_assoc()):
                        $logoParceiro = !empty($parceiro['logo']) ? $parceiro['logo'] : 'placeholder.jpg'; ?>
                        <div class="parceiro-card"
                            onclick="window.location.href='login/lib/paginas/loja_parceiro/loja_parceiro.php?id=<?php echo $parceiro['id']; ?>'">
                            <img src="login/lib/paginas/parceiros/arquivos/<?php echo htmlspecialchars($logoParceiro); ?>"
                                alt="Loja não encontrada">
                            <h3><?php echo mb_strimwidth(htmlspecialchars($parceiro['nomeFantasia'] ?? ''), 0, 18, '...'); ?>
                            </h3>
                            <p><?php echo htmlspecialchars($parceiro['categoria']); ?></p>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p>Nenhum parceiro encontrado.</p>
            <?php endif; ?>
            <!-- Mensagem de Parceiro Não Encontrado -->
            <p id="mensagemParNaoEncontradoCatalogo" style="display: none;">Parceiro não encontrado.</p>

            <!-- Produtos -->
            <h2>Produtos</h2>
            <?php
            if ($result_parceiros->num_rows > 0 && $result_produtos->num_rows > 0): ?>
                <!-- Pesquisa de Produtos -->
                <input id="inputPesquisaCatalogo" class="input" type="text" placeholder="Pesquisar Produto.">
                <div class="container">
                    <div class="products">
                        <?php while ($produto = $result_produtos->fetch_assoc()):
                            // Determinar o valor do produto
                            if ($produto['promocao'] === '1') {
                                $valorProduto = $produto['valor_promocao'] + ($produto['valor_promocao'] * ($taxa['taxa_padrao'] / 100));
                            } else {
                                $valorProduto = $produto['valor_venda_vista'];
                            }

                            // Determinar o valor do frete
                            $valorFrete = ($produto['promocao'] === '1' && $produto['frete_gratis_promocao'] === '1')
                                ? $produto['valor_frete_promocao']
                                : $produto['valor_frete'];

                            $imagens = !empty($produto['imagens']) ? explode(',', $produto['imagens']) : [];
                            $primeira_imagem = $imagens[0] ?? 'placeholder.jpg'; ?>
                            <div class="product-card">
                                <img src="login/lib/paginas/parceiros/produtos/img_produtos/<?php echo htmlspecialchars($primeira_imagem); ?>"
                                    alt="<?php echo htmlspecialchars($produto['nome_produto']); ?>">
                                <?php if ($produto['frete_gratis'] == '1' || ($produto['promocao'] == '1' && $produto['frete_gratis_promocao'] == '1')): ?>
                                    <span class="icone-frete-gratis" title="Frete grátis">🚚</span>
                                <?php endif; ?>
                                <?php if ($produto['promocao'] == '1'): ?>
                                    <span class="icone-promocao" title="Produto em promoção">🔥</span>
                                <?php endif; ?>
                                <?php
                                $dataCadastro = new DateTime($produto['data']);
                                $dataAtual = new DateTime();
                                $diasDesdeCadastro = $dataCadastro->diff($dataAtual)->days;
                                if ($diasDesdeCadastro <= 30): ?>
                                    <span class="icone-novidades" title="Novidades">🆕</span>
                                <?php endif; ?>
                                <h3><?php echo htmlspecialchars($produto['nome_produto']); ?></h3>
                                <p class="moeda">R$ <?php echo number_format($valorProduto, 2, ',', '.'); ?></p>
                                <a href="login/lib/detalhes_produto.php?id_produto=<?php echo $produto['id_produto']; ?>"
                                    class="btn">Detalhes</a>
                                <?php if (isset($usuarioLogado) && $usuarioLogado): ?>
                                    <a href="#" class="btn">Comprar</a>
                                <?php else: ?>
                                    <a href="login/lib/login.php" class="btn">Faça login para comprar</a>
                                <?php endif; ?>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p>Não há produtos no momento.</p>
                <?php endif; ?>
                <!-- Mensagem de produto não encontrado -->
                <p id="mensagemNaoEncontradoCatalogo" style="display: none;">Produto não encontrado.</p>
            </div>
        </div>

        <!-- Conteúdos correspondentes às abas -->
        <div id="conteudo-promocoes" class="conteudo-aba" style="display: none;">
            <h2>Nossos Parceiros</h2>
            <?php
            // Consulta para buscar parceiros ativos
            $sql_parceiros = "
                    SELECT DISTINCT mp.* 
                    FROM meus_parceiros mp
                    JOIN produtos p ON mp.id = p.id_parceiro
                    WHERE mp.status = '1' AND p.promocao = '1' AND p.oculto != '1' AND p.produto_aprovado = '1'";
            $result_parceiros = $mysqli->query($sql_parceiros) or die($mysqli->error);

            // Consulta para buscar produtos em promoção, visíveis e aprovados
            $sql_produtos = "
                    SELECT * 
                    FROM produtos 
                    WHERE promocao = '1' AND oculto != '1' AND produto_aprovado = '1'";
            $result_produtos = $mysqli->query($sql_produtos) or die($mysqli->error);

            if ($result_parceiros->num_rows > 0 && $result_produtos->num_rows > 0): ?>
                <!-- Pesquisa de Parceiros -->
                <input id="inputPesquisaParceiroPromocao" class="input" type="text" placeholder="Pesquisar Parceiro.">
                <!-- Carrossel de Parceiros -->
                <div class="parceiros-carousel owl-carousel">
                    <?php while ($parceiro = $result_parceiros->fetch_assoc()):
                        $logoParceiro = !empty($parceiro['logo']) ? $parceiro['logo'] : 'placeholder.jpg'; ?>
                        <div class="parceiro-card"
                            onclick="window.location.href='login/lib/paginas/loja_parceiro/loja_parceiro.php?id=<?php echo $parceiro['id']; ?>'">
                            <img src="login/lib/paginas/parceiros/arquivos/<?php echo htmlspecialchars($logoParceiro); ?>"
                                alt="Loja não encontrada">
                            <h3><?php echo mb_strimwidth(htmlspecialchars($parceiro['nomeFantasia'] ?? ''), 0, 18, '...'); ?>
                            </h3>
                            <p><?php echo htmlspecialchars($parceiro['categoria']); ?></p>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p>Nenhum parceiro encontrado.</p>
            <?php endif; ?>
            <!-- Mensagem de Parceiro Não Encontrado -->
            <p id="mensagemParNaoEncontradoPromocao" style="display: none;">Parceiro não encontrado.</p>

            <!-- Produtos -->
            <h2>Produtos</h2>
            <?php
            if ($result_parceiros->num_rows > 0 && $result_produtos->num_rows > 0): ?>
                <!-- Pesquisa de Produtos -->
                <input id="inputPesquisaPromocao" class="input" type="text" placeholder="Pesquisar Produto.">
                <div class="container">
                    <div class="products">
                        <?php while ($produto = $result_produtos->fetch_assoc()):
                            // Determinar o valor do produto
                            if ($produto['promocao'] === '1') {
                                $valorProduto = $produto['valor_promocao'] + ($produto['valor_promocao'] * ($taxa['taxa_padrao'] / 100));
                            } else {
                                $valorProduto = $produto['valor_venda_vista'];
                            }

                            // Determinar o valor do frete
                            $valorFrete = ($produto['promocao'] === '1' && $produto['frete_gratis_promocao'] === '1')
                                ? $produto['valor_frete_promocao']
                                : $produto['valor_frete'];

                            $imagens = !empty($produto['imagens']) ? explode(',', $produto['imagens']) : [];
                            $primeira_imagem = $imagens[0] ?? 'placeholder.jpg'; ?>
                            <div class="product-card">
                                <img src="login/lib/paginas/parceiros/produtos/img_produtos/<?php echo htmlspecialchars($primeira_imagem); ?>"
                                    alt="<?php echo htmlspecialchars($produto['nome_produto']); ?>">
                                <?php if ($produto['frete_gratis'] == '1' || ($produto['promocao'] == '1' && $produto['frete_gratis_promocao'] == '1')): ?>
                                    <span class="icone-frete-gratis" title="Frete grátis">🚚</span>
                                <?php endif; ?>
                                <?php if ($produto['promocao'] == '1'): ?>
                                    <span class="icone-promocao" title="Produto em promoção">🔥</span>
                                <?php endif; ?>
                                <?php
                                $dataCadastro = new DateTime($produto['data']);
                                $dataAtual = new DateTime();
                                $diasDesdeCadastro = $dataCadastro->diff($dataAtual)->days;
                                if ($diasDesdeCadastro <= 30): ?>
                                    <span class="icone-novidades" title="Novidades">🆕</span>
                                <?php endif; ?>
                                <h3><?php echo htmlspecialchars($produto['nome_produto']); ?></h3>
                                <p class="moeda">R$ <?php echo number_format($valorProduto, 2, ',', '.'); ?></p>
                                <a href="login/lib/detalhes_produto.php?id_produto=<?php echo $produto['id_produto']; ?>"
                                    class="btn">Detalhes</a>
                                <?php if (isset($usuarioLogado) && $usuarioLogado): ?>
                                    <a href="#" class="btn">Comprar</a>
                                <?php else: ?>
                                    <a href="login/lib/login.php" class="btn">Faça login para comprar</a>
                                <?php endif; ?>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p>Não há produtos no momento.</p>
                <?php endif; ?>
                <!-- Mensagem de produto não encontrado -->
                <p id="mensagemNaoEncontradoPromocao" style="display: none;">Produto não encontrado.</p>
            </div>
        </div>

        <!-- Conteúdos correspondentes às abas -->
        <div id="conteudo-frete_gratis" class="conteudo-aba" style="display: none;">
            <h2>Nossos Parceiros</h2>
            <?php
            // Consulta para buscar parceiros ativos com produtos com frete grátis
            $sql_parceiros = "
                    SELECT DISTINCT mp.* 
                    FROM meus_parceiros mp
                    JOIN produtos p ON mp.id = p.id_parceiro
                    WHERE mp.status = '1' AND 
                          (p.frete_gratis = '1' OR (p.promocao = '1' AND p.frete_gratis_promocao = '1')) 
                          AND p.oculto != '1' AND p.produto_aprovado = '1'";
            $result_parceiros = $mysqli->query($sql_parceiros) or die($mysqli->error);

            // Consulta para buscar produtos com frete grátis, visíveis e aprovados
            $sql_produtos = "
                    SELECT * 
                    FROM produtos 
                    WHERE (frete_gratis = '1' OR (promocao = '1' AND frete_gratis_promocao = '1')) 
                          AND oculto != '1' AND produto_aprovado = '1'";
            $result_produtos = $mysqli->query($sql_produtos) or die($mysqli->error);

            if ($result_parceiros->num_rows > 0 && $result_produtos->num_rows > 0): ?>
                <!-- Pesquisa de Parceiros -->
                <input id="inputPesquisaParceiroFrete_gratis" class="input" type="text" placeholder="Pesquisar Parceiro.">
                <!-- Carrossel de Parceiros -->
                <div class="parceiros-carousel owl-carousel">
                    <?php while ($parceiro = $result_parceiros->fetch_assoc()):
                        $logoParceiro = !empty($parceiro['logo']) ? $parceiro['logo'] : 'placeholder.jpg'; ?>
                        <div class="parceiro-card"
                            onclick="window.location.href='login/lib/paginas/loja_parceiro/loja_parceiro.php?id=<?php echo $parceiro['id']; ?>'">
                            <img src="login/lib/paginas/parceiros/arquivos/<?php echo htmlspecialchars($logoParceiro); ?>"
                                alt="Loja não encontrada">
                            <h3><?php echo mb_strimwidth(htmlspecialchars($parceiro['nomeFantasia'] ?? ''), 0, 18, '...'); ?>
                            </h3>
                            <p><?php echo htmlspecialchars($parceiro['categoria']); ?></p>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p>Nenhum parceiro encontrado.</p>
            <?php endif; ?>
            <!-- Mensagem de Parceiro Não Encontrado -->
            <p id="mensagemParNaoEncontradoFrete_gratis" style="display: none;">Parceiro não encontrado.</p>

            <!-- Produtos -->
            <h2>Produtos</h2>
            <?php
            if ($result_parceiros->num_rows > 0 && $result_produtos->num_rows > 0): ?>
                <!-- Pesquisa de Produtos -->
                <input id="inputPesquisaFrete_gratis" class="input" type="text" placeholder="Pesquisar Produto.">
                <div class="container">
                    <div class="products">
                        <?php while ($produto = $result_produtos->fetch_assoc()):
                            // Determinar o valor do produto
                            if ($produto['promocao'] === '1') {
                                $valorProduto = $produto['valor_promocao'] + ($produto['valor_promocao'] * ($taxa['taxa_padrao'] / 100));
                            } else {
                                $valorProduto = $produto['valor_venda_vista'];
                            }

                            // Determinar o valor do frete
                            $valorFrete = ($produto['promocao'] === '1' && $produto['frete_gratis_promocao'] === '1')
                                ? $produto['valor_frete_promocao']
                                : $produto['valor_frete'];

                            $imagens = !empty($produto['imagens']) ? explode(',', $produto['imagens']) : [];
                            $primeira_imagem = $imagens[0] ?? 'placeholder.jpg'; ?>
                            <div class="product-card">
                                <img src="login/lib/paginas/parceiros/produtos/img_produtos/<?php echo htmlspecialchars($primeira_imagem); ?>"
                                    alt="<?php echo htmlspecialchars($produto['nome_produto']); ?>">
                                <?php if ($produto['frete_gratis'] == '1' || ($produto['promocao'] == '1' && $produto['frete_gratis_promocao'] == '1')): ?>
                                    <span class="icone-frete-gratis" title="Frete grátis">🚚</span>
                                <?php endif; ?>
                                <?php if ($produto['promocao'] == '1'): ?>
                                    <span class="icone-promocao" title="Produto em promoção">🔥</span>
                                <?php endif; ?>
                                <?php
                                $dataCadastro = new DateTime($produto['data']);
                                $dataAtual = new DateTime();
                                $diasDesdeCadastro = $dataCadastro->diff($dataAtual)->days;
                                if ($diasDesdeCadastro <= 30): ?>
                                    <span class="icone-novidades" title="Novidades">🆕</span>
                                <?php endif; ?>
                                <h3><?php echo htmlspecialchars($produto['nome_produto']); ?></h3>
                                <p class="moeda">R$ <?php echo number_format($valorProduto, 2, ',', '.'); ?></p>
                                <a href="login/lib/detalhes_produto.php?id_produto=<?php echo $produto['id_produto']; ?>"
                                    class="btn">Detalhes</a>
                                <?php if (isset($usuarioLogado) && $usuarioLogado): ?>
                                    <a href="#" class="btn">Comprar</a>
                                <?php else: ?>
                                    <a href="login/lib/login.php" class="btn">Faça login para comprar</a>
                                <?php endif; ?>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p>Não há produtos no momento.</p>
                <?php endif; ?>
                <!-- Mensagem de produto não encontrado -->
                <p id="mensagemNaoEncontradoFrete_gratis" style="display: none;">Produto não encontrado.</p>
            </div>
        </div>

        <!-- Conteúdos correspondentes às abas -->
        <div id="conteudo-novidades" class="conteudo-aba" style="display: none;">
            <h2>Nossos Parceiros</h2>
            <?php
            // Consulta para buscar parceiros ativos com produtos cadastrados nos últimos 30 dias
            $sql_parceiros = "
                    SELECT DISTINCT mp.* 
                    FROM meus_parceiros mp
                    JOIN produtos p ON mp.id = p.id_parceiro
                    WHERE mp.status = '1' AND 
                          DATEDIFF(NOW(), p.data) <= 30 AND 
                          p.oculto != '1' AND p.produto_aprovado = '1'";
            $result_parceiros = $mysqli->query($sql_parceiros) or die($mysqli->error);

            // Consulta para buscar produtos cadastrados nos últimos 30 dias, visíveis e aprovados
            $sql_produtos = "
                    SELECT * 
                    FROM produtos 
                    WHERE DATEDIFF(NOW(), data) <= 30 AND 
                          oculto != '1' AND produto_aprovado = '1'";
            $result_produtos = $mysqli->query($sql_produtos) or die($mysqli->error);

            if ($result_parceiros->num_rows > 0 && $result_produtos->num_rows > 0): ?>
                <!-- Pesquisa de Parceiros -->
                <input id="inputPesquisaParceiroNovidades" class="input" type="text" placeholder="Pesquisar Parceiro.">
                <!-- Carrossel de Parceiros -->
                <div class="parceiros-carousel owl-carousel">
                    <?php while ($parceiro = $result_parceiros->fetch_assoc()):
                        $logoParceiro = !empty($parceiro['logo']) ? $parceiro['logo'] : 'placeholder.jpg'; ?>
                        <div class="parceiro-card"
                            onclick="window.location.href='login/lib/paginas/loja_parceiro/loja_parceiro.php?id=<?php echo $parceiro['id']; ?>'">
                            <img src="login/lib/paginas/parceiros/arquivos/<?php echo htmlspecialchars($logoParceiro); ?>"
                                alt="Loja não encontrada">
                            <h3><?php echo mb_strimwidth(htmlspecialchars($parceiro['nomeFantasia'] ?? ''), 0, 18, '...'); ?>
                            </h3>
                            <p><?php echo htmlspecialchars($parceiro['categoria']); ?></p>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p>Nenhum parceiro encontrado.</p>
            <?php endif; ?>
            <!-- Mensagem de Parceiro Não Encontrado -->
            <p id="mensagemParNaoEncontradoNovidades" style="display: none;">Parceiro não encontrado.</p>

            <!-- Produtos -->
            <h2>Produtos</h2>
            <?php
            if ($result_parceiros->num_rows > 0 && $result_produtos->num_rows > 0): ?>
                <!-- Pesquisa de Produtos -->
                <input id="inputPesquisaNovidades" class="input" type="text" placeholder="Pesquisar Produto.">
                <div class="container">
                    <div class="products">
                        <?php while ($produto = $result_produtos->fetch_assoc()):
                            // Determinar o valor do produto
                            if ($produto['promocao'] === '1') {
                                $valorProduto = $produto['valor_promocao'] + ($produto['valor_promocao'] * ($taxa['taxa_padrao'] / 100));
                            } else {
                                $valorProduto = $produto['valor_venda_vista'];
                            }

                            // Determinar o valor do frete
                            $valorFrete = ($produto['promocao'] === '1' && $produto['frete_gratis_promocao'] === '1')
                                ? $produto['valor_frete_promocao']
                                : $produto['valor_frete'];

                            $imagens = !empty($produto['imagens']) ? explode(',', $produto['imagens']) : [];
                            $primeira_imagem = $imagens[0] ?? 'placeholder.jpg'; ?>
                            <div class="product-card">
                                <img src="login/lib/paginas/parceiros/produtos/img_produtos/<?php echo htmlspecialchars($primeira_imagem); ?>"
                                    alt="<?php echo htmlspecialchars($produto['nome_produto']); ?>">
                                <?php if ($produto['frete_gratis'] == '1' || ($produto['promocao'] == '1' && $produto['frete_gratis_promocao'] == '1')): ?>
                                    <span class="icone-frete-gratis" title="Frete grátis">🚚</span>
                                <?php endif; ?>
                                <?php if ($produto['promocao'] == '1'): ?>
                                    <span class="icone-promocao" title="Produto em promoção">🔥</span>
                                <?php endif; ?>
                                <?php
                                $dataCadastro = new DateTime($produto['data']);
                                $dataAtual = new DateTime();
                                $diasDesdeCadastro = $dataCadastro->diff($dataAtual)->days;
                                if ($diasDesdeCadastro <= 30): ?>
                                    <span class="icone-novidades" title="Novidades">🆕</span>
                                <?php endif; ?>
                                <h3><?php echo htmlspecialchars($produto['nome_produto']); ?></h3>
                                <p class="moeda">R$ <?php echo number_format($valorProduto, 2, ',', '.'); ?></p>
                                <a href="login/lib/detalhes_produto.php?id_produto=<?php echo $produto['id_produto']; ?>"
                                    class="btn">Detalhes</a>
                                <?php if (isset($usuarioLogado) && $usuarioLogado): ?>
                                    <a href="#" class="btn">Comprar</a>
                                <?php else: ?>
                                    <a href="login/lib/login.php" class="btn">Faça login para comprar</a>
                                <?php endif; ?>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p>Não há produtos no momento.</p>
                <?php endif; ?>
                <!-- Mensagem de produto não encontrado -->
                <p id="mensagemNaoEncontradoNovidades" style="display: none;">Produto não encontrado.</p>
            </div>
        </div>
    </main>

    <script>

        $(document).ready(function () {
            var totalParceiros = <?php echo $result_parceiros->num_rows; ?>; // Total de parceiros no banco

            $(".parceiros-carousel").owlCarousel({
                loop: totalParceiros > 1, // Loop apenas se houver mais de 1 parceiro
                margin: 10,
                center: true, // Centraliza os itens no carrossel
                nav: true,
                autoplay: true,
                autoplayTimeout: 3000,
                responsive: {
                    0: { items: 1 },       // Mostra 1 parceiro por vez em telas pequenas
                    600: { items: 2 },    // Mostra 2 parceiros em telas médias
                    1000: { items: 4 }    // Mostra 4 parceiros em telas grandes
                }
            });
        });

        function mostrarConteudo(aba, element) {

            // Oculta todos os conteúdos das abas
            //console.log('eee');
            var conteudos = document.querySelectorAll('.conteudo-aba');
            conteudos.forEach(function (conteudo) {
                conteudo.style.display = 'none';
            });

            // Remove a classe 'active' de todas as abas
            var tabs = document.querySelectorAll('.tab');
            tabs.forEach(function (tab) {
                tab.classList.remove('active');
            });

            // Mostra o conteúdo da aba clicada
            document.getElementById('conteudo-' + aba).style.display = 'block';

            // Adiciona a classe 'active' à aba clicada
            element.classList.add('active');
            //console.log('eee');

        }

        // Define que a aba "catalogo" está ativa ao carregar a página
        window.onload = function () {
            mostrarConteudo('catalogo', document.querySelector('.tab.active'));
        };

        document.addEventListener("DOMContentLoaded", function () {
            function configurarPesquisa(inputId, itemSelector, mensagemId, abaId) {
                const inputPesquisa = document.getElementById(inputId);
                const itens = document.querySelectorAll(`#${abaId} ${itemSelector}`);
                const mensagem = document.getElementById(mensagemId);

                if (inputPesquisa && mensagem) { // Verifica se os elementos existem
                    inputPesquisa.addEventListener("input", function () {
                        const termo = inputPesquisa.value.toLowerCase();
                        let encontrou = false;

                        itens.forEach(item => {
                            const textoItem = item.textContent.toLowerCase();
                            if (textoItem.includes(termo)) {
                                item.style.display = "block";
                                encontrou = true;
                            } else {
                                item.style.display = "none";
                            }
                        });

                        mensagem.style.display = encontrou ? "none" : "block";
                    });
                }
            }

            // Configurar pesquisa para aba "Catálogo"
            configurarPesquisa(
                "inputPesquisaParceiroCatalogo",
                ".parceiro-card",
                "mensagemParNaoEncontradoCatalogo",
                "conteudo-catalogo"
            );
            configurarPesquisa(
                "inputPesquisaCatalogo",
                ".product-card",
                "mensagemNaoEncontradoCatalogo",
                "conteudo-catalogo"
            );

            // Configurar pesquisa para aba "Promoções"
            configurarPesquisa(
                "inputPesquisaParceiroPromocao",
                ".parceiro-card",
                "mensagemParNaoEncontradoPromocao",
                "conteudo-promocoes"
            );
            configurarPesquisa(
                "inputPesquisaPromocao",
                ".product-card",
                "mensagemNaoEncontradoPromocao",
                "conteudo-promocoes"
            );

            // Configurar pesquisa para aba "Frete Grátis"
            configurarPesquisa(
                "inputPesquisaParceiroFrete_gratis",
                ".parceiro-card",
                "mensagemParNaoEncontradoFrete_gratis",
                "conteudo-frete_gratis"
            );
            configurarPesquisa(
                "inputPesquisaFrete_gratis",
                ".product-card",
                "mensagemNaoEncontradoFrete_gratis",
                "conteudo-frete_gratis"
            );

            // Configurar pesquisa para aba "Novidades"
            configurarPesquisa(
                "inputPesquisaParceiroNovidades",
                ".parceiro-card",
                "mensagemParNaoEncontradoNovidades",
                "conteudo-novidades"
            );
            configurarPesquisa(
                "inputPesquisaNovidades",
                ".product-card",
                "mensagemNaoEncontradoNovidades",
                "conteudo-novidades"
            );
        });
    </script>
</body>
<!-- Footer -->
<footer>
    <p>&copy; 2024 <?php echo htmlspecialchars($dadosEscolhido['nomeFantasia']); ?> - Todos os direitos reservados</p>
    <div class="contato">
        <p><strong>Contato:</strong></p>
        <p>Email: <?php echo htmlspecialchars($dadosEscolhido['email_suporte']); ?> | WhatsApp:
            <?php echo htmlspecialchars($dadosEscolhido['telefoneComercial']); ?>.</p>
    </div>
</footer>

</html>