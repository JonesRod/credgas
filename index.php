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

// Carregar produtos (Exemplo)
$sql_produtos = "SELECT * FROM produtos";  // Query para buscar produtos
$result_produtos = $mysqli->query($sql_produtos) or die($mysqli->error);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minha Loja</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        header .container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 20px;
            position: relative;
        }

        .logo {
            display: flex;
            align-items: center;
        }

        .logo-img {
            width: 200px;
            height: 200px;
            border-radius: 50%;
            margin-right: 10px;
        }

        .nome-fantasia {
            font-size: 2.5rem; /* Tamanho maior */
            font-weight: bold;
            color: #333; /* Cor mais suave para o texto */
            text-align: left; /* Alinha à esquerda para ficar mais natural ao lado da logo */
            margin: 0;
            line-height: 1.2;
            display: flex;
            justify-content: flex-start; /* Garante que o texto fique alinhado à esquerda */
            align-items: center;
            flex-grow: 1; /* Permite que o nome ocupe o máximo de espaço disponível ao lado da logo */
            padding-left: 15px; /* Espaço entre a logo e o nome */
        }

        .user-area {
            position: absolute;
            top: 10px;
            right: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn-login {
            background-color: #007bff;
            color: white;
            text-decoration: none;
            padding: 5px 10px;
            border-radius: 5px;
        }

        .btn-login:hover {
            background-color: #0056b3;
        }

        .profile-dropdown {
            position: relative;
        }

        .profile-dropdown #dropdownMenu {
            display: none;
            position: absolute;
            top: 100%;
            right: 0;
            background: #fff;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 4px;
            list-style: none;
            padding: 10px;
        }

        .profile-dropdown:hover #dropdownMenu {
            display: block;
        }

        /* Faixa de Navegação */
.sub-nav {
    display: flex;
    justify-content: center;
    align-items: center;
    background-color: #f8f8f8; /* Cor de fundo suave */
    padding: 10px 0;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); /* Sombras sutis para destacar */
}

.sub-nav div {
    font-size: 1.2rem;
    font-weight: bold;
    color: #333; /* Cor do texto */
    margin: 0 20px; /* Espaçamento entre os itens */
    cursor: pointer;
    transition: all 0.3s ease; /* Suavização do efeito de hover */
}

.sub-nav div:hover {
    color: #007bff; /* Cor de destaque quando o item é hover */
    text-decoration: underline; /* Adiciona um sublinhado no hover */
}

@media (max-width: 768px) {
    .sub-nav {
        flex-direction: column; /* Coloca os itens em coluna em telas menores */
        align-items: flex-start; /* Alinha os itens à esquerda */
        padding: 15px; /* Aumenta o padding em telas menores */
    }

    .sub-nav div {
        margin: 10px 0; /* Reduz o espaçamento entre os itens em telas menores */
        text-align: left; /* Alinha os itens à esquerda */
    }
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
                    <span>Bem-vindo, Visitante</span>
                    <a href="login/lib/login.php" class="btn-login">Entrar</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- Faixa de navegação -->
    <div class="sub-nav">
        <div>Início</div>
        <div>Promoções</div>
        <div>Novidades</div>
        <div>Frete Grátis</div>
    </div>

    <!-- Produtos -->
    <div class="container">
        <h2>Produtos em Destaque</h2>
        <div class="products">
            <?php if ($result_produtos->num_rows > 0): ?>
                <?php while ($produto = $result_produtos->fetch_assoc()): ?>
                    <div class="product-card">
                        <img src="https://via.placeholder.com/150" alt="<?php echo htmlspecialchars($produto['nome']); ?>">
                        <h3><?php echo htmlspecialchars($produto['nome']); ?></h3>
                        <p>R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></p>
                        <a href="detalhes_produto.php?id=<?php echo $produto['id']; ?>" class="btn">Detalhes</a>

                        <!-- Verifica se o usuário está logado para permitir a compra -->
                        <?php if ($usuarioLogado): ?>
                            <a href="#" class="btn">Comprar</a>
                        <?php else: ?>
                            <a href="login.php" class="btn">Faça login para comprar</a>
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

    <!-- Footer -->
    <footer>
        <p>&copy; 2024 Minha Loja - Todos os direitos reservados</p>
        <div class="contato">
            <p><strong>Contato:</strong></p>
            <p>Email: contato@minhaloja.com.br | Telefone: (11) 1234-5678</p>
        </div>
    </footer>

</body>
</html>

