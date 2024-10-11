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
            <!-- Ícone de notificações com contagem -->
            <div class="notificacoes">
                <i class="fas fa-bell" onclick="toggleNotificacoes()"></i>
                <span id="notificacao-count" class="notificacao-count">4</span> <!-- Exemplo de contagem -->
            </div>
            <i class="fas fa-bars" onclick="toggleMenu()"></i>
        </div>
    </header>

    <!-- Painel de notificações que aparece ao clicar no ícone de notificações -->
    <aside id="painel-notificacoes">
        <h2>Notificações</h2>
        <ul id="lista-notificacoes">
            <li onclick="abrirNotificacao(1)">Solicitação de cadastro de Parceiro</li>
            <li onclick="abrirNotificacao(2)">Solicitação de crediario</li>
            <li onclick="abrirNotificacao(3)">Novo Produto</li>            
            <li onclick="abrirNotificacao(4)">Nova mensagem recebida</li>
        </ul>
    </aside>


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
    <main id="main-content">
        <div class="opcoes">
            <div class="tab active" onclick="mostrarConteudo('dashboard',this)">
                <span>Dashboard</span>
            </div>
            <div class="tab" onclick="mostrarConteudo('gerenciamento',this)">
                <span>Gerenciamento</span>
            </div>
        </div>

        <!-- Conteúdos correspondentes às abas -->
        <div id="conteudo-dashboard" class="conteudo-aba" style="display: block;">
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
            <li><a href="admin_home.php"><i class="fas fa-home"></i></a></li>
            <li><i class="fas fa-user"></i></li>
            <li><i class="fas fa-users"></i></li>
            <li><i class="fas fa-cog"></i></li>
            <li><a href="admin_logout.php"><i class="fas fa-sign-out-alt"></i></a></li>
        </ul>
    </footer>
    <script src="admin_home.js"></script> 
    <script>
        // Obtém o ID da sessão do PHP
        var sessionId = <?php echo $id; ?>;

        function abrirNotificacao(id) {
            // Verifique se o ID da notificação e o ID da sessão estão definidos corretamente
            //console.log("ID da Notificação:", id);
            //console.log("ID da Sessão:", sessionId);
            
            // Redireciona para a página de detalhes com o ID da notificação e o ID da sessão
            var url = `detalhes_notificacao.php?id=${id}&session_id=${sessionId}`;
            //console.log("Redirecionando para:", url);
            
            // Verifica se a URL está correta antes de redirecionar
            window.location.href = url;
        }
    </script>

</body>
</html>
