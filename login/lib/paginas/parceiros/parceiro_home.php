<?php
    include('../../conexao.php');

    // Inicia a sessão
    if (!isset($_SESSION)) {
        session_start(); 
    }

    // Verifica se o usuário está logado
    if (isset($_SESSION['id'])) {
        $id = $_SESSION['id'];

        // Consulta para buscar o parceiro
        $sql_query = $mysqli->query("SELECT * FROM meus_parceiros WHERE id = '$id'") or die($mysqli->$error);
        $parceiro = $sql_query->fetch_assoc();

        // Verifica e ajusta a logo
        if(isset($parceiro['logo'])) {
            $logo = $parceiro['logo'];
            if($logo === '0'){
                $logo = '../arquivos_fixos/icone_loja.jpg';
            }else{
                $logo = '../arquivos_fixos/'. $logo;
            }
        }
    } else {
        session_unset();
        session_destroy(); 
        header("Location: ../../../../index.php");
        exit();
    }

    // Consulta para buscar produtos do catálogo
    $produtos_catalogo = $mysqli->query("SELECT * FROM produtos WHERE id_loja = '$id'") or die($mysqli->error);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $parceiro['nomeFantasia']; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="parceiro_home.css">

</head>
<body>

    <!-- Header -->
    <header>
        <div class="container">
            <img src="<?php echo $logo; ?>" alt="Logo da Loja" class="logo-img">
            <div class="logo-text">
                <h1><?php echo $parceiro['nomeFantasia']; ?></h1>
            </div>
            <div class="user-area">
                <!-- Ícone de notificações -->
                <i class="fas fa-bell"></i>

                <!-- Ícone da loja -->
                <div class="store-dropdown">
                    <i class="fas fa-store" id="storeIcon"></i>
                    <ul class="dropdown-menu" id="storeDropdown">
                        <li><a href="#perfil"><i class="fas fa-user"></i> Perfil da Loja</a></li>
                        <li><a href="configuracoes.php"><i class="fas fa-cog"></i> Configurações</a></li>
                        <li><a href="parceiro_logout.php"><i class="fas fa-sign-out-alt"></i> Sair</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </header>

    <!-- Navegação -->
    <div class="sub-nav">
        <div><a href="#catalogo">Catálogo de Produtos</a></div>
        <div><a href="#promocoes">Minhas Promoções</a></div>
        <div><a href="#mais_vendidos">Mais Vendidos</a></div>
        <div><a href="#frete_gratis">Frete Grátis</a></div>
    </div>

    <!-- Footer -->
    <footer>
        <p>&copy; 2024 <?php echo $parceiro['nomeFantasia']; ?> - Todos os direitos reservados</p>
        <div class="contato">
            <p><strong>Contato:</strong></p>
            <p>Email: <?php echo $parceiro['email']; ?> | Telefone: <?php echo $parceiro['telefoneComercial']; ?></p>
        </div>
    </footer>

    <script>
        // Script para rolagem suave ao clicar nos links do menu
        document.querySelectorAll('.sub-nav a').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();

                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });

        // Script para mostrar/ocultar o menu suspenso ao clicar no ícone da loja
        document.getElementById("storeIcon").addEventListener("click", function() {
            var dropdownMenu = document.getElementById("storeDropdown");
            dropdownMenu.classList.toggle("show"); // Alterna a classe "show"
        });

        // Fechar o menu suspenso ao clicar fora dele
        window.onclick = function(event) {
            if (!event.target.matches('#storeIcon')) {
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
</body>
</html>
