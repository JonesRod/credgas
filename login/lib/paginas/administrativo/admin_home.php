<?php
    include('../../conexao.php');
    //echo '1';
    //die();
    if(!isset($_SESSION)){
        session_start(); 
        //echo '2';
        //die();
    }
        if($_SERVER["REQUEST_METHOD"] === "POST") {  
            //echo '3';
            if (isset($_POST["tipoLogin"])) {
                //echo '4';
                if(isset($_SESSION['id'])){ 
                    //echo '5';
                    // Obter o valor do input radio
                    $usuario = $_SESSION['id'];
                    $valorSelecionado = $_POST["tipoLogin"];
                    $admin = $valorSelecionado;

                    if($admin == 0){
                        //echo '6';
                        
                        header(header: "Location: ../clientes/cliente_home.php"); 

                    }else if($admin == 1){
                        //echo '7';
                        //echo($_SESSION['id']);
                        //var_dump(value: $_POST["tipoLogin"]);
                        $usuario = $_SESSION['id'];
                        $admin = $_SESSION['admin'];
                        $_SESSION['id'];
                        $_SESSION['admin'];  
                        
                        $id = $_SESSION['id'];
                        $sql_query = $mysqli->query(query: "SELECT * FROM config_admin WHERE id_cliente = '$id'") or die($mysqli->$error);
                        $usuario = $sql_query->fetch_assoc(); 

                        //$logo = $dadosEscolhido['logo'];
                        if(isset($usuario['logo'])) {
                            $logo = $usuario['logo'];
                            
                            if($logo == ''){
                                $logo = '../arquivos_fixos/imagem_credgas.jpg';
                            }else{
                                $logo = '../arquivos_fixos/'. $logo;
                                //echo $logo;
                            }
                        }

                    }else{
                        //echo '8';
                        session_unset();
                        session_destroy();
                        header(header: "Location: ../../../../index.php"); 
                    }
                }else{

                    session_unset();
                    session_destroy();
                    header(header: "Location: ../../../../index.php"); 
                }    
            }else{

                session_unset();
                session_destroy();
                header(header: "Location: ../../../../index.php"); 
            }  
        }else if(isset($_SESSION['id'])){    
            //echo '3';
            //die();
            $usuario = $_SESSION['id'];
            $admin = $_SESSION['admin'];
            $_SESSION['id'];
            $_SESSION['admin'];  
    
            $id = $_SESSION['id'];
            $sql_query = $mysqli->query(query: "SELECT * FROM config_admin WHERE id_cliente = '$id'") or die($mysqli->$error);
            $usuario = $sql_query->fetch_assoc(); 
            
            //$logo = $dadosEscolhido['logo'];
            if(isset($usuario['logo'])) {
                $logo = $usuario['logo'];
                
                if($logo == ''){
                    $logo = '../arquivos_fixos/imagem_credgas.jpg';
                }else{
                    $logo = '../arquivos_fixos/'. $logo;
                    //echo $logo;
                }
            }
    
        }else{

            session_unset();
            session_destroy();
            header(header: "Location: ../../../../index.php"); 
        }

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Administrativo</title>
    <link rel="stylesheet" href="admin_home.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {

            background-color: #007BFF;

        }
        /* Estilos para o contêiner principal */
        main {
            display: flex;
            flex-direction: column;
            height: 100vh; /* O contêiner principal ocupa a altura total da tela */
            box-sizing: border-box;
        }
        /* Estilos para as abas */
        main .opcoes {
            background-color:#007BFF;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin-top: 0px;
            padding: auto;
        }

        main .tab {
            padding: 10px 20px;
            border-radius: 8px 8px 0 0; /* Bordas arredondadas só no topo, estilo de aba */
            background-color: #f0f0f0;
            cursor: pointer;
            font-size: 18px;
            font-weight: bold;
            text-align: center;
            transition: background-color 0.3s ease, transform 0.3s ease;
        }

        main .tab:hover {
            background-color: #afa791;
            color: white;
            transform: scale(1.05);
        }

        main .tab.active {
            background-color: #ffb300; /* Aba ativa com cor diferente */
            color: white;
            transform: scale(1.05);
        }

        /* Estilos para o conteúdo das abas */
        .conteudo-aba {
            flex-grow: 1; /* Faz o conteúdo ocupar todo o espaço restante */
            margin-left: 2px;
            margin-right: 2px;
            margin-top: 0px;
            padding: 10px;
            border: 2px solid #ffb300;
            border-radius: 8px;
            display: none; /* Por padrão, todos os conteúdos estão escondidos */
            padding-top: 5px;
            box-sizing: border-box; /* Garante que o padding seja incluído no tamanho */
            overflow: auto; /* Para que o conteúdo role se for maior que a tela */
            background-color: #d3d0ce;

        }
        /* Responsividade para telas pequenas */
        @media (max-width: 768px) {
            main .opcoes {
                /*flex-direction: column;*/
                gap: 10px;

            }
            /* Diminui o tamanho das letras em telas menores */
            main .tab span {
                font-size: 15px; /* Ajuste conforme o necessário */
            }

            main .tab {
                width: 30%;
                max-width: 200px;
            }
        }
    </style>
</head>
<body>

    <!-- Cabeçalho com logo e notificações -->
    <header>
        <div class="logo">
            <img src="<?php echo $logo; ?>" alt="Logo da Loja" class="logo-img">
        </div>
        <h1>Painel Administrativo</h1>
        <div class="menu-superior-direito">
            <span>Olá, <strong><?php echo $usuario['primeiro_nome']; ?></strong></span>
            <i class="fas fa-bell"></i>
            <i class="fas fa-bars" onclick="toggleMenu()"></i>
        </div>
    </header>

    <!-- Menu lateral que aparece abaixo do ícone de menu -->
    <aside id="menu-lateral">
        <ul>
            <li><i class="fas fa-home"></i><span>Inicio</span></li>
            <li><i class="fas fa-user"></i> <span>Perfil da Loja</span></li>
            <li><i class="fas fa-users"></i><span>Solicitações</span></li>
            <li><i class="fas fa-cog"></i> <span>Configurações</span></li>
            <li><a href="admin_logout.php"><i class="fas fa-sign-out-alt"></i> <span>Sair</span></a></li>
        </ul>
    </aside>


    <!-- Conteúdo principal -->
    <main>
        <div class="opcoes">
            <div class="tab" onclick="mostrarConteudo('dashboard')">
                <span>Dashboard</span>
            </div>
            <div class="tab" onclick="mostrarConteudo('gerenciamento')">
                <span>Gerenciamento</span>
            </div>
        </div>

        <!-- Conteúdos correspondentes às abas -->
        <div id="conteudo-dashboard" class="conteudo-aba">
            <h2>Dashboard</h2>
            <p>Conteúdo do Dashboard aparece aqui.</p>
        </div>

        <div id="conteudo-gerenciamento" class="conteudo-aba" style="display:none;">
            <h2>Gerenciamento</h2>
            <p>Conteúdo do Gerenciamento aparece aqui.</p>
        </div>
    </main>



    <footer class="menu-mobile">
        <ul>
            <li><i class="fas fa-home"></i></li>
            <li><i class="fas fa-user"></i></li>
            <li><i class="fas fa-users"></i></li>
            <li><i class="fas fa-cog"></i></li>
            <li><i class="fas fa-sign-out-alt"></i></li>
        </ul>
    </footer>

    <script src="admin_home.js"></script>
</body>
</html>
