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

        $sql = "SELECT * FROM joias_receber WHERE 1=1";

        if ($situacao == 'ATRASADOS') {
            $sql .= " AND vencimento < CURDATE() AND id_socio = $id";
        } elseif ($situacao == 'EM_DIA') {
            $sql .= " AND (id_socio = $id AND vencimento >= CURDATE())";
        }else{
            $sql .= " AND id_socio = '$id'";
        }

        // Adicionando a cláusula ORDER BY
        $sql .= " ORDER BY vencimento ASC, nome_completo ASC";

       // $sql .= " ";

       $result = $mysqli->query($sql);

       if ($result) {
           echo "<p>Total de parcelas: " . $result->num_rows . "</p>";
           echo "<table border='1'>";
           echo "<tr>
               <th>Apelido</th>
               <th>Nome</th>
               <th>Valor da Jóia</th>
               <th>Entrada</th>
               <th>Parcela</th>
               <th>Valor da Parcela</th>
               <th>Desconto</th>
               <th>Vencimento</th>
               <th>Valor Recebido</th>
               <th>Valor á Receber</th>
               <th>Detalhes</th>
           </tr>";

           $valor_total_a_receber = 0;
           while ($row = $result->fetch_assoc()) {
               //if($id != $row["id"]){
                   $valor_joia = $row["valor"];
                   $entrada = $row["entrada"];
                   $restante = $row["restante"];
                   $valor_parcelas = $row["valor_parcelas"];
                   $desconto_parcela = $row["desconto_parcela"];
                   $recebido = $row["recebido"];
                   $a_receber = $valor_parcelas - $desconto_parcela - $recebido;
           
                   $vencimento_formatada = date('d/m/Y', strtotime($row["vencimento"]));
                   /*$valor_recebido = $row["valor_recebido"];
                   $valor_a_receber = $valor_mensalidade - $valor_recebido - $desconto_mensalidade + $multa_mensalidade;*/
                   $valor_total_a_receber += $a_receber;
           
                   echo "<tr>
                       <td>" . $row["apelido"] . "</td>
                       <td style='text-align: left; padding-left: 5px;'>" . $row["nome_completo"] . "</td>
                       <td>" . $row["valor"] ."</td>
                       <td>" . $row["entrada"] . ",00"."</td>
                       <td>" . $row["num_parcela"] . "/". $row["qt_parcelas"] ."</td>
                       <td>" . $row["valor_parcelas"] . ",00"."</td>
                       <td>" . $row["desconto_parcela"] . ",00"."</td>
                       <td>" . $vencimento_formatada ."</td>
                       <td>" . $row["recebido"] . ",00"."</td>
                       <td>" . $a_receber .",00". "</td>
                       <td><a href='ddd.php?id_sessao=" . $id . "&id_joias_receber=" . $row["id"] ."'target='_blank'>Pagar</a></td>
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
