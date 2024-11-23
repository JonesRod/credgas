<?php
include('login/lib/conexao.php');

if(!isset($_SESSION)) {
    session_start();
}

// Verifica se o usuário está logado
$usuarioLogado = isset($_SESSION['id']);
//$id_conf = '1';


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

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minha Loja</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>

    <script src="cadastro_inicial/localizador.js" defer></script>
    <link rel="stylesheet" href="index.css">


    <style>

        
.parceiros-carousel .parceiro-card {
    text-align: center;
    padding: 10px;
    background: #f9f9f9;
    border: 1px solid #ddd;
    border-radius: 10px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    margin: 10px auto; /* Centraliza e ajusta margens vertical e horizontal */
    max-width: 200px; /* Define o comprimento máximo do cartão */
}


.parceiros-carousel .parceiro-card img {
    max-width: 120px; /* Ajuste o tamanho da logo */
    height: 120px;   /* Para mantê-la circular */
    margin: 0 auto 10px; /* Centraliza horizontalmente e adiciona espaço abaixo */
    border-radius: 50%; /* Torna a imagem redonda */
    display: block; /* Garante que o elemento seja tratado como bloco */
    border: 2px solid #ddd; /* Borda ao redor da imagem */
}

.parceiros-carousel .parceiro-card h3 {
    font-size: 1.2em;
    font-weight: bold;
    margin: 5px 0;
    color: #333; /* Cor do texto */
}

.parceiros-carousel .parceiro-card p {
    font-size: 0.9em;
    color: #666; /* Cor da categoria */
    margin: 5px 0 0;
}


/* Contêiner da seção de produtos */
.products {
    display: flex;
    flex-wrap: wrap;
    gap: 10px; /* Espaçamento entre os cartões */
    justify-content: center; /* Centraliza os produtos */
    margin: 10px 0;
}

/* Cartão do produto */
.product-card {
    background: #ffffff;
    border: 1px solid #ddd;
    border-radius: 10px;
    width: 150px; /* Largura do cartão */
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    text-align: center;
    padding: 10px;
}

/* Efeito ao passar o mouse */
.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 10px rgba(0, 0, 0, 0.15);
}

/* Imagem do produto */
.product-card img {
    max-width: 100%;
    height: auto;
    border-radius: 5px;
    margin-bottom: 15px;
}

/* Nome do produto */
.product-card h3 {
    font-size: 1.2em;
    color: #333;
    margin-bottom: 10px;
    font-weight: 600;
}

/* Descrição do produto */
.product-card p {
    font-size: 0.9em;
    color: #666;
    margin-bottom: 10px;
    line-height: 1.4;
}

/* Preço do produto */
.product-card p:last-child {
    font-size: 1em;
    color: #27ae60; /* Verde para o preço */
    font-weight: bold;
}

/* Botões */
.product-card .btn {
    display: inline-block;
    background: #27ae60; /* Cor do botão */
    color: #fff;
    text-decoration: none;
    padding: 10px 20px;
    border-radius: 5px;
    margin-top: 10px;
    transition: background-color 0.3s ease;
    font-size: 0.9em;
}

/* Efeito ao passar o mouse no botão */
.product-card .btn:hover {
    background:darkorange;
}

/* Footer */
footer {
    text-align: center;
    padding: 30px 0;
    background-color: #333;
    color: white;
    margin-top: 30px;
}

footer .contato {
    margin: 10px 0;
}

</style>

</head>
<body>

    <!-- Header -->
    <header>
        <div class="container">
            <div class="logo">
                <img src="<?php if(isset($logo)) echo $logo; ?>" alt="Logo" class="logo-img">
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
                <?php if ($usuarioLogado): ?>
                    <span>Bem-vindo, <strong><?php echo htmlspecialchars($_SESSION['nome_completo']); ?></strong></span>
                    <i class="fas fa-bell"></i>
                    <i class="fas fa-shopping-cart"></i>
                <?php else: ?>
                    <span>Seja bem-vindo!</span>
                    <a href="login/lib/login.php" class="btn-login">Entrar</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- Faixa de navegação -->
    <div class="sub-nav">
        <div>Catálogo</div>
        <div>Promoções</div>
        <div>Novidades</div>
        <div>Frete Grátis</div>
    </div>

    <?php

        // Consulta para buscar parceiros pelo CEP
        $sql_parceiros = "SELECT * FROM meus_parceiros WHERE status = 'ATIVO' && aberto_fechado = 'Aberto'";
        $result_parceiros = $mysqli->query($sql_parceiros) or die($mysqli->error);

        if ($result_parceiros->num_rows > 0) {
            while ($parceiro = $result_parceiros->fetch_assoc()) {
                $id_parceiro = $parceiro['id'];
                
                // Consulta para carregar produtos do parceiro
                $sql_produtos = "SELECT * FROM produtos WHERE id_parceiro = $id_parceiro AND oculto != 'sim' AND produto_aprovado = 'sim'";
                $result_produtos = $mysqli->query($sql_produtos) or die($mysqli->error);
            }
        } else {
            //echo "<p>Nenhum parceiro encontrado.</p>";
        }
        
    ?>
    <h2>Parceiros</h2>
    
    <!-- Carrossel de Parceiros -->
    <div class="parceiros-carousel owl-carousel">
        <div class="parc">
            <?php 
            // Consulta para buscar parceiros ativos e abertos
            $sql_parceiros = "SELECT * FROM meus_parceiros WHERE status = 'ATIVO' AND aberto_fechado = 'Aberto'";
            $result_parceiros = $mysqli->query($sql_parceiros) or die($mysqli->error);

            if ($result_parceiros->num_rows > 0): 
                while ($parceiro = $result_parceiros->fetch_assoc()): 
                    // Exibe cada parceiro no carrossel
                    $logoParceiro = !empty($parceiro['logo']) ? $parceiro['logo'] : 'placeholder.jpg'; 
            ?>
                <div class="parceiro-card">
                    <img src="login/lib/paginas/parceiros/arquivos/<?php echo htmlspecialchars($logoParceiro); ?>" 
                    alt="<?php echo htmlspecialchars($parceiro['nomeFantasia']); ?>">
                    <h3><?php echo htmlspecialchars($parceiro['nomeFantasia']); ?></h3>
                    <p><?php echo htmlspecialchars($parceiro['categoria']); ?></p>
                </div>
            <?php 
                endwhile; ?>
            <?php else: ?>
                <p>Nenhum parceiro ativo no momento.</p>
            <?php endif; 
            ?>
        </div>
    </div>


    <!-- Produtos -->
    <div class="container">
        <h2>Produtos</h2>
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
                        <h3><?php echo htmlspecialchars($produto['nome_produto']); ?></h3>
                        <p><?php echo htmlspecialchars($produto['descricao_produto']); ?></p>
                        <p>R$ <?php echo number_format($produto['valor_produto'], 2, ',', '.'); ?></p>
                        <a href="detalhes_produto.php?id=<?php echo $produto['id_produto']; ?>" class="btn">Detalhes</a>

                        <!-- Verifica se o usuário está logado para permitir a compra -->
                        <?php if (isset($usuarioLogado) && $usuarioLogado): ?>
                            <a href="#" class="btn">Comprar</a>
                        <?php else: ?>
                            <a href="login/lib/login.php" class="btn">Faça login para comprar</a>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>Não há produtos em destaque no momento.</p>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Script para mostrar/ocultar o menu suspenso ao clicar no ícone de perfil
        document.getElementById("profileIcon").addEventListener("click", function() {
            var dropdownMenu = document.getElementById("dropdownMenu");
            dropdownMenu.classList.toggle("show"); // Alterna a classe "show" para exibir ou ocultar o menu
        });

        // Fechar o menu suspenso ao clicar fora dele
        window.onclick = function(event) {
            if (!event.target.matches('#profileIcon')) {
                var dropdowns = document.getElementsByClassName("dropdown-menu");
                for (var i = 0; i < dropdowns.length; i++) {
                    var openDropdown = dropdowns[i];
                    if (openDropdown.classList.contains('show')) {
                        openDropdown.classList.remove('show');
                    }
                }
            }
        };
    </script>
    <script>
        // Função para simular o clique no botão ao carregar a página
        window.onload = function() {
            setTimeout(function() {
                var cep = document.getElementById('cep').value;
                if (cep) {
                    document.getElementById('buscarButton').click();
                }
            }, 5000); // 2000 milissegundos = 2 segundos
        };

        $(document).ready(function() {
            var totalParceiros = <?php echo $result_parceiros->num_rows; ?>; // Total de parceiros no banco

            $(".parceiros-carousel").owlCarousel({
                loop: totalParceiros > 1, // Loop apenas se houver mais de 1 parceiro
                margin: 10,
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

    </script>

    <!-- Footer -->
    <footer>
        <p>&copy; 2024 <?php echo htmlspecialchars($dadosEscolhido['nomeFantasia']); ?> - Todos os direitos reservados</p>
        <div class="contato">
            <p><strong>Contato:</strong></p>
            <p>Email: <?php echo htmlspecialchars($dadosEscolhido['email_suporte']); ?> | Telefone: <?php echo htmlspecialchars($dadosEscolhido['telefoneComercial']); ?></p>
        </div>
    </footer>

</body>
</html>

