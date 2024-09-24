<?php
    include('../../conexao.php');
    //echo '1';
    //die();
    if(!isset($_SESSION)){
        session_start(); 
        //echo '2';
        //die();
        if($_SERVER["REQUEST_METHOD"] === "POST") {  
            //echo '3';
            if (isset($_POST["tipoLogin"])) {
                //echo '4';
                if(isset($_SESSION['usuario'])){ 
                    //echo '5';
                    // Obter o valor do input radio
                    $usuario = $_SESSION['usuario'];
                    $valorSelecionado = $_POST["tipoLogin"];
                    $admin = $valorSelecionado;

                    if($admin == 0){
                        //echo '6';
                        $_SESSION['usuario'];
                        header("Location: ../usuarios/usuario_home.php");       
                    }else if($admin == 1){
                        //echo '7';
                        $usuario = $_SESSION['usuario'];
                        $admin = $_SESSION['admin'];
                        $_SESSION['usuario'];
                        $_SESSION['admin'];  
                        
                        $id = $_SESSION['usuario'];
                        $sql_query = $mysqli->query("SELECT * FROM socios WHERE id = '$id'") or die($mysqli->$error);
                        $usuario = $sql_query->fetch_assoc();    
                    }else{
                        //echo '8';
                        session_unset();
                        session_destroy();
                        header("Location: ../../../../index.php"); 
                    }
                }else{

                    session_unset();
                    session_destroy();
                    header("Location: ../../../../index.php"); 
                }    
            }else{

                session_unset();
                session_destroy();
                header("Location: ../../../../index.php"); 
            }  
        }else if(isset($_SESSION['usuario'])){    
            //echo '3';
            //die();
            $usuario = $_SESSION['usuario'];
            $admin = $_SESSION['admin'];
            $_SESSION['usuario'];
            $_SESSION['admin'];  
    
            $id = $_SESSION['usuario'];
            $sql_query = $mysqli->query("SELECT * FROM socios WHERE id = '$id'") or die($mysqli->$error);
            $usuario = $sql_query->fetch_assoc();    
    
        }else{

            session_unset();
            session_destroy();
            header("Location: ../../../../index.php"); 
        }
    }else if(isset($_SESSION['usuario'])){    
        //echo '3';
        //die();
        $usuario = $_SESSION['usuario'];
        $admin = $_SESSION['admin'];
        $_SESSION['usuario'];
        $_SESSION['admin'];  

        $id = $_SESSION['usuario'];
        $sql_query = $mysqli->query("SELECT * FROM socios WHERE id = '$id'") or die($mysqli->$error);
        $usuario = $sql_query->fetch_assoc();    

    }else{
        //echo '4';
        //die();
        if($_SERVER["REQUEST_METHOD"] === "POST") {  

            if (isset($_POST["tipoLogin"])) {
                // Obter o valor do input radio
                $usuario = $_SESSION['usuario'];
                $valorSelecionado = $_POST["tipoLogin"];
                $admin = $valorSelecionado;

                if($admin == 0){

                    $_SESSION['usuario'];
                    header("Location: ../usuario_home.php");       
                }else if($admin == 1){
                    $usuario = $_SESSION['usuario'];
                    $admin = $_SESSION['admin'];
                    $_SESSION['usuario'];
                    $_SESSION['admin'];  

                    $id = $_SESSION['usuario'];
                    $sql_query = $mysqli->query("SELECT * FROM socios WHERE id = '$id'") or die($mysqli->$error);
                    $usuario = $sql_query->fetch_assoc();    

                }else{

                    session_unset();
                    session_destroy();
                    header("Location: ../../../../index.php"); 
                }
            }  
        }else{

            session_unset();
            session_destroy();
            header("Location: ../../../../index.php"); 
        }
    }

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="admin_home.css">
    <script>
        //atualiza a pagian a cada 10 min
        setTimeout(function() {
            location.reload();
        }, 1000000);
        
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
            abrirNaDiv('paginas_div/inicio.php');
        }
    </script>
    <title>Tela Admin</title>
</head>
<body>
    <div id="idivMenu">
        <div id="imenuBtn" onclick="toggleMenu()">
            <div class="iconeMenu"></div>
            <div class="iconeMenu"></div>
            <div class="iconeMenu"></div>
        </div> 
        <div id="iusuario"> 
            <a> Olá, <?php echo $usuario['apelido']; ?></a> 
        </div> 
    </div>
    <div class="titulo">
        <div class="menu" id="imenu">
            <ul id="ilista" class="lista">
                <li><a href="#" onclick="abrirNaDiv('paginas_div/inicio.php');toggleMenu()">Inicío</a></li>
                <li><a href="#" onclick="abrirNaDiv('paginas_div/admin_config.php');toggleMenu()">Configurações</a></li> 
                <li><a href="#" onclick="abrirNaDiv('paginas_div/integrarSocio.php');toggleMenu()">Integrar de Sócios</a></li>  
                <li><a href="#" onclick="abrirNaDiv('paginas_div/incluir_joia.php');toggleMenu()">Incluir Jóia</a></li> 
                <li><a href="#" onclick="abrirNaDiv('paginas_div/joia_para_receber.php');toggleMenu()">Jóia á Receber</a></li>
                <li><a href="#" onclick="abrirNaDiv('paginas_div/listaSocios.php');toggleMenu()">Lista de Sócios</a></li>              
                <li><a href="#" onclick="abrirNaDiv('paginas_div/GerarMensalidades.php');toggleMenu()">Gerar Mensalidades</a></li>
                <li><a href="#" onclick="abrirNaDiv('paginas_div/CarregarMensalidades.php');toggleMenu()">Carregar Mensalidades</a></li>
                <li><a href="admin_logout.php">Sair</a></li>
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
    <script src="admin_home.js"></script>
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
