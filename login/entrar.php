<?php
// Incluindo o arquivo de conexão
include("lib/conexao.php");

// Iniciando a sessão, caso ainda não tenha sido iniciada
if (!isset($_SESSION)) {
    session_start();
}

$msg = false;

if (isset($_POST['email']) || isset($_POST['senha'])) {
    // Captura do email e senha fornecidos pelo usuário
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    // Primeiro, verificamos se há algum registro na tabela 'socios'
    $sql_primeiro_registro = "SELECT * FROM socios";
    $registros = $mysqli->query(query: $sql_primeiro_registro) or die("Falha na execução do código SQL: " . $mysqli->error);

    if ($registros->num_rows == 0) {
        header(header: "Location: lib/cadastro_usuario.php");
        exit();
    }

    // Validação dos campos
    if (strlen(string: $_POST['email']) == 0) {
        $msg = "Preencha o campo Usuário.";
    } else if (strlen(string: $_POST['senha']) == 0) {
        $msg = "Preencha sua senha.";
    } else {
        // Consulta no banco de dados com base no email
        $sql_code = "SELECT * FROM socios WHERE email = '$email' LIMIT 1";
        $sql_query = $mysqli->query(query: $sql_code) or die("Falha na execução do código SQL: " . $mysqli->error);
        $usuario = $sql_query->fetch_assoc();
        $quantidade = $sql_query->num_rows;

        if ($quantidade == 1) {
            // Verificação da senha usando password_verify
            if (password_verify(password: $senha, hash: $usuario['senha'])) {
                $admin = $usuario['admin'];

                // Sessões são iniciadas com o ID do usuário e seu status de admin
                $_SESSION['usuario'] = $usuario['id'];
                $_SESSION['admin'] = $admin;

                // Redirecionamentos baseados no tipo de usuário
                if ($admin == 1) {
                    header(header: "Location: lib/tipo_login.php");
                    $_SESSION['usuario'] = $usuario['id'];
                    $_SESSION['admin'] = $admin;
                    die();
                } else {
                    header(header: "Location: lib/paginas/usuario_home.php");
                    $_SESSION['usuario'] = $usuario['id'];
                }
                exit();
            } else {
                $msg = "Usuário ou senha estão inválidos!";
            }
        } else {
            // Se o email não foi encontrado, verificar o CPF
            $sql_cpf = "SELECT * FROM socios WHERE cpf = '$email' LIMIT 1"; // Email também é CPF
            $sql_query = $mysqli->query(query: $sql_cpf) or die("Falha na execução do código SQL: " . $mysqli->error);
            $usuario = $sql_query->fetch_assoc();
            $quantidade_cpf = $sql_query->num_rows;

            if ($quantidade_cpf == 1) {
                if (password_verify(password: $senha, hash: $usuario['senha'])) {
                    $admin = $usuario['admin'];

                    $_SESSION['usuario'] = $usuario['id'];
                    $_SESSION['admin'] = $admin;

                    if ($admin == 1) {
                        header(header: "Location: lib/tipo_login.php");
                    } else {
                        header(header: "Location: lib/paginas/usuario_home.php");
                    }
                    exit();
                } else {
                    $msg = "Usuário ou senha estão inválidos!";
                }
            } else {
                $msg = "O usuário informado não está correto ou não está cadastrado!";
            }
        }
    }
}

// Caso exista alguma mensagem de erro, ela será exibida aqui
if ($msg) {
    echo "<p>$msg</p>";
}
?>
