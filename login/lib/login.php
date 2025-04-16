<?php
include("conexao.php");

if (!isset($_SESSION)) {
    session_start();
}

if (isset($_SESSION['cliente'])) {
    // Redireciona para a página do cliente
    header("Location: paginas/clientes/cliente_home.php");
    exit();
} elseif (isset($_SESSION['admin'])) {
    // Redireciona para a página do administrador
    header("Location: paginas/administrativo/admin_home.php");
    exit();
} elseif (isset($_SESSION['parceiro'])) {
    // Redireciona para a página do parceiro
    header("Location: paginas/parceiros/parceiro_home.php");
    exit();
}

$msg = false;

if (isset($_POST['usuario']) && isset($_POST['senha'])) {
    $usuario = $mysqli->escape_string($_POST['usuario']);
    $senha = $mysqli->escape_string($_POST['senha']);

    $verifica = "SELECT * FROM meus_clientes WHERE cpf = '$usuario' LIMIT 1";
    $sql_verifica = $mysqli->query($verifica) or die("Falha na execução do código SQL: " . $mysqli->error);
    $cliente = $sql_verifica->fetch_assoc();
    $quantidade = $sql_verifica->num_rows;

    if ($quantidade == 1) {
        if (password_verify($senha, $cliente['senha_login'])) {
            if ($cliente['admin'] == 1) {
                $_SESSION['id'] = $cliente['id'];
                $_SESSION['usuario'] = $cliente['primeiro_nome'];
                $_SESSION['admin'] = $cliente['admin'];

                unset($_POST);
                header("Location: tipo_login.php");
                exit();
            } else {
                $_SESSION['id'] = $cliente['id'];
                $_SESSION['cliente_info'] = $cliente;

                unset($_POST);
                header("Location: paginas/clientes/cliente_home.php");
                exit();
            }
        } else {
            $msg = "Usuário ou senha inválidos!";
        }
    } else {
        $verifica = "SELECT * FROM meus_parceiros WHERE cnpj = '$usuario' LIMIT 1";
        $sql_verifica = $mysqli->query($verifica) or die("Falha na execução do código SQL: " . $mysqli->error);
        $parceiro = $sql_verifica->fetch_assoc();
        $quantidade = $sql_verifica->num_rows;

        if ($quantidade == 1) {
            if (password_verify($senha, $parceiro['senha'])) {
                $_SESSION['id'] = $parceiro['id'];
                $_SESSION['usuario'] = $parceiro['nomeFantasia'];

                unset($_POST);
                header("Location: paginas/parceiros/parceiro_home.php");
                exit();
            } else {
                $msg = "Usuário ou senha inválidos!";
            }
        } else {
            $msg = "Usuário ou senha inválidos!";
        }
    }
}

$dados = $mysqli->query("SELECT * FROM config_admin WHERE razao != '' ORDER BY data_alteracao DESC LIMIT 1") or die($mysqli->error);
$dadosEscolhido = $dados->fetch_assoc();

if (isset($dadosEscolhido['logo'])) {
    $logo = $dadosEscolhido['logo'];
    $logo = $logo == '' ? 'paginas/arquivos_fixos/imagem_credgas.jpg' : 'paginas/administrativo/arquivos/' . $logo;
}
$mysqli->close();
?>
<!DOCTYPE html>
<html lang="PT-BR">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <link rel="stylesheet" href="login.css">
    <style>
        img {
            width: 200px;
            height: 200px;
            /* Certifique-se de que a altura seja igual à largura */
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
    <form id="login" action="" method="POST">
        <img src="<?php echo $logo; ?>" alt="">
        <h1 id="ititulo">Entrar</h1>
        <span id="msg"><?php echo $msg; ?></span>
        <p>
            <label for="iusuario">Usuário</label>
            <input required type="text" name="usuario" id="iusuario" placeholder="CPF, CNPJ ou E-mail"
                oninput="formatarCampo(this)" value="<?php if (isset($_POST['usuario']))
                    echo $_POST['usuario']; ?>">
        </p>
        <p>
        <div id="senhaInputContainer">
            <label for="senhaInput">Senha</label>
            <input required type="password" name="senha" id="senhaInput" placeholder="Sua Senha"
                value="<?php if (isset($_POST['senha']))
                    echo $_POST['senha']; ?>">
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
            let value = input.value.replace(/\D/g, '');

            if (/^[0-9]+$/.test(value)) {
                if (value.length > 12) {
                    value = value.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{1})/, '$1.$2.$3/$4-$5');
                } else if (value.length > 11) {
                    value = value.replace(/(\d{2})(\d{3})(\d{3})(\d{1})/, '$1.$2.$3/$4');
                } else if (value.length > 9) {
                    value = value.replace(/(\d{3})(\d{3})(\d{3})(\d{1})/, '$1.$2.$3-$4');
                } else if (value.length > 6) {
                    value = value.replace(/(\d{3})(\d{3})(\d{1})/, '$1.$2.$3');
                } else if (value.length > 3) {
                    value = value.replace(/(\d{3})(\d{1})/, '$1.$2');
                }
                input.value = value;
            }
        }
    </script>
</body>

</html>