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
                    header("Location: ../usuarios/usuario_home.php");      
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
            header("Location: ../../../../index.php");  
        }
    
    }else{
        //echo "6";
        session_unset();
        session_destroy(); 
        header("Location: ../../../../index.php");  
    }

    $id = $_SESSION['usuario'];
    $sql_query = $mysqli->query("SELECT * FROM socios WHERE id = '$id'") or die($mysqli->$error);
    $usuario = $sql_query->fetch_assoc();

    // Verifique se a variável 'status' foi enviada via POST
    if (isset($_POST)) {

        // Obtém a data e hora atual
        $dataHoraAtual = date('Y-m-d H:i:s');

        // Construa a consulta SQL com base no valor do botão de rádio
        $sql_int_associar = "SELECT * FROM int_associar WHERE em_votacao = 'SIM' 
                            AND ('{$dataHoraAtual}' >= CONCAT(inicio_votacao, ' ', inicio_hora))";

        // Agora, vamos buscar os sócios ordenados pelo nome em ordem alfabética
        $sql_int_associar .= " ORDER BY fim_votacao ASC";

        // Execute a consulta SQL
        $result = $mysqli->query($sql_int_associar);

        // Construa a tabela HTML com os dados
        if ($result->num_rows > 0) {
            echo "<h1>Vote</h1>";
            echo "<p>Total de Sócios: " . $result->num_rows . "</p>";
            echo "<form method='post'>";
            echo "<table border='1'>";
            echo "<tr>
                <th>Foto</th>
                <th>Apelido</th>
                <th>Nome</th>
                <th>Idade</th>
                <th>Incio da Votação</th>
                <th>Fim da Votação</th>
                <th>Vote</th>
                <th>Resultados</th>
            </tr>";

            $linha = 1; // Inicializamos a variável linha

            while ($row = $result->fetch_assoc()) {
                // Verifica se o usuário já votou
                $sql_voto = "SELECT * FROM em_votacao WHERE id_socio = '$id' AND id_inscrito = '{$row['id']}'";
                $result_voto = $mysqli->query($sql_voto);

                if ($result_voto->num_rows > 0) {
                    // O usuário já votou, esconder campo de votação
                    $campo_voto = "<p>VOCÊ JÁ VOTOU</p>";

                } else {
                    $campo_voto ="<a href='votar.php?id_sessao=" . $id . "&id_inscrito=" . $row["id"] ."'>VOTAR</a>";
                }
            
                // Calcula a idade a partir da data de nascimento
                $dataNascimento = new DateTime($row["nascimento"]);
                $hoje = new DateTime();
                $idade = $dataNascimento->diff($hoje)->y;
                $data_ini = new DateTime($row["inicio_votacao"]);
                $data_ini_Formatada = $data_ini->format('d/m/Y');
                $hora_inicio = new DateTime($row["inicio_hora"]);
                $hora_inicio_formatada = $hora_inicio->format('H:i');
                $data_final = new DateTime($row["fim_votacao"]);
                $data_final_Formatada = $data_final->format('d/m/Y');
                $hora_final = new DateTime($row["fim_hora"]);
                $hora_final_formatada = $hora_final->format('H:i');
                $voto_sim = ($row["voto_sim"]);
                $voto_nao = ($row["voto_nao"]);
            
                if($dataHoraAtual < $row["fim_votacao"] . " " . $row["fim_hora"]){
                    $resultado = "Ainda não finalizada";
                } else {
                    $campo_voto = "FINALIZADO";
                    $resultado = ($voto_sim > $voto_nao) ? 'APROVADO' : 'REPROVADO';
                }
                echo "<tr>
                    <td><img src='../usuarios/" . $row["foto"] . "' width='50'></td>
                    <td>" . $row["apelido"] . "</td>
                    <td style='text-align: left; padding-left: 5px;'>" . $row["nome_completo"] . "</td>
                    <td>" . $idade . "</td>
                    <td>" . $data_ini_Formatada . " " . $hora_inicio_formatada . "</td>
                    <td>" . $data_final_Formatada . " " . $hora_final_formatada . "</td>
                    <td>" . $campo_voto . "</td>
                    <td>" . $resultado . "</td>
                </tr>";
                $linha++; // Incrementamos a variável linha      
            } 
                 
            echo "</table>";
            echo "</form>";
        } else {
           // echo "Nenhum inscrito para votação!";
        }

        // Fecha a conexão
        $mysqli->close();
    }

?>
