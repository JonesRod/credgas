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

    $sql_dados = $mysqli->query("SELECT * FROM config_admin WHERE id = '1'") or die($mysqli->$error);
    $dados = $sql_dados->fetch_assoc();

    $dia = $dados['dia_fecha_mes'];
    
    if($dia <= 0){
        $dia = 10;
    }else if($dia > 28){
        $dia = 1;
    }


    $id = $_SESSION['usuario'];
    $sql_query = $mysqli->query("SELECT * FROM socios WHERE id = '$id'") or die($mysqli->$error);
    $usuario = $sql_query->fetch_assoc();
    $usuario_admin = $usuario['apelido'];

    $sql_ultimo_mes = $mysqli->query("SELECT id, mensalidade_mes, mensalidade_ano FROM mensalidades_geradas ORDER BY id DESC LIMIT 1") or die($mysqli->error);
    $ultimo_mes = $sql_ultimo_mes->fetch_assoc();
    
    if ($ultimo_mes) {
        $ultimo_id = $ultimo_mes['id'];
        $ultima_mensalidade = $ultimo_mes['mensalidade_mes'];
        //$ultima_mensalidade = $ultima_mensalidade + 1;
        $ultimo_ano = $ultimo_mes['mensalidade_ano'];
        //echo "O ID da última entrada é: $ultimo_id";
    } else {
        //echo "Não foi possível encontrar a última entrada.";
        $mes_atual = date('n');
        $ano_atual = date('Y');
        //echo $mes_atual;
        //date('n', strtotime('+1 month'))
        $ultima_mensalidade = $mes_atual - 1;
        $ultimo_ano = $ano_atual;
        if($ultima_mensalidade < 1){
            $ultima_mensalidade = 12;
            $ultimo_ano = $ano_atual - 1;
        }
        
        //echo $ultima_mensalidade;
    }

    $mysqli->close();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script>
        function validarQuantidadeMeses() {
            var qtmes = document.getElementById("iqtmes").value;
            if (qtmes < 1 || qtmes > 6) {
                alert("A quantidade de meses deve estar entre 1 e 6.");
                return false; // Impede o envio do formulário
            }
            return true; // Permite o envio do formulário
        }
    </script>
    <style>
        body {
            font-family: Arial, sans-serif;
            /*margin: 0;*/
            background-color: #f8f8f8;
            align-items: center;
            margin-bottom: 10px;
            text-align: center;
        }
        h2{
            margin-top: 30px;
        }
        form{
            margin-top: 30px;
        }
        input{
            text-align: center;
            width: 30px;
        }
    </style>
    <title>Gerador de Mensalidades</title>
</head>
<body>
    <h2>Gerador de Mensalidades</h2>
    <form action="gerar.php" method="POST" onsubmit="return validarQuantidadeMeses()">
        <p>
            <label for="iultmes">Ultima mensalidade gerada foi do mês: </label><br>
            <input readonly id="iultmes" name="ultmes" type="text" value="<?php echo $ultima_mensalidade; ?>">
        </p>
        <p>
            <label for="iultano">Ultimo Ano: </label><br>
            <input readonly id="iultano" name="ultano" type="text" value="<?php echo $ultimo_ano; ?>"><br>
        </p>
        <p>
            <label for="iqtmes">Quantidade de meses que você pretende gerar: </label><br>
            <input required id="iqtmes" name="qtmes" type="number" value="1"><br>
        </p>
        <p>
            <label for="ivalormes">Valor da Mensalidade: R$</label><br>
            <input readonly type="text" id="ivalormes" name="valormes" value="<?php echo $dados['valor_mensalidades']; ?>"><br>
        </p>
        <p>
            <label for="idiavenc">Dia de vencimento: </label><br>
            <input readonly type="text" id="idiavenc" name="diavenc" value="<?php echo $dia; ?>"><br>
        </p>
        <p> 
            <label for="idesc">Desconto: R$</label><br>
            <input readonly type="text" id="idesc" name="desc" value="<?php echo $dados['desconto_mensalidades']; ?>"><br>
        </p>
        <p>
            <label for="imulta">Multa: R$</label><br>
            <input readonly type="text" id="imulta" name="multa" value="<?php echo $dados['multa']; ?>"><br>            
        </p>
        <button type="submit">Gerar Mensalidades</button>
    </form>
</body>
</html>