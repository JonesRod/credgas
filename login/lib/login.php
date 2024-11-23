<?php
    include("conexao.php");

    if(!isset($_SESSION)) {
        session_start(); 
    }
    
    if (isset($_SESSION['cliente'])) {
        // Redireciona para a página do cliente
        header(header: "Location: paginas/clientes/cliente_home.php");
        exit();
    } elseif (isset($_SESSION['admin'])) {
        // Redireciona para a página do administrador
        header(header: "Location: paginas/administrativo/admin_home.php");
        exit();
    } elseif (isset($_SESSION['parceiro'])) {
        // Redireciona para a página do parceiro
        header(header: "Location: paginas/parceiros/parceiro_home.php");
        exit();
    }
    
        
    $msg= false;


    if(isset($_POST['usuario']) || isset($_POST['senha'])) {

        $sql_primeiro_registro = "SELECT * FROM meus_clientes";
        $registros = $mysqli->query($sql_primeiro_registro) or die("Falha na execução do código SQL: " . $mysqli->error);

        $usuario = $mysqli->escape_string($_POST['usuario']);//$mysqli->escape_string SERVE PARA PROTEGER O ACESSO 
        $senha = $mysqli->escape_string($_POST['senha']);

        $verifica = "SELECT * FROM meus_clientes WHERE cpf = '$usuario' LIMIT 1";
        $sql_verifica =$mysqli->query(query: $verifica) or die("Falha na execução do código SQL: " . $mysqli->error);
        $cliente = $sql_verifica->fetch_assoc();
        $quantidade = $sql_verifica->num_rows;//retorna a quantidade encontrado     
        //var_dump($quantidade);    
        //die();             
        if(($quantidade ) == 1) {
        //var_dump($_POST['usuario']);       
        //var_dump($_POST['senha']); 
            if (password_verify(password: $senha, hash: $cliente['senha_login'])) {
                // Verifica se o usuário é administrador

                if ($cliente['admin'] == 1) {
                    $_SESSION['id'] = $cliente['id'];
                    $_SESSION['usuario'] = $cliente['primeiro_nome'];
                    $_SESSION['admin'] = $cliente['admin'];

                    // Redireciona para a página de tipo de login
                    unset($_POST);
                    header(header: "Location: tipo_login.php");
                    exit();
                } else {
                    var_dump( $cliente['id']);

                    //die();
                    // Caso o usuário não seja admin, é cliente
                    $_SESSION['id'] = $cliente['id'];
                    $_SESSION['cliente_info'] = $cliente; // Armazena mais informações do cliente, se necessário
                    
                    unset($_POST);
                    header(header: "Location: paginas/clientes/cliente_home.php");
                    exit();
                }
            } else {
                // Mensagem de erro caso a senha ou usuário esteja incorreto
                $msg = "Usuário ou senha inválidos!";
            }
        
        }else{

            $verifica = "SELECT * FROM meus_parceiros WHERE cnpj = '$usuario' LIMIT 1";
            $sql_verifica =$mysqli->query(query: $verifica) or die("Falha na execução do código SQL: " . $mysqli->error);
            $parceiro = $sql_verifica->fetch_assoc();
            $quantidade = $sql_verifica->num_rows;//retorna a quantidade encontrado

            if(($quantidade ) == 1) {
                        //echo "parceiro 1";
                if (password_verify(password: $senha, hash: $parceiro['senha'])) {

                    $_SESSION['id'] = $parceiro['id'];
                    $_SESSION['usuario'] = $cliente['nomeFantasia'];
                    
                    unset($_POST);
                    header(header: "Location: paginas/parceiros/parceiro_home.php");
                    exit();
                    
                } else {
                    // Mensagem de erro caso a senha ou usuário esteja incorreto
                    $msg = "Usuário ou senha inválidos!";
                }
            }else{
                // Mensagem de erro caso a senha ou usuário esteja incorreto
                $msg = "Usuário ou senha inválidos!";
            }

        }
    }
    $id = '1';
    $dados = $mysqli->query("SELECT * FROM config_admin WHERE razao != '' ORDER BY data_alteracao DESC LIMIT 1") or die($mysqli->error);
                    
    $dadosEscolhido = $dados->fetch_assoc();


    //$dados = $mysqli->query(query: "SELECT * FROM config_admin WHERE id = '$id'") or die($mysqli->error);
    //$dadosEscolhido = $dados->fetch_assoc();

    //$logo = $dadosEscolhido['logo'];
    if(isset($dadosEscolhido['logo'])) {
        $logo = $dadosEscolhido['logo'];
        
        if($logo == ''){
            $logo = 'paginas/arquivos_fixos/imagem_credgas.jpg';
        }else{
            $logo = 'paginas/administrativo/arquivos/'. $logo;
        }
    }
    $mysqli->close();
?>
<!DOCTYPE html>
<html lang="PT-BR">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />    <link rel="stylesheet" href="entrar/lib/css/index.css">
    <link rel="stylesheet" href="login.css">
    <style>
        img{
            width: 200px;
            height: 200px; /* Certifique-se de que a altura seja igual à largura */
            border-radius: 50%;
        }
    </style>
    <title>Entrar</title>
    <script>
        function toggleSenha() {
            var senhaInput = document.getElementById('senhaInput');
            var toggleSenha = document.getElementById('toggleSenha');

            if (senhaInput.type === 'password') {
                senhaInput.type = 'text';
                toggleSenha.textContent = 'visibility';
            } else {
                senhaInput.type = 'password';
                toggleSenha.textContent = 'visibility_off';
            }
        }
    </script>
</head>
<body>  
    <form id ="login" action="" method="POST" >
        <img src="<?php echo $logo; ?>" alt="">
        <h1 id="ititulo">Entrar</h1>
        <span id="msg"><?php echo $msg; ?></span>
        <p>
            <label id="usuario" for="iusuario">Usuário</label>
            <input required type="text" name="usuario" id="iusuario" placeholder="CPF, CNPJ ou E-mail" oninput="formatarCampo(this)" value="<?php if(isset($_POST['usuario'])) echo $_POST['usuario']; ?>">
        </p>
        <p>
            <div id="senhaInputContainer">
                <label id="isenha" for="senha">Senha</label>
                <input required type="password" name="senha" id="senhaInput" placeholder="Sua Senha" value="<?php if(isset($_POST['senha'])) echo $_POST['senha']; ?>">
                <span id="toggleSenha" class="material-symbols-outlined" onclick="toggleSenha()">visibility_off</span>
            </div>
        </p>
        <p> 
            <a style="margin-right:10px;" href="../../cadastro_inicial/cadastro_inicial.html">Cadastre-se.</a> 
            <a style="margin-right:10px;" href="Recupera_Senha.php">Esqueci minha Senha!</a> 
        </p>
        <a style="margin-right:10px;" href="../../index.php">Agora não!</a> 
        <button type="submit">Entrar</button>

    </form>
    <script>
        function formatarCampo(input) {
            let value = input.value.replace(/\D/g, ''); // Remove caracteres não numéricos

            if (/^[0-9]+$/.test(value)) {
                if (value.length > 12) {
                    value = value.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{1})/, '$1.$2.$3/$4-$5');
                } else if (value.length > 11) {
                    value = value.replace(/(\d{2})(\d{3})(\d{3})(\d{1})/, '$1.$2.$3/$4');
                }else if (value.length > 9){
                    value = value.replace(/(\d{3})(\d{3})(\d{3})(\d{1})/, '$1.$2.$3-$4');
                } else if (value.length > 6) {
                    value = value.replace(/(\d{3})(\d{3})(\d{1})/, '$1.$2.$3');
                } else if (value.length > 3) {
                    value = value.replace(/(\d{3})(\d{1})/, '$1.$2');
                }
                input.value = value; 
            }   

            if (value.includes('@')) {
                // Se o valor contiver '@', formatar como E-mail
                let usuarioElement = document.getElementById('iusuario');
                usuarioElement.textContent = value;
            }
        }
    </script>
</body>
</html>