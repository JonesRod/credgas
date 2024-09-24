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

    $id_parcela = $_GET['id_joias_receber'];
    $sql_parcela= $mysqli->query("SELECT * FROM joias_receber WHERE id = '$id_parcela'") or die($mysqli->error);

    if ($sql_parcela && $sql_parcela->num_rows > 0) {
        $parcela = $sql_parcela->fetch_assoc();

        // Obtém os valores e formata para string com duas casas decimais
        $valor_parcela = number_format($parcela['valor_parcelas'], 2, ',', '.');
        $desconto = number_format($parcela['desconto_parcela'], 2, ',', '.');
        $valor_recebido = number_format($parcela['recebido'], 2, ',', '.');
        //echo $valor_recebido;
        // Formata a data de vencimento no formato brasileiro
        $data_vencimento_formatada = date('d/m/Y', strtotime($parcela['vencimento']));
        $vencimento = date('Y-m-d', strtotime($parcela['vencimento']));

        // Define o valor a receber inicial como o valor da mensalidade
        $valor = $parcela['valor_parcelas'];

        $valor_receber = $valor - floatval($valor_recebido) - floatval($desconto);
    } else {
        echo "Parcela não encontrada. Por favor, atualize a pagian anterior e tente novamente. <br>";
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
            margin-left: 20%; 
            margin-right: 30%;
        }
    </style>
    <title>Tela de Recebimento</title>
</head>
<body>
    <h2>Receber</h2>
    <form action="gravar_recibo_joia.php" method="POST" onsubmit="return verificarRecebimento()">

        <!-- Campos ocultos para enviar o ID do usuário e o ID da mensalidade -->
        <input type="hidden" name="id_usuario" value="<?php echo $id; ?>">
        <input type="hidden" name="id_parcela" value="<?php echo $id_parcela; ?>">

        <label for="iapelido">Apelido: </label>
        <input readonly name="apelido" id="iapelido" type="text" value="<?php echo $parcela['apelido']; ?>"><br>

        <label for="inome">Nome Completo: </label>
        <input readonly name="nome" id="inome" type="text" value="<?php echo $parcela['nome_completo']; ?>"><br>

        <label for="iparcela">Parcela: </label>
        <input readonly name="parcela" id="iparcela" type="text" value="<?php echo $parcela['num_parcela']."/". $parcela['qt_parcelas']; ?>"><br>

        <label for="ivalor">Valor: </label>
        <input readonly name="valor" id="ivalor" type="text" value="<?php echo $valor_receber.",00"; ?>"><br>

        <label for="idesconto">Desconto: </label>
        <input name="desconto" id="idesconto" type="text" value="<?php echo "0,00"; ?>" onchange="calcularRestante()" oninput="validarNumero(this)"><br>

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
            var valorJoia = parseFloat(document.getElementById("ivalor").value.replace(',', '.'));
            var desconto = parseFloat(document.getElementById("idesconto").value.replace(',', '.'));
            var valor_recebido = parseFloat(document.getElementById("irecebido").value.replace(',', '.'));
            var valorReceber = parseFloat(document.getElementById("ireceber").value.replace(',', '.'));

            if (isNaN(valorJoia)) valorJoia = 0;
            if (isNaN(desconto)) desconto = 0;
            if (isNaN(valor_recebido)) valor_recebido = 0;
            if (isNaN(valorReceber)) valorReceber = 0;


            var restante = valorJoia - desconto - valorReceber;

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

            var confirmacao = confirm("Você realmente deseja receber essa parcela?");
            return confirmacao;
        }
    </script>
</body>
</html>