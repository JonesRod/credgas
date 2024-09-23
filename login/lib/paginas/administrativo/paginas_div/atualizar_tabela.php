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

        // Construa a consulta SQL com base no valor do botão de rádio
        $sql = "SELECT * FROM socios";

        if($status != 'TODOS') {
            $sql .= " WHERE status = '$status'";
        }

        // Agora, vamos buscar os sócios ordenados pelo nome em ordem alfabética
        $sql .= " ORDER BY nome_completo ASC";

        // Execute a consulta SQL
        $result = $mysqli->query($sql);

        // Construa a tabela HTML com os dados
        if ($result->num_rows > 0) {
            echo "<p>Total de Sócios: " . $result->num_rows . "</p>";
            echo "<table border='1'>";
            echo "<tr>
                <th>Cadastrado</th>
                <th>Foto</th>
                <th>Apelido</th>
                <th>Nome</th>
                <th>Idade</th>
                <th>E-mail</th>
                <th>Celular</th>
                <th>Detalhes</th>
            </tr>";

            while ($row = $result->fetch_assoc()) {
                //if($id != $row["id"]){
                    // Calcula a idade a partir da data de nascimento
                    $data = date('d/m/Y', strtotime($row["data"]));
                    
                    $dataNascimento = new DateTime($row["nascimento"]);
                    $hoje = new DateTime();
                    $idade = $dataNascimento->diff($hoje)->y;

                    if($row["foto"] ==''){
                        $foto = '../../arquivos_fixos/9734564-default-avatar-profile-icon-of-social-media-user-vetor.jpg';
                    }else{
                        $foto = '../../usuarios/arquivos/' . $row["foto"];
                    }

                    echo "<tr>
                        <td>" . $data . "</td>
                        <td><img src='" . $foto . "' width='50'></td>
                        <td>" . $row["apelido"] . "</td>
                        <td style='text-align: left; padding-left: 5px;'>" . $row["nome_completo"] . "</td>
                        <td>" . $idade . "</td>
                        <td>" . $row["email"] . "</td>
                        <td>" . $row["celular1"] . " / " . $row["celular2"] . "</td>

                        <td><a href='detalhes_socio.php?id_sessao=<?php echo $id; ?>&id_socio=" . $row["id"] ."'>Ver</a></td>

                    </tr>";
                //}
            }

        echo "</table>";
        } else {
            echo "Nenhum sócio registrado";
        }

    // Fecha a conexão
    $mysqli->close();
}
?>
