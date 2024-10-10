<?php
    include('../../conexao.php');

    if (!isset($_SESSION)) {
        session_start();
    }

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        // Verifica se o tipo de login foi enviado
        if (isset($_POST["tipoLogin"]) && isset($_SESSION['id'])) {
            $id = $_SESSION['id'];
            $usuario = $_SESSION['usuario'];
            $valorSelecionado = $_POST["tipoLogin"];
            $admin = $valorSelecionado;

            // Se for usuário normal
            if ($admin == 0) {
                header(header: "Location: ../clientes/cliente_home.php");
                exit();
            } 
            // Se for administrador
            else if ($admin == 1) {
                $sql_query = $mysqli->query(query: "SELECT * FROM administradores WHERE id_cliente = '$id'") or die($mysqli->error);
                if ($sql_query->num_rows > 0) {
                    $usuario = $sql_query->fetch_assoc();
                    $_SESSION['usuario'] = $usuario;

                    // Verifica se o administrador tem configuração
                    $sql_config = $mysqli->query(query: "SELECT * FROM config_admin WHERE id_cliente = '$id'") or die($mysqli->error);
                    $config_admin = $sql_config->fetch_assoc();

                    //$logo = $config_admin['logo'];
                    if(isset($config_admin['logo'])) {
                        $logo = $config_admin['logo'];
                        if($logo == ''){
                            $logo = '../arquivos_fixos/imagem_credgas.jpg';
                        }else{
                            $logo = '../arquivos_fixos/'. $logo;
                        }
                    }

                    header(header: "Location: ../admin_home.php");
                    exit();
                } else {
                    session_unset();
                    session_destroy();
                    header(header: "Location: ../../../../index.php");
                    exit();
                }
            }
        } else {
            session_unset();
            session_destroy();
            header(header: "Location: ../../../../index.php");
            exit();
        }

    // Se não for um POST, verifica se a sessão existe
    }/*else if (isset($_SESSION['id'])) { 

        $id = $_SESSION['id'];

        // Consulta para buscar o usuário
        $sql_query = $mysqli->query(query: "SELECT * FROM meus_clientes WHERE id = '$id'") or die($mysqli->error);
        $usuario = $sql_query->fetch_assoc();
        $_SESSION['usuario'] = $usuario;

        // Consulta para buscar a configuração do admin
        $sql_config = $mysqli->query(query: "SELECT * FROM config_admin WHERE id_cliente = '$id'") or die($mysqli->error);
        $admin_conf = $sql_config->fetch_assoc();

        // Configura a logo
        $dados = $mysqli->query(query: "SELECT * FROM config_admin WHERE id_cliente = '$id'") or die($mysqli->error);
        $config_admin = $dados->fetch_assoc();
    
        //$logo = $config_admin['logo'];
        if(isset($config_admin['logo'])) {
            $logo = $config_admin['logo'];
            if($logo == ''){
                $logo = '../arquivos_fixos/imagem_credgas.jpg';
            }else{
                $logo = '../arquivos_fixos/'. $logo;
            }
        }
    } */
    // Caso nenhuma sessão exista, redireciona para o login
    else {
        session_unset();
        session_destroy();
        header(header: "Location: ../../../../index.php");
        exit();
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
            <i class="fas fa-bell"></i>
            <i class="fas fa-bars" onclick="toggleMenu()"></i>
        </div>
    </header>

    <!-- Barra lateral/menu -->
    <aside id="menu-lateral">
        <ul>
            <li><i class="fas fa-home"></i> Dashboard</li>
            <li><i class="fas fa-users"></i> Solicitações de Cadastro</li>
            <li><i class="fas fa-cog"></i> Configurações</li>
            <li><a href="admin_logout.php"><i class="fas fa-sign-out-alt"></i> Sair</a></li>
        </ul>
    </aside>

    <!-- Conteúdo principal -->
    <main>
        <div class="opcoes">
            <div class="dashboard">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </div>
            <div class="solicitacoes">
                <i class="fas fa-users"></i>
                <span>Solicitações de Cadastro</span>
            </div>
        </div>

        <div class="configuracoes-pagamento">
            <h2>Configurações de Métodos de Pagamento</h2>
            <p>Aqui você pode adicionar ou editar os métodos de pagamento aceitos na plataforma.</p>
            <button onclick="adicionarMetodoPagamento()">Adicionar Método de Pagamento</button>
        </div>

        <div class="lista-aprovacao">
            <h2>Solicitações de Cadastro para Aprovação</h2>
            <table>
                <thead>
                    <tr>
                        <th>Empresa</th>
                        <th>CNPJ</th>
                        <th>Email</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody id="lista-solicitacoes">
                    <!-- Conteúdo gerado dinamicamente -->
                </tbody>
            </table>
        </div>

    </main>

    <footer class="menu-mobile">
        <ul>
            <li><i class="fas fa-home"></i> Dashboard</li>
            <li><i class="fas fa-users"></i> Solicitações</li>
            <li><i class="fas fa-cog"></i> Configurações</li>
            <li><i class="fas fa-sign-out-alt"></i> Sair</li>
        </ul>
    </footer>

    <script src="admin_home.js"></script>
</body>
</html>
