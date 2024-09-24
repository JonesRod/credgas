<?php
    include('../../../conexao.php');

    if(!isset($_SESSION)){
        session_start(); 

        if(isset($_SESSION['usuario'])){

            if (isset($_POST["tipoLogin"])) {
                // echo "1";
                $usuario = $_SESSION['usuario'];
                $valorSelecionado = $_POST["tipoLogin"];// Obter o valor do input radio
                $admin = $valorSelecionado;

                if($admin != 1){
                    $usuario = $_SESSION['usuario'];
                    $admin = $_SESSION['admin'];
                    //echo "1";
                    header("Location: ../../usuarios/usuario_home.php");      
                }else{
                    $usuario = $_SESSION['usuario'];
                    $admin = $_SESSION['admin'];
                    $_SESSION['usuario'];
                    $_SESSION['admin'];  
                }
            }  

        }else{
            //echo "5";
            session_unset();
            session_destroy(); 
            header("Location: ../../../../../index.php");  
        }
    
    }else{
        //echo "6";
        session_unset();
        session_destroy(); 
        header("Location: ../../../../../index.php");  
    }

    $id = $_SESSION['usuario'];
    $sql_query = $mysqli->query("SELECT * FROM socios WHERE id = '$id'") or die($mysqli->$error);
    $usuario = $sql_query->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <Style>
        body{
            text-align: center;
        }
        body #table{
            text-align: center;
        }
        img{
            border-radius: 50%; /* Define um raio de borda de 50% (forma circular) */
            width: 50px; /* Defina a largura desejada para a imagem */
            height: 50px; /* Defina a altura desejada para a imagem */
            /*object-fit: cover; /* Garante que a imagem cubra completamente a área */
        }
        table {
            margin-left: auto;
            margin-right: auto;
            width: 100%; /* A tabela ocupará 100% da largura do contêiner pai */
            max-width: 100%; /* A tabela não irá além de 100% da largura do contêiner pai */
            border-collapse: collapse; /* Combina as bordas das células */
        }
        a {
            text-decoration: none; /* Remove o sublinhado */
        }
        a {
            text-decoration: none; /* Remove o sublinhado */
            color: blue;   /* Cor do texto padrão */
        }

        /* Estilo quando o mouse passa por cima */
        a:hover {
            color: brown; /* Cor do texto quando o mouse passa por cima */
        }
    </Style>
    <script>
        $(document).ready(function() {
            // Função para atualizar a tabela com base na seleção do botão de rádio
            function atualizarTabela(status) {
                $.ajax({
                    type: 'POST',
                    url: 'atualizar_tabela_inscritos.php', // Nome do arquivo PHP que buscará os dados
                    data: { status: status },
                    success: function(response) {
                        $('#tabela-inscritos').html(response); // Atualiza a tabela com os novos dados
                    }
                });
            }

            // Define um manipulador de eventos para os botões de rádio
            $('input[name="status"]').change(function() {
                var statusSelecionado = $(this).val();
                atualizarTabela(statusSelecionado);
            });

            // Inicialmente, carrega a tabela com "TODOS" selecionados
            atualizarTabela('ATIVO');

            function atualizarTabela_aceitacao(situacao) {
                $.ajax({
                    type: 'POST',
                    url: 'atualizar_tabela_aceitacao.php', // Nome do arquivo PHP que buscará os dados
                    data: { situacao: situacao },
                    success: function(response) {
                        $('#tabela-em-votacao').html(response); // Atualiza a tabela com os novos dados
                    }
                });
            }

            // Define um manipulador de eventos para os botões de rádio
            $('input[name="situacao"]').change(function() {
                var situacaoSelecionado = $(this).val();
                atualizarTabela_aceitacao(situacaoSelecionado);
                //console.log(situacaoSelecionado);
            });

            // Inicialmente, carrega a tabela com aquele que estão para votação
            atualizarTabela_aceitacao('todos');

        });
    </script>

    <title>Lista de Inscritos</title>
</head>
<body>
    <h2>Para votação</h2>

    <!-- Adicionados os botões de rádio -->
    <label>
        <input type="radio" name="situacao" checked value="todos">TODOS
    </label>
    <label>
        <input type="radio" name="situacao" value="votacao">EM VOTAÇÃO
    </label>
    <label>
        <input type="radio" name="situacao" value="encerrados">ENCERRADOS
    </label>
    <p></p><br>
    <div id="tabela-em-votacao"></div>

    <h2>Lista de Inscritos</h2>

    <!-- Adicionados os botões de rádio -->
    <label>
        <input type="radio" name="status" checked value="ATIVO">ATIVOS
    </label>
    <label>
        <input type="radio" name="status" value="EXPIRADO">EXPIRADOS
    </label>
    <label>
        <input type="radio" name="status" value="TODOS">TODOS
    </label>
    <p></p><br>
    <div id="tabela-inscritos"></div>
</body>
</html>

