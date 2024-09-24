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


    $id_mensalidade = $_GET['id_mensalidade'];
    $sql_mensalidade = $mysqli->query("SELECT * FROM mensalidades WHERE id = '$id_mensalidade'") or die($mysqli->error);

    if ($sql_mensalidade && $sql_mensalidade->num_rows > 0) {
        $mensalidade = $sql_mensalidade->fetch_assoc();

        // Obtém os valores e formata para string com duas casas decimais
        $valor_mensalidade = number_format($mensalidade['valor_mensalidade'], 2, ',', '.');
        $desconto = number_format($mensalidade['desconto_mensalidade'], 2, ',', '.');
        $multa = number_format($mensalidade['multa_mensalidade'], 2, ',', '.');
        $valor_recebido = number_format($mensalidade['valor_recebido'], 2, ',', '.');
        //echo $valor_recebido;
        // Formata a data de vencimento no formato brasileiro
        $data_vencimento_formatada = date('d/m/Y', strtotime($mensalidade['data_vencimento']));
        $vencimento = date('Y-m-d', strtotime($mensalidade['data_vencimento']));

        // Define o valor a receber inicial como o valor da mensalidade
        $valor = $mensalidade['valor_mensalidade'];

        // Verifica se a data de vencimento é maior que a data atual
        if (strtotime($vencimento) > strtotime(date('Y-m-d'))) {
            // Se sim, aplica o desconto
            $valor_receber = $valor - floatval($valor_recebido) - $mensalidade['desconto_mensalidade'];
            //$desconto = "0,00";  // Define o desconto como zero
            $multa = "0,00";  // Define a multa como zero
        } else {
            // Se não, aplica a multa
            $valor_receber = $valor -$valor_recebido + $mensalidade['multa_mensalidade'];
            $desconto = "0,00";  // Define o desconto como zero
            //$multa = "0,00";  // Define a multa como zero
        }
    } else {
        echo "Mensalidade não encontrada. Por favor, atualize a pagian anterior e tente novamente. <br>";
        echo "<script>
            setTimeout(function() {
                window.close();
            }, 5000);
        </script>";
        die();
    }
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tela de Recebimento</title>
    <style>
        body h2{
            text-align: center;
        }
        body form{
            /*text-align: center;*/
            border: 1px solid black;
            /*width: 200px;*/
            position: absolute;
            top: 55%;
            left: 50%;
            transform: translate(-50%, -50%);
            padding: 15px;
        }
        form label{
            margin: 10px;
            /*padding: 50px;*/
        }
        form label,
        form input {
            margin-bottom: 10px;
            max-width: 100%;
        }

        form input {
            width: 100%;
            padding: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        input{
            margin: 5px;
        }
        a{
            margin-left: 100px; 
            margin-right: 40px;
        }
    </style>
</head>
<body>
    
    <form action="gravar_recibo.php" method="POST" onsubmit="return verificarRecebimento()">
        <h2>Receber</h2>
        <!-- Campos ocultos para enviar o ID do usuário e o ID da mensalidade -->
        <input type="hidden" name="id_usuario" value="<?php echo $id; ?>">
        <input type="hidden" name="id_mensalidade" value="<?php echo $id_mensalidade; ?>">

        <label for="iapelido">Apelido: </label>
        <input readonly name="apelido" id="iapelido" type="text" value="<?php echo $mensalidade['apelido']; ?>"><br>

        <label for="inome">Nome Completo: </label>
        <input readonly name="nome" id="inome" type="text" value="<?php echo $mensalidade['nome_completo']; ?>"><br>

        <label for="imensalidade">Mensalidade: </label>
        <input readonly name="mensalidade" id="imensalidade" type="text" value="<?php echo $mensalidade['mensalidade_mes']."/". $mensalidade['mensalidade_ano']; ?>"><br>

        <label for="ivalor">Valor: </label>
        <input readonly name="valor" id="ivalor" type="text" value="<?php echo $valor_mensalidade; ?>"><br>

        <label for="idesconto">Desconto: </label>
        <input readonly name="desconto" id="idesconto" type="text" value="<?php echo $desconto; ?>"><br>

        <label for="imulta">Multa: </label>
        <input readonly name="multa" id="imulta" type="text" value="<?php echo $multa; ?>"><br>

        <label for="ivence">Vencimento: </label>
        <input readonly name="vence" id="ivence" type="text" value="<?php echo $data_vencimento_formatada; ?>"><br>

        <label for="irecebido">Valor Recebido: </label>
        <input readonly name="recebido" id="irecebido" type="text" value="<?php echo $valor_recebido; ?>"><br>

        <label for="ireceber">Valor á Receber: </label>
        <input name="receber" id="ireceber" type="text" value="<?php echo $valor_receber.",00"; ?>" onchange="calcularRestante()" oninput="validarNumero(this)"><br>

        <label for="irestante">Restante: </label>
        <input readonly name="restante" id="irestante" type="text"value="<?php echo '0,00'; ?>"><br>  

        <a href="#" id="ivoltar">Voltar</a><button type="submit">Receber</button>      
    </form>

    <script>
        function validarNumero(input) {
            input.value = input.value.replace(/[^0-9,]/g, ''); // Remove todos os caracteres exceto números e vírgulas
        }

        document.getElementById('ivoltar').addEventListener('click', function(event) {
            event.preventDefault(); // Impede o comportamento padrão do link
            window.close(); // Fecha a janela ou guia atual
        });

        function calcularRestante() {
            var valorMensalidade = parseFloat(document.getElementById("ivalor").value.replace(',', '.'));
            var desconto = parseFloat(document.getElementById("idesconto").value.replace(',', '.'));
            var multa = parseFloat(document.getElementById("imulta").value.replace(',', '.'));
            var valor_recebido = parseFloat(document.getElementById("irecebido").value.replace(',', '.'));
            var valorReceber = parseFloat(document.getElementById("ireceber").value.replace(',', '.'));

            if (isNaN(valorMensalidade)) valorMensalidade = 0;
            if (isNaN(desconto)) desconto = 0;
            if (isNaN(multa)) multa = 0;
            if (isNaN(valor_recebido)) valor_recebido = 0;
            if (isNaN(valorReceber)) valorReceber = 0;


            var restante = valorMensalidade - valor_recebido - desconto + multa - valorReceber;

            if (restante < 0) {
                alert("O valor a receber não pode ser maior que o valor da mensalidade.");
                document.getElementById("ireceber").value = "";
                document.getElementById("irestante").value = "";
            } else {
                document.getElementById("irestante").value = restante.toFixed(2).replace('.', ',');
            }
        }

        function verificarRecebimento() {
            var valorReceber = document.getElementById("ireceber").value;

            if (valorReceber.trim() === '') {
                alert("O campo 'Valor a Receber' está vazio!");
                return false;
            }

            var confirmacao = confirm("Você realmente deseja receber essa mensalidade?");
            return confirmacao;
        }
    </script>
</body>
</html>