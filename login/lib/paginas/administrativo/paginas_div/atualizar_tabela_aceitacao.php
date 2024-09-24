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

    // Verifique se a variável 'status' foi enviada via POST
    if (isset($_POST['situacao'])) {
        $situacao = $_POST['situacao'];

        // Construa a consulta SQL com base no valor do botão de rádio
        $sql = "SELECT * FROM int_associar";
        $dataAtual = date('Y-m-d');
        $horaAtual = date('H:i:s');

        if ($situacao == 'todos'){
            $sql .= " WHERE em_votacao = 'SIM'";
            $sql .= " ORDER BY inicio_votacao ASC";

            // Execute a consulta SQL
            $result = $mysqli->query($sql);

            // Construa a tabela HTML com os dados
            if ($result->num_rows > 0) {
                // Inicializa o contador
                $totalCarregados = 0;

                echo "<table border='1'>";
                echo "<tr>
                <th>Inscrito</th>
                <th>Foto</th>
                <th>Apelido</th>
                <th>Nome</th>
                <th>Idade</th>
                <th>Data De Inicio</th>
                <th>Hora De Inicio</th>
                <th>Data De Final</th>
                <th>Hora De Final</th>";
                echo "</tr>";

                while ($row = $result->fetch_assoc()) {
                    //$sim = 'SIM';
                    //if($sim == $row['em_votacao']){
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

                        echo "<tr>
                        <td>" . $row["data"] . "</td>
                        <td><img src='../../usuarios/" . $row["foto"] . "' width='50'></td>
                        <td>" . $row["apelido"] . "</td>
                        <td>" . $row["nome_completo"] . "</td>
                        <td>" . $idade . "</td>
                        <td>" . $data_ini_Formatada . "</td>
                        <td>" . $hora_inicio_formatada . "</td>
                        <td>" . $data_final_Formatada . "</td>
                        <td>" . $hora_final_formatada . "</td>";
                        
                        echo "</tr>";
                        // Incrementa o contador
                        $totalCarregados++;
                    //}
                }
                echo "</table>";
                // Exibe o total de registros carregados
                echo "<p>Total em Votação: " . $totalCarregados . "</p>";
            } else {
                echo "Nenhum inscrito em votação.";
            }
        } 
        if ($situacao == 'votacao') {
            $sql = "SELECT * FROM int_associar WHERE em_votacao = 'SIM' AND inicio_votacao <= CURDATE() AND fim_votacao >= CURDATE() AND inicio_hora <= CURTIME() AND fim_hora >= CURTIME() ORDER BY inicio_votacao ASC";
            $result = $mysqli->query($sql);
        
            if ($result->num_rows > 0) {
                // Inicializa o contador
                $totalCarregados = 0;
        
                echo "<table border='1'>";
                echo "<tr>
                    <th>Inscrito</th>
                    <th>Foto</th>
                    <th>Apelido</th>
                    <th>Nome</th>
                    <th>Idade</th>
                    <th>Data De Inicio</th>
                    <th>Hora De Inicio</th>
                    <th>Data De Final</th>
                    <th>Hora De Final</th>";
                echo "</tr>";
        
                while ($row = $result->fetch_assoc()) {
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
        
                    echo "<tr>
                        <td>" . $row["data"] . "</td>
                        <td><img src='../../usuarios/" . $row["foto"] . "' width='50'></td>
                        <td>" . $row["apelido"] . "</td>
                        <td>" . $row["nome_completo"] . "</td>
                        <td>" . $idade . "</td>
                        <td>" . $data_ini_Formatada . "</td>
                        <td>" . $hora_inicio_formatada . "</td>
                        <td>" . $data_final_Formatada . "</td>
                        <td>" . $hora_final_formatada . "</td>";
                        
                        echo "</tr>";
                        // Incrementa o contador
                        $totalCarregados++;
                    //}
                }
                echo "</table>";
                // Exibe o total de registros carregados
                echo "<p>Total em Votação: " . $totalCarregados . "</p>";
            } else {
                echo "Nenhum inscrito em votação.";
            }
        } 
        if ($situacao == 'encerrados') {
            $sql = "SELECT * FROM int_associar WHERE em_votacao = 'SIM' AND (fim_votacao < CURDATE() OR (fim_votacao = CURDATE() AND fim_hora < CURTIME())) ORDER BY inicio_votacao ASC";
            $result = $mysqli->query($sql);
        
            if ($result->num_rows > 0) {
                // Inicializa o contador
                $totalCarregados = 0;
        
                echo "<table border='1'>";
                echo "<tr>
                    <th>Inscrito</th>
                    <th>Foto</th>
                    <th>Apelido</th>
                    <th>Nome</th>
                    <th>Idade</th>
                    <th>Data De Inicio</th>
                    <th>Hora De Inicio</th>
                    <th>Data De Final</th>
                    <th>Hora De Final</th>
                    <th>Voto SIM</th>
                    <th>Voto NÃO</th>
                    <th>Resultado</th>
                    <th>Prosseguir</th>";
                echo "</tr>";
        
                while ($row = $result->fetch_assoc()) {
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
                    
                    $voto_sim = $row['voto_sim'];
                    $voto_nao = $row['voto_nao'];
                    if($voto_sim > $voto_nao){
                        $resultado ='APROVADO';
                        $link = 'adicionar.php';
                        $ler = 'Tornar Sócio';
                    }else{
                        $resultado ='REPROVADO';
                        $link = 'excluir.php';
                        $ler = 'Desativar inscrito';
                    }
                    
                    echo "<tr>
                        <td>" . $row["data"] . "</td>
                        <td><img src='../../usuarios/" . $row["foto"] . "' width='50'></td>
                        <td>" . $row["apelido"] . "</td>
                        <td style='text-align: left; padding-left: 5px;'>" . $row["nome_completo"] . "</td>
                        <td>" . $idade . "</td>
                        <td>" . $data_ini_Formatada . "</td>
                        <td>" . $hora_inicio_formatada . "</td>
                        <td>" . $data_final_Formatada . "</td>
                        <td>" . $hora_final_formatada . "</td>
                        <td>" . $voto_sim . "</td>
                        <td>" . $voto_nao. "</td>
                        <td>" . $resultado . "</td>
                        <td><a href='$link?id_sessao=<?php echo $id; ?>&id_socio=" . $row["id"] ."'>$ler</a></td>";
                        
                    echo "</tr>";
                    // Incrementa o contador
                    $totalCarregados++;
                }
                echo "</table>";
                // Exibe o total de registros carregados
                echo "<p>Total em Votação: " . $totalCarregados . "</p>";
            } else {
                echo "Nenhum votação encerrada.";
            }
        }
        // Fecha a conexão
        $mysqli->close();
    }
    
?>
