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
        /* Estilos gerais */
body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
    background-color: #f8f8f8;
}

/* Header e Navegação */
header {
    background-color: #333;
    color: white;
    padding: 20px 0;
}
.logo {
    font-size: 24px;
    font-weight: bold;
}

.logo-img {
    width: 150px; /* Tamanho da imagem */
    height: auto;
    margin-right: 15px; /* Espaço entre a logo e o texto */
    border-radius: 50%;
}

.container {
    width: 95%;
    margin: 0 auto;
}

/* Estilos da área de usuário */
.user-area {
    display: flex;
    align-items: center;
}

.user-area i {
    margin-left: 15px;
    cursor: pointer;
}

.profile-dropdown {
    position: relative;
    display: inline-block;
}

.dropdown-menu {
    display: none;
    position: absolute;
    right: 0;
    background-color: #fff;
    box-shadow: 0 8px 16px rgba(0,0,0,0.1);
    z-index: 1;
    list-style-type: none;
    padding: 0;
    margin: 0;
    border-radius: 5px;
    width: 200px;
}

.dropdown-menu li {
    padding: 10px;
}

.dropdown-menu li a {
    display: flex;
    align-items: center;
    text-decoration: none;
    color: #333;
}

.dropdown-menu li a i {
    margin-right: 10px;
}

.dropdown-menu li:hover {
    background-color: #f1f1f1;
}

/* Classe para mostrar o menu suspenso */
.show {
    display: block;
}

.user-area {
    float: right;
    margin-left: 20px;
    color: white;
}

.user-area i {
    margin-left: 15px;
    cursor: pointer;
}

.user-area i {
    color: white; /* Cor padrão do ícone */
    transition: color 0.3s ease; /* Transição suave ao mudar a cor */
}

.user-area i:hover {
    color: #f8c407; /* Cor verde escuro ao passar o mouse */
}

.sub-nav {
    background-color: #444;
    padding: 10px 0;
    display: flex;
    justify-content: center; /* Centraliza */
    gap: 20px; /* Espaçamento entre os itens, ajusta como quiser */
}


.sub-nav div {
    padding: 10px 20px;
    cursor: pointer;
    font-weight: bold;
    color: white;
}

.sub-nav div:hover {
    background-color: #fa9508;
    border-radius: 10px;
}

.sub-nav div a {
    color: #006400; /* Verde escuro */
    text-decoration: none; /* Remove o sublinhado */
}

.sub-nav div a:hover {
    color: #004d00; /* Verde escuro mais intenso ao passar o mouse */
}
/* Cards de Produtos */
.products {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 20px;
    margin: 20px 0;
}

.product-card {
    background-color: white;
    padding: 15px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    border-radius: 10px;
    text-align: center;
}

.product-card img {
    width: 100%;
    height: auto;
    border-bottom: 1px solid #eee;
    margin-bottom: 10px;
}

.product-card h3 {
    margin: 10px 0;
    font-size: 18px;
}

.product-card p {
    color: green;
    font-weight: bold;
}

.add-cart {
    display: inline-block;
    background-color: #333;
    color: white;
    padding: 10px 20px;
    border-radius: 5px;
    text-decoration: none;
}

.add-cart:hover {
    background-color: #555;
}

/* Parceiros */
.parceiros {
    text-align: center;
    margin-top: 30px;
}

.parceiros img {
    width: 150px;
    height: auto;
    margin: 0 20px;
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

        .btn-login {
            background-color: #007bff;
            color: #fff;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }

        .btn-login:hover {
            background-color: #0056b3;
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

                <div class="profile-dropdown">
                    <i class="fas fa-user" id="profileIcon"></i>
                    <ul class="dropdown-menu" id="dropdownMenu">
                        <li><a href="configuracoes.html"><i class="fas fa-cog"></i> Configurações</a></li>
                        <li><a href="editar_perfil.html"><i class="fas fa-user-edit"></i> Editar Perfil</a></li>
                        <li><a href="cliente_logout.php"><i class="fas fa-sign-out-alt"></i> Sair</a></li>
                    </ul>
                </div>
            <?php else: ?>
                <span>Bem-vindo, Visitante</span>
                <a href="login.php" class="btn-login">Entrar</a>
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
