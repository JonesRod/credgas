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
    if (isset($_POST['status'])) {
        $status = $_POST['status'];
        
        // Obtenha o valor de validade_insc do config_admin
        $result_config = $mysqli->query("SELECT validade_insc FROM config_admin WHERE Id = 1");
        $row_config = $result_config->fetch_assoc();
        $validade_insc = $row_config['validade_insc'];

        // Construa a consulta SQL com base no valor do botão de rádio
        $sql = "SELECT * FROM int_associar";

        if($status != 'TODOS') {
            $sql .= " WHERE status = '$status'";
        }

        // Agora, vamos buscar os sócios ordenados pela data em ordem alfabética
        $sql .= " ORDER BY data ASC";

        $validade = $mysqli->query("SELECT id, validade FROM int_associar");

        // Inicializa o contador
        $totalCarregados = 0;

        while ($row = $validade->fetch_assoc()) {
            $id_insc = $row['id'];
            $data_validade = $row['validade'];
        
            // Calcule a data de expiração
            $data_expiracao = date('Y-m-d');
        
            // Verifique se a data atual é posterior à data de expiração
            if (strtotime($data_expiracao) > strtotime($data_validade)) {
                // Atualize o status para "EXPIRADO" na tabela int_associar
                $mysqli->query("UPDATE int_associar SET status = 'EXPIRADO' WHERE id = '$id_insc'");
                //echo $id_insc;
            }else{
                // Atualize o status para "EXPIRADO" na tabela int_associar
                $mysqli->query("UPDATE int_associar SET status = 'ATIVO' WHERE id = '$id_insc'");
                //echo 'oii';
            }
        }
        
        // Execute a consulta SQL
        $result = $mysqli->query($sql);

        // Construa a tabela HTML com os dados
        if ($result->num_rows > 0) {
            echo "<table border='1'>";
            echo "<tr>
                <th>Inscrito</th>
                <th>Foto</th>
                <th>Apelido</th>
                <th>Nome</th>
                <th>Idade</th>
                <th>E-mail</th>
                <th>Celular</th>";

                if ($status == 'ATIVO') {
                    echo "<th>Asceitação</th>";
                }
                
                echo "</tr>";

            while ($row = $result->fetch_assoc()) {
                $sim = 'SIM';
                if($sim != $row['em_votacao']){

                    $dataNascimento = new DateTime($row["nascimento"]);
                    $hoje = new DateTime();
                    $idade = $dataNascimento->diff($hoje)->y;

                    echo "<tr>
                    <td>" . $row["data"] . "</td>
                    <td><img src='../../usuarios/" . $row["foto"] . "' width='50'></td>
                    <td>" . $row["apelido"] . "</td>
                    <td style='text-align: left; padding-left: 5px;'>" . $row["nome_completo"] . "</td>
                    <td>" . $idade . "</td>
                    <td>" . $row["email"] . "</td>
                    <td>" . $row["celular1"] . " / " . $row["celular2"] . "</td>";
    
                    // Verifica se o status é ATIVO antes de exibir a coluna "Aceitação"
                    if ($status == 'ATIVO') {
                        echo "<td><a href='em_votacao.php?id_sessao=<?php echo $id; ?>&id_socio=" . $row["id"] ."'>Por em Votação</a></td>";
                    }
                    
                    echo "</tr>";
                    // Incrementa o contador
                    $totalCarregados++;
                }
            }

        echo "</table>";
        echo "<p>Total de Inscritos: " . $totalCarregados . "</p>";
        } else {
            echo "Nenhum inscrito";
        }

    // Fecha a conexão
    $mysqli->close();
    }
?>
