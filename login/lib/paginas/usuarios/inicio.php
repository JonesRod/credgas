<?php
    include('../../conexao.php');

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
    <style>
        body {
            display: flex;
            justify-content: center; /* Centraliza o conteúdo horizontalmente */
            /*align-items: center; /* Centraliza o conteúdo verticalmente */
            /*height: 100vh; /* Isso garante que o corpo ocupa 100% da altura da tela */
            margin: 0; /* Remove a margem padrão do corpo */
            background-color: #fff;
        }

        .container {
            display: flex;
            justify-content: center; /* Centraliza o conteúdo horizontalmente */
            text-align: center;
            width: 80%;
            /*height: auto; /* Isso define a altura da div como 100% da altura da tela (viewport height) */
            /*overflow: hidden; /* Isto esconde as barras de rolagem caso a página dentro da div seja maior do que a própria div */
        }

        .aniversariantes, .conteudo {
            /*box-sizing: border-box; /* Adiciona a borda e o preenchimento ao tamanho total do elemento */
            /*width: 60%;*/
            /*max-width: 80%;*/
            margin: 5px;
        /*background-clip: content-box; /* Faz com que o fundo termine no conteúdo, não na borda */
        }

        .aniversariantes {
            width: 60%;
            max-width: 60%;
            background-color: #fff;
            padding: 10px; /* Adiciona um preenchimento para o conteúdo */
            height: auto;
        }

        .conteudo {
            width: 50%;
            max-width: 50%;
            background-color: #fff;
            padding: 10px; /* Adiciona um preenchimento para o conteúdo */
        }

        table {
            margin: 0 auto; /* Centraliza a tabela dentro da div */
            width: 100%;
            max-width: 100%;
            border-collapse: collapse;
        }

        table, th, td {
            border: 1px solid black;
        }

        img {
            border-radius: 50%;
            width: 40px;
            height: 40px;
        }
    </style>

    <title>Lista de Sócios</title>
    <script>
        $(document).ready(function() {
            // Função para atualizar a tabela com base na seleção do botão de rádio
            function atualizarTabela(status) {
                $.ajax({
                    type: 'POST',
                    url: 'aptos.php', // Nome do arquivo PHP que buscará os dados
                    data: { status: status },
                    success: function(response) {
                        $('#tabela-socios').html(response); // Atualiza a tabela com os novos dados
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
        });
    </script>
</head>
<body>
    <div class="container">
        <div class="aniversariantes">
            <!-- Conteúdo da div direita (outras atividades) -->
            <div class="aniver">
                <h3>Aniversariantes do mês de 
                    <?php
                        $meses = [
                            1 => 'Janeiro',
                            2 => 'Fevereiro',
                            3 => 'Março',
                            4 => 'Abril',
                            5 => 'Maio',
                            6 => 'Junho',
                            7 => 'Julho',
                            8 => 'Agosto',
                            9 => 'Setembro',
                            10 => 'Outubro',
                            11 => 'Novembro',
                            12 => 'Dezembro'
                        ];

                        $mes_atual = date('n');
                        $nome_mes = $meses[$mes_atual];
                        echo $nome_mes;
                    ?>
                </h3>
                <?php
                    // Agora, vamos buscar os aniversariantes do mês
                    $result = $mysqli->query("SELECT * FROM socios WHERE MONTH(nascimento) = $mes_atual ORDER BY DAY(nascimento)") or die($mysqli->error);

                    // Verifica se há resultados
                    if ($result->num_rows > 0) {
                        echo "<p>Total de Aniversariantes: " . $result->num_rows . "</p>";
                        echo "<table border='1'>";
                        echo "<tr>
                                <th>Foto</th>
                                <th>Data Nasc.</th>
                                <th>Apelido</th>
                                <th>Nome</th>
                                <th>Idade</th>
                            </tr>";

                        while($row = $result->fetch_assoc()) {
                            $nascimento_formatado = date('d/m/Y', strtotime($row["nascimento"]));
                            $dataNascimento = new DateTime($row["nascimento"]);
                            $intervalo = $dataNascimento->diff(new DateTime());
                            $idade = $intervalo->y;

                            if($row["foto"] ==''){
                                $foto = '../arquivos_fixos/9734564-default-avatar-profile-icon-of-social-media-user-vetor.jpg';
                            }else{
                                $foto = '../usuarios/arquivos/' . $row["foto"];
                            }

                            echo "<tr>
                                <td><img src='" . $foto . "'></td>
                                <td>" . $nascimento_formatado . "</td>
                                <td>" . $row["apelido"] . "</td>
                                <td style='text-align: left; padding-left: 5px;'>" . $row["nome_completo"] . "</td>
                                <td>" . $idade . "</td>
                            </tr>";
                        }

                        echo "</table>";
                    } else {
                        echo "Nenhum Aniversariante!";
                    }
                ?>
            </div>
            <div class="aniver">
                <h3>Aniversáriantes de Hoje</h3>
                <?php
                    // Obtém a data atual
                    $dataAtual = date('m-d');
                    
                    // Executa a consulta para obter a lista de aniversariantes do dia
                    $sql = "SELECT * FROM socios WHERE DATE_FORMAT(nascimento, '%m-%d') = '$dataAtual'";
                    $result = $mysqli->query($sql);

                    // Verifica se há resultados
                    if ($result->num_rows > 0) {
                        echo "<p>Total de Aniversariantes: " . $result->num_rows . "</p>";

                        // Exibe os dados em uma tabela
                        echo "<table border='1'>";
                        echo "<tr>
                                <th>Foto</th>
                                <th>Data Nasc.</th>
                                <th>Apelido</th>
                                <th>Nome</th>
                                <th>Idade</th>
                            </tr>";

                        while($row = $result->fetch_assoc()) {
                            $nasc = $row['nascimento'];

                            $dataNascimento = new DateTime($nasc);
                            $dataAtual = new DateTime();
                            $intervalo = $dataNascimento->diff($dataAtual);
                            $idade = $intervalo->y;

                            $nascimento_formatado = date('d/m/Y', strtotime($row["nascimento"]));

                            if($row["foto"] ==''){
                                $foto = '../arquivos_fixos/9734564-default-avatar-profile-icon-of-social-media-user-vetor.jpg';
                            }else{
                                $foto = '../usuarios/arquivos/' . $row["foto"];
                            }

                            echo "<tr>
                                    <td><img src='" . $foto . "'></td>
                                    <td>" . $nascimento_formatado. "</td>
                                    <td>" . $row["apelido"] . "</td>
                                    <td style='text-align: left; padding-left: 5px;'>" . $row["nome_completo"] . "</td>
                                    <td>" . $idade . "</td>
                                </tr>";
                        }

                        echo "</table>";
                    } else {
                        echo "Nenhum Aniversariante!";
                    }
                    // Fecha a conexão
                    //$mysqli->close();
                ?>
            </div>
            <div class="aniver">
                <h3>Aniversáriantes de Amanhâ</h3>
                <?php
                    // Obtém a data de amanhã
                    $dataAmanha = date('m-d', strtotime('+1 day'));
                    
                    // Executa a consulta para obter a lista de aniversariantes do dia
                    $sql = "SELECT * FROM socios WHERE DATE_FORMAT(nascimento, '%m-%d') = '$dataAmanha'";
                    $result = $mysqli->query($sql);

                    // Verifica se há resultados
                    if ($result->num_rows > 0) {
                        echo "<p>Total de Aniversariantes: " . $result->num_rows . "</p>";

                        // Exibe os dados em uma tabela
                        echo "<table border='1'>";
                        echo "<tr>
                                <th>Foto</th>
                                <th>Data Nasc.</th>
                                <th>Apelido</th>
                                <th>Nome</th>
                                <th>Idade</th>
                            </tr>";

                        while($row = $result->fetch_assoc()) {
                            $nasc = $row['nascimento'];

                            $dataNascimento = new DateTime($nasc);
                            $dataAtual = new DateTime();
                            $intervalo = $dataNascimento->diff($dataAtual);
                            $idade = $intervalo->y;

                            $nascimento_formatado = date('d/m/Y', strtotime($row["nascimento"]));

                            if($row["foto"] ==''){
                                $foto = '../arquivos_fixos/9734564-default-avatar-profile-icon-of-social-media-user-vetor.jpg';
                            }else{
                                $foto = '../usuarios/arquivos/' . $row["foto"];
                            }

                            echo "<tr>
                                    <td><img src='" . $foto . "'></td>
                                    <td>" . $nascimento_formatado. "</td>
                                    <td>" . $row["apelido"] . "</td>
                                    <td style='text-align: left; padding-left: 5px;'>" . $row["nome_completo"] . "</td>
                                    <td>" . $idade . "</td>
                                </tr>";
                        }

                        echo "</table>";
                    } else {
                        echo "Nenhum Aniversariante!";
                    }
                    // Fecha a conexão
                    $mysqli->close();
                ?>
            </div>
            <div>
                <p>
                    Publicidades...
                </p>
            </div>
        </div>

        <div class="conteudo" id="iconteudo">
            <h3>Lista de Sócios Aptos para treinar</h3>
            <p>
                <input type="hidden" name="status" id="iativo" value="ATIVO">
            </p>
            <div id="tabela-socios"></div>
            <p>
                Os demais que não estiver nessa lista, não estão aptos á participarem das atividades. Procure o tesoureiro para mais informações!
            </p>
        </div>
    </div>
</body>
</html>
