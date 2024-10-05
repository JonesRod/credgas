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

    if (isset($_POST)) {
        //$situacao = $_POST['situacao'];
        //$nome_socio = $_POST['nome_socio'];

        $sql = "SELECT * FROM historico_joias_receber WHERE id_socio ='$id'";

        //if ($situacao == 'ATRASADOS') {
            //$sql .= " AND data_vencimento < CURDATE() AND id_socio = $id";
        //} elseif ($situacao == 'EM_DIA') {
            $sql .= " AND (status_pagamento = 'PAGO')";
        //}else{
            $sql .= " AND id_socio = '$id'";
        //}

        // Adicionando a cláusula ORDER BY
        $sql .= " ORDER BY data_recebeu DESC";

       // $sql .= " ";

        $result = $mysqli->query($sql);

        if ($result) {
            echo "<p>Total de Parcelas pagas: " . $result->num_rows . "</p>";
            echo "<table border='1'>";
            echo "<tr>
                <th>Apelido</th>
                <th>Nome</th>
                <th>Parcela</th>
                <th>Vencimento</th>
                <th>Data Recebida</th>
                <th>Status</th>
            </tr>";

            $valor_total_a_receber = 0;
            while ($row = $result->fetch_assoc()) {
                
                    //$valor_mensalidade = $row["valor_mensalidade"];
                    //$desconto_mensalidade = ($row["vencimento"] >= date('Y-m-d')) ? $row["desconto_parcela"] : 0;
                    //$multa_mensalidade = ($row["data_vencimento"] < date('Y-m-d')) ? $row["multa_mensalidade"] : 0;
            
                    $data_vencimento_formatada = date('d/m/Y', strtotime($row["vencimento"]));
                    $data_recebimento_formatada = date('d/m/Y', strtotime($row["data_recebeu"]));
                    /*$valor_recebido = $row["recebido"];
                    $valor_a_receber = $valor - $valor_recebido - $desconto_mensalidade + $multa_mensalidade;
                    $valor_total_a_receber += $valor_a_receber;*/
                //if($id == $row["id"]){
                    echo "<tr>
                        <td>" . $row["apelido"] . "</td>
                        <td style='text-align: left; padding-left: 5px;'>" . $row["nome_completo"] . "</td>
                        <td>" . $row["num_parcela"] . "/". $row["qt_parcelas"] ."</td>
                        <td>" . $data_vencimento_formatada . "</td>
                        <td>" . $data_recebimento_formatada . "</td>
                        <td>". 'Pago' ."</td>
                    </tr>";
                //}
            }

            echo "</table>";
           // echo "<p>Valor Total a Receber: $valor_total_a_receber,00</p>";
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




