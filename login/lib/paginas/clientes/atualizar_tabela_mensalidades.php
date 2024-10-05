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

    // Inclua aqui a conexão com o banco de dados
    //include 'conexao.php';

    if (isset($_POST['situacao'])) {
        $situacao = $_POST['situacao'];
        //$nome_socio = $_POST['nome_socio'];

        $sql = "SELECT * FROM mensalidades WHERE 1=1";

        if ($situacao == 'ATRASADOS') {
            $sql .= " AND data_vencimento < CURDATE() AND id_socio = $id";
        } elseif ($situacao == 'EM_DIA') {
            $sql .= " AND (id_socio = $id AND data_vencimento >= CURDATE() AND mensalidade_mes <= MONTH(CURDATE()) AND mensalidade_ano <= YEAR(CURDATE()))";
        }else{
            $sql .= " AND id_socio = '$id'";
        }

        // Adicionando a cláusula ORDER BY
        $sql .= " ORDER BY data_vencimento ASC, nome_completo ASC";

       // $sql .= " ";

        $result = $mysqli->query($sql);

        if ($result) {
            echo "<p>Total de Mensalidades: " . $result->num_rows . "</p>";
            echo "<table border='1'>";
            echo "<tr>
                <th>Apelido</th>
                <th>Nome</th>
                <th>Mensalidade</th>
                <th>Valor</th>
                <th>Desconto</th>
                <th>Multa</th>
                <th>Vencimento</th>
                <th>Valor Recebido</th>
                <th>Valor á Receber</th>
                <th>Pagar</th>
            </tr>";

            $valor_total_a_receber = 0;
            while ($row = $result->fetch_assoc()) {
                
                    $valor_mensalidade = $row["valor_mensalidade"];
                    $desconto_mensalidade = ($row["data_vencimento"] >= date('Y-m-d')) ? $row["desconto_mensalidade"] : 0;
                    $multa_mensalidade = ($row["data_vencimento"] < date('Y-m-d')) ? $row["multa_mensalidade"] : 0;
            
                    $data_vencimento_formatada = date('d/m/Y', strtotime($row["data_vencimento"]));
                    $valor_recebido = $row["valor_recebido"];
                    $valor_a_receber = $valor_mensalidade - $valor_recebido - $desconto_mensalidade + $multa_mensalidade;
                    $valor_total_a_receber += $valor_a_receber;
                //if($id == $row["id"]){
                    echo "<tr>
                        <td>" . $row["apelido"] . "</td>
                        <td style='text-align: left; padding-left: 5px;'>" . $row["nome_completo"] . "</td>
                        <td>" . $row["mensalidade_mes"] . "/" . $row["mensalidade_ano"] ."</td>
                        <td>" . $valor_mensalidade . ",00"."</td>
                        <td>" . $desconto_mensalidade . ",00"."</td>
                        <td>" . $multa_mensalidade . ",00"."</td>
                        <td>" . $data_vencimento_formatada . "</td>
                        <td>" . $valor_recebido . ",00"."</td>
                        <td>" . $valor_a_receber . ",00"."</td>
                        <td><a href='receber.php?id_sessao=" . $id . "&id_mensalidade=" . $row["id"] ."'target='_blank'>Pagar</a></td>
                    </tr>";
                //}
            }

            echo "</table>";
            echo "<p>Valor Total a Receber: $valor_total_a_receber,00</p>";
        } else {
            echo "Nenhum registrado";
        }
    } else {
        echo "Nenhum parâmetro de busca recebido.";
    }

    $mysqli->close();
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        a {
            text-decoration: none; /* Remove o sublinhado */
        }
        a {
            text-decoration: none; /* Remove o sublinhado */
            color: #000; /* Cor do texto padrão */
        }

        /* Estilo quando o mouse passa por cima */
        a:hover {
            color: #00F; /* Cor do texto quando o mouse passa por cima */
        }
    </style>
    <title></title>
</head>
<body>
    
</body>
</html>




