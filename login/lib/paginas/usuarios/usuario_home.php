<?php
    include('../../conexao.php');

    if(!isset($_SESSION)){
        session_start(); 
    }

    if(isset($_SESSION['usuario'])){
        $usuario = $_SESSION['usuario'];
        $id = $_SESSION['usuario'];
        $sql_query = $mysqli->query("SELECT * FROM socios WHERE id = '$id'") or die($mysqli->$error);
        $usuario = $sql_query->fetch_assoc(); 

    } else {
        // Se não houver uma sessão de usuário, redirecione para a página de login
        session_unset();
        session_destroy(); 
        header("Location: ../../../../index.php");  
        exit(); // Importante adicionar exit() após o redirecionamento
    }

?>

<!DOCTYPE html>
<html lang="PT-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="usuario_home.css">
    <script>
        //atualiza a pagian a cada 10 min
        setTimeout(function() {
            location.reload();
        }, 100000);
        
        // Função para carregar o conteúdo na div
        function abrirNaDiv(pagina) {
            var xhttp = new XMLHttpRequest();
            xhttp.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    document.getElementById("iconteudo").innerHTML = this.responseText;
                }
            };
            xhttp.open("GET", pagina, true);
            xhttp.send();
        }

        // Carregar a página de início ao carregar a página
        window.onload = function() {
            abrirNaDiv('inicio.php');
        }
    </script>
    <title>Meu Site</title>
</head>
<body>

    <div id="idivMenu">
        <div id="imenuBtn" onclick="<?php if (!isset($_SESSION['usuario'])) { ?>
                location.reload();
            <?php } else { ?>
                toggleMenu();
            <?php } ?>">
            <div class="iconeMenu"></div>
            <div class="iconeMenu"></div>
            <div class="iconeMenu"></div>
        </div>

        <div id="iusuario">
            <a> Olá, <?php echo $usuario['apelido']; ?></a><br>
            <a> Status: <?php echo $usuario['status']; ?></a>
        </div>
    </div>

    <div class="titulo">
        <div class="menu" id="imenu">
            <ul id="ilista" class="lista">
                <li><a href="#" onclick="abrirNaDiv('inicio.php');toggleMenu()">Inicío </a></li>
                <li><a href="#" onclick="abrirNaDiv('perfil.php');toggleMenu()">Meu Perfil </a></li>
                <li><a href="#" onclick="abrirNaDiv('CarregarMensalidades.php');toggleMenu()">Minhas Mensalidades</a></li>
                <li><a href="#" onclick="abrirNaDiv('Carregar_joia.php');toggleMenu()">Jóia</a></li>
                <li><a href="usuario_logout.php">Sair</a></li>
            </ul>
        </div>
        <div id="ititulo">
            <H1>Associação 40Ribas</H1>
        </div>
    </div>
    <div class="container">
        <div class="conteudo" id="iconteudo">
            <!-- Conteúdo central (dados escolhidos) -->
        </div>
    </div>
    <script src="usuario_home.js"></script>
</body>
    <footer>
        <div class="container-rodape">
            <div class="row">
                <div class="col-md-6">
                    <h3>Links Úteis</h3>
                    <ul>
                        <li><a href="#">Sobre Nós</a></li>
                        <li><a href="#">Contato</a></li>
                        <li><a href="#">Política de Privacidade</a></li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h3>Redes Sociais</h3>
                    <ul>
                        <li><a href="#">Facebook</a></li>
                        <li><a href="#">Twitter</a></li>
                        <li><a href="#">Instagram</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="bottom-bar">
            <p>&copy; 2023 <?php echo 'Associação 40 Ribas';?>. Todos os direitos reservados.</p>
        </div>
    </footer>
</html>