<?php
    include('../../conexao.php');

    if(!isset($_SESSION)){
        session_start(); 
    }

    if(isset($_SESSION['id'])){
        $id = $_SESSION['id'];
        //$id = $_SESSION['usuario'];
        $sql_query = $mysqli->query(query: "SELECT * FROM meus_clientes WHERE id = '$id'") or die($mysqli->$error);
        $usuario = $sql_query->fetch_assoc(); 

    } else {
        // Se não houver uma sessão de usuário, redirecione para a página de login
        session_unset();
        session_destroy(); 
        header(header: "Location: ../../../../index.php");  
        exit(); // Importante adicionar exit() após o redirecionamento
    }
    $id_conf = '1';
    $dados = $mysqli->query(query: "SELECT * FROM config_admin WHERE id = '$id_conf'") or die($mysqli->error);
    $dadosEscolhido = $dados->fetch_assoc();

    //$logo = $dadosEscolhido['logo'];
    if(isset($dadosEscolhido['logo'])) {
        $logo = $dadosEscolhido['logo'];
        
        if($logo == ''){
            $logo = '../arquivos_fixos/imagem_credgas.jpg';
        }else{
            $logo = '../arquivos_fixos/'. $logo;
            //echo $logo;
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
    <link rel="stylesheet" href="cliente_home.css">

</head>
<body>

    <!-- Header -->
    <header>
        <div class="container">
            <div class="logo"></div>
            <div class="user-area">
                <span>Bem-vindo, <strong><?php echo $usuario['nome_completo']; ?></strong></span>
                <i class="fas fa-bell"></i> <!-- Ícone de Notificação -->
                <!--<i class="fas fa-cog"></i> <!-- Ícone de Configurações -->
                <i class="fas fa-shopping-cart"></i> <!-- Ícone de Carrinho -->

                <div class="profile-dropdown">
                    <i class="fas fa-user" id="profileIcon"></i> <!-- Ícone de Perfil -->
                    <ul class="dropdown-menu" id="dropdownMenu">
                        <li><a href="configuracoes.html"><i class="fas fa-cog"></i> Configurações</a></li>
                        <li><a href="editar_perfil.html"><i class="fas fa-user-edit"></i> Editar Perfil</a></li>
                        <li><a href="cliente_logout"><i class="fas fa-sign-out-alt"></i> Sair</a></li>
                    </ul>
                </div>
            </div>
            <img src="<?php if(isset($logo)) echo $logo; ?>" alt="" class="logo-img"> <!-- Adicionando a imagem da logo -->
        </div>
    </header>

    <!-- Faixa de navegação (Início, Promoções, etc.) -->
    <div class="sub-nav">
        <div>Início</div>
        <div>Promoções</div>
        <div>Novidades</div>
        <div>Frete Grátis</div>
    </div>

    <!-- Parceiros -->
    <div class="container">
        <h2>Lojas Parceiras</h2>
        <div class="parceiros">
            <img src="https://via.placeholder.com/150x50" alt="Parceiro 1">
            <img src="https://via.placeholder.com/150x50" alt="Parceiro 2">
            <img src="https://via.placeholder.com/150x50" alt="Parceiro 3">
        </div>

        <!-- Cards de Produtos -->
        <h2>Produtos em Destaque</h2>
        <div class="products">
            <div class="product-card">
                <img src="https://via.placeholder.com/150" alt="Produto 1">
                <h3>Produto 1</h3>
                <p>R$ 99,99</p>
                <a href="#" class="add-cart">Adicionar ao Carrinho</a>
            </div>
            <div class="product-card">
                <img src="https://via.placeholder.com/150" alt="Produto 2">
                <h3>Produto 2</h3>
                <p>R$ 199,99</p>
                <a href="#" class="add-cart">Adicionar ao Carrinho</a>
            </div>
            <div class="product-card">
                <img src="https://via.placeholder.com/150" alt="Produto 3">
                <h3>Produto 3</h3>
                <p>R$ 299,99</p>
                <a href="#" class="add-cart">Adicionar ao Carrinho</a>
            </div>
            <div class="product-card">
                <img src="https://via.placeholder.com/150" alt="Produto 4">
                <h3>Produto 4</h3>
                <p>R$ 399,99</p>
                <a href="#" class="add-cart">Adicionar ao Carrinho</a>
            </div>
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
        // Função para encerrar a sessão e fechar a página
         document.getElementById("logoutButton").addEventListener("click", function() {
            // Redireciona para a página de logout no servidor
            window.location.href = "cliente_logout.php"; // Logout no PHP ou outra ação de logout
        });
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
